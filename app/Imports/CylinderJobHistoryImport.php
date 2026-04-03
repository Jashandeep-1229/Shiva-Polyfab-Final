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
        // Key mapping: name_of_job, cylinder_given_to, check_out_date, check_in_date
        
        $jobName = $row['name_of_job'] ?? null;
        $agentName = $row['cylinder_given_to'] ?? null;
        $checkOutDateRaw = $row['check_out_date'] ?? null;
        $checkInDateRaw = $row['check_in_date'] ?? null;

        // Condition: Check In Date null or N/A then ignore entry
        if (!$checkInDateRaw || strtoupper($checkInDateRaw) == 'N/A') {
            return null;
        }

        // Find or create agent
        $agent = CylinderAgent::firstOrCreate(
            ['name' => strtoupper($agentName)],
            ['user_id' => 1, 'status' => 1]
        );

        // Date transformation (CSV format: 20 Dec, 2025)
        $checkInDate = $this->transformDate($checkInDateRaw);
        $checkOutDate = $this->transformDate($checkOutDateRaw);

        // Calculate days
        $days = 0;
        if ($checkInDate && $checkOutDate) {
            $days = Carbon::parse($checkOutDate)->diffInDays(Carbon::parse($checkInDate)->startOfDay());
        }

        return new CylinderJob([
            'job_card_id' => 0,
            'cylinder_agent_id' => $agent->id,
            'name_of_job' => $jobName,
            'check_in_by' => 1,
            'check_in_date' => $checkOutDate, // Swapped as per user request
            'check_out_by' => 1,
            'check_out_date' => $checkInDate, // Swapped as per user request
            'total_no_of_days' => $days . ($days <= 1 ? ' DAY' : ' DAYS'),
            'remarks' => 'Excel Data History Uploaded',
        ]);
    }

    private function transformDate($value)
    {
        if (!$value || strtoupper($value) == 'N/A') return null;

        try {
            // Try "20 Dec, 2025" or similar
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
