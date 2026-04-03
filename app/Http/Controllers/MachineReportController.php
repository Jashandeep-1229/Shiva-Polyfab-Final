<?php

namespace App\Http\Controllers;

use App\Models\JobCardProcess;
use App\Models\Machine;
use App\Models\BlockageReason;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MachineReportController extends Controller
{
    public function index(Request $request)
    {
        $processes = [
            'Printing' => 'Printing',
            'Lamination' => 'Lamination',
            'Box' => 'Box',
            'Cutting' => 'Cutting'
        ];
        
        return view('admin.report.machine_wise.index', compact('processes'));
    }

    public function get_machines(Request $request)
    {
        $process = strtolower($request->process);
        // The machine types are stored as 'printing', 'lamination', 'box', 'cutting'
        $machines = Machine::where('type', $process)->get();
        
        $html = '<option value="">All Machines</option>';
        foreach($machines as $machine){
            $html .= '<option value="'.$machine->id.'">'.$machine->name.'</option>';
        }
        return response()->json($html);
    }

    public function report_data(Request $request)
    {
        $query = JobCardProcess::with(['user', 'machine', 'job_card']);
        
        // Only get completed processes (status 2 = Completed/Moved)
        $query->where('status', 2);

        if($request->process){
            // Mapping UI process names to database 'from' column
            $query->where('from', $request->process);
        }
        
        if($request->machine_id){
            $query->where('machine_id', $request->machine_id);
        }
        
        if($request->from_date){
            $query->whereDate('date', '>=', $request->from_date);
        }
        if($request->to_date){
            $query->whereDate('date', '<=', $request->to_date);
        }

        $report_data = $query->latest('date')->get();
        
        $totals = [
            'estimate_production' => $report_data->sum('estimate_production'),
            'actual_order' => $report_data->sum('actual_order'),
            'wastage' => $report_data->sum('wastage'),
            'blockage_time' => $report_data->sum('blockage_time'),
            'working_hours' => $report_data->map(function($item) {
                return (float)$item->working_hours;
            })->sum(),
        ];

        // Blockage stats
        $blockage_reason_ids = $report_data->whereNotNull('blockage_reason_id')->pluck('blockage_reason_id')->unique();
        $reasons = BlockageReason::whereIn('id', $blockage_reason_ids)->get()->keyBy('id');

        $blockage_stats = $report_data->whereNotNull('blockage_reason_id')
            ->groupBy('blockage_reason_id')
            ->map(function($items, $id) use ($reasons) {
                return [
                    'reason' => $reasons[$id]->name ?? 'Unknown',
                    'count' => $items->count()
                ];
            })->sortByDesc('count');

        return view('admin.report.machine_wise.report_table', compact('report_data', 'totals', 'blockage_stats'));
    }
}
