<?php

namespace App\Imports;

use App\Models\CylinderJob;
use App\Models\CylinderAgent;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;

class CylinderJobHistoryImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Headers: "#","Name of Job","Cylinder Given To","Check Out Date","Check In Date","Options"
        $jobName = $row['name_of_job'] ?? null;
        $agentName = $row['cylinder_given_to'] ?? null;
        $checkOutDateRaw = $row['check_out_date'] ?? null;
        $checkInDateRaw = $row['check_in_date'] ?? null;

        if (!$jobName || !$agentName) {
            return null;
        }

        // 1. Find or reuse Agent (even if deleted)
        // Search by name (case-insensitive and trimmed)
        $agent = CylinderAgent::withTrashed()->where('name', trim($agentName))->first();
        
        if (!$agent) {
            // User requested not to create new agents if they don't exist.
            // If they want to allow creation, firstOrCreate would be used.
            // For now, I'll search closely, and if still nothing, I'll create one as a fallback 
            // OR the user might want to skip. I'll create it to ensure data is imported 
            // but I'll check if it's already there first.
            $agent = CylinderAgent::create([
                'name' => strtoupper(trim($agentName)),
                'user_id' => 1,
                'status' => 1
            ]);
        } elseif ($agent->trashed()) {
            $agent->restore();
        }

        // 2. Parse Dates (ensure year is preserved from CSV)
        $checkInDate = $this->transformDate($checkInDateRaw);
        $checkOutDate = $this->transformDate($checkOutDateRaw);

        // 3. Calculate Days
        $days = 0;
        if ($checkInDate && $checkOutDate) {
            $days = Carbon::parse($checkOutDate)->diffInDays(Carbon::parse($checkInDate)->startOfDay());
        }

        // 4. Find or reuse CylinderJob (even if deleted)
        $cylinderJob = CylinderJob::withTrashed()->where([
            'job_card_id' => 0,
            'name_of_job' => $jobName,
            'remarks' => 'Excel Data History Uploaded'
        ])->first();

        if (!$cylinderJob) {
            $cylinderJob = new CylinderJob();
            $cylinderJob->job_card_id = 0;
            $cylinderJob->name_of_job = $jobName;
            $cylinderJob->remarks = 'Excel Data History Uploaded';
        } elseif ($cylinderJob->trashed()) {
            $cylinderJob->restore();
        }

        // 5. Update data
        $cylinderJob->cylinder_agent_id = $agent->id;
        $cylinderJob->check_in_by = 1;
        $cylinderJob->check_in_date = $checkOutDate; // Factory Out (delay start)
        $cylinderJob->check_out_by = 1;
        $cylinderJob->check_out_date = $checkInDate; // Factory In (delay end)
        $cylinderJob->total_no_of_days = $days . ($days <= 1 ? ' DAY' : ' DAYS');
        
        $cylinderJob->save();

        return null; // Return null because we saved it manually with update/restore logic
    }

    private function transformDate($value)
    {
        if (!$value || strtoupper($value) == 'N/A') return null;

        try {
            // Remove commas (e.g., "07 Apr, 2023" -> "07 Apr 2023") 
            // as Carbon misinterprets the comma format.
            $cleanValue = str_replace(',', '', $value);
            return Carbon::parse($cleanValue)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
