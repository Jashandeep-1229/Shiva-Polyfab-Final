<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\AgentCustomer;
use App\Models\JobCard;
use App\Models\CustomerLedger;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;

class BillController extends Controller
{
    public function index()
    {
        if (!PermissionHelper::check('bill_management')) {
            abort(403, 'Unauthorized access to Bill Management.');
        }
        $customers = AgentCustomer::where('status', 1)->get();
        return view('admin.bill.index', compact('customers'));
    }

    public function datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $query = Bill::with(['customer', 'items', 'creator'])->latest('id');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('bill_no', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', function($cq) use ($request) {
                      $cq->where('name', 'like', "%{$request->search}%");
                  });
            });
        }
        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->from_date) {
            $query->whereDate('bill_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('bill_date', '<=', $request->to_date);
        }

        if ($request->due_status) {
            $today = now()->toDateString();
            if ($request->due_status == 'overdue') {
                $query->whereDate('due_date', '<', $today);
            } elseif ($request->due_status == 'due_7') {
                $query->whereDate('due_date', '>=', $today)
                      ->whereDate('due_date', '<=', now()->addDays(7)->toDateString());
            }
        }

        $bills = $query->paginate($number);
        return view('admin.bill.datatable', compact('bills'));
    }

    public function create()
    {
        if (!PermissionHelper::check('bill_management', 'add')) {
            abort(403, 'Unauthorized. Cannot add bills.');
        }
        $customers = AgentCustomer::where('status', 1)->orderBy('name', 'asc')->get();
        return view('admin.bill.create', compact('customers'));
    }

    public function store(Request $request)
    {
        if (!PermissionHelper::check('bill_management', 'add')) {
            return response()->json(['result' => -1, 'message' => 'Unauthorized']);
        }

        $request->validate([
            'bill_no' => 'required',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'customer_id' => 'required|exists:agent_customers,id',
            'items' => 'required|array'
        ]);

        $bill = new Bill();
        $bill->bill_no = $request->bill_no;
        $bill->bill_date = $request->bill_date;
        $bill->due_date = $request->due_days ? Carbon::parse($request->bill_date)->addDays($request->due_days)->setTime(12, 0, 0) : null;
        $bill->customer_id = $request->customer_id;
        $bill->remarks = $request->remarks;
        $bill->created_by = Auth::id();
        $bill->save();

        $total_amount = 0;
        $total_gst = 0;
        $grand_total = 0;

        foreach ($request->items as $item) {
            if (!empty($item['description']) && ($item['qty'] > 0 || $item['amount'] > 0)) {
                $qty = floatval($item['qty'] ?? 0);
                $rate = floatval($item['rate'] ?? 0);
                $gst_perc = floatval($item['gst_percent'] ?? 0);
                
                $amount = round($qty * $rate, 2);
                $gst_amount = round($amount * ($gst_perc / 100), 2);
                $row_total = $amount + $gst_amount;

                BillItem::create([
                    'bill_id' => $bill->id,
                    'description' => $item['description'],
                    'hsn_code' => $item['hsn_code'] ?? null,
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

        $bill->total_amount = $total_amount;
        $bill->taxable_amount = $total_amount;
        $bill->igst_amount = $total_gst;
        $bill->grand_total = $grand_total;
        $bill->save();

        CustomerLedger::create([
            'customer_id' => $bill->customer_id,
            'job_card_id' => $bill->job_card_id,
            'bill_id' => $bill->id,
            'transaction_date' => $bill->bill_date,
            'amount' => $total_amount,
            'gst' => $total_gst,
            'total_amount' => $grand_total,
            'extra_charge_amount' => 0,
            'extra_charge_gst' => 0, 
            'extra_total_amount' => 0,
            'grand_total_amount' => $grand_total,
            'dr_cr' => 'Dr',
            'remarks' => "Bill #{$bill->bill_no}",
            'software_remarks' => "Bill No: {$bill->bill_no} | Grand Total: {$grand_total}",
            'user_id' => Auth::id()
        ]);

        return response()->json(['result' => 1, 'message' => 'Bill created successfully', 'url' => route('bill.index')]);
    }

    public function edit($id)
    {
        if (!PermissionHelper::check('bill_management', 'edit')) {
            abort(403, 'Unauthorized. Cannot edit bills.');
        }
        $bill = Bill::with('items')->findOrFail($id);
        if (!empty($bill->job_card_id)) {
            abort(403, 'System generated bills from Job Cards cannot be edited manually.');
        }
        $customers = AgentCustomer::where('status', 1)->orderBy('name', 'asc')->get();
        return view('admin.bill.edit', compact('bill', 'customers'));
    }

    public function update(Request $request, $id)
    {
        if (!PermissionHelper::check('bill_management', 'edit')) {
            return response()->json(['result' => -1, 'message' => 'Unauthorized']);
        }

        $bill = Bill::findOrFail($id);
        if (!empty($bill->job_card_id)) {
            return response()->json(['result' => -1, 'message' => 'System generated bills cannot be edited.']);
        }

        $request->validate([
            'bill_no' => 'required',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'customer_id' => 'required|exists:agent_customers,id',
            'items' => 'required|array'
        ]);

        $total_amount = 0;
        $total_gst = 0;
        $grand_total = 0;

        $bill->items()->delete();

        foreach ($request->items as $item) {
            if (!empty($item['description']) && ($item['qty'] > 0 || $item['amount'] > 0)) {
                $qty = floatval($item['qty'] ?? 0);
                $rate = floatval($item['rate'] ?? 0);
                $gst_perc = floatval($item['gst_percent'] ?? 0);
                
                $amount = round($qty * $rate, 2);
                $gst_amount = round($amount * ($gst_perc / 100), 2);
                $row_total = $amount + $gst_amount;

                BillItem::create([
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

        $bill->bill_no = $request->bill_no;
        $bill->bill_date = $request->bill_date;
        $bill->due_date = $request->due_days ? Carbon::parse($request->bill_date)->addDays($request->due_days)->setTime(12, 0, 0) : null;
        $bill->customer_id = $request->customer_id;
        $bill->remarks = $request->remarks;
        $bill->total_amount = $total_amount;
        $bill->taxable_amount = $total_amount;
        $bill->igst_amount = $total_gst;
        $bill->grand_total = $grand_total;
        $bill->save();

        // Update ledger logic
        $ledger = CustomerLedger::where('bill_id', $bill->id)
                    ->orWhere('remarks', "Manual Bill #{$bill->bill_no}")
                    ->orWhere('remarks', "Bill #{$bill->bill_no}")
                    ->first();
        if ($ledger) {
            $ledger->update([
                'customer_id' => $bill->customer_id,
                'bill_id' => $bill->id,
                'transaction_date' => $bill->bill_date,
                'amount' => $total_amount,
                'gst' => $total_gst,
                'total_amount' => $grand_total,
                'grand_total_amount' => $grand_total,
                'remarks' => "Bill #{$bill->bill_no}",
                'software_remarks' => "Bill No: {$bill->bill_no} | Grand Total: {$grand_total}",
            ]);
        }

        return response()->json(['result' => 1, 'message' => 'Bill updated successfully', 'url' => route('bill.index')]);
    }

    public function show($id)
    {
        $bill = Bill::with(['customer', 'items'])->findOrFail($id);

        $pdf = PDF::loadView('admin.bill.pdf', compact('bill'));
        return $pdf->stream('Bill_' . $bill->bill_no . '.pdf');
    }

    public function destroy($id)
    {
        if (Auth::user()->role_as != 'Admin') {
            return response()->json(['result' => -1, 'message' => 'Access Denied! Only Admin can delete records.']);
        }
        $bill = Bill::find($id);
        if ($bill) {
            $bill->items()->delete();
            $bill->delete();
            return response()->json(['result' => 1, 'message' => 'Bill deleted successfully']);
        }
        return response()->json(['result' => -1, 'message' => 'Bill not found']);
    }
}
