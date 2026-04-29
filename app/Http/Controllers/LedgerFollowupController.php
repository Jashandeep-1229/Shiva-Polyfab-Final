<?php

namespace App\Http\Controllers;

use App\Models\LedgerFollowup;
use App\Models\LedgerFollowupHistory;
use App\Models\AgentCustomer;
use Illuminate\Http\Request;
use Auth;
use DB;

class LedgerFollowupController extends Controller
{
    public function index(Request $request)
    {
        $customers_query = AgentCustomer::where('status', 1);
        $customers_query = auth()->user()->applyDataRestriction($customers_query, 'sale_executive_id', 'ledger_followups');
        $customers = $customers_query->orderBy('name', 'asc')->get();

        // Summary Stats
        $base_query = LedgerFollowup::query();
        $base_query = auth()->user()->applyDataRestriction($base_query, 'user_id', 'ledger_followup_private');
        
        $base_query->whereHas('customer', function($q) {
            auth()->user()->applyDataRestriction($q, 'sale_executive_id', 'ledger_followups');
        });

        $stats = [
            'today' => (clone $base_query)->whereHas('activeHistory', function($q) {
                $q->whereDate('followup_date_time', date('Y-m-d'));
            })->count(),
            'pending' => (clone $base_query)->where('status', 'Pending')->whereHas('activeHistory', function($q) {
                $q->where('followup_date_time', '<', now());
            })->count(),
            'closed' => (clone $base_query)->where('status', 'Closed')->count(),
            'total' => (clone $base_query)->count(),
        ];

        $active_filter = $request->filter ?? '';
        
        $executives = [];
        if (Auth::user()->role_as == 'Admin') {
            $executives = \App\Models\User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive'])->get();
        }

        return view('admin.ledger_followup.index', compact('customers', 'stats', 'active_filter', 'executives'));
    }

    public function history_all(Request $request)
    {
        $customers_query = AgentCustomer::where('status', 1);
        $customers_query = auth()->user()->applyDataRestriction($customers_query, 'sale_executive_id', 'ledger_followups');
        $customers = $customers_query->orderBy('name', 'asc')->get();

        $executives = [];
        if (Auth::user()->role_as == 'Admin') {
            $executives = \App\Models\User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive'])->get();
        }

        return view('admin.ledger_followup.history_all', compact('customers', 'executives'));
    }

    public function history_all_datatable(Request $request)
    {
        $number = $request->value ?? 50;
        
        $query = LedgerFollowupHistory::with(['followup.customer', 'completedBy', 'user'])
            ->where('status', 0) // Only completed ones
            ->whereNotNull('complete_date_time');

        // Permissions
        $query->whereHas('followup', function($fq) {
            auth()->user()->applyDataRestriction($fq, 'user_id', 'ledger_followup_private');
        });
        
        $query->whereHas('followup.customer', function($q) {
            auth()->user()->applyDataRestriction($q, 'sale_executive_id', 'ledger_followups');
        });

        if ($request->customer_id) {
            $query->whereHas('followup', function($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }

        if ($request->executive_id) {
            $query->whereHas('followup.customer', function($q) use ($request) {
                $q->where('sale_executive_id', $request->executive_id);
            });
        }

        if ($request->from_date) {
            $query->whereDate('complete_date_time', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('complete_date_time', '<=', $request->to_date);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('remarks', 'like', "%$search%")
                  ->orWhereHas('followup', function($sq) use ($search) {
                      $sq->where('subject', 'like', "%$search%")
                        ->orWhereHas('customer', function($ssq) use ($search) {
                            $ssq->where('name', 'like', "%$search%")
                                ->orWhere('phone_no', 'like', "%$search%");
                        });
                  });
            });
        }

        $histories = $query->orderBy('complete_date_time', 'desc')->paginate($number);
        
        return view('admin.ledger_followup.history_all_datatable', compact('histories'));
    }

    public function report(Request $request)
    {
        $customers_query = AgentCustomer::where('status', 1);
        $customers_query = auth()->user()->applyDataRestriction($customers_query, 'sale_executive_id', 'ledger_followups');
        $customers = $customers_query->orderBy('name', 'asc')->get();

        $executives = [];
        if (Auth::user()->role_as == 'Admin') {
            $executives = \App\Models\User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive'])->get();
        }

        $employee_id = $request->employee_id;
        $report_data = null;

        if ($employee_id) {
            $from_date = $request->from_date ?? date('Y-m-d', strtotime('-15 days'));
            $to_date = $request->to_date ?? date('Y-m-d');
            $customer_id = $request->customer_id;

            $report_data = $this->calculate_performance($employee_id, $from_date, $to_date, $customer_id);
            
            // Calculate Previous Period
            $start = new \DateTime($from_date);
            $end = new \DateTime($to_date);
            $interval = $start->diff($end);
            $days = $interval->days + 1;
            
            $prev_to = date('Y-m-d', strtotime($from_date . ' -1 day'));
            $prev_from = date('Y-m-d', strtotime($prev_to . " -$days days +1 day"));
            
            $prev_data = $this->calculate_performance($employee_id, $prev_from, $prev_to, $customer_id);
            
            $report_data['previous'] = $prev_data;
            $report_data['from_date'] = $from_date;
            $report_data['to_date'] = $to_date;
            $report_data['prev_from'] = $prev_from;
            $report_data['prev_to'] = $prev_to;
        }

        return view('admin.ledger_followup.report', compact('customers', 'executives', 'report_data', 'employee_id'));
    }

    public function customer_report(Request $request)
    {
        $customers_query = AgentCustomer::where('status', 1);
        $customers_query = auth()->user()->applyDataRestriction($customers_query, 'sale_executive_id', 'ledger_followups');
        $customers = $customers_query->orderBy('name', 'asc')->get();

        $customer_id = $request->customer_id;
        $report_data = null;

        if ($customer_id) {
            $customer = AgentCustomer::findOrFail($customer_id);
            $threads = LedgerFollowup::where('customer_id', $customer_id)->with('histories')->get();
            
            $total_threads = $threads->count();
            $closed_threads = $threads->where('status', 'Closed');
            $pending_threads = $threads->where('status', 'Pending');
            
            $total_interactions = $threads->sum(function($t) { return $t->histories->count(); });
            $avg_iterations = $total_threads > 0 ? round($total_interactions / $total_threads, 1) : 0;
            
            $total_close_days = $closed_threads->sum('total_no_of_days');
            $avg_days_to_close = $closed_threads->count() > 0 ? round($total_close_days / $closed_threads->count(), 1) : 0;

            // Rating logic
            $health_score = 100;
            $crit_reasons = [];

            // 1. High Iterations indicates difficulty
            if ($avg_iterations > 5) {
                $health_score -= 30;
                $crit_reasons[] = "Account requires high communication intensity ($avg_iterations iterations/thread).";
            } elseif ($avg_iterations > 3) {
                $health_score -= 15;
            }

            // 2. Slow closure
            if ($avg_days_to_close > 15) {
                $health_score -= 30;
                $crit_reasons[] = "Payment cycles are very slow ($avg_days_to_close days to close).";
            }

            // 3. Ratio of pending
            $pending_ratio = $total_threads > 0 ? ($pending_threads->count() / $total_threads) : 0;
            if ($pending_ratio > 0.5) {
                $health_score -= 20;
                $crit_reasons[] = "Over 50% of followups remain unsettled.";
            }

            $report_data = [
                'customer' => $customer,
                'total_followups' => $total_threads,
                'closed_count' => $closed_threads->count(),
                'pending_count' => $pending_threads->count(),
                'avg_iterations' => $avg_iterations,
                'avg_days_to_close' => $avg_days_to_close,
                'health_score' => max($health_score, 0),
                'crit_reasons' => $crit_reasons
            ];
        }

        return view('admin.ledger_followup.customer_report', compact('customers', 'report_data', 'customer_id'));
    }

    private function calculate_performance($employee_id, $from, $to, $customer_id = null)
    {
        $base_query = LedgerFollowup::whereHas('customer', function($q) use ($employee_id, $customer_id) {
            $q->where('sale_executive_id', $employee_id);
            if ($customer_id) $q->where('id', $customer_id);
        })->where('user_id', $employee_id);

        $history_query = LedgerFollowupHistory::whereHas('followup', function($q) use ($employee_id) {
            $q->where('user_id', $employee_id);
        })->whereHas('followup.customer', function($q) use ($employee_id, $customer_id) {
            $q->where('sale_executive_id', $employee_id);
            if ($customer_id) $q->where('id', $customer_id);
        });

        // Pending followups that exist in this period
        $pending = (clone $base_query)->where('status', 'Pending')
            ->whereDate('created_at', '<=', $to)
            ->count();

        // Delayed pending (activeHistory date < specifically $to_date)
        $delayed_pending = (clone $base_query)->where('status', 'Pending')
            ->whereHas('activeHistory', function($q) use ($to) {
                $q->whereDate('followup_date_time', '<', $to);
            })->count();

        // Completed stats (interactions done in this period)
        $on_time = (clone $history_query)->whereBetween('complete_date_time', [$from.' 00:00:00', $to.' 23:59:59'])
            ->where('total_no_of_days', '<=', 0)->count();

        $delayed_complete = (clone $history_query)->whereBetween('complete_date_time', [$from.' 00:00:00', $to.' 23:59:59'])
            ->where('total_no_of_days', '>', 0)->count();

        // Average Iterations (Total history items in period / Followups involved)
        $involved_followups = (clone $history_query)->whereBetween('complete_date_time', [$from.' 00:00:00', $to.' 23:59:59'])
            ->distinct('followup_id')->count('followup_id');
        
        $total_interactions = (clone $history_query)->whereBetween('complete_date_time', [$from.' 00:00:00', $to.' 23:59:59'])
            ->count();

        $avg_iterations = $involved_followups > 0 ? round($total_interactions / $involved_followups, 1) : 0;

        return [
            'pending' => $pending,
            'delayed_pending' => $delayed_pending,
            'on_time' => $on_time,
            'delayed_complete' => $delayed_complete,
            'avg_iterations' => $avg_iterations
        ];
    }

    public function datatable(Request $request)
    {
        $number = $request->value ?? 50;
        
        $query = LedgerFollowup::with(['customer', 'user', 'activeHistory']);
        $query = auth()->user()->applyDataRestriction($query, 'user_id', 'ledger_followup_private');

        $query->whereHas('customer', function($q) {
            auth()->user()->applyDataRestriction($q, 'sale_executive_id', 'ledger_followups');
        });

        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->executive_id) {
            $actor = \App\Models\User::find($request->executive_id);
            if ($actor) {
                $managed_ids = $actor->getPermittedUserIds('ledger_followups');
                $query->whereHas('customer', function($q) use ($managed_ids) {
                    $q->whereIn('sale_executive_id', $managed_ids);
                });
            } else {
                $query->whereHas('customer', function($q) use ($request) {
                    $q->where('sale_executive_id', $request->executive_id);
                });
            }
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->filter_type == 'today') {
            $query->whereHas('activeHistory', function($q) {
                $q->whereDate('followup_date_time', date('Y-m-d'));
            });
        } elseif ($request->filter_type == 'pending') {
            $query->where('status', 'Pending');
            
            if ($request->from_date) {
                $query->whereHas('activeHistory', function($q) use ($request) {
                    $q->whereDate('followup_date_time', '>=', $request->from_date);
                });
            }

            if ($request->to_date) {
                $query->whereHas('activeHistory', function($q) use ($request) {
                    $q->whereDate('followup_date_time', '<=', $request->to_date);
                });
            } else {
                // Default to everything up to today if to_date not provided in pending view
                $query->whereHas('activeHistory', function($q) {
                    $q->where('followup_date_time', '<=', date('Y-m-d 23:59:59'));
                });
            }
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%$search%")
                  ->orWhereHas('customer', function($sq) use ($search) {
                      $sq->where('name', 'like', "%$search%");
                  });
            });
        }

        $followups = $query->latest()->paginate($number);
        
        return view('admin.ledger_followup.datatable', compact('followups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'followup_date_time' => 'required',
            'subject' => 'required',
        ]);

        $followup = LedgerFollowup::create([
            'customer_id' => $request->customer_id,
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'start_date' => $request->followup_date_time,
            'status' => 'Pending'
        ]);

        LedgerFollowupHistory::create([
            'followup_id' => $followup->id,
            'user_id' => Auth::id(),
            'followup_date_time' => $request->followup_date_time,
            'remarks' => $request->remarks,
            'status' => 1
        ]);

        return response()->json(['result' => 1, 'message' => 'Followup Created Successfully']);
    }

    public function update_thread(Request $request)
    {
        $request->validate([
            'parent_id' => 'required', // This is now followup_id
            'status' => 'required|in:Continue,Closed'
        ]);

        $followup = LedgerFollowup::findOrFail($request->parent_id);
        
        // Find active history
        $activeHistory = LedgerFollowupHistory::where('followup_id', $followup->id)
            ->where('status', 1)
            ->first();

        if ($activeHistory) {
            $complete_date_time = now();
            $scheduled_date = strtotime($activeHistory->followup_date_time);
            $completed_at = strtotime($complete_date_time);
            $diff = $completed_at - $scheduled_date;
            $delay_days = $diff > 0 ? round($diff / 86400, 1) : 0;

            $activeHistory->update([
                'complete_date_time' => $complete_date_time,
                'complete_by' => Auth::id(),
                'total_no_of_days' => $delay_days,
                'status' => 0
            ]);
        }

        if ($request->status == 'Continue') {
            LedgerFollowupHistory::create([
                'followup_id' => $followup->id,
                'user_id' => Auth::id(),
                'remarks' => $request->remarks,
                'followup_date_time' => $request->followup_date_time,
                'status' => 1
            ]);
        } else {
            // Closed
            $final_complete_date = now();
            $start_date = strtotime($followup->start_date);
            $end_date = strtotime($final_complete_date);
            $total_days = round(($end_date - $start_date) / 86400, 1);

            // Important: Save the final remarks as a history record even for closure
            LedgerFollowupHistory::create([
                'followup_id' => $followup->id,
                'user_id' => Auth::id(),
                'remarks' => $request->remarks,
                'followup_date_time' => $final_complete_date,
                'complete_date_time' => $final_complete_date,
                'complete_by' => Auth::id(),
                'total_no_of_days' => 0,
                'status' => 0
            ]);

            $followup->update([
                'status' => 'Closed',
                'complete_date' => $final_complete_date,
                'completed_by' => Auth::id(),
                'total_no_of_days' => $total_days
            ]);
        }

        return response()->json(['result' => 1, 'message' => 'Followup Updated Successfully']);
    }

    public function get_history($id)
    {
        $followup = LedgerFollowup::with(['customer', 'user', 'histories.user'])->findOrFail($id);
        
        if (auth()->user()->role_as != 'Admin' && $followup->user_id != auth()->id()) {
            return response()->json(['result' => 0, 'message' => 'Unauthorized Access'], 403);
        }

        // Check if any debit transaction was added after the followup was created
        $has_debit = \App\Models\CustomerLedger::where('customer_id', $followup->customer_id)
            ->where('dr_cr', 'Dr')
            ->where('created_at', '>', $followup->created_at)
            ->exists();

        return view('admin.ledger_followup.history_modal', compact('followup', 'has_debit'));
    }

    public function pending_today(Request $request)
    {
        $customers_query = AgentCustomer::where('status', 1);
        $customers_query = auth()->user()->applyDataRestriction($customers_query, 'sale_executive_id', 'ledger_followups');
        $customers = $customers_query->orderBy('name', 'asc')->get();

        $executives = [];
        if (Auth::user()->role_as == 'Admin') {
            $executives = \App\Models\User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive'])->get();
        }

        return view('admin.ledger_followup.pending_today', compact('customers', 'executives'));
    }

    public function pending_today_datatable(Request $request)
    {
        $number = $request->value ?? 50;
        
        $query = LedgerFollowupHistory::with(['followup.customer', 'followup.user'])
            ->where('status', 1);

        // Date Filtering
        if ($request->to_date) {
            $query->whereDate('followup_date_time', '<=', $request->to_date);
        } else {
            // Default to everything up to today if no date is picked
            $query->where('followup_date_time', '<=', date('Y-m-d 23:59:59'));
        }

        if ($request->from_date) {
            $query->whereDate('followup_date_time', '>=', $request->from_date);
        }

        // Basic permissions
        $query->whereHas('followup', function($fq) {
            auth()->user()->applyDataRestriction($fq, 'user_id', 'ledger_followup_private');
        });

        $query->whereHas('followup.customer', function($q) {
            auth()->user()->applyDataRestriction($q, 'sale_executive_id', 'ledger_followups');
        });

        if ($request->customer_id) {
            $query->whereHas('followup', function($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }

        if ($request->executive_id) {
            $query->whereHas('followup.customer', function($q) use ($request) {
                $q->where('sale_executive_id', $request->executive_id);
            });
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('remarks', 'like', "%$search%")
                  ->orWhereHas('followup', function($sq) use ($search) {
                      $sq->where('subject', 'like', "%$search%")
                        ->orWhereHas('customer', function($ssq) use ($search) {
                            $ssq->where('name', 'like', "%$search%")
                                ->orWhere('phone_no', 'like', "%$search%");
                        });
                  });
            });
        }

        $histories = $query->orderBy('followup_date_time', 'asc')->paginate($number);
        
        return view('admin.ledger_followup.pending_today_datatable', compact('histories'));
    }
}
