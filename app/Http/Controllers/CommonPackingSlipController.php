<?php

namespace App\Http\Controllers;

use App\Models\PackingSlip;
use App\Models\PackingDetail;
use App\Models\JobCard;
use App\Models\JobCardProcess;
use App\Models\AgentCustomer;
use App\Models\ColorMaster;
use App\Models\SizeMaster;
use App\Models\CommonManageStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CommonPackingSlipController extends Controller
{
    public function index(Request $request)
    {
        if (!\App\Helpers\PermissionHelper::check('packing_slip_common')) {
            abort(403, 'Unauthorized access to Common Packing Slips.');
        }
        return view('admin.packing_slip.common_index');
    }

    public function datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $query = PackingSlip::where('packing_slip_no', 'like', 'PSC-%')
            ->with(['packing_details.job_card.customer_agent']);

        if($request->search){
            $query->where(function($q) use ($request){
                $q->where('packing_slip_no', 'like', "%{$request->search}%")
                  ->orWhereHas('packing_details.job_card.customer_agent', function($q) use ($request){
                      $q->where('name', 'like', "%{$request->search}%");
                  });
            });
        }

        $packing_slips = $query->latest()->paginate($number);
        return view('admin.packing_slip.common_datatable', compact('packing_slips'));
    }

    public function pdf($id)
    {
        $packing_slip = PackingSlip::with(['packing_details.job_card.customer_agent', 'packing_details.size', 'packing_details.color'])->find($id);
        if($packing_slip){
            // For common packing slips, we might have multiple job cards. 
            // We'll pick the customer from the first detail's job card.
            $firstDetail = $packing_slip->packing_details->first();
            $customerName = $firstDetail->job_card->customer_agent->name ?? 'N/A';
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.packing_slip.common_pdf', compact('packing_slip', 'customerName'));
            return $pdf->stream($packing_slip->packing_slip_no . '.pdf');
        }
    }

    public function create()
    {
        if (!\App\Helpers\PermissionHelper::check('packing_slip_common', 'add')) {
            abort(403, 'Access Denied! You do not have permission to create packing slips.');
        }
        $customers = AgentCustomer::where('status', 1);
        $customers = auth()->user()->applyDataRestriction($customers, 'sale_executive_id', 'packing_slip_common');
        $customers = $customers->orderBy('name', 'asc')->get();
        $colors = ColorMaster::where('status', 1)->orderBy('name')->get();
        $sizes = SizeMaster::where('status', 1)->orderBy('name')->get();

        return view('admin.packing_slip.common_form', compact('customers', 'colors', 'sizes'));
    }

    public function store(Request $request)
    {
        if (!\App\Helpers\PermissionHelper::check('packing_slip_common', 'add')) {
            return response()->json(['result' => -1, 'message' => 'Access Denied!']);
        }
        $request->validate([
            'customer_agent_id' => 'required',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.size_id' => 'required',
            'items.*.color_id' => 'required',
            'items.*.weight' => 'required|numeric|min:0.001',
        ]);

        DB::beginTransaction();
        try {
            $total_weight = collect($request->items)->sum('weight');
            $total_bags = count($request->items);
            $groupedItems = collect($request->items)->groupBy(fn($i) => $i['size_id'] . '-' . $i['color_id']);

            // 1. STOCK VALIDATION
            foreach ($groupedItems as $key => $bags) {
                $firstBag = $bags->first();
                $size = SizeMaster::find($firstBag['size_id']);
                $color = ColorMaster::find($firstBag['color_id']);
                $required_weight = $bags->sum('weight');

                $current_stock = CommonManageStock::where('color_id', $color->id)
                    ->where('size_id', $size->id)
                    ->selectRaw("SUM(CASE WHEN in_out = 'In' THEN quantity ELSE -quantity END) as total")
                    ->first()->total ?? 0;

                if ($required_weight > $current_stock) {
                    throw new \Exception("Insufficient stock for {$color->name} - {$size->name}. Available: " . number_format($current_stock, 3) . " KG.");
                }
            }

            // 2. CREATE PACKING SLIP (SILENTLY)
            $lastSlip = PackingSlip::latest('id')->first();
            $nextId = $lastSlip ? $lastSlip->id + 1 : 1;
            $packing_slip_no = 'PSC-' . $nextId;

            $packing_slip = new PackingSlip();
            $packing_slip->user_id = Auth::id();
            $packing_slip->packing_slip_no = $packing_slip_no;
            $packing_slip->total_weight = $total_weight;
            $packing_slip->pending_weight = 0;
            $packing_slip->dispatch_weight = $total_weight;
            $packing_slip->total_bags = $total_bags;
            $packing_slip->pending_bags = 0;
            $packing_slip->dispatch_bags = $total_bags;
            $packing_slip->packing_date = $request->date;
            $packing_slip->dispatch_date = $request->date;
            $packing_slip->complete_date = $request->date;
            $packing_slip->remarks = "Created via Common Packing Slip #{$packing_slip_no}";
            $packing_slip->status = 2; 
            $packing_slip->dispatch_by = Auth::id();
            
            PackingSlip::withoutEvents(fn() => $packing_slip->save());

            // 3. CREATE JOB CARD (SILENTLY)
            $jobCard = new JobCard();
            $jobCard->user_id = Auth::id();
            $jobCard->job_type = 'Common';
            $jobCard->name_of_job = "Common Packing - " . $packing_slip->packing_slip_no;
            $jobCard->no_of_pieces = (int)$total_weight;
            $jobCard->actual_pieces = $total_weight;
            $jobCard->job_card_date = $request->date;
            $jobCard->dispatch_date = $request->date;
            $jobCard->customer_agent_id = $request->customer_agent_id;
            $jobCard->status = 'Account Pending';
            $jobCard->job_card_process = 'Account Pending';
            $jobCard->is_editable = 1;
            
            JobCard::withoutEvents(fn() => $jobCard->save());

            $packing_slip->job_card_id = $jobCard->id;
            PackingSlip::withoutEvents(fn() => $packing_slip->save());

            // 4. PROCESS GROUPS (Stock Out + Details Linking)
            $itemsSummary = [];
            foreach ($groupedItems as $key => $bags) {
                $firstBag = $bags->first();
                $size = SizeMaster::find($firstBag['size_id']);
                $color = ColorMaster::find($firstBag['color_id']);
                $group_weight = $bags->sum('weight');

                $itemsSummary[] = [
                    'size' => $size->name,
                    'color' => $color->name,
                    'weight' => $group_weight,
                    'bags' => count($bags)
                ];

                // DEDUCT STOCK (SILENTLY)
                CommonManageStock::withoutEvents(fn() => CommonManageStock::create([
                    'user_id' => Auth::id(),
                    'date' => $request->date,
                    'in_out' => 'Out',
                    'color_id' => $color->id,
                    'size_id' => $size->id,
                    'quantity' => $group_weight,
                    'remarks' => "Stock Out (PSC-{$nextId})",
                    'from' => 'Packing Slip',
                    'from_id' => $packing_slip->id
                ]));

                foreach ($bags as $bag) {
                    $pd = new PackingDetail();
                    $pd->packing_slip_id = $packing_slip->id;
                    $pd->job_card_id = $jobCard->id;
                    $pd->size_id = $bag['size_id'];
                    $pd->color_id = $bag['color_id'];
                    $pd->weight = $bag['weight'];
                    $pd->status = 2; // Complete
                    PackingDetail::withoutEvents(fn() => $pd->save());
                }
            }

            // 5. ONE UNIFIED LOG
            activity('PackingSlip')
                ->performedOn($packing_slip)
                ->causedBy(Auth::user())
                ->withProperties([
                    'context' => [
                        'ps_no' => $packing_slip_no,
                        'job_name' => $jobCard->name_of_job,
                        'total_weight' => $total_weight,
                        'total_bags' => $total_bags,
                        'items' => $itemsSummary,
                        'stock_out' => true,
                        'job_card_created' => true,
                        'status' => 'Account Pending',
                        'event_type' => 'common_created'
                    ]
                ])
                ->log("Packing Slip Ref: {$packing_slip_no} (Stock Out & Account Pending Created)");

            DB::commit();
            return response()->json(['result' => 1, 'message' => 'Common Packing Slip Saved Successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }



    public function edit($id)
    {
        if (!\App\Helpers\PermissionHelper::check('packing_slip_common', 'edit')) {
            abort(403, 'Access Denied! You do not have permission to edit packing slips.');
        }
        $packing_slip = PackingSlip::with(['job_card.customer_agent', 'packing_details.job_card.customer_agent'])->find($id);
        if (!$packing_slip) {
            return redirect()->route('packing_slip_common.index')->with('error', 'Packing Slip not found');
        }

        $customers = AgentCustomer::where('status', 1);
        $customers = auth()->user()->applyDataRestriction($customers, 'sale_executive_id', 'packing_slip_common');
        $customers = $customers->orderBy('name', 'asc')->get();
        $colors = ColorMaster::where('status', 1)->orderBy('name')->get();
        $sizes = SizeMaster::where('status', 1)->orderBy('name')->get();

        // Get the customer ID from the packing slip's job card or first detail's job card
        $selectedCustomerId = $packing_slip->job_card_id ? ($packing_slip->job_card->customer_agent_id ?? null) : null;
        
        if (!$selectedCustomerId && $packing_slip->packing_details->isNotEmpty()) {
            $firstDetail = $packing_slip->packing_details->first();
            $selectedCustomerId = $firstDetail->job_card->customer_agent_id ?? null;
        }

        return view('admin.packing_slip.common_form', compact('packing_slip', 'customers', 'colors', 'sizes', 'selectedCustomerId'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_agent_id' => 'required',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.size_id' => 'required',
            'items.*.color_id' => 'required',
            'items.*.weight' => 'required|numeric|min:0.001',
        ]);

        $packing_slip = PackingSlip::find($id);
        if (!$packing_slip) {
            return response()->json(['result' => 0, 'message' => 'Packing Slip not found']);
        }

        $oldValues = [
            'total_weight' => $packing_slip->total_weight,
            'total_bags' => $packing_slip->total_bags,
            'remarks' => $packing_slip->remarks,
            'items' => PackingDetail::where('packing_slip_id', $id)->with(['size', 'color'])->get()->map(function($d) {
                return [
                    'size' => $d->size->name ?? 'N/A',
                    'color' => $d->color->name ?? 'N/A',
                    'weight' => $d->weight
                ];
            })->toArray()
        ];

        DB::beginTransaction();
        try {
            // 1. REVERT OLD STOCK AND DELETE OLD DATA (SILENTLY)
            $jobCardIds = PackingDetail::where('packing_slip_id', $id)->pluck('job_card_id')->unique();
            
            CommonManageStock::withoutEvents(fn() => CommonManageStock::where('from', 'Packing Slip')->where('from_id', $id)->delete());
            PackingDetail::withoutEvents(fn() => PackingDetail::where('packing_slip_id', $id)->delete());
            JobCardProcess::withoutEvents(fn() => JobCardProcess::whereIn('job_card_id', $jobCardIds)->delete());
            JobCard::withoutEvents(fn() => JobCard::whereIn('id', $jobCardIds)->delete());

            // 2. NEW DATA PREP
            $total_weight = collect($request->items)->sum('weight');
            $total_bags = count($request->items);
            $groupedItems = collect($request->items)->groupBy(fn($i) => $i['size_id'] . '-' . $i['color_id']);

            // 3. STOCK VALIDATION
            foreach ($groupedItems as $key => $bags) {
                $firstBag = $bags->first();
                $size = SizeMaster::find($firstBag['size_id']);
                $color = ColorMaster::find($firstBag['color_id']);
                $required_weight = $bags->sum('weight');

                $current_stock = CommonManageStock::where('color_id', $color->id)
                    ->where('size_id', $size->id)
                    ->selectRaw("SUM(CASE WHEN in_out = 'In' THEN quantity ELSE -quantity END) as total")
                    ->first()->total ?? 0;

                if ($required_weight > $current_stock) {
                    throw new \Exception("Insufficient stock for {$color->name} - {$size->name}. Available: " . number_format($current_stock, 3) . " KG.");
                }
            }

            // 4. UPDATE PACKING SLIP (SILENTLY)
            $packing_slip->total_weight = $total_weight;
            $packing_slip->dispatch_weight = $total_weight;
            $packing_slip->total_bags = $total_bags;
            $packing_slip->dispatch_bags = $total_bags;
            $packing_slip->packing_date = $request->date;
            $packing_slip->dispatch_date = $request->date;
            $packing_slip->complete_date = $request->date;
            
            PackingSlip::withoutEvents(fn() => $packing_slip->save());

            // 5. CREATE SINGLE JOB CARD (SILENTLY)
            $jobCard = new JobCard();
            $jobCard->user_id = Auth::id();
            $jobCard->job_type = 'Common';
            $jobCard->name_of_job = "Common Packing - " . $packing_slip->packing_slip_no;
            $jobCard->no_of_pieces = (int)$total_weight;
            $jobCard->actual_pieces = $total_weight;
            $jobCard->job_card_date = $request->date;
            $jobCard->dispatch_date = $request->date;
            $jobCard->customer_agent_id = $request->customer_agent_id;
            $jobCard->status = 'Account Pending';
            $jobCard->job_card_process = 'Account Pending';
            $jobCard->is_editable = 1;
            JobCard::withoutEvents(fn() => $jobCard->save());

            $packing_slip->job_card_id = $jobCard->id;
            PackingSlip::withoutEvents(fn() => $packing_slip->save());

            // 6. PROCESS GROUPS (Stock Out + Details Linking)
            $itemsSummary = [];
            foreach ($groupedItems as $key => $bags) {
                $firstBag = $bags->first();
                $size = SizeMaster::find($firstBag['size_id']);
                $color = ColorMaster::find($firstBag['color_id']);
                $group_weight = $bags->sum('weight');

                $itemsSummary[] = [
                    'size' => $size->name,
                    'color' => $color->name,
                    'weight' => $group_weight,
                    'bags' => count($bags)
                ];

                CommonManageStock::withoutEvents(fn() => CommonManageStock::create([
                    'user_id' => Auth::id(),
                    'date' => $request->date,
                    'in_out' => 'Out',
                    'color_id' => $color->id,
                    'size_id' => $size->id,
                    'quantity' => $group_weight,
                    'remarks' => "Stock Updated (PSC-{$packing_slip->id})",
                    'from' => 'Packing Slip',
                    'from_id' => $packing_slip->id
                ]));

                foreach ($bags as $bag) {
                    $pd = new PackingDetail();
                    $pd->packing_slip_id = $packing_slip->id;
                    $pd->job_card_id = $jobCard->id;
                    $pd->size_id = $bag['size_id'];
                    $pd->color_id = $bag['color_id'];
                    $pd->weight = $bag['weight'];
                    $pd->status = 2;
                    PackingDetail::withoutEvents(fn() => $pd->save());
                }
            }

            // 7. CAPTURE CHANGES AND LOG ONE UNIFIED 'UPDATED' EVENT
            $newValues = [
                'total_weight' => $total_weight,
                'total_bags' => $total_bags,
                'remarks' => $packing_slip->remarks,
                'items' => $itemsSummary
            ];

            activity('PackingSlip')
                ->performedOn($packing_slip)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old' => $oldValues,
                    'attributes' => $newValues,
                    'context' => [
                        'ps_no' => $packing_slip->packing_slip_no,
                        'job_name' => $jobCard->name_of_job,
                        'total_weight' => $total_weight,
                        'total_bags' => $total_bags,
                        'items' => $itemsSummary,
                        'stock_out' => true,
                        'job_card_created' => true,
                        'status' => 'Account Pending',
                        'event_type' => 'common_updated' // Use high-detail view
                    ]
                ])
                ->event('updated')
                ->log("Packing Slip Ref: {$packing_slip->packing_slip_no} Updated");

            DB::commit();
            return response()->json(['result' => 1, 'message' => 'Common Packing Slip Updated Successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }



    public function delete($id)
    {
        if (auth()->user()->role_as != 'Admin') {
            return response()->json(['result' => -1, 'message' => 'Access Denied! Only Admin can delete packing slips.']);
        }
        $packing_slip = PackingSlip::find($id);
        if (!$packing_slip) {
            return response()->json(['result' => 0, 'message' => 'Packing Slip not found']);
        }

        DB::beginTransaction();
        try {
            $jobCardIds = PackingDetail::where('packing_slip_id', $id)->pluck('job_card_id')->unique();
            
            // Delete Stock Records (This reverts the stock out)
            CommonManageStock::where('from', 'Packing Slip')->where('from_id', $id)->delete();
            
            // Delete Packing Details
            PackingDetail::where('packing_slip_id', $id)->delete();
            
            // Delete Job Card Processes
            JobCardProcess::whereIn('job_card_id', $jobCardIds)->delete();
            
            // Delete Job Cards
            JobCard::whereIn('id', $jobCardIds)->delete();

            // CAPTURE CONTEXT BEFORE DELETE FOR AUDIT LOG
            $itemsSummary = PackingDetail::where('packing_slip_id', $id)->with(['size', 'color'])->get()->map(function($d) {
                return [
                    'size' => $d->size->name ?? 'N/A',
                    'color' => $d->color->name ?? 'N/A',
                    'weight' => $d->weight
                ];
            })->toArray();
            
            $jobCard = JobCard::find($jobCardIds->first());

            activity('PackingSlip')
                ->performedOn($packing_slip)
                ->causedBy(Auth::user())
                ->withProperties([
                    'context' => [
                        'ps_no' => $packing_slip->packing_slip_no,
                        'job_name' => $jobCard->name_of_job ?? 'N/A',
                        'total_weight' => $packing_slip->total_weight,
                        'total_bags' => $packing_slip->total_bags,
                        'items' => $itemsSummary,
                        'event_type' => 'common_deleted'
                    ]
                ])
                ->event('deleted')
                ->log("Packing Slip Ref: {$packing_slip->packing_slip_no} Deleted");

            // Finally Delete Packing Slip
            $packing_slip->delete();

            DB::commit();
            return response()->json(['result' => 1, 'message' => 'Packing Slip Deleted Successfully. Stock reverted.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
