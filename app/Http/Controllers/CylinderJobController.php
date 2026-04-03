<?php

namespace App\Http\Controllers;

use App\Models\CylinderJob;
use App\Models\CylinderAgent;
use Illuminate\Http\Request;

class CylinderJobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $type = $request->type;
        $cylinder_agents = CylinderAgent::where('status',1)->get();
        return view('admin.cylinder_job.index', compact('type','cylinder_agents'));
    }

    public function datatable(Request $request)
    {
       $number = $request->value ?? 50;
       $query = CylinderJob::query();
       $query = auth()->user()->applyDataRestriction($query, 'check_in_by', 'cylinder_management');
       if($request->search){
           $query->where('name_of_job','like','%'.$request->search.'%');
       }
       if($request->type == 'pending'){
        $query->where('check_out_date',null);
       }
       if($request->type == 'report'){
        $query->where('check_out_date','!=',null);
       }
       if($request->cylinder_agent){
        $query->where('cylinder_agent_id',$request->cylinder_agent);
       }
       if($request->from_date){
        $query->where($request->filter_by,'>=',$request->from_date);
       }
       if($request->to_date){
        $query->where($request->filter_by,'<=',$request->to_date);
       }
       if($request->status_filter){
           if($request->status_filter == 'early'){
               $query->whereRaw("CAST(SUBSTRING_INDEX(total_no_of_days, ' ', 1) AS SIGNED) < 7");
           } elseif($request->status_filter == 'ontime'){
               $query->whereRaw("CAST(SUBSTRING_INDEX(total_no_of_days, ' ', 1) AS SIGNED) BETWEEN 7 AND 10");
           } elseif($request->status_filter == 'late'){
               $query->whereRaw("CAST(SUBSTRING_INDEX(total_no_of_days, ' ', 1) AS SIGNED) > 10");
           }
       }
       $cylinder_job = $query->latest()->paginate($number);
       return view('admin.cylinder_job.datatable',compact('cylinder_job'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CylinderJob  $cylinderJob
     * @return void
     */
    public function show(CylinderJob $cylinderJob)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CylinderJob  $cylinderJob
     * @return void
     */
    public function edit(CylinderJob $cylinderJob)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CylinderJob  $cylinderJob
     * @return void
     */
    public function update(Request $request, CylinderJob $cylinderJob)
    {
        //
    }

    /**
     * Display a report of the agent.
     *
     * @return \Illuminate\View\View
     */
    public function agent_report()
    {
        $cylinder_agents = CylinderAgent::where('status', 1)->get();
        return view('admin.cylinder_job.agent_report', compact('cylinder_agents'));
    }

    public function agent_report_datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $query = CylinderJob::query();
        
        if($request->cylinder_agent){
            $query->where('cylinder_agent_id', $request->cylinder_agent);
        }
        
        // Only include completed jobs for average calculation
        $query->whereNotNull('check_out_date');
        
        if($request->from_date){
            $query->where('check_out_date', '>=', $request->from_date);
        }
        if($request->to_date){
            $query->where('check_out_date', '<=', $request->to_date);
        }

        $cylinder_jobs = $query->latest()->paginate($number);

        // Calculate Stats
        $stats_query = clone $query;
        $total_jobs = $stats_query->count();
        $total_days = 0;
        $all_jobs = $stats_query->get();
        
        foreach($all_jobs as $job) {
            $days = (int) filter_var($job->total_no_of_days, FILTER_SANITIZE_NUMBER_INT);
            $total_days += $days;
        }
        
        $average_days = $total_jobs > 0 ? round($total_days / $total_jobs, 2) : 0;

        return view('admin.cylinder_job.agent_report_datatable', compact('cylinder_jobs', 'total_jobs', 'average_days'));
    }

    public function import(Request $request)
    {
        if (auth()->user()->role_as != 'Admin') {
            return response()->json(['result' => -1, 'message' => 'Access Denied! Only Admin can import history.']);
        }
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\CylinderJobHistoryImport, $request->file('file'));
            return response()->json(['result' => 1, 'message' => 'Cylinder History Imported Successfully']);
        } catch (\Exception $e) {
            return response()->json(['result' => -1, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
