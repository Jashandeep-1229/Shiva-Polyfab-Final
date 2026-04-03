<?php

namespace App\Imports;

use App\Models\OldData;
use App\Models\Bopp;
use App\Models\Fabric;
use App\Models\Loop;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Illuminate\Support\Facades\File;

class OldDataImport implements ToModel, WithHeadingRow, WithEvents, WithChunkReading, WithBatchInserts
{
    private $drawings = [];
    private $currentRow = 1;

    public function batchSize(): int
    {
        return 10;
    }

    public function chunkSize(): int
    {
        return 10;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();
                foreach ($sheet->getDrawingCollection() as $drawing) {
                    $coordinates = $drawing->getCoordinates(); // e.g., A1, B5
                    $this->drawings[$coordinates] = $drawing;
                }
            },
        ];
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $this->currentRow++;
        
        $job_name = $row['name'] ?? null;
        if (!$job_name) return null;

        $order_date = $this->transformDate($row['order_date'] ?? null);
        $bopp_name = $row['bopp'] ?? null;
        $fabric_name = $row['fabric'] ?? null;

        // --- STEP 1: Aggressive Duplicate Check BEFORE any heavy processing ---
        // We check by name, date and string representations if possible, or we resolve IDs quickly
        $bopp_id = null;
        if ($bopp_name) {
            $bopp = Bopp::where('name', $bopp_name)->first();
            if ($bopp) $bopp_id = $bopp->id;
        }

        $fabric_id = null;
        if ($fabric_name) {
            $fabric = Fabric::where('name', $fabric_name)->first();
            if ($fabric) $fabric_id = $fabric->id;
        }

        if ($order_date) {
            $exists = OldData::where('order_date', $order_date)
                ->where('name_of_job', $job_name)
                // If IDs aren't found yet, it might still be a duplicate if the names match 
                // but let's be safe and only skip if we are certain.
                ->when($bopp_id, fn($q) => $q->where('bopp_id', $bopp_id))
                ->when($fabric_id, fn($q) => $q->where('fabric_id', $fabric_id))
                ->exists();

            if ($exists) {
                return null; // Skip everything, including image download
            }
        }

        // --- STEP 2: Heavy Processing (Only for NEW records) ---
        $imagePath = null;

        // Check for image in current row (Drawings)
        foreach ($this->drawings as $coordinate => $drawing) {
            preg_match('/[A-Z]+(\d+)/', $coordinate, $matches);
            if (isset($matches[1]) && $matches[1] == $this->currentRow) {
                $imagePath = $this->saveDrawing($drawing);
                break; 
            }
        }

        // Check for URL
        $rawImageUrl = $row['image'] ?? $row['image_url'] ?? null;
        if (!$imagePath && !empty($rawImageUrl)) {
            if (filter_var($rawImageUrl, FILTER_VALIDATE_URL)) {
                $imagePath = $this->downloadImage($rawImageUrl);
            } else {
                $imagePath = $rawImageUrl;
            }
        }

        $dispatch_date = $this->transformDate($row['dispatch_date'] ?? null);
        $loop_color_name = $row['loop_color'] ?? null;

        // Resolve or Create Masters
        if ($bopp_name && !$bopp_id) {
            $bopp = Bopp::firstOrCreate(
                ['name' => $bopp_name],
                ['status' => 1, 'user_id' => auth()->id()]
            );
            $bopp_id = $bopp->id;
        }

        if ($fabric_name && !$fabric_id) {
            $fabric = Fabric::firstOrCreate(
                ['name' => $fabric_name],
                ['status' => 1, 'user_id' => auth()->id()]
            );
            $fabric_id = $fabric->id;
        }

        $loop_id = null;
        if ($loop_color_name) {
            $loop = Loop::firstOrCreate(
                ['name' => $loop_color_name],
                ['status' => 1, 'user_id' => auth()->id()]
            );
            $loop_id = $loop->id;
        }

        return new OldData([
            'order_date'    => $order_date,
            'dispatch_date' => $dispatch_date,
            'name_of_job'   => $job_name,
            'bopp_id'       => $bopp_id,
            'fabric_id'     => $fabric_id,
            'loop_color_id' => $loop_id,
            'remarks'       => $row['notes'] ?? null,
            'pieces'        => $row['pieces'] ?? null,
            'send_for'      => $row['send_for'] ?? null,
            'image'         => $imagePath,
        ]);
    }

    private function saveDrawing($drawing)
    {
        try {
            if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                $imageResource = $drawing->getImageResource();
            } else {
                $path = $drawing->getPath();
                // Check if file exists to avoid errors
                if (!file_exists($path)) return null;
                $imageResource = @imagecreatefromstring(file_get_contents($path));
            }

            if (!$imageResource) return null;

            return $this->processAndSaveAsWebp($imageResource);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function processAndSaveAsWebp($imageResource)
    {
        try {
            // Reset time limit right before processing
            @set_time_limit(30);
            @ini_set('max_execution_time', '30');

            $filename = time() . '_' . uniqid() . '.webp';
            $path = public_path('order_imgs/' . $filename);

            if (!File::isDirectory(public_path('order_imgs'))) {
                File::makeDirectory(public_path('order_imgs'), 0777, true, true);
            }

            // Convert to webp with 80% quality
            if ($imageResource) {
                imagewebp($imageResource, $path, 80);
                imagedestroy($imageResource);
                return 'order_imgs/' . $filename;
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function downloadImage($url)
    {
        try {
            @set_time_limit(30); 
            @ini_set('max_execution_time', '30');

            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n",
                    'timeout' => 10
                ]
            ]);
            
            $contents = @file_get_contents($url, false, $context);
            if ($contents === false) return null;

            $imageResource = @imagecreatefromstring($contents);
            if (!$imageResource) return null;

            return $this->processAndSaveAsWebp($imageResource);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Transform date from Excel format to Y-m-d.
     */
    private function transformDate($value)
    {
        if (!$value) return null;
        
        try {
            if (is_numeric($value)) {
                return Carbon::instance(Date::excelToDateTimeObject($value))->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
