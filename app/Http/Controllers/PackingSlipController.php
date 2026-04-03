<?php

namespace App\Http\Controllers;

use App\Models\PackingSlip;
use App\Models\PackingDetail;
use App\Models\JobCard;
use App\Models\JobCardProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Helpers\PermissionHelper;

class PackingSlipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!PermissionHelper::check('packing_slip')) {
            abort(403, 'Unauthorized access to Packing Slips.');
        }
        $type = $request->type;
        return view('admin.packing_slip.index', compact('type'));
    }

    public function datatable(Request $request){
        $number = $request->value ?? 50;
        $query = PackingSlip::query();
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'packing_slip');
        if($request->search){
            $query->where(function($q) use ($request){
                $q->whereHas('job_card', function($q) use ($request){
                    $q->where('name_of_job', 'like', "%{$request->search}%")
                    ->orWhereHas('customer_agent', function($q) use ($request){
                        $q->where('name', 'like', "%{$request->search}%");
                    });
                });
            });
        }
        if($request->type == 'pending'){
            $query->where('status', 1);
        }
        if($request->type == 'complete'){
            $query->where('status', 2);
        }
        $packing_slips = $query->latest()->paginate($number);
        return view('admin.packing_slip.datatable', compact('packing_slips'));
    }

    public function view_modal(Request $request){
        if (!PermissionHelper::check('packing_slip')) {
            return '<div class="alert alert-danger m-3">Access Denied! You do not have permission to view packing slips.</div>';
        }
        // Reset is_undo for all details of this slip when viewing (locks them)
        PackingDetail::where('packing_slip_id', $request->id)->update(['is_undo' => 0]);
        
        $packing_slip = PackingSlip::with('packing_details', 'job_card')->find($request->id);
        if($packing_slip){
            $job_card = $packing_slip->job_card;
            return view('admin.packing_slip.modal', compact('packing_slip', 'job_card'));
        }
    }

    public function complete_detail(Request $request){
        if (!PermissionHelper::check('packing_slip', 'edit')) {
            return response()->json(['result' => -1, 'message' => 'Access Denied! You do not have permission to update records.']);
        }
        $detail = PackingDetail::find($request->id);
        if($detail){
            $detail->complete_date = now();
            $detail->complete_by = Auth::id();
            $detail->status = 2;
            $detail->is_undo = 1; // Allow undo in current session
            $detail->save();

            $this->recalculate_slip($detail->packing_slip_id);

            return response()->json([
                'result' => 1,
                'message' => 'Bag Completed',
                'slip' => PackingSlip::find($detail->packing_slip_id),
                'formatted_complete_date' => date('d M, Y', strtotime($detail->complete_date))
            ]);
        }
        return response()->json(['result' => 0, 'message' => 'Detail not found']);
    }

    public function undo_detail(Request $request){
        if (!PermissionHelper::check('packing_slip', 'edit')) {
            return response()->json(['result' => -1, 'message' => 'Access Denied! You do not have permission to update records.']);
        }
        $detail = PackingDetail::find($request->id);
        if($detail && $detail->is_undo == 1){
            $detail->complete_date = null;
            $detail->complete_by = null;
            $detail->status = 1;
            $detail->is_undo = 0;
            $detail->save();

            $this->recalculate_slip($detail->packing_slip_id);

            return response()->json([
                'result' => 1,
                'message' => 'Bag Undo Successful',
                'slip' => PackingSlip::find($detail->packing_slip_id)
            ]);
        }
        return response()->json(['result' => 0, 'message' => 'Cannot undo this bag anymore']);
    }

    private function recalculate_slip($slip_id){
        $slip = PackingSlip::with('packing_details')->find($slip_id);
        if($slip){
            $completed = $slip->packing_details()->where('status', 2)->get();
            
            $dispatch_weight = round($completed->sum('weight'), 3);
            $dispatch_bags = $completed->count();
            
            $slip->dispatch_weight = $dispatch_weight;
            $slip->dispatch_bags = $dispatch_bags;
            $slip->pending_weight = max(0, round((float)$slip->total_weight - (float)$dispatch_weight, 3));
            $slip->pending_bags = (int)$slip->total_bags - (int)$dispatch_bags;
            
            if($slip->pending_bags <= 0){
                $slip->status = 2; // Completed
                $slip->complete_date = now();
            } else {
                $slip->status = 1; // Pending
                $slip->complete_date = null;
            }
            
            $slip->save();

            // JOB CARD COMPLETION LOGIC
            $job_card = JobCard::find($slip->job_card_id);
            if ($job_card) {
                // Check if ALL packing slips for this job card are completed (status 2)
                $all_slips_completed = PackingSlip::where('job_card_id', $job_card->id)
                    ->where('status', '!=', 2)
                    ->count() == 0;

                if ($all_slips_completed) {
                    // Move to Account Pending instead of direct Completed for Roto/Other orders
                    if ($job_card->job_card_process != 'Completed') {
                        $job_card->job_card_process = 'Account Pending';
                        $job_card->status = 'Account Pending';
                    }
                    $job_card->is_editable = 1; 
                } else {
                    // If any slip is NOT completed, revert job card to active process
                    // Revert if it was previously marked as "Completed" or "Account Pending"
                    if ($job_card->job_card_process == 'Completed' || $job_card->job_card_process == 'Account Pending') {
                        $job_card->complete_date = null;
                        $job_card->job_card_process = 'Dispatch Material';
                        $job_card->complete_by_id = null;
                        $job_card->is_editable = 1;
                        $job_card->status = 'Progress';
                    }
                }
                $job_card->save();
            }

            // SYNC JOB CARD PROCESS
            $process = JobCardProcess::where('packing_slip_id', $slip->id)->first();
            if ($process) {
                if ($slip->status == 2) { 
                    // Slip is fully dispatched
                    $process->process_end_date = now();
                    $process->total_time = now()->diffInHours($process->process_start_date);
                    $process->status = 2;
                    
                } else { 
                    // Slip is reverted to pending/undo
                    $process->process_end_date = null;
                    $process->total_time = null;
                    $process->status = 1;
                }
                $process->save();
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PackingSlip  $packingSlip
     * @return \Illuminate\Http\Response
     */
    public function show(PackingSlip $packingSlip)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PackingSlip  $packingSlip
     * @return \Illuminate\Http\Response
     */
    public function edit(PackingSlip $packingSlip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PackingSlip  $packingSlip
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PackingSlip $packingSlip)
    {
        //
    }

    public function pdf($id){
        $packing_slip = PackingSlip::with('packing_details', 'job_card.customer_agent')->find($id);
        if($packing_slip){
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.packing_slip.pdf', compact('packing_slip'));
            return $pdf->stream($packing_slip->packing_slip_no . '.pdf');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PackingSlip  $packingSlip
     * @return \Illuminate\Http\Response
     */
    public function destroy(PackingSlip $packingSlip)
    {
        //
    }
}
