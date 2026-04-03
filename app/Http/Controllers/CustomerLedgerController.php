<?php

namespace App\Http\Controllers;

use App\Models\CustomerLedger;
use App\Models\AgentCustomer;
use App\Models\PaymentMethod;
use App\Models\JobCard;
use App\Models\LedgerFollowup;
use Illuminate\Http\Request;
use App\Models\LedgerFollowupHistory;
use App\Models\CustomerLedgerLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Exception;
use Auth;
use DB;
use App\Helpers\PermissionHelper;
use PDF;
use Excel;
use App\Exports\CustomerLedgerSummaryExport;

class CustomerLedgerController extends Controller
{
    public function index()
    {
        if (!PermissionHelper::check('customer_ledger')) {
            abort(403, 'Unauthorized access to Customer Ledger.');
        }
        $customers = AgentCustomer::where('status', 1);
        $customers = auth()->user()->applyDataRestriction($customers, 'sale_executive_id', 'customer_ledger');
        $customers = $customers->orderBy('name', 'asc')->get();
        
        $payment_methods = PaymentMethod::where('status', 1)->get();
        
        $executives_query = User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive']);
        if (auth()->user()->role_as != 'Admin' && PermissionHelper::accessMode('customer_ledger') != 'all') {
            $managedIds = auth()->user()->getManagedUserIds();
            $executives_query->whereIn('id', array_merge($managedIds, [auth()->id()]));
        }
        $sale_executives = $executives_query->get();
        $roles = AgentCustomer::distinct()->pluck('role');
        return view('admin.customer_ledger.index', compact('customers', 'payment_methods', 'sale_executives', 'roles'));
    }

    public function datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $query = AgentCustomer::with('sale_executive')->where('status', 1)->orderBy('name', 'asc');
        $query = auth()->user()->applyDataRestriction($query, 'sale_executive_id', 'customer_ledger');
        
        // Filter: conditionally show customers where cumulative net balance across all time is NOT 0
        if($request->balance_type != 'all') {
            $query->whereIn('id', function($sub) {
                $sub->select('customer_id')
                    ->from('customer_ledgers')
                    ->whereNull('deleted_at')
                    ->groupBy('customer_id')
                    ->havingRaw("ROUND(SUM(CASE WHEN dr_cr = 'Dr' THEN grand_total_amount ELSE -grand_total_amount END), 2) != 0");
            });
        }

        if($request->search){
            $query->where(function($q) use ($request){
                $q->where('name','like','%'.$request->search.'%')
                  ->orWhere('phone_no','like','%'.$request->search.'%')
                  ->orWhere('code','like','%'.$request->search.'%');
            });
        }

        if($request->role){
            $query->where('role', $request->role);
        }

        if(isset($request->is_bad_debt) && $request->is_bad_debt !== 'all'){
            $query->where('is_bad_debt', $request->is_bad_debt);
        }

        if($request->sale_executive_id){
            $actor = \App\Models\User::find($request->sale_executive_id);
            if ($actor) {
                $ids = $actor->getPermittedUserIds('customer_ledger');
                $query->whereIn('sale_executive_id', $ids);
            } else {
                $query->where('sale_executive_id', $request->sale_executive_id);
            }
        }
        
        $customers = $query->paginate($number);
        
        foreach($customers as $customer) {
            // Opening Balance: Total before from_date
            $obQuery = CustomerLedger::where('customer_id', $customer->id);
            if($request->from_date) {
                $obQuery->where('transaction_date', '<', $request->from_date);
            } else {
                // If no from date, opening is 0
                $obQuery->whereRaw('1=0'); 
            }
            $customer->opening_balance = $obQuery->select(DB::raw("SUM(CASE WHEN dr_cr = 'Dr' THEN grand_total_amount ELSE -grand_total_amount END) as balance"))
                ->first()->balance ?? 0;

            // Period Totals: Between from_date and to_date
            $ledgerQuery = CustomerLedger::where('customer_id', $customer->id);
            if($request->from_date) {
                $ledgerQuery->where('transaction_date', '>=', $request->from_date);
            }
            if($request->to_date) {
                $ledgerQuery->where('transaction_date', '<=', $request->to_date);
            }

            $customer->total_due = (clone $ledgerQuery)->where('dr_cr', 'Dr')->sum('grand_total_amount');
            $customer->total_recd = (clone $ledgerQuery)->where('dr_cr', 'Cr')->sum('grand_total_amount');
            
            // Net Balance = Opening + Period Dues - Period Receipts
            $customer->balance = $customer->opening_balance + $customer->total_due - $customer->total_recd;
        }

        $from_date = $request->from_date;
        $to_date = $request->to_date;

        return view('admin.customer_ledger.datatable', compact('customers', 'from_date', 'to_date'));
    }

    public function export_pdf(Request $request)
    {
        $query = AgentCustomer::with('sale_executive')->where('status', 1)->orderBy('name', 'asc');
        $query = auth()->user()->applyDataRestriction($query, 'sale_executive_id', 'customer_ledger');
        
        if($request->balance_type != 'all') {
            $query->whereIn('id', function($sub) {
                $sub->select('customer_id')
                    ->from('customer_ledgers')
                    ->whereNull('deleted_at')
                    ->groupBy('customer_id')
                    ->havingRaw("ROUND(SUM(CASE WHEN dr_cr = 'Dr' THEN grand_total_amount ELSE -grand_total_amount END), 2) != 0");
            });
        }

        if($request->search){
            $query->where(function($q) use ($request){
                $q->where('name','like','%'.$request->search.'%')
                  ->orWhere('phone_no','like','%'.$request->search.'%')
                  ->orWhere('code','like','%'.$request->search.'%');
            });
        }

        if($request->role){
            $query->where('role', $request->role);
        }

        if(isset($request->is_bad_debt) && $request->is_bad_debt !== 'all'){
            $query->where('is_bad_debt', $request->is_bad_debt);
        }

        if($request->sale_executive_id){
            $actor = \App\Models\User::find($request->sale_executive_id);
            if ($actor) {
                $ids = $actor->getPermittedUserIds('customer_ledger');
                $query->whereIn('sale_executive_id', $ids);
            } else {
                $query->where('sale_executive_id', $request->sale_executive_id);
            }
        }
        
        $customers = $query->get();
        
        foreach($customers as $customer) {
            $obQuery = CustomerLedger::where('customer_id', $customer->id);
            if($request->from_date) {
                $obQuery->where('transaction_date', '<', $request->from_date);
            } else {
                $obQuery->whereRaw('1=0'); 
            }
            $customer->opening_balance = $obQuery->select(DB::raw("SUM(CASE WHEN dr_cr = 'Dr' THEN grand_total_amount ELSE -grand_total_amount END) as balance"))
                ->first()->balance ?? 0;

            $ledgerQuery = CustomerLedger::where('customer_id', $customer->id);
            if($request->from_date) {
                $ledgerQuery->where('transaction_date', '>=', $request->from_date);
            }
            if($request->to_date) {
                $ledgerQuery->where('transaction_date', '<=', $request->to_date);
            }

            $customer->total_due = (clone $ledgerQuery)->where('dr_cr', 'Dr')->sum('grand_total_amount');
            $customer->total_recd = (clone $ledgerQuery)->where('dr_cr', 'Cr')->sum('grand_total_amount');
            $customer->balance = $customer->opening_balance + $customer->total_due - $customer->total_recd;
        }

        $from_date = $request->from_date;
        $to_date = $request->to_date;

        $pdf = PDF::loadView('admin.customer_ledger.export_pdf', compact('customers', 'from_date', 'to_date'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Customer_Ledger_Summary_'.date('Y-m-d').'.pdf');
    }

    public function export_excel(Request $request)
    {
        $query = AgentCustomer::with('sale_executive')->where('status', 1)->orderBy('name', 'asc');
        $query = auth()->user()->applyDataRestriction($query, 'sale_executive_id', 'customer_ledger');
        
        if($request->balance_type != 'all') {
            $query->whereIn('id', function($sub) {
                $sub->select('customer_id')
                    ->from('customer_ledgers')
                    ->whereNull('deleted_at')
                    ->groupBy('customer_id')
                    ->havingRaw("ROUND(SUM(CASE WHEN dr_cr = 'Dr' THEN grand_total_amount ELSE -grand_total_amount END), 2) != 0");
            });
        }

        if($request->search){
            $query->where(function($q) use ($request){
                $q->where('name','like','%'.$request->search.'%')
                  ->orWhere('phone_no','like','%'.$request->search.'%')
                  ->orWhere('code','like','%'.$request->search.'%');
            });
        }

        if($request->role){
            $query->where('role', $request->role);
        }

        if(isset($request->is_bad_debt) && $request->is_bad_debt !== 'all'){
            $query->where('is_bad_debt', $request->is_bad_debt);
        }

        if($request->sale_executive_id){
            $actor = \App\Models\User::find($request->sale_executive_id);
            if ($actor) {
                $ids = $actor->getPermittedUserIds('customer_ledger');
                $query->whereIn('sale_executive_id', $ids);
            } else {
                $query->where('sale_executive_id', $request->sale_executive_id);
            }
        }
        
        $customers = $query->get();
        
        foreach($customers as $customer) {
            $obQuery = CustomerLedger::where('customer_id', $customer->id);
            if($request->from_date) {
                $obQuery->where('transaction_date', '<', $request->from_date);
            } else {
                $obQuery->whereRaw('1=0'); 
            }
            $customer->opening_balance = $obQuery->select(DB::raw("SUM(CASE WHEN dr_cr = 'Dr' THEN grand_total_amount ELSE -grand_total_amount END) as balance"))
                ->first()->balance ?? 0;

            $ledgerQuery = CustomerLedger::where('customer_id', $customer->id);
            if($request->from_date) {
                $ledgerQuery->where('transaction_date', '>=', $request->from_date);
            }
            if($request->to_date) {
                $ledgerQuery->where('transaction_date', '<=', $request->to_date);
            }

            $customer->total_due = (clone $ledgerQuery)->where('dr_cr', 'Dr')->sum('grand_total_amount');
            $customer->total_recd = (clone $ledgerQuery)->where('dr_cr', 'Cr')->sum('grand_total_amount');
            $customer->balance = $customer->opening_balance + $customer->total_due - $customer->total_recd;
        }

        return Excel::download(new CustomerLedgerSummaryExport($customers, $request->from_date, $request->to_date), 'Customer_Ledger_Summary_'.date('Y-m-d').'.xlsx');
    }

    public function view_ledger(Request $request, $id)
    {
        $customer = AgentCustomer::find($id);
        if (!$customer) return redirect()->back()->with('error', 'Customer not found');
        
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $payment_methods = PaymentMethod::where('status', 1)->get();

        $followup_query = LedgerFollowup::with(['histories', 'user'])
            ->where('customer_id', $id)
            ->orderBy('id', 'desc');
        
        if (auth()->user()->role_as != 'Admin') {
            $followup_query->where('user_id', auth()->id());
        }
        $followups = $followup_query->get();

        return view('admin.customer_ledger.detail', compact('customer', 'from_date', 'to_date', 'payment_methods', 'followups'));
    }

    public function detail_datatable(Request $request, $id)
    {
        $number = $request->value ?? 50;
        $query = CustomerLedger::with(['job_card', 'packing_slip', 'payment_method', 'bill', 'job_card.bill'])
            ->where('customer_id', $id);

        if($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('remarks', 'like', '%' . $request->search . '%')
                  ->orWhereHas('job_card', function($sq) use ($request) {
                      $sq->where('name_of_job', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if($request->from_date) {
            $query->where('transaction_date', '>=', $request->from_date);
        }
        if($request->to_date) {
            $query->where('transaction_date', '<=', $request->to_date);
        }

        if($request->type) {
            if($request->type == 'BadDebt') {
                $query->where('is_bad_debt', 1);
            } else {
                $query->where('dr_cr', $request->type)->where('is_bad_debt', 0);
            }
        }

        $query->orderBy('transaction_date', 'asc')->orderBy('id', 'asc');
            
        $ledger = $query->paginate($number);
        
        // Calculate Opening Balance (All records before the from_date or before the first record of this page)
        $opening_balance = 0;
        $firstItem = $ledger->first();
        
        $obQuery = CustomerLedger::where('customer_id', $id);
        if ($request->from_date) {
            // If from_date is set, opening balance is everything before from_date
            $obQuery->where('transaction_date', '<', $request->from_date);
        } else if ($firstItem) {
            // Otherwise it's everything before this page's first item
            $obQuery->where(function($q) use ($firstItem) {
                $q->where('transaction_date', '<', $firstItem->transaction_date)
                  ->orWhere(function($sq) use ($firstItem) {
                      $sq->where('transaction_date', '=', $firstItem->transaction_date)
                         ->where('id', '<', $firstItem->id);
                  });
            });
        }
        
        $opening_balance = $obQuery->select(DB::raw("SUM(CASE WHEN dr_cr = 'Dr' THEN grand_total_amount ELSE -grand_total_amount END) as balance"))
            ->first()->balance ?? 0;

        $payment_methods = PaymentMethod::where('status', 1)->get();

        return view('admin.customer_ledger.detail_datatable', compact('ledger', 'opening_balance', 'payment_methods'));
    }

    public function store_payment(Request $request)
    {
        if (!PermissionHelper::check('customer_ledger', 'edit')) {
            return response()->json(['result' => -1, 'message' => 'Access Denied! You do not have permission to update ledgers.']);
        }
        $request->validate([
            'customer_id' => 'required',
            'amount' => 'required|numeric|min:0.001',
            'date' => 'required|date',
            'type' => 'required|in:Dr,Cr',
            'payment_method_id' => 'required_if:type,Cr',
            'remarks' => 'required'
        ]);

        $payment_method_id = ($request->type == 'Cr') ? $request->payment_method_id : null;

        $oldData = null;
        if($request->id) {
            $oldLedger = CustomerLedger::find($request->id);
            if($oldLedger) $oldData = $oldLedger->toJson();
        }

        $ledgerEntry = CustomerLedger::updateOrCreate(
            ['id' => $request->id],
            [
                'customer_id' => $request->customer_id,
                'transaction_date' => $request->date,
                'grand_total_amount' => $request->amount,
                'dr_cr' => $request->is_bad_debt ? 'Cr' : $request->type,
                'is_bad_debt' => $request->is_bad_debt ? 1 : 0,
                'payment_method_id' => $payment_method_id,
                'remarks' => $request->remarks,
                'user_id' => Auth::id()
            ]
        );

        if($request->id && $oldData) {
            CustomerLedgerLog::create([
                'customer_ledger_id' => $ledgerEntry->id,
                'customer_id' => $ledgerEntry->customer_id,
                'action' => 'Edit',
                'old_data' => $oldData,
                'new_data' => $ledgerEntry->toJson(),
                'user_id' => Auth::id()
            ]);
        }

        return response()->json(['result' => 1, 'message' => 'Ledger Entry Saved Successfully']);
    }

    public function store_multi_payment(Request $request)
    {
        $request->validate([
            'dates' => 'required|array',
            'customer_ids' => 'required|array',
            'type' => 'required|in:Dr,Cr',
        ]);

        DB::transaction(function() use ($request) {
            foreach($request->dates as $key => $date) {
                if(!isset($request->customer_ids[$key])) continue;
                
                $is_bad_debt = $request->is_bad_debt[$key] ?? 0;
                $amount = $request->amounts[$key] ?? 0;
                $remarks = $request->remarks[$key] ?? null;

                if($is_bad_debt && $amount > 0) {
                    $remarks = ($remarks ? $remarks . ' ' : '') . '[AMOUNT: ' . $amount . ']';
                    $amount = 0; // Per user request: "remove amount just only text"
                }

                CustomerLedger::create([
                    'customer_id' => $request->customer_ids[$key],
                    'transaction_date' => $date,
                    'grand_total_amount' => $amount,
                    'dr_cr' => $is_bad_debt ? 'Cr' : $request->type,
                    'is_bad_debt' => $is_bad_debt,
                    'payment_method_id' => $request->payment_method_ids[$key] ?? null,
                    'remarks' => $remarks,
                    'user_id' => Auth::id()
                ]);

                if($is_bad_debt) {
                    AgentCustomer::where('id', $request->customer_ids[$key])->update(['is_bad_debt' => 1]);
                }
            }
        });

        return response()->json(['result' => 1, 'message' => 'Multiple Entries Saved Successfully']);
    }

    public function edit_modal($id)
    {
        $ledger = CustomerLedger::find($id);
        if(!$ledger || $ledger->job_card_id || $ledger->packing_slip_id) {
            return response()->json(['result' => 0, 'message' => 'Fixed entries cannot be edited.']);
        }
        $customers = AgentCustomer::where('status', 1);
        $customers = auth()->user()->applyDataRestriction($customers, 'sale_executive_id', 'customer_ledger');
        $customers = $customers->orderBy('name', 'asc')->get();
        
        $payment_methods = PaymentMethod::where('status', 1)->get();
        return view('admin.customer_ledger.edit_modal', compact('ledger', 'customers', 'payment_methods'));
    }

    public function delete($id)
    {
        if (!PermissionHelper::check('customer_ledger', 'delete')) {
            return response()->json(['result' => 0, 'message' => 'Access Denied! You do not have permission to delete entries.']);
        }
        // Even if permission is granted, we might want to keep the Admin-only restriction for extra safety if desired,
        // but PermissionHelper::check will return true for Admin anyway.
        
        $ledger = CustomerLedger::find($id);
        if(!$ledger || $ledger->job_card_id || $ledger->packing_slip_id) {
            return response()->json(['result' => 0, 'message' => 'Fixed entries cannot be deleted.']);
        }
        $oldData = $ledger->toJson();
        $ledgerId = $ledger->id;
        $customerId = $ledger->customer_id;

        $ledger->delete();

        CustomerLedgerLog::create([
            'customer_ledger_id' => $ledgerId,
            'customer_id' => $customerId,
            'action' => 'Delete',
            'old_data' => $oldData,
            'new_data' => null,
            'user_id' => Auth::id()
        ]);
        return response()->json(['result' => 1, 'message' => 'Entry deleted successfully.']);
    }

    public function transactions_index()
    {
        $customers = AgentCustomer::where('status', 1);
        $customers = auth()->user()->applyDataRestriction($customers, 'sale_executive_id', 'customer_ledger');
        $customers = $customers->orderBy('name', 'asc')->get();
        $executives_query = User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive']);
        if (auth()->user()->role_as != 'Admin' && PermissionHelper::accessMode('customer_ledger') != 'all') {
            $managedIds = auth()->user()->getManagedUserIds();
            $executives_query->whereIn('id', array_merge($managedIds, [auth()->id()]));
        }
        $sale_executives = $executives_query->get();
        $roles = AgentCustomer::distinct()->pluck('role');
        $payment_methods = PaymentMethod::where('status', 1)->get();
        return view('admin.customer_ledger.transactions', compact('customers', 'sale_executives', 'roles', 'payment_methods'));
    }

    public function transactions_datatable(Request $request)
    {
        $number = $request->value ?? 50;
        $query = CustomerLedger::with(['customer', 'customer.sale_executive', 'payment_method', 'bill', 'job_card.bill', 'packing_slip']);
        $query->whereHas('customer', function($q) {
            auth()->user()->applyDataRestriction($q, 'sale_executive_id', 'customer_ledger');
        });

        if($request->from_date) {
            $query->where('transaction_date', '>=', $request->from_date);
        }
        if($request->to_date) {
            $query->where('transaction_date', '<=', $request->to_date);
        }
        if($request->type) {
            if($request->type == 'BadDebt') {
                $query->where('is_bad_debt', 1);
            } else {
                $query->where('dr_cr', $request->type)->where('is_bad_debt', 0);
            }
        }
        if($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }
        if($request->payment_method_id) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if($request->role || $request->sale_executive_id) {
            $query->whereHas('customer', function($q) use ($request) {
                if($request->role) {
                    $q->where('role', $request->role);
                }
                if($request->sale_executive_id) {
                    $actor = \App\Models\User::find($request->sale_executive_id);
                    if ($actor) {
                        $ids = $actor->getPermittedUserIds('customer_ledger');
                        $q->whereIn('sale_executive_id', $ids);
                    } else {
                        $q->where('sale_executive_id', $request->sale_executive_id);
                    }
                }
            });
        }

        if($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('remarks', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($sq) use ($request) {
                      $sq->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('code', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $ledger = $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->paginate($number);
        
        return view('admin.customer_ledger.transactions_datatable', compact('ledger'));
    }

    public function report(Request $request)
    {
        if (!PermissionHelper::check('customer_ledger_report')) {
            abort(403, 'Unauthorized access to Ledger Reports.');
        }
        $executives_query = User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive']);
        if (auth()->user()->role_as != 'Admin' && PermissionHelper::accessMode('customer_ledger_report') != 'all') {
            $managedIds = auth()->user()->getManagedUserIds();
            $executives_query->whereIn('id', array_merge($managedIds, [auth()->id()]));
        }
        $executives = $executives_query->get();
        $customers = AgentCustomer::where('status', 1);
        $customers = auth()->user()->applyDataRestriction($customers, 'sale_executive_id', 'customer_ledger_report');
        $customers = $customers->orderBy('name', 'asc')->get();
        
        return view('admin.customer_ledger.report', compact('executives', 'customers'));
    }

    public function report_datatable(Request $request)
    {
        $query = AgentCustomer::with(['sale_executive'])->where('status', 1);
        $query = auth()->user()->applyDataRestriction($query, 'sale_executive_id', 'customer_ledger_report');

        if ($request->customer_id) {
            $query->where('id', $request->customer_id);
        }

        if ($request->sale_executive_id) {
            $actor = \App\Models\User::find($request->sale_executive_id);
            if ($actor) {
                $ids = $actor->getPermittedUserIds('customer_ledger_report');
                $query->whereIn('sale_executive_id', $ids);
            } else {
                $query->where('sale_executive_id', $request->sale_executive_id);
            }
        }

        try {
            $all_customers = $query->get();
            $customer_ids = $all_customers->pluck('id');

            $all_balances = CustomerLedger::whereIn('customer_id', $customer_ids)
                ->select('customer_id', 
                    DB::raw('SUM(CASE WHEN dr_cr = "Dr" THEN grand_total_amount ELSE 0 END) as total_dr'),
                    DB::raw('SUM(CASE WHEN dr_cr = "Cr" THEN grand_total_amount ELSE 0 END) as total_cr')
                )->groupBy('customer_id')->get()->keyBy('customer_id');

            $followup_query = LedgerFollowup::whereIn('customer_id', $customer_ids);
            if (auth()->user()->role_as != 'Admin') {
                $followup_query->where('user_id', auth()->id());
            }
            $all_followup_ids = $followup_query->pluck('id');
            if ($all_followup_ids->isNotEmpty()) {
                $all_remarks = LedgerFollowupHistory::with('followup')
                    ->whereIn('followup_id', $all_followup_ids)
                    ->orderBy('id', 'desc')
                    ->get()
                    ->groupBy('followup.customer_id');
            } else {
                $all_remarks = collect();
            }
            
            $active_followups_query = LedgerFollowup::whereIn('customer_id', $customer_ids)
                ->where('status', 'Pending');
            
            if (auth()->user()->role_as != 'Admin') {
                $active_followups_query->where('user_id', auth()->id());
            }

            $active_followups = $active_followups_query->with(['activeHistory' => function($q) {
                    $q->where('status', 1);
                }])
                ->get()
                ->keyBy('customer_id');

            // Pre-fetch last credit date for each customer
            $last_credits = CustomerLedger::whereIn('customer_id', $customer_ids)
                ->where('dr_cr', 'Cr')
                ->select('customer_id', DB::raw('MAX(transaction_date) as last_date'))
                ->groupBy('customer_id')
                ->get()
                ->keyBy('customer_id');

            // Pre-fetch all debits for FIFO calculation
            $all_debits = CustomerLedger::whereIn('customer_id', $customer_ids)
                ->where('dr_cr', 'Dr')
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->get()
                ->groupBy('customer_id');

            $report_data = [];
            $now = Carbon::now();

            foreach ($all_customers as $customer) {
                $balance_row = $all_balances->get($customer->id);
                $debits_sum = $balance_row ? $balance_row->total_dr : 0;
                $credits_sum = $balance_row ? $balance_row->total_cr : 0;
                $net_balance = round($debits_sum - $credits_sum, 2);

                $buckets = ['1-15' => 0, '16-30' => 0, '31-45' => 0, '45+' => 0];

                if ($net_balance > 0) {
                    $remaining = $net_balance;
                    $customer_debits = $all_debits->get($customer->id) ?? collect();

                    // Reference date for aging: max(now, last_credit_date)
                    $last_cr_row = $last_credits->get($customer->id);
                    $reference_date = $last_cr_row ? Carbon::parse($last_cr_row->last_date) : $now;
                    
                    if ($reference_date->lt($now)) {
                        $reference_date = $now;
                    }

                    foreach ($customer_debits as $dr) {
                        if ($remaining <= 0.001) break;
                        
                        $dr_amount = round($dr->grand_total_amount, 2);
                        $amount = min($remaining, $dr_amount);
                        $date = $dr->transaction_date ? Carbon::parse($dr->transaction_date) : null;
                        
                        if ($date) {
                            $days = $reference_date->diffInDays($date);
                            if ($days <= 15) $buckets['1-15'] += $amount;
                            elseif ($days <= 30) $buckets['16-30'] += $amount;
                            elseif ($days <= 45) $buckets['31-45'] += $amount;
                            else $buckets['45+'] += $amount;
                        } else {
                            $buckets['45+'] += $amount; 
                        }

                        $remaining = round($remaining - $amount, 2);
                    }
                }

                $customer_remarks = $all_remarks->get($customer->id);
                $remarks = $customer_remarks ? $customer_remarks->take(3)->map(function($r) {
                    return [
                        'text' => $r->remarks,
                        'date' => Carbon::parse($r->followup_date_time)->format('d-m-Y'),
                        'status' => $r->status == 1 ? 'Pending' : 'Completed'
                    ];
                })->toArray() : [];

                $active_fup = $active_followups->get($customer->id);
                $has_followup = $active_fup ? true : false;
                $can_close = false;
                $highlight_7_days = false;

                if ($active_fup && $active_fup->activeHistory) {
                    // Check for debit added after this specific followup started
                    $can_close = CustomerLedger::where('customer_id', $customer->id)
                        ->where('dr_cr', 'Dr')
                        ->where('created_at', '>', $active_fup->created_at)
                        ->exists();

                    $fup_date = Carbon::parse($active_fup->activeHistory->followup_date_time);
                    if ($fup_date->lte($now->copy()->addDays(7)->endOfDay())) {
                        $highlight_7_days = true;
                    }
                }

                $report_data[] = [
                    'customer' => $customer,
                    'net_balance' => $net_balance,
                    'buckets' => $buckets,
                    'remarks' => $remarks,
                    'has_followup' => $has_followup,
                    'active_followup_id' => $active_fup ? $active_fup->id : null,
                    'can_close' => $can_close,
                    'highlight_7_days' => $highlight_7_days
                ];
            }

            // Sorting & Filtering
            $balance_type = $request->balance_type ?? 'active';
            if ($balance_type == 'active') {
                $report_data = array_filter($report_data, fn($d) => abs($d['net_balance']) > 0.01);
            }

            if ($request->filter_by == 'top_amount') {
                usort($report_data, fn($a, $b) => $b['net_balance'] <=> $a['net_balance']);
            }

            if ($request->filter_by && $request->filter_by != 'top_amount') {
                $report_data = array_filter($report_data, function($d) use ($request) {
                    if ($request->filter_by == 'bad_debt') return $d['customer']->is_bad_debt == 1;
                    if ($request->filter_by == 'top_amount') return true;
                    if ($request->filter_by == '0-15') return $d['buckets']['1-15'] > 1;
                    if ($request->filter_by == '15-30') return $d['buckets']['16-30'] > 1;
                    if ($request->filter_by == '30-45') return $d['buckets']['31-45'] > 1;
                    if ($request->filter_by == '45+') return $d['buckets']['45+'] > 1;
                    if ($request->filter_by == 'advance') return $d['net_balance'] < -1;
                    if ($request->filter_by == '7_days_followup') return $d['highlight_7_days'] === true;
                    if ($request->filter_by == 'less_than_1_lakh') return $d['net_balance'] <= 100000 && $d['net_balance'] >= 0;
                    if ($request->filter_by == 'less_than_50k') return $d['net_balance'] <= 50000 && $d['net_balance'] >= 0;
                    return true;
                });
            }

            // Convert to Collection for pagination
            $report_data_collection = collect($report_data);
            
            // Manual pagination for the collection
            $perPage = 50;
            $currentPage = $request->get('page', 1);
            $pagedData = $report_data_collection->forPage($currentPage, $perPage);
            
            $paginated_report = new LengthAwarePaginator(
                $pagedData,
                $report_data_collection->count(),
                $perPage,
                $currentPage,
                ['path' => route('customer_ledger.report_datatable')]
            );

            return view('admin.customer_ledger.report_datatable', [
                'report_data' => $paginated_report,
                'summary' => [
                    'total_net' => $report_data_collection->sum('net_balance'),
                    'total_15' => $report_data_collection->sum(fn($d) => $d['buckets']['1-15']),
                    'total_30' => $report_data_collection->sum(fn($d) => $d['buckets']['16-30']),
                    'total_45' => $report_data_collection->sum(fn($d) => $d['buckets']['31-45']),
                    'total_plus' => $report_data_collection->sum(fn($d) => $d['buckets']['45+']),
                ]
            ]);
        } catch (Exception $e) {
            return '<div class="alert alert-danger">Report Error: ' . $e->getMessage() . '</div>';
        }
    }

    public function logs_index()
    {
        if(auth()->user()->role_as != 'Admin') return abort(403);
        $customers = AgentCustomer::where('status', 1)->get();
        return view('admin.customer_ledger.logs', compact('customers'));
    }

    public function logs_datatable(Request $request)
    {
        if(auth()->user()->role_as != 'Admin') return abort(403);
        $number = $request->value ?? 50;
        
        $query = CustomerLedgerLog::with(['user', 'customer']);

        if($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if($request->action) {
            $query->where('action', $request->action);
        }

        if($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->orderBy('id', 'desc')->paginate($number);
        
        return view('admin.customer_ledger.logs_datatable', compact('logs'));
    }

    public function export_individual_ledger_pdf(Request $request, $id)
    {
        $customer = AgentCustomer::find($id);
        if (!$customer) return redirect()->back()->with('error', 'Customer not found');

        $from_date = $request->from_date;
        $to_date = $request->to_date;

        // Calculate Opening Balance
        $obQuery = CustomerLedger::where('customer_id', $id);
        if ($from_date) {
            $obQuery->where('transaction_date', '<', $from_date);
        } else {
            // If no from date, opening is 0
            $obQuery->whereRaw('1=0'); 
        }
        $opening_balance = $obQuery->select(DB::raw("SUM(CASE WHEN dr_cr = 'Dr' THEN grand_total_amount ELSE -grand_total_amount END) as balance"))
            ->first()->balance ?? 0;

        // Current Period Transactions
        $query = CustomerLedger::with(['job_card', 'packing_slip', 'payment_method', 'bill', 'job_card.bill'])
            ->where('customer_id', $id);

        if($from_date) {
            $query->where('transaction_date', '>=', $from_date);
        }
        if($to_date) {
            $query->where('transaction_date', '<=', $to_date);
        }

        $ledger = $query->orderBy('transaction_date', 'asc')->orderBy('id', 'asc')->get();

        // Extract PAN from GST (if GST is valid)
        $pan_no = null;
        if ($customer->gst && strlen($customer->gst) >= 12) {
            $pan_no = substr($customer->gst, 2, 10);
        }

        $pdf = PDF::loadView('admin.customer_ledger.individual_ledger_pdf', compact('customer', 'ledger', 'opening_balance', 'from_date', 'to_date', 'pan_no'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream('Ledger_'.str_replace(' ', '_', $customer->name).'.pdf');
    }
}
