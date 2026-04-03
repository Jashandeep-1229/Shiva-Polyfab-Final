<?php

namespace App\Http\Controllers;

use App\Models\JobCard;
use App\Models\JobCardProcess;
use App\Models\PackingSlip;
use App\Models\PackingDetail;
use App\Models\CylinderJob;
use App\Models\Bopp;
use App\Models\Fabric;
use App\Models\CylinderAgent;

use App\Models\AgentCustomer;
use App\Models\Machine;
use App\Models\BlockageReason;
use App\Models\User;
use App\Models\CustomerLedger;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobCardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type = $request->type;
        $executives = User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive'])->where('status', 1)->get();
        return view('admin.job_card.index', compact('type', 'executives'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $query = JobCard::with(['fabric', 'bopp', 'cylinder_agent', 'customer_agent', 'sale_executive']);
        
        $category = $request->category ?? 'roto';
        $menu_key = ($request->type == 'Common' || $category == 'common') ? 'common_orders' : 'roto_orders';
        $column = $menu_key == 'roto_orders' ? ['user_id', 'sale_executive_id'] : 'user_id';
        $query = auth()->user()->applyDataRestriction($query, $column, $menu_key);

        if ($request->process_status) {
            if ($request->process_status == 'Completed') {
                $query->where('status', 'Completed')
                      ->where('job_card_no', 'NOT LIKE', '%-R%');
            } else {
                $query->where('status', '!=', 'Completed')
                      ->where('job_card_process', $request->process_status);
            }
        }

        if (in_array($request->type, ['all', 'pending', 'new', 'repeat'])) {
            $query->where('job_type', '!=', 'Common');
            if ($request->type == 'pending') {
                $query->where('status', '!=', 'Completed');
            }
        } elseif ($request->type == 'Common') {
            $query->where('job_type', 'Common')
                  ->whereDoesntHave('packing_slips');
        } elseif ($request->type == 'Completed') {
            $query->where('status', 'Completed');
            if ($request->category == 'roto') {
                $query->where('job_type', '!=', 'Common');
            } elseif ($request->category == 'common') {
                $query->where('job_type', 'Common')
                      ->where('job_card_no', 'NOT LIKE', '%-R%');
            }
        } elseif ($request->type) {
            $query->where('job_type', $request->type);
        }


        if($request->search){
            $search = $request->search;
            $query->where(function($q) use ($search){
                $q->where('name_of_job','like','%'.$search.'%')
                ->orWhere('job_type','like','%'.$search.'%')
                ->orWhere('job_card_process','like','%'.$search.'%')
                ->orWhereHas('fabric', function($fq) use ($search){
                    $fq->where('name','like','%'.$search.'%');
                })
                ->orWhereHas('bopp', function($bq) use ($search){
                    $bq->where('name','like','%'.$search.'%');
                })
                ->orWhereHas('cylinder_agent', function($caq) use ($search){
                    $caq->where('name','like','%'.$search.'%');
                });
            });
        }

        if ($request->from_date) {
            $query->whereDate('job_card_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('job_card_date', '<=', $request->to_date);
        }
        if ($request->executive_id) {
            $query->where('sale_executive_id', $request->executive_id);
        }
        if ($request->id) {
            $query->where('id', $request->id);
        }

        $job_card = $query->latest('id')->paginate($number);
        return view('admin.job_card.datatable',compact('job_card'));
    }
    public function edit_modal(Request $request,$id){
        $job_card = JobCard::find($id);
        $bopps = Bopp::where('status',1)->get();
        $fabrics = Fabric::where('status',1)->get();
        $cylinder_agent = CylinderAgent::where('status',1)->get();
        $loops = \App\Models\Loop::where('status',1)->get();
        $sale_executive = User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive'])->where('status', 1)->get();
        $customer_agent = [];
        if($job_card->customer_agent_id){
            $customer_agent = AgentCustomer::where('status',1)->where('role',$job_card->customer_agent->role)->get();
        }
        return view('admin.job_card.modal',compact('job_card','bopps','fabrics','cylinder_agent','loops','sale_executive','customer_agent'));
    }

    public function process(Request $request)
    {
        $process = $request->type;
        
        $permission_map = [
            'Cylinder Come' => 'process_cylinder_come',
            'Order List' => 'process_order_list',
            'Schedule For Printing' => 'process_printing',
            'Printed Bopp List' => 'process_bopp_list',
            'Schedule For Lamination' => 'process_lamination',
            'Laminated Rolls' => 'process_laminated_rolls',
            'Schedule For Box / Cutting' => 'process_box_cutting',
            'Ready Bags List' => 'process_ready_bags',
            'Packing Slip' => 'process_packing_slip',
            'Dispatch Material' => 'process_dispatch',
            'Account Pending' => 'account_pending'
        ];

        if (auth()->user()->role_as != 'Admin' && isset($permission_map[$process]) && !PermissionHelper::check($permission_map[$process])) {
            abort(403, 'Unauthorized access to this process step.');
        }

        $category = $request->category ?? 'all';
        return view('admin.job_card.process_index', compact('process', 'category'));
    }

    public function process_datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $query = JobCard::with(['fabric', 'bopp', 'cylinder_agent', 'customer_agent', 'sale_executive']);
        
        $permission_map = [
            'Cylinder Come' => 'process_cylinder_come',
            'Order List' => 'process_order_list',
            'Schedule For Printing' => 'process_printing',
            'Printed Bopp List' => 'process_bopp_list',
            'Schedule For Lamination' => 'process_lamination',
            'Laminated Rolls' => 'process_laminated_rolls',
            'Schedule For Box / Cutting' => 'process_box_cutting',
            'Ready Bags List' => 'process_ready_bags',
            'Packing Slip' => 'process_packing_slip',
            'Dispatch Material' => 'process_dispatch',
            'Account Pending' => 'account_pending'
        ];

        $category = $request->category ?? 'all';
        $menu_key = ($request->process == 'Account Pending') ? 'account_pending' : 'order_process';
        $column = $category == 'roto' ? ['user_id', 'sale_executive_id'] : 'user_id';
        $query = auth()->user()->applyDataRestriction($query, $column, $menu_key);
        
        if($request->category == 'common'){
            $query->where('job_type', 'Common');
        } elseif($request->category == 'roto'){
            $query->where('job_type', '!=', 'Common');
        } elseif($request->category == 'all'){
            // No job_type filter
        }

        if($request->process && $request->process != 'all'){
            $query->where('job_card_process', $request->process);
            
            if(($request->process == 'Schedule For Box / Cutting' || $request->process == 'Ready Bags List') && $request->sub_type && $request->sub_type != 'all'){
                $query->where('order_send_for', $request->sub_type);
            }
        }

        if($request->search){
            $search = $request->search;
            $query->where(function($q) use ($search){
                $q->where('name_of_job','like','%'.$search.'%')
                ->orWhere('job_type','like','%'.$search.'%')
                ->orWhere('job_card_process','like','%'.$search.'%')
                ->orWhereHas('fabric', function($fq) use ($search){
                    $fq->where('name','like','%'.$search.'%');
                })
                ->orWhereHas('bopp', function($bq) use ($search){
                    $bq->where('name','like','%'.$search.'%');
                })
                ->orWhereHas('cylinder_agent', function($caq) use ($search){
                    $caq->where('name','like','%'.$search.'%');
                });
            });
        }

        if ($request->from_date) {
            $query->whereDate('job_card_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('job_card_date', '<=', $request->to_date);
        }
        if ($request->executive_id) {
            $actor = \App\Models\User::find($request->executive_id);
            if ($actor) {
                // Get all IDs this executive manages for job cards
                $managed_ids = $actor->getPermittedUserIds('roto_orders');
                $query->whereIn('sale_executive_id', $managed_ids);
            } else {
                $query->where('sale_executive_id', $request->executive_id);
            }
        }

        if ($request->id) {
            $query->where('id', $request->id);
        }

        $is_common_view = ($request->category == 'common' || $request->type == 'Common');
        $process = $request->process;
        $job_card = $query->latest()->paginate($number);
        return view('admin.job_card.datatable', compact('job_card', 'is_common_view', 'process'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $menu_key = ($input['job_type'] ?? 'new') == 'Common' ? 'common_orders' : 'roto_orders';
        
        if (!empty($input['id'])) {
            // Update
            if (!PermissionHelper::check($menu_key, 'edit')) {
                return response()->json(['result' => -1, 'message' => 'Access Denied! You do not have permission to edit this order.']);
            }
        } else {
            // New Create
            if (!PermissionHelper::check($menu_key, 'add')) {
                return response()->json(['result' => -1, 'message' => 'Access Denied! You do not have permission to add new orders.']);
            }
        }

        $input['job_card_date'] = now();
        $input['user_id'] = auth()->user()->id;
        $input['is_editable'] = 1;
        $input['actual_pieces'] = $input['no_of_pieces'];
        $input['software_remarks'] = 'New job Card Created - '.$input['job_type'];
        if($input['job_type'] == 'new'){
                $input['job_card_process'] = 'Cylinder Come';
                $input['status'] = 'Pending';
        }
        else{
                $input['job_card_process'] = 'Order List';
                $input['status'] = 'Pending';
        }

        if (empty($input['id'])) {
            $now = now();
            $startYear = $now->month >= 4 ? $now->year : $now->year - 1;
            $endYear = str_pad(($startYear + 1) % 100, 2, '0', STR_PAD_LEFT);
            $startYearShort = str_pad($startYear % 100, 2, '0', STR_PAD_LEFT);
            $fy = $startYearShort . '-' . $endYear;

            $prefix = ($input['job_type'] == 'Common' ? "JC-C-" : "JC-") . $fy . '-';

            $latestJobCard = \App\Models\JobCard::withTrashed()
                ->where('job_card_no', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            if ($latestJobCard && $latestJobCard->job_card_no) {
                $lastNo = intval(substr($latestJobCard->job_card_no, strlen($prefix)));
                $nextNo = $lastNo + 1;
            } else {
                $nextNo = 1;
            }

            $input['job_card_no'] = $prefix . str_pad($nextNo, 2, '0', STR_PAD_LEFT);
        }

        if($request->hasFile('file_upload')){
            $file = $request->file('file_upload');
            $filename = 'job_card_'.time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/job_card'), $filename);
            $input['file_upload'] = $filename;
        } else if (!empty($input['old_data_img_path'])) {
            // Copy existing old data image if no new file is uploaded
            $oldPath = public_path($input['old_data_img_path']);
            if (file_exists($oldPath)) {
                $ext = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newFilename = 'job_card_copy_'.time() . '.' . $ext;
                copy($oldPath, public_path('uploads/job_card/' . $newFilename));
                $input['file_upload'] = $newFilename;
            }
        }
      
        $isUpdate = ($input['id'] > 0);
        $oldJobCard = null;
        if($isUpdate){
            $oldJobCard = JobCard::find($input['id']);
        }
        
        $jobCard = JobCard::updateOrCreate(['id' => $input['id']], $input);

        // If it was an update, track and log changes
        if($isUpdate && $oldJobCard){
            $changes = [];
            $trackFields = [
                'name_of_job' => 'Name of Job',
                'bopp_id' => 'Bopp (mm)',
                'fabric_id' => 'Fabric (mm)',
                'no_of_pieces' => 'No Of Pieces',
                'loop_color' => 'Loop Color',
                'order_send_for' => 'Send For',
                'dispatch_date' => 'Dispatch Date',
                'cylinder_given_id' => 'Cylinder Given To',
                'sale_executive_id' => 'Sale Executive',
                'customer_agent_id' => 'Customer/Agent',
            ];

            foreach($trackFields as $field => $label){
                $oldVal = $oldJobCard->$field;
                $newVal = $jobCard->$field;

                if($oldVal != $newVal){
                    if(str_contains($field, '_id')){
                        // Try to get names for IDs if possible
                        if($field == 'bopp_id'){
                            $oldVal = Bopp::find($oldVal)->name ?? $oldVal;
                            $newVal = Bopp::find($newVal)->name ?? $newVal;
                        } elseif($field == 'fabric_id'){
                            $oldVal = Fabric::find($oldVal)->name ?? $oldVal;
                            $newVal = Fabric::find($newVal)->name ?? $newVal;
                        } elseif($field == 'cylinder_given_id'){
                            $oldVal = CylinderAgent::find($oldVal)->name ?? $oldVal;
                            $newVal = CylinderAgent::find($newVal)->name ?? $newVal;
                        } elseif($field == 'sale_executive_id' || $field == 'user_id'){
                            $oldVal = User::find($oldVal)->name ?? $oldVal;
                            $newVal = User::find($newVal)->name ?? $newVal;
                        } elseif($field == 'customer_agent_id'){
                            $oldVal = AgentCustomer::find($oldVal)->name ?? $oldVal;
                            $newVal = AgentCustomer::find($newVal)->name ?? $newVal;
                        }
                    }
                    $changes[] = "$label: '$oldVal' -> '$newVal'";
                }
            }

            if(count($changes) > 0){
                $log = new JobCardProcess();
                $log->job_card_id = $jobCard->id;
                $log->process_name = 'Job Card Edited';
                $log->from_where = ucfirst($jobCard->job_type).' Order';
                $log->user_id = auth()->id();
                $log->date = now();
                $log->process_start_date = now();
                $log->process_end_date = now();
                $log->status = 2; // History entry
                $log->process_remarks = 'Changes: ' . implode(', ', $changes);
                $log->save();
            }
        }

       // Automatically update the default Sale Executive for the Customer/Agent if changed in the Roto Order form
       if($jobCard && !empty($input['sale_executive_id']) && !empty($input['customer_agent_id'])){
           AgentCustomer::where('id', $input['customer_agent_id'])->update([
               'sale_executive_id' => $input['sale_executive_id']
           ]);
       }

       if($input['job_type'] == 'new'){
        
        $cylinderJob = CylinderJob::firstOrNew(['job_card_id' => $jobCard->id]);
        $cylinderJob->cylinder_agent_id = $input['cylinder_given_id'];
        $cylinderJob->name_of_job = $input['name_of_job'];
        if (!$cylinderJob->exists) {
            $cylinderJob->check_in_by = auth()->user()->id;
            $cylinderJob->check_in_date = now();
            $cylinderJob->remarks = '';
        }
        $cylinderJob->save();
       }
       else{
        CylinderJob::where('job_card_id', $jobCard->id)->delete();
       }
       if($jobCard){
        $job_card_process = JobCardProcess::where('job_card_id', $jobCard->id)->where('process_remarks','New Job Card Created')->first();
        if(!$job_card_process){
            $job_card_process = new JobCardProcess();
            $job_card_process->from_where = ucfirst($jobCard->job_type).' Order';
            $job_card_process->user_id = auth()->user()->id;
            $job_card_process->job_card_id = $jobCard->id;
            $job_card_process->date = now();
            $job_card_process->process_start_date = now();
            $job_card_process->process_remarks = 'New Job Card Created';
            $job_card_process->status = 1;
        }
        if($input['job_type'] == 'new'){
            $job_card_process->process_name = 'Cylinder Come';
        }
        else{
            $job_card_process->process_name = 'Order List';
        }
       
       
        $job_card_process->save();
       }
       $data = [
        'result' => 1,
        'message' => 'Job Card Created Successfully',
        'from' => 'Job Card'
       ];
       return response()->json($data);
    }
    public function next_process(Request $request,$id){
        $job_card = JobCard::find($id);
        
        $permission_map = [
            'Cylinder Come' => 'process_cylinder_come',
            'Order List' => 'process_order_list',
            'Schedule For Printing' => 'process_printing',
            'Printed Bopp List' => 'process_bopp_list',
            'Schedule For Lamination' => 'process_lamination',
            'Laminated Rolls' => 'process_laminated_rolls',
            'Schedule For Box / Cutting' => 'process_box_cutting',
            'Ready Bags List' => 'process_ready_bags',
            'Packing Slip' => 'process_packing_slip',
            'Dispatch Material' => 'process_dispatch',
            'Account Pending' => 'account_pending'
        ];

        $stage_key = $permission_map[$request->process] ?? 'order_process';
        if (!PermissionHelper::check($stage_key, 'next_process')) {
            if ($request->ajax()) {
                return '<div class="alert alert-danger p-2">Access Denied! You do not have permission to shift this stage.</div>';
            }
            return back()->with('danger', 'Access Denied! You do not have permission to shift this stage.');
        }

        // 🔒 HOLD CHECK: Block next process if job card is on hold
        if ($job_card->is_hold == 1) {
            $holdReason = $job_card->hold_reason_id ? (\App\Models\BlockageReason::find($job_card->hold_reason_id)->name ?? 'N/A') : 'N/A';
            $msg = '⛔ This order is currently ON HOLD. Reason: ' . $holdReason . '. Please unhold it before moving to the next process.';
            if ($request->ajax()) {
                return '<div class="alert alert-danger p-2"><i class="fa fa-lock me-1"></i> ' . $msg . '</div>';
            }
            return response()->json(['result' => 0, 'message' => $msg]);
        }

        $next_process = '';
        
        
        if($request->process == 'Cylinder Come'){
            $next_process = 'Order List';
        }
        if($request->process == 'Order List'){
            $next_process = 'Schedule For Printing';
        }
        if($request->process == 'Schedule For Printing'){
            $next_process = 'Printed Bopp List';
            $machines = Machine::where('type','printing')->where('status',1)->get();
            $block_reasons = BlockageReason::where('type','printing')->where('status',1)->get();
            return view('admin.job_card.printing_report',compact('job_card','machines','block_reasons','next_process'));
        }
        if($request->process == 'Printed Bopp List'){
            $next_process = 'Schedule For Lamination';
        }
        if($request->process == 'Schedule For Lamination'){
            $next_process = 'Laminated Rolls';
            $machines = Machine::where('type','lamination')->where('status',1)->get();
            $block_reasons = BlockageReason::where('type','lamination')->where('status',1)->get();
            return view('admin.job_card.lamination_report',compact('job_card','machines','block_reasons','next_process'));
        }
        if($request->process == 'Laminated Rolls'){
            $next_process = 'Schedule For Box / Cutting';
        }
        if($request->process == 'Schedule For Box / Cutting'){
            $next_process = 'Ready Bags List';
            $machines = Machine::where('type', strtolower($job_card->order_send_for))->where('status',1)->get();
            $block_reasons = BlockageReason::where('type', strtolower($job_card->order_send_for))->where('status',1)->get();
            return view('admin.job_card.box_cutting_report',compact('job_card','machines','block_reasons','next_process'));
        }
        
        if($job_card->job_type == 'Common' && $request->process == 'Ready Bags List'){
            $next_process = 'Completed';
        } else if($request->process == 'Ready Bags List'){
            $next_process = 'Packing Slip';
        }
        
        if($request->process == 'Account Pending'){
            $next_process = 'Completed';
            return view('admin.job_card.billing_modal', compact('job_card', 'next_process'));
        }
        
        if($request->process == 'Packing Slip' || $request->process == 'Dispatch Material'){
            $next_process = 'Packing Material';
            return view('admin.job_card.packing_modal',compact('job_card','next_process'));
        }

        // Steps that should move directly without showing the remarks modal (for all orders)
        $simple_auto_move_steps = ['Order List', 'Cylinder Come'];
        
        // Additional auto-move steps specifically for Common orders
        if($job_card->job_type == 'Common'){
            $simple_auto_move_steps = array_merge($simple_auto_move_steps, ['Printed Bopp List', 'Ready Bags List']);
        }

        if(in_array($request->process, $simple_auto_move_steps)){
            return $this->update_process_internally($job_card, $request->process, $next_process);
        }

        return view('admin.job_card.next_process',compact('job_card','next_process'));
    }

    private function update_process_internally($job_card, $current_process, $next_process) {
        $old_process = $job_card->job_card_process;
        $job_card->job_card_process = $next_process;
        if ($next_process == 'Completed') {
            $job_card->status = 'Completed';
            $job_card->complete_date = now();
            $job_card->complete_by_id = auth()->user()->id;
            
            // Check if this was a rollout and if the parent should also be completed
            $this->markParentCompletedIfEligible($job_card);
        } elseif (!in_array($next_process, ['Cylinder Come', 'Order List']) && $job_card->status == 'Pending') {
            $job_card->status = 'Progress';
        }
        $job_card->save();
        
        // Handle Cylinder Checkout when moving from "Cylinder Come"
        if ($current_process == 'Cylinder Come') {
            $cylinderJob = \App\Models\CylinderJob::where('job_card_id', $job_card->id)->first();
            if ($cylinderJob) {
                $cylinderJob->check_out_by = auth()->user()->id;
                $cylinderJob->check_out_date = now();
                $days = now()->diffInDays(\Carbon\Carbon::parse($cylinderJob->check_in_date)->startOfDay());
                $cylinderJob->total_no_of_days = $days . ($days <= 1 ? ' DAY' : ' DAYS');
                $cylinderJob->save();
            }
        }

        // Handle Auto-Replenishment for Common Orders when moving FROM Printed List
        if ($job_card->job_type == 'Common' && $current_process == 'Printed Bopp List' && $job_card->is_hold == 0) {
            $newJob = $job_card->replicate();
            
            // Generate NEW Job Card No for the replenished job
            $now = now();
            $startYear = $now->month >= 4 ? $now->year : $now->year - 1;
            $endYear = str_pad(($startYear + 1) % 100, 2, '0', STR_PAD_LEFT);
            $startYearShort = str_pad($startYear % 100, 2, '0', STR_PAD_LEFT);
            $fy = $startYearShort . '-' . $endYear;
            $prefix = "JC-C-" . $fy . '-';
            
            $latestJobCard = \App\Models\JobCard::withTrashed()
                ->where('job_card_no', 'like', $prefix . '%')
                ->where('job_card_no', 'NOT LIKE', '%-R%') // Don't count partial rollouts
                ->orderBy('id', 'desc')
                ->first();

            if ($latestJobCard && $latestJobCard->job_card_no) {
                // Extract number from prefix
                $lastNoPart = str_replace($prefix, "", $latestJobCard->job_card_no);
                $lastNo = intval($lastNoPart);
                $nextNo = $lastNo + 1;
            } else {
                $nextNo = 1;
            }
            
            $newJob->job_card_no = $prefix . str_pad($nextNo, 2, '0', STR_PAD_LEFT);
            $newJob->job_card_process = 'Order List';
            $newJob->actual_pieces = $newJob->no_of_pieces; 
            $newJob->file_upload = null; 
            $newJob->status = 'Progress';
            $newJob->job_card_date = now();
            $newJob->is_hold = 0; 
            $newJob->save();

            JobCardProcess::create([
                'job_card_id' => $newJob->id,
                'process_name' => 'Order List',
                'process_start_date' => now(),
                'process_remarks' => 'Auto Replenished from JC #'.$job_card->id,
                'user_id' => auth()->id(),
                'status' => 1,
                'from_where' => 'Common Order'
            ]);
        }

        // SECURITY: Close any and all active processes before opening a new one
        JobCardProcess::where('job_card_id', $job_card->id)->where('status', 1)->update([
            'status' => 2,
            'process_end_date' => now(),
            'result_remarks' => 'Auto Moved to ' . $next_process
        ]);

        $process = new JobCardProcess();
        $process->from_where = ucfirst($job_card->job_type).' Order';
        $process->job_card_id = $job_card->id;
        $process->process_name = $next_process;
        $process->process_start_date = now();
        $process->user_id = auth()->user()->id;
        $process->process_remarks = $old_process.' To '.$next_process;
        $process->status = 1;
        $process->date = now();
        $process->save();

        return [
            'result' => 1,
            'message' => 'Process Updated From ' . $old_process . ' To ' . $next_process . ' (Auto)',
            'auto_refresh' => true
        ];
    }

    public function packing_store(Request $request,$id){
        $job_card = JobCard::find($id);
        if($job_card){
            $obsIds = [];
            $packing_slip = new PackingSlip();
                $packing_slip->user_id = auth()->user()->id;
                $packing_slip->job_card_id = $job_card->id;
                $packing_slip->packing_slip_no = 'TEMP';
                $packing_slip->total_weight = $request->total_weight;
                $packing_slip->pending_weight = $request->total_weight;
                $packing_slip->dispatch_weight = 0;
                $packing_slip->total_bags = $request->total_bags;
                $packing_slip->pending_bags = $request->total_bags;
                $packing_slip->dispatch_bags = 0;
                $packing_slip->packing_date = $request->date;
                $packing_slip->remarks = $request->remarks;
                $packing_slip->status = 1;
                $packing_slip->save();

                $packing_slip->packing_slip_no = 'PS-' . $packing_slip->id;
                $packing_slip->save();
                
                foreach($request->get('packing_slip',[]) as $o => $obs){
                    $obs['packing_slip_id'] = $packing_slip->id;
                    $obs['job_card_id'] = $id;
                    $obs['weight'] = $obs['weight'];
                    $obs['start_date'] = $request->date;
                    $obs['status'] = 1;
                    $obs['is_undo'] = 0;
                    $packing_detail = PackingDetail::updateOrCreate(['id' => $obs['id']],$obs);
                    $obsIds[] = $packing_detail->id;
                }
           
            if($obsIds){
                PackingDetail::where('packing_slip_id',$packing_slip->id)->where('job_card_id',$job_card->id)->whereNotIn('id',$obsIds)->delete();
            }
            $job_card->job_card_process = 'Dispatch Material';
            $job_card->save();

            // SECURITY: Close any active production/dispatch process when adding a packing slip
            JobCardProcess::where('job_card_id', $job_card->id)->where('status', 1)->update([
                'status' => 2,
                'process_end_date' => now(),
                'result_remarks' => 'Packing Material Added - '.$packing_slip->packing_slip_no
            ]);
            if(false && $old_process){
                $old_process->process_end_date = now();
                $old_process->total_time = now()->diffInHours($old_process->process_start_date);
                $old_process->result_remarks = $request->remarks;
                $old_process->status = 2;
                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $filename = 'job_process_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/job_card'), $filename);
                    $old_process->file = $filename;
                }
                $old_process->save();

               
            }
            $next_process = new JobCardProcess();
            $next_process->from_where = ucfirst($job_card->job_type).' Order';
            $next_process->job_card_id = $job_card->id;
            $next_process->process_name = 'Dispatch Material';
            $next_process->process_remarks = 'Packing Material Added - '.$packing_slip->packing_slip_no;
            $next_process->packing_slip_id = $packing_slip->id;
            $next_process->process_start_date = now();
            $next_process->user_id = auth()->user()->id;
            $next_process->status = 1;
            $next_process->save();
            


            $data = [
                'result' => 1,
                'message' => 'Packing Material Added Successfully',
                'from' => 'Job Card'
            ];
            return response()->json($data);
        }
        $data = [
            'result' => 0,
            'message' => 'Job Card Not Found',
            'from' => 'Job Card'
        ];
        return response()->json($data);
    }

    
    public function update_process(Request $request, $id)
    {
        $job_card = JobCard::find($id);
        if ($job_card) {
            $permission_map = [
                'Cylinder Come' => 'process_cylinder_come',
                'Order List' => 'process_order_list',
                'Schedule For Printing' => 'process_printing',
                'Printed Bopp List' => 'process_bopp_list',
                'Schedule For Lamination' => 'process_lamination',
                'Laminated Rolls' => 'process_laminated_rolls',
                'Schedule For Box / Cutting' => 'process_box_cutting',
                'Ready Bags List' => 'process_ready_bags',
                'Packing Slip' => 'process_packing_slip',
                'Dispatch Material' => 'process_dispatch',
                'Account Pending' => 'account_pending'
            ];

            $stage_key = $permission_map[$request->job_card_process] ?? 'order_process';
            if (!PermissionHelper::check($stage_key, 'next_process')) {
                return response()->json([
                    'result' => -1,
                    'message' => 'Access Denied! You do not have permission to update this stage (' . $request->job_card_process . ').',
                    'from' => 'Job Card'
                ]);
            }

            if ($request->job_card_process == 'Cylinder Come') {
                $cylinderJob = CylinderJob::where('job_card_id', $job_card->id)->first();
                if ($cylinderJob) {
                    $cylinderJob->check_out_by = auth()->user()->id;
                    $cylinderJob->check_out_date = now();
                    $days = now()->diffInDays(\Carbon\Carbon::parse($cylinderJob->check_in_date)->startOfDay());
                    $cylinderJob->total_no_of_days = $days . ($days <= 1 ? ' DAY' : ' DAYS');
                    $cylinderJob->save();
                }
            }

            // Update Job Card the actual process
            $old_process = $job_card->job_card_process;
            $job_card->job_card_process = $request->next_process;
            
            // If moving beyond Order List and currently Pending, set status to Progress
            if (!in_array($job_card->job_card_process, ['Cylinder Come', 'Order List']) && $job_card->status == 'Pending') {
                $job_card->status = 'Progress';
            }
            
            $job_card->save();

            // First find and update the EXACT process being moved
            $old_job_process = JobCardProcess::where('job_card_id', $job_card->id)
                ->where('process_name', $request->job_card_process)
                ->where('status', 1)
                ->first();
                
            if($old_job_process){
                $old_job_process->status = 2;
                $old_job_process->process_end_date = now();
                $old_job_process->total_time = now()->diffInHours($old_job_process->process_start_date);
                $old_job_process->result_remarks = $request->remarks;
                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $filename = 'job_process_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/job_card'), $filename);
                    $old_job_process->file = $filename;
                }

                if($request->job_card_process == 'Schedule For Printing' || ($job_card->job_type == 'Common' && $request->job_card_process == 'Order List')){
                    $old_job_process->from = 'Printing';
                    $old_job_process->estimate_production = $request->estimate_production;
                    $old_job_process->actual_order = $request->actual_order;
                    $old_job_process->wastage = $request->wastage;
                    $old_job_process->working_hours = $request->working_hours;
                    $old_job_process->machine_id = $request->machine_id;
                    $old_job_process->shift_time = $request->shift_time;
                    $old_job_process->blockage_reason_id = $request->blockage_reason_id;
                    $old_job_process->blockage_time = $request->blockage_time;

                    $job_card->actual_pieces = $request->estimate_production;
                    $job_card->save();
                }
                if($request->job_card_process == 'Schedule For Lamination' || ($job_card->job_type == 'Common' && $request->job_card_process == 'Printed Bopp List')){
                    $old_job_process->from = 'Lamination';
                    $old_job_process->estimate_production = $request->estimate_production;
                    $old_job_process->shift_time = $request->shift_time;
                    $old_job_process->blockage_reason_id = $request->blockage_reason_id;
                    $old_job_process->blockage_time = $request->blockage_time;
                    $job_card->save();
                }
                if($request->job_card_process == 'Schedule For Box / Cutting' || ($job_card->job_type == 'Common' && $request->job_card_process == 'Laminated Rolls')){
                    $old_job_process->from = $job_card->order_send_for;
                    $old_job_process->estimate_production = $request->estimate_production;
                    $old_job_process->actual_order = $request->actual_order;
                    $old_job_process->wastage = $request->wastage;
                    $old_job_process->working_hours = $request->working_hours;
                    $old_job_process->machine_id = $request->machine_id;
                    $old_job_process->shift_time = $request->shift_time;
                    $old_job_process->blockage_reason_id = $request->blockage_reason_id;
                    $old_job_process->blockage_time = $request->blockage_time;

                    $job_card->actual_pieces = $request->estimate_production;
                    $job_card->save();
                }
                $old_job_process->save();
            }

            // SECURITY: Also close any OTHER stray active processes for this job
            JobCardProcess::where('job_card_id', $job_card->id)
                ->where('id', '!=', $old_job_process?->id ?? 0)
                ->where('status', 1)
                ->update([
                    'status' => 2,
                    'process_end_date' => now(),
                    'result_remarks' => 'Auto-closed by move to ' . $request->next_process
                ]);

            $process = new JobCardProcess();
            $process->from_where = ucfirst($job_card->job_type).' Order';
            $process->job_card_id = $job_card->id;
            $process->process_name = $request->next_process;
            $process->process_start_date = now();
            $process->user_id = auth()->user()->id;
            $process->process_remarks = $old_process.' To '.$request->next_process;
            $process->status = 1;
            $process->date = now();
             
            if ($request->next_process == 'Completed') {
                if ($request->job_card_process == 'Account Pending') {
                    $job_card->billing_date = $request->billing_date;
                    $job_card->billing_invoice_no = $request->billing_invoice_no;

                    // Create Bill
                    $bill = new \App\Models\Bill();
                    $bill->bill_no = $request->billing_invoice_no;
                    $bill->bill_date = $request->billing_date ?? date('Y-m-d');
                    $bill->customer_id = $job_card->customer_agent_id;
                    $bill->job_card_id = $job_card->id;
                    $bill->remarks = $request->remarks;
                    $bill->created_by = auth()->id();
                    $bill->status = 1;
                    $bill->save();

                    $total_amount = 0;
                    $total_gst = 0;
                    $grand_total = 0;

                    if ($request->has('items')) {
                        foreach ($request->items as $item) {
                            if (!empty($item['description']) && ($item['qty'] > 0 || $item['amount'] > 0)) {
                                $qty = floatval($item['qty'] ?? 0);
                                $rate = floatval($item['rate'] ?? 0);
                                $gst_perc = floatval($item['gst_percent'] ?? 0);
                                
                                $amount = round($qty * $rate, 2);
                                $gst_amount = round($amount * ($gst_perc / 100), 2);
                                $row_total = $amount + $gst_amount;

                                \App\Models\BillItem::create([
                                    'bill_id' => $bill->id,
                                    'description' => $item['description'],
                                    'qty' => $qty,
                                    'unit' => $item['unit'] ?? 'Kgs',
                                    'rate' => $rate,
                                    'amount' => $amount,
                                    'gst_percent' => $gst_perc,
                                    'gst_amount' => $gst_amount,
                                    'total_amount' => $row_total
                                ]);

                                $total_amount += $amount;
                                $total_gst += $gst_amount;
                                $grand_total += $row_total;
                            }
                        }
                    }

                    // Update Bill Totals
                    $bill->total_amount = $total_amount;
                    $bill->taxable_amount = $total_amount;
                    // Simplified GST split, we can just store the total gst as IGST or split CGST/SGST if same state, but normally we just need the total
                    $bill->igst_amount = $total_gst;
                    $bill->grand_total = $grand_total;
                    $bill->save();

                    // CREATE CUSTOMER LEDGER ENTRY
                    \App\Models\CustomerLedger::create([
                        'customer_id' => $job_card->customer_agent_id,
                        'job_card_id' => $job_card->id,
                        'transaction_date' => $request->billing_date ?? date('Y-m-d'),
                        
                        'amount' => $total_amount,
                        'gst' => $total_gst,
                        'total_amount' => $grand_total,
                        
                        'extra_charge_amount' => 0,
                        'extra_charge_gst' => 0, 
                        'extra_total_amount' => 0,
                        
                        'grand_total_amount' => $grand_total,
                        'dr_cr' => 'Dr',
                        'remarks' => "Billed: Job #{$job_card->id} - {$job_card->name_of_job}",
                        'software_remarks' => "Bill No: {$request->billing_invoice_no} | Grand Total: {$grand_total}",
                        'user_id' => \Illuminate\Support\Facades\Auth::id()
                    ]);
                }
                $job_card->status = 'Completed';
                $job_card->complete_date = now();
                $job_card->complete_by_id = auth()->user()->id;
                $job_card->save();
                
                // Cascading completion for parents
                $this->markParentCompletedIfEligible($job_card);
            }
            $process->save();

            return response()->json([
                'result' => 1,
                'message' => 'Process Updated From ' . $old_process . ' To ' . $request->next_process,
                'from' => 'Job Card'
            ]);
        }
    }

    public function common_roll_out_modal(Request $request, $id)
    {
        $job_card = JobCard::findOrFail($id);
        $total_rolls = $job_card->processes()->where('process_name', 'Schedule For Lamination')->orderBy('id', 'desc')->first()?->estimate_production ?? 0;
        $out_rolls = $job_card->roll_outs()->sum('rolls_out');
        $remaining_rolls = $total_rolls - $out_rolls;
        
        $mode = $request->mode ?? 'Manual Out';
        $view = ($mode == 'Next Process') ? 'admin.job_card.common_roll_out_next_modal' : 'admin.job_card.common_roll_out_manual_modal';
        
        return view($view, compact('job_card', 'total_rolls', 'out_rolls', 'remaining_rolls', 'mode'));
    }

    public function store_roll_out(Request $request, $id)
    {
        $job_card = JobCard::findOrFail($id);
        
        $total_rolls = $job_card->processes()->where('process_name', 'Schedule For Lamination')->orderBy('id', 'desc')->first()?->estimate_production ?? 0;
        $out_rolls = $job_card->roll_outs()->sum('rolls_out');
        $remaining_before = $total_rolls - $out_rolls;

        if ($request->rolls_out > $remaining_before) {
            return response()->json(['result' => 0, 'message' => 'Rolls out cannot exceed remaining rolls (' . $remaining_before . ')']);
        }

        DB::beginTransaction();
        try {
            $roll_out = new \App\Models\CommonRollOut();
            $roll_out->job_card_id = $job_card->id;
            $roll_out->user_id = auth()->id();
            $roll_out->rolls_out = $request->rolls_out;
            $roll_out->action_type = $request->action_type;
            $roll_out->date = $request->date ?? date('Y-m-d');
            $roll_out->remarks = $request->remarks;
            $roll_out->save();

            $remaining_after = $remaining_before - $request->rolls_out;

            if ($request->action_type == 'Next Process') {
                // Replicate for next process if user wants to continue with these rolls
                $new_job = $job_card->replicate();
                
                // Calculate pieces based on ratio of rolls being moved
                $ratio = ($total_rolls > 0) ? ($request->rolls_out / $total_rolls) : 0;
                $new_job->no_of_pieces = round($job_card->no_of_pieces * $ratio);
                $new_job->actual_pieces = $new_job->no_of_pieces;
                
                $new_job->job_card_process = 'Schedule For Box / Cutting';
                
                // Better sub-numbering: JC-C-25-26-05-R1, JC-C-25-26-05-R2 etc.
                $base_no = $job_card->job_card_no;
                // If the base job already has a suffix like -R1, don't double it (though unlikely here)
                $next_suffix = $job_card->roll_outs()->where('action_type', 'Next Process')->count();
                $new_job->job_card_no = $base_no . '-R' . $next_suffix;
                $new_job->save();

                JobCardProcess::create([
                    'job_card_id' => $new_job->id,
                    'process_name' => 'Schedule For Box / Cutting',
                    'process_start_date' => now(),
                    'process_remarks' => 'Moved from Main JC #' . $job_card->id,
                    'user_id' => auth()->id(),
                    'status' => 1,
                    'estimate_production' => $request->rolls_out,
                    'from_where' => 'Common Order'
                ]);
            }

            if ($remaining_after <= 0) {
                // Determine if we should complete the parent job card
                // Only complete if moving to DISCARD and no other pending rollouts exist
                $hasPendingRollouts = JobCard::where('job_card_no', 'LIKE', $job_card->job_card_no . '-R%')
                    ->where('status', '!=', 'Completed')
                    ->exists();

                if ($request->action_type == 'Discard' && !$hasPendingRollouts) {
                    $job_card->status = 'Completed';
                    $job_card->job_card_process = 'Completed';
                    $job_card->save();
                    
                    JobCardProcess::where('job_card_id', $job_card->id)->where('status', 1)->update([
                        'status' => 2,
                        'process_end_date' => now(),
                        'result_remarks' => 'All rolls out - ' . $request->action_type
                    ]);
                }
            }

            DB::commit();
            return response()->json(['result' => 1, 'message' => 'Roll out recorded successfully. Remaining: ' . (float)$remaining_after]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function change_hold_status($id)
    {
        $job_card = JobCard::find($id);
        if($job_card){
            $job_card->is_hold = $job_card->is_hold == 1 ? 0 : 1;
            $job_card->save();
            return response()->json([
                'result' => 1, 
                'message' => 'Status Changed Successfully. Auto-Replenish is now ' . ($job_card->is_hold ? 'HOLD' : 'ACTIVE')
            ]);
        }
        return response()->json(['result' => 0, 'message' => 'Job Card Not Found']);
    }

    /**
     * Returns active Hold reasons (type = 'hold') as JSON for modal dropdown.
     */
    public function hold_reasons()
    {
        $reasons = BlockageReason::where('type', 'hold')->where('status', 1)->get(['id', 'name']);
        return response()->json(['result' => 1, 'reasons' => $reasons]);
    }

    /**
     * PUT order on HOLD with a selected reason and optional notes.
     * Next Process is BLOCKED while is_hold = 1.
     */
    public function holdJobCard(Request $request, $id)
    {
        $request->validate([
            'hold_reason_id' => 'required|exists:blockage_reasons,id',
            'hold_notes'     => 'nullable|string|max:500',
        ]);

        $job_card = JobCard::find($id);
        if (!$job_card) {
            return response()->json(['result' => 0, 'message' => 'Job Card Not Found']);
        }

        if ($job_card->is_hold == 1) {
            return response()->json(['result' => 0, 'message' => 'Order is already on HOLD.']);
        }

        $job_card->is_hold          = 1;
        $job_card->hold_reason_id   = $request->hold_reason_id;
        $job_card->hold_notes       = $request->hold_notes;
        $job_card->held_at          = now();
        $job_card->held_by_id       = auth()->id();
        $job_card->save();

        // Log hold in job card processes
        JobCardProcess::create([
            'job_card_id'        => $job_card->id,
            'process_name'       => 'On Hold',
            'process_start_date' => now(),
            'process_remarks'    => 'Placed on HOLD. Reason: ' . (BlockageReason::find($request->hold_reason_id)->name ?? '-') . ($request->hold_notes ? ' | Notes: ' . $request->hold_notes : ''),
            'user_id'            => auth()->id(),
            'status'             => 1,
            'from_where'         => 'Hold Action',
        ]);

        return response()->json(['result' => 1, 'message' => 'Order #' . $job_card->job_card_no . ' has been placed on HOLD successfully.']);
    }

    /**
     * UNHOLD an order – clears is_hold flag and logs the action.
     */
    public function unholdJobCard($id)
    {
        $job_card = JobCard::find($id);
        if (!$job_card) {
            return response()->json(['result' => 0, 'message' => 'Job Card Not Found']);
        }

        if ($job_card->is_hold == 0) {
            return response()->json(['result' => 0, 'message' => 'Order is not on HOLD.']);
        }

        $job_card->is_hold        = 0;
        $job_card->hold_reason_id = null;
        $job_card->hold_notes     = null;
        $job_card->held_at        = null;
        $job_card->held_by_id     = null;
        $job_card->save();

        // Close the "On Hold" process log
        JobCardProcess::where('job_card_id', $job_card->id)
            ->where('process_name', 'On Hold')
            ->where('status', 1)
            ->update([
                'status'          => 2,
                'process_end_date'=> now(),
                'result_remarks'  => 'Released from HOLD by ' . auth()->user()->name,
            ]);

        return response()->json(['result' => 1, 'message' => 'Order #' . $job_card->job_card_no . ' has been RELEASED from HOLD successfully.']);
    }

    /**
     * Load the Hold modal for a specific job card.
     */
    public function holdModal($id)
    {
        $job_card    = JobCard::findOrFail($id);
        $hold_reasons = BlockageReason::where('type', 'hold')->where('status', 1)->get(['id', 'name']);
        return view('admin.job_card.hold_modal', compact('job_card', 'hold_reasons'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JobCard  $jobCard
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $job_card = JobCard::find($id);
        return view('admin.job_card.view', compact('job_card'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobCard  $jobCard
     * @return \Illuminate\Http\Response
     */
    public function edit(JobCard $jobCard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\JobCard  $jobCard
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JobCard $jobCard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JobCard  $jobCard
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $jobCard = JobCard::find($id);
        if($jobCard){
            // Prevent direct deletion of Common Job Cards linked to Packing Slips
            if($jobCard->job_type == 'Common' && $jobCard->packing_slips()->exists()){
                return response()->json([
                    'result' => 0, 
                    'message' => 'This is a Common Packing Slip entry. To delete this, please go to Packing Slip - Common -> All Packing Slip and delete from there.'
                ]);
            }

            $jobCard->cylinder_job()->delete();
            $jobCard->processes()->delete();
            // Delete linked ledger entries
            CustomerLedger::where('job_card_id', $jobCard->id)->delete();
            $jobCard->delete();
            $data = [
                'result' => 1,
                'message' => 'Job Card Deleted Successfully',
                'from' => 'Job Card'
            ];
            return response()->json($data);
        }
    }

    public function report(Request $request)
    {
        $customers = AgentCustomer::where('status', 1)->get();
        // Fetching users who are either sale executives or have been assigned as one in job cards
        $executives = User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive'])->where('status', 1)->get(); 
        if($executives->isEmpty()){
            // Fallback: get unique executive IDs from JobCard
            $execIds = JobCard::whereNotNull('sale_executive_id')->distinct()->pluck('sale_executive_id');
            $executives = User::whereIn('id', $execIds)->get();
        }
        
        $processes = [
            'Order List',
            'Schedule For Printing',
            'Printed Bopp List',
            'Schedule For Lamination',
            'Laminated Rolls',
            'Schedule For Box / Cutting',
            'Ready Bags List',
            'Packing Slip',
            'Dispatch Material',
            'Account Pending',
            'Completed'
        ];
        
        return view('admin.report.job_card.report', compact('customers', 'executives', 'processes'));
    }

    public function report_datatable(Request $request)
    {
        $query = JobCard::where('job_type', '!=', 'Common');
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'roto_order_report');

        if ($request->from_date) {
            $query->whereDate('job_card_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('job_card_date', '<=', $request->to_date);
        }
        if ($request->customer_id) {
            $query->where('customer_agent_id', $request->customer_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->process) {
            $query->where('job_card_process', $request->process);
        }
        if ($request->executive_id) {
            $actor = \App\Models\User::find($request->executive_id);
            if ($actor) {
                // Get all IDs this executive manages for job cards
                $managed_ids = $actor->getPermittedUserIds('roto_orders');
                $query->whereIn('sale_executive_id', $managed_ids);
            } else {
                $query->where('sale_executive_id', $request->executive_id);
            }
        }
        
        if ($request->delivery_filter) {
            $query->where('status', 'Completed');
            if ($request->delivery_filter == 'on_time') {
                $query->whereColumn('complete_date', '<=', 'dispatch_date');
            } elseif ($request->delivery_filter == 'late') {
                $query->whereColumn('complete_date', '>', 'dispatch_date');
            } elseif ($request->delivery_filter == 'before_time') {
                $query->whereColumn('complete_date', '<', 'dispatch_date');
            }
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search){
                $q->where('name_of_job', 'like', "%{$search}%")
                  ->orWhereHas('customer_agent', function($ca) use ($search){
                      $ca->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $total_orders = (clone $query)->count();
        $progress_orders = (clone $query)->whereIn('status', ['Pending', 'Progress', 'Account Pending'])->count();
        $completed_orders = (clone $query)->where('status', 'Completed')->count();

        $job_cards = $query->with(['customer_agent', 'sale_executive', 'processes', 'packing_slips', 'hold_reason', 'heldByUser'])
                         ->latest()
                         ->paginate($request->value ?? 50);

        return view('admin.report.job_card.report_datatable', compact('job_cards', 'total_orders', 'progress_orders', 'completed_orders'));
    }

    public function view_timeline($id)
    {
        $job_card = JobCard::with(['processes.user'])->find($id);
        if(!$job_card) return "Job Card not found";
        
        $processes = $job_card->processes()->orderBy('id', 'asc')->get();
        return view('admin.report.job_card.timeline_modal', compact('job_card', 'processes'));
    }

    public function view_billing_details($id)
    {
        $job_card = JobCard::with(['customer_agent', 'sale_executive', 'bopp', 'fabric'])->find($id);
        if(!$job_card) return "Order not found";
        
        return view('admin.report.job_card.view_billing_modal', compact('job_card'));
    }

    public function view_packing_details($id)
    {
        $job_card = JobCard::with(['packing_slips.packing_details'])->find($id);
        if(!$job_card) return "Order not found";
        
        return view('admin.report.job_card.view_packing_modal', compact('job_card'));
    }
    private function markParentCompletedIfEligible($job_card) {
        if ($job_card->job_type == 'Common' && str_contains($job_card->job_card_no, '-R')) {
            $parentNo = substr($job_card->job_card_no, 0, strrpos($job_card->job_card_no, '-R'));
            $parent = JobCard::where('job_card_no', $parentNo)->first();
            
            if ($parent && $parent->status != 'Completed') {
                // Check remaining rolls on parent
                $lam_proc = $parent->processes()->where('process_name', 'Schedule For Lamination')->orderBy('id', 'desc')->first();
                $total_rolls = $lam_proc ? $lam_proc->estimate_production : 0;
                $out_rolls = $parent->roll_outs()->sum('rolls_out');
                $remaining_rolls = $total_rolls - $out_rolls;
                
                if ($remaining_rolls <= 0) {
                    // Check if any OTHER children are still pending
                    $hasOtherPending = JobCard::where('job_card_no', 'LIKE', $parentNo . '-R%')
                        ->where('id', '!=', $job_card->id)
                        ->where('status', '!=', 'Completed')
                        ->exists();
                        
                    if (!$hasOtherPending) {
                        $parent->status = 'Completed';
                        $parent->job_card_process = 'Completed';
                        $parent->complete_date = now();
                        $parent->complete_by_id = auth()->user()->id;
                        $parent->save();
                        
                        // Also close any active process for the parent
                        JobCardProcess::where('job_card_id', $parent->id)->where('status', 1)->update([
                            'status' => 2,
                            'process_end_date' => now(),
                            'result_remarks' => 'Auto-completed via rollout completion'
                        ]);
                    }
                }
            }
        }
    }
}
