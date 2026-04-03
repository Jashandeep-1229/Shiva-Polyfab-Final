<?php

namespace App\Http\Controllers;

use App\Models\ColorMaster;
use App\Models\SizeMaster;
use App\Models\JobCard;
use App\Models\JobCardProcess;
use App\Models\Fabric;
use App\Models\Bopp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommonOrderController extends Controller
{
    public function create(Request $request)
    {
        $query_color = ColorMaster::where('status', 1);
        if ($request->color_search) {
            $query_color->where('name', 'like', '%' . $request->color_search . '%');
        }
        $colors = $query_color->get();
        
        $boppsQuery = Bopp::where('status', 1)
            ->whereHas('sizes', function($q) {
                $q->where('status', 1);
            })
            ->with(['sizes' => function($q) {
                $q->where('status', 1)->with(['fabric', 'bopp']);
            }]);

        if ($request->size_search) {
            $boppsQuery->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->size_search . '%')
                  ->orWhereHas('sizes', function($sq) use ($request) {
                      $sq->where('name', 'like', '%' . $request->size_search . '%');
                  });
            });
        }
        $bopps = $boppsQuery->orderBy('name')->get();
        $all_bopps = Bopp::where('status', 1)->get();

        // Production Lock Logic: Disable button if any job card is in progress for this combo
        $disabled_cells = [];
        $active_jobs = JobCard::where('job_type', 'Common')
            ->where('status', '!=', 'Completed')
            ->get(['id', 'color_id', 'size_id', 'is_hold', 'no_of_pieces', 'remarks']);

        foreach($active_jobs as $job) {
            $disabled_cells[$job->color_id][$job->size_id] = [
                'id' => $job->id,
                'is_hold' => $job->is_hold,
                'no_of_pieces' => $job->no_of_pieces,
                'remarks' => $job->remarks,
                'size_id' => $job->size_id
            ];
        }

        $fabrics = Fabric::where('status', 1)->get();
        $all_bopps = Bopp::where('status', 1)->get();

        if ($request->ajax()) {
            return view('admin.common_order.matrix_partial', compact('colors', 'bopps', 'disabled_cells'))->render();
        }
        
        return view('admin.common_order.create', compact('colors', 'bopps', 'disabled_cells', 'fabrics', 'all_bopps'));
    }

    public function store(Request $request)
    {
        // Handle single entry creation from modal
        $color_id = $request->color_id;
        $size_id = $request->size_id;
        $qty = $request->qty;
        
        if (!$qty || $qty <= 0) {
            return response()->json(['result' => 0, 'message' => 'Please enter a valid quantity']);
        }

        DB::beginTransaction();
        try {
            $size = SizeMaster::find($size_id);
            $color = ColorMaster::find($color_id);
            
            if (!$size || !$color) {
                throw new \Exception('Invalid size or color selected');
            }

            // Handle existing ID (Resume case)
            if ($request->job_card_id) {
                $jobCard = JobCard::find($request->job_card_id);
                if (!$jobCard) throw new \Exception('Job Card not found');
                $jobCard->is_hold = 0;
            } else {
                $jobCard = new JobCard();
                $jobCard->job_card_process = 'Order List';
                $jobCard->status = 'Progress';
            }

            $jobCard->name_of_job = "Common Order - " . $color->name . " - " . ($size->bopp->name ?? 'N/A');
            $jobCard->job_type = 'Common';
            $jobCard->color_id = $color_id;
            $jobCard->size_id = $size_id;
            $jobCard->no_of_pieces = $qty;
            $jobCard->actual_pieces = $qty;
            $jobCard->fabric_id = null; 
            $jobCard->bopp_id = $size->bopp_id;
            $jobCard->order_send_for = $size->order_send_for;
            $jobCard->job_card_date = $jobCard->job_card_date ?? now();
            $jobCard->user_id = auth()->id();
            $jobCard->is_editable = 1;
            $jobCard->remarks = $request->software_remarks ?? ($request->job_card_id ? 'Job Resumed' : 'Bulk Common Order Created');
            
            if (!$jobCard->job_card_no) {
                $now = now();
                $startYear = $now->month >= 4 ? $now->year : $now->year - 1;
                $endYear = str_pad(($startYear + 1) % 100, 2, '0', STR_PAD_LEFT);
                $startYearShort = str_pad($startYear % 100, 2, '0', STR_PAD_LEFT);
                $fy = $startYearShort . '-' . $endYear;

                $prefix = "JC-C-" . $fy . '-';

                $latestJobCard = JobCard::withTrashed()
                    ->where('job_card_no', 'like', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latestJobCard && $latestJobCard->job_card_no) {
                    $lastNo = intval(substr($latestJobCard->job_card_no, strlen($prefix)));
                    $nextNo = $lastNo + 1;
                } else {
                    $nextNo = 1;
                }

                $jobCard->job_card_no = $prefix . str_pad($nextNo, 2, '0', STR_PAD_LEFT);
            }
            
            // Handle file upload
            if ($request->hasFile('file_upload')) {
                $file = $request->file('file_upload');
                $filename = 'job_card_'.time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/job_card'), $filename);
                $jobCard->file_upload = $filename;
            }

            $jobCard->save();

            // Add/Update process record
            JobCardProcess::create([
                'job_card_id' => $jobCard->id,
                'process_name' => $jobCard->job_card_process,
                'process_start_date' => now(),
                'process_remarks' => $request->job_card_id ? 'Job Resumed: ' . ($request->software_remarks ?? 'N/A') : 'New Common Order Created',
                'user_id' => auth()->id(),
                'status' => 1,
                'from_where' => 'Common Order'
            ]);

            DB::commit();
            $msg = $request->job_card_id ? 'Job Resumed Successfully' : 'Job Card Created Successfully';
            return response()->json(['result' => 1, 'message' => $msg]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
