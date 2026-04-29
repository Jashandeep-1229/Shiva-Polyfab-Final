<?php

namespace App\Http\Controllers;

use App\Models\JobCard;
use App\Models\JobCardProcess;
use App\Models\CylinderJob;
use App\Models\Machine;
use App\Models\BlockageReason;
use App\Models\AgentCustomer;
use App\Models\Bill;
use App\Models\Lead;
use App\Models\CustomerLedger;
use App\Models\ManageStock;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function getAccountPendingDetail(Request $request)
    {
        $jobs = JobCard::where('status', 'Account Pending')
            ->whereNull('complete_date')
            ->whereNull('cancel_date')
            ->with('customer_agent')
            ->get();

        $details = $jobs->map(function($j) {
            $order_date = $j->job_card_date ? Carbon::parse($j->job_card_date) : null;
            return [
                'job_card_no' => $j->job_card_no,
                'job_card_name' => $j->name_of_job ?? 'N/A',
                'customer' => $j->customer_agent->name ?? 'N/A',
                'order_date' => $order_date ? $order_date->format('d-M-Y') : 'N/A',
                'process' => $j->job_card_process ?? 'N/A',
                'remarks' => $j->remarks ?? 'N/A'
            ];
        });

        return response()->json($details);
    }

    public function getOverdueDispatchesDetail(Request $request)
    {
        $overdue = JobCard::whereNull('complete_date')
            ->whereNull('cancel_date')
            ->whereNotNull('dispatch_date')
            ->whereDate('dispatch_date', '<', Carbon::now())
            ->with('customer_agent')
            ->get();

        $details = $overdue->map(function($j) {
            $dispatch = Carbon::parse($j->dispatch_date);
            $order_date = $j->job_card_date ? Carbon::parse($j->job_card_date) : null;
            return [
                'job_card_no' => $j->job_card_no,
                'job_card_name' => $j->name_of_job ?? 'N/A',
                'customer' => $j->customer_agent->name ?? 'N/A',
                'order_date' => $order_date ? $order_date->format('d-M-Y') : 'N/A',
                'delivery_date' => $dispatch->format('d-M-Y'),
                'current_process' => $j->job_card_process ?? 'Pending',
                'days_late' => $dispatch->diffInDays(Carbon::now()),
                'remarks' => $j->remarks ?? 'N/A'
            ];
        })->sortByDesc('days_late')->values();

        return response()->json($details);
    }

    public function index(Request $request)
    {
        if (auth()->user()->role_as != 'Admin') {
            abort(403, 'Unauthorized access to Admin Dashboard.');
        }

        // 0. Indian Currency Formatter Helper
        $indian_format = function($num) {
            $num = round($num, 2);
            $explodeno = explode('.', $num);
            $dec = isset($explodeno[1]) ? $explodeno[1] : '00';
            if (strlen($dec) == 1) $dec .= '0';
            $num = $explodeno[0];
            $lastthree = substr($num, strlen($num)-3);
            $restunits = substr($num, 0, strlen($num)-3);
            $res = "";
            if (strlen($restunits) > 0) {
                $restunits = strrev($restunits);
                $expunit = str_split($restunits, 2);
                for ($i = 0; $i < sizeof($expunit); $i++) {
                    if ($i == sizeof($expunit) - 1) {
                        $res .= $expunit[$i];
                    } else {
                        $res .= $expunit[$i] . ",";
                    }
                }
                $res = strrev($res);
            }
            $final = (strlen($res) > 0 ? $res . "," : "") . $lastthree;
            return $final . '.' . $dec;
        };

        // 1. Dynamic Settings (Days Limits from Website Settings/ENV)
        $limits = [
            'cylinder' => $request->limit_cylinder ?? env('CYLINDER_LIMIT', 10),
            'printing' => $request->limit_printing ?? env('PRINTING_LIMIT', 3),
            'lamination' => $request->limit_lamination ?? env('LAMINATION_LIMIT', 4),
            'cutting' => $request->limit_cutting ?? env('CUTTING_LIMIT', 2),
        ];

        // 2. Date Filter
        $from_date = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $to_date = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : Carbon::now()->endOfDay();

        // 3. Production Late Analytics
        $late_jobs = $this->getLateJobs($limits, $from_date, $to_date);

        // 4. Cylinder Agent Delay Analytics
        $cylinder_delays = $this->getCylinderDelays($limits['cylinder'], $from_date, $to_date);

        // 5. Machine Performance & Blockage
        $machine_stats = $this->getMachineStats($from_date, $to_date);

        // 6. Financial Overdue Analytics
        $financial_stats = $this->getFinancialStats();

        // 7. Stock Alerts (Min/Max)
        $stock_alerts = $this->getStockAlerts($request->filter_stock_by ?? 'All', $request->filter_stock_status ?? 'All');

        // 8. Dispatch Overdue (Jobs not completed + dispatch_date passed)
        $overdue_dispatches = JobCard::whereNull('complete_date')
            ->whereNull('cancel_date')
            ->whereNotNull('dispatch_date')
            ->whereDate('dispatch_date', '<', Carbon::now())
            ->with('customer_agent')
            ->get();

        // 9. Account Pending (Overall-No Date)
        $account_pending_count = JobCard::where('status', 'Account Pending')
            ->whereNull('complete_date')
            ->whereNull('cancel_date')
            ->count();

        // 9b. On Hold Jobs (All active jobs currently on hold)
        $on_hold_jobs = JobCard::where('is_hold', 1)
            ->whereNull('complete_date')
            ->whereNull('cancel_date')
            ->with(['customer_agent', 'hold_reason', 'heldByUser'])
            ->orderBy('held_at', 'desc')
            ->get();

        // Pre-map for JS (avoid closure inside @json in blade)
        $hold_jobs_for_js = $on_hold_jobs->map(function($j) {
            return [
                'job_card_no' => $j->job_card_no,
                'name_of_job' => $j->name_of_job ?? 'N/A',
                'customer'    => optional($j->customer_agent)->name ?? 'N/A',
                'process'     => $j->job_card_process ?? 'N/A',
                'hold_reason' => optional($j->hold_reason)->name ?? 'No Reason Specified',
                'hold_notes'  => $j->hold_notes ?? '-',
                'held_by'     => optional($j->heldByUser)->name ?? 'N/A',
                'held_at'     => $j->held_at ? $j->held_at->format('d M Y, h:i A') : 'N/A',
            ];
        })->values()->toArray();

        // 10. Global Blockage Analytics (Top reasons across factory)
        $blockage_analytics = JobCardProcess::whereBetween('date', [$from_date->format('Y-m-d'), $to_date->format('Y-m-d')])
            ->where('blockage_time', '>', 0)
            ->with(['blockage_reason', 'machine', 'job_card'])
            ->get()
            ->groupBy('blockage_reason_id')
            ->map(function ($group, $reason_id) {
                // Get unique machine names for this reason
                $machines = $group->pluck('machine.name')->unique()->filter()->implode(', ');
                $job_cards = $group->pluck('job_card.job_card_no')->unique()->filter()->implode(', ');
                
                return [
                    'id' => $reason_id,
                    'reason' => $group->first()->blockage_reason->reason ?? 'Other',
                    'total_min' => $group->sum('blockage_time'),
                    'count' => $group->count(),
                    'machines' => $machines,
                    'job_cards' => $job_cards
                ];
            })
            ->sortByDesc('total_min')
            ->take(10);

        // 10b. Top Hold Reasons (Grouping current active holds by reason)
        $hold_reasons_analytics = JobCard::where('is_hold', 1)
            ->whereNull('complete_date')
            ->whereNull('cancel_date')
            ->with('hold_reason')
            ->get()
            ->groupBy('hold_reason_id')
            ->map(function($group, $reason_id) {
                return [
                    'id' => $reason_id,
                    'reason' => $group->first()->hold_reason->reason ?? 'Other/General',
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(10);

        // 11. Comprehensive Stock Out Transactions (All Categories) - DATE WISE
        $stock_out_query = ManageStock::where('in_out', 'out')
            ->whereBetween('date', [$from_date, $to_date]);
        
        // Apply Stock By filter to this list as well
        if ($request->filter_stock_by && $request->filter_stock_by != 'All') {
            $stock_out_query->where('stock_name', strtolower($request->filter_stock_by));
        }

        $stock_out_list = $stock_out_query->latest('date')
            ->take(20)
            ->get()
            ->map(function($record) {
                // Determine Master Model
                $type = ucfirst($record->stock_name);
                $modelClass = "App\\Models\\" . $type;
                $master = $modelClass::find($record->stock_id);
                
                return [
                    'name' => $master->name ?? 'Unknown',
                    'type' => $type,
                    'date' => Carbon::parse($record->date),
                    'qty' => $record->average,
                    'unit' => $record->stock_name == 'ink' ? 'Kg' : ($record->stock_name == 'fabric' ? 'Kg' : 'Pcs')
                ];
            });

        // 12. Top 10 Customers (New vs Repeat Orders) - ROLE WISE from Master
        $customer_ids = AgentCustomer::where('role', 'Customer')->pluck('id');
        
        $customer_stats = JobCard::whereIn('customer_agent_id', $customer_ids)
            ->whereBetween('job_card_date', [$from_date->format('Y-m-d'), $to_date->format('Y-m-d')])
            ->select('customer_agent_id', \DB::raw('count(*) as total'))
            ->groupBy('customer_agent_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $customer_reports = $customer_stats->map(function($stat) use ($from_date, $to_date) {
            $master = AgentCustomer::find($stat->customer_agent_id);
            
            $period_jobs = JobCard::where('customer_agent_id', $stat->customer_agent_id)
                ->whereBetween('job_card_date', [$from_date->format('Y-m-d'), $to_date->format('Y-m-d')])
                ->orderBy('job_card_date')
                ->get();

            $new = 0;
            $repeat = 0;
            $first_job_ever = JobCard::where('customer_agent_id', $stat->customer_agent_id)->orderBy('job_card_date')->first();

            foreach($period_jobs as $job) {
                if ($first_job_ever && $job->id == $first_job_ever->id) {
                    $new++;
                } else {
                    $repeat++;
                }
            }

            return [
                'id' => $stat->customer_agent_id,
                'name' => $master->name ?? 'Unknown',
                'new' => $new,
                'repeat' => $repeat,
                'total' => $stat->total
            ];
        });

        // 13. Top 10 Agents - ROLE WISE from Master
        $agent_ids = AgentCustomer::where('role', 'Agent')->pluck('id');
        
        $agent_stats = JobCard::whereIn('customer_agent_id', $agent_ids)
            ->whereBetween('job_card_date', [$from_date->format('Y-m-d'), $to_date->format('Y-m-d')])
            ->select('customer_agent_id', \DB::raw('count(*) as total'))
            ->groupBy('customer_agent_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $agent_reports = $agent_stats->map(function($stat) use ($from_date, $to_date) {
            $master = AgentCustomer::find($stat->customer_agent_id);

            $period_jobs = JobCard::where('customer_agent_id', $stat->customer_agent_id)
                ->whereBetween('job_card_date', [$from_date->format('Y-m-d'), $to_date->format('Y-m-d')])
                ->orderBy('job_card_date')
                ->get();

            $new = 0; $repeat = 0;
            $first_job_ever = JobCard::where('customer_agent_id', $stat->customer_agent_id)->orderBy('job_card_date')->first();

            foreach($period_jobs as $job) {
                if ($first_job_ever && $job->id == $first_job_ever->id) { $new++; } else { $repeat++; }
            }

            return [
                'id' => $stat->customer_agent_id,
                'name' => $master->name ?? 'N/A',
                'new' => $new,
                'repeat' => $repeat,
                'total' => $stat->total
            ];
        });

        // 14. Top 10 Sale Executives - Performance
        $exec_stats = JobCard::whereNotNull('sale_executive_id')
            ->whereBetween('job_card_date', [$from_date->format('Y-m-d'), $to_date->format('Y-m-d')])
            ->select('sale_executive_id', \DB::raw('count(*) as total'))
            ->groupBy('sale_executive_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $executive_reports = $exec_stats->map(function($stat) use ($from_date, $to_date) {
            $exec = \App\Models\User::find($stat->sale_executive_id);

            $period_job_ids = JobCard::where('sale_executive_id', $stat->sale_executive_id)
                ->whereBetween('job_card_date', [$from_date->format('Y-m-d'), $to_date->format('Y-m-d')])
                ->pluck('customer_agent_id');

            $cust_orders = AgentCustomer::whereIn('id', $period_job_ids)->where('role', 'Customer')->count();
            $agent_orders = AgentCustomer::whereIn('id', $period_job_ids)->where('role', 'Agent')->count();

            return [
                'id' => $stat->sale_executive_id,
                'name' => $exec->name ?? 'N/A',
                'cust' => $cust_orders,
                'agent' => $agent_orders,
                'total' => $stat->total
            ];
        });

        // 15. Overdue Bills (Only from Bill Management - prioritized by due_date)
        $overdue_bills = Bill::where('status', '!=', 'paid')
            ->where(function($q) {
                $q->whereNotNull('due_date')->where('due_date', '<=', Carbon::now()->format('Y-m-d'))
                  ->orWhere(function($sq) {
                      $sq->whereNull('due_date')->where('bill_date', '<=', Carbon::now()->format('Y-m-d'));
                  });
            })
            ->with(['customer'])
            ->orderByRaw('COALESCE(due_date, bill_date) ASC')
            ->take(10)
            ->get()
            ->map(function($b) {
                $check_date = $b->due_date ?? $b->bill_date;
                return [
                    'customer_id' => $b->customer_id,
                    'customer' => $b->customer->name ?? 'N/A',
                    'bill_no' => $b->bill_no,
                    'amount' => $b->grand_total,
                    'due_days' => Carbon::parse($check_date)->diffInDays(Carbon::now())
                ];
            });

        // 16. Top Customer Payments Received (CR transactions in period)
        $top_payments = CustomerLedger::where('dr_cr', 'Cr')
            ->whereBetween('transaction_date', [$from_date->format('Y-m-d'), $to_date->format('Y-m-d')])
            ->select('customer_id', \DB::raw('SUM(grand_total_amount) as total_received'))
            ->groupBy('customer_id')
            ->orderByDesc('total_received')
            ->take(10)
            ->get()
            ->map(function($l) {
                return [
                    'customer_id' => $l->customer_id,
                    'customer' => $l->customer->name ?? 'N/A',
                    'amount' => $l->total_received
                ];
            });

        // 17. Top Due Amount Pending (Grouped Balance from Ledger)
        $top_pending = AgentCustomer::select('id', 'name')
            ->get()
            ->map(function($c) {
                $dr = CustomerLedger::where('customer_id', $c->id)->where('dr_cr', 'Dr')->sum(\DB::raw('COALESCE(grand_total_amount, total_amount)'));
                $cr = CustomerLedger::where('customer_id', $c->id)->where('dr_cr', 'Cr')->sum('grand_total_amount');
                return [
                    'customer_id' => $c->id,
                    'customer' => $c->name,
                    'balance' => $dr - $cr
                ];
            })
            ->filter(fn($item) => $item['balance'] > 1) 
            ->sortByDesc('balance')
            ->take(10)
            ->values();

        // 18. Payment Late (45 Days - Full Ledger Scan)
        $payment_late_45 = CustomerLedger::where('dr_cr', 'Dr')
            ->where('transaction_date', '<', Carbon::now()->subDays(45)->format('Y-m-d'))
            ->with(['customer', 'packing_slip'])
            ->orderByDesc(\DB::raw('COALESCE(grand_total_amount, total_amount)'))
            ->take(10)
            ->get()
            ->map(function($l) {
                return [
                    'customer_id' => $l->customer_id,
                    'customer' => $l->customer->name ?? 'N/A',
                    'bill_no' => $l->packing_slip->slip_no ?? ($l->remarks ?: 'DR-'.$l->id),
                    'amount' => $l->grand_total_amount > 0 ? $l->grand_total_amount : $l->total_amount,
                    'days' => Carbon::parse($l->transaction_date)->diffInDays(Carbon::now())
                ];
            });

        if ($request->is_ajax) {
            return view('admin.dashboard.overall_data', compact(
                'limits', 'from_date', 'to_date', 
                'late_jobs', 'cylinder_delays', 
                'machine_stats', 'financial_stats', 
                'stock_alerts', 'overdue_dispatches',
                'account_pending_count', 'blockage_analytics', 'stock_out_list',
                'customer_reports', 'agent_reports', 'executive_reports',
                'overdue_bills', 'top_payments', 'top_pending', 'payment_late_45', 'indian_format',
                'on_hold_jobs', 'hold_jobs_for_js', 'hold_reasons_analytics'
            ));
        }

        return view('admin.dashboard.overall', compact(
            'limits', 'from_date', 'to_date', 
            'late_jobs', 'cylinder_delays', 
            'machine_stats', 'financial_stats', 
            'stock_alerts', 'overdue_dispatches',
            'account_pending_count', 'blockage_analytics', 'stock_out_list',
            'customer_reports', 'agent_reports', 'executive_reports',
            'overdue_bills', 'top_payments', 'top_pending', 'payment_late_45', 'indian_format',
            'on_hold_jobs', 'hold_jobs_for_js', 'hold_reasons_analytics'
        ));
    }

    public function getLateJobsDetail(Request $request)
    {
        $limits = [
            'cylinder' => $request->limit_cylinder ?? 10,
            'printing' => $request->limit_printing ?? 3,
            'lamination' => $request->limit_lamination ?? 4,
            'cutting' => $request->limit_cutting ?? 2,
        ];
        $from_date = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : null;
        $to_date = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : null;
        $type = $request->type; // 'Cylinder', 'Printing', etc.

        $active_process_map = [
            'Cylinder' => ['Cylinder Come'],
            'Printing' => ['Schedule For Printing'],
            'Lamination' => ['Schedule For Lamination'],
            'Cutting' => ['Schedule For Box / Cutting']
        ];

        $target_processes = $active_process_map[$type] ?? [];

        $query = JobCard::whereNull('complete_date')
            ->whereNull('cancel_date')
            ->with(['customer_agent', 'hold_reason', 'heldByUser', 'processes' => function($q) {
                $q->where('status', 1);
            }]);

        /*
        if ($from_date && $to_date) {
            $query->whereBetween('job_card_date', [$from_date, $to_date]);
        }
        */

        $active_jobs = $query->get();

        $late_list = [];
        foreach ($active_jobs as $jc) {
            $p = $jc->processes->first();
            if (!$p) continue;

            $limit = 0;
            $ptype = '';
            $pname = $p->process_name;

            if ($pname == 'Cylinder Come') { $limit = $limits['cylinder']; $ptype = 'Cylinder'; }
            elseif ($pname == 'Schedule For Printing') { $limit = $limits['printing']; $ptype = 'Printing'; }
            elseif ($pname == 'Schedule For Lamination') { $limit = $limits['lamination']; $ptype = 'Lamination'; }
            elseif ($pname == 'Schedule For Box / Cutting') { $limit = $limits['cutting']; $ptype = 'Cutting'; }

            // If a specific type was requested, filter by it
            if ($type && $ptype != $type) continue;
            if (!$ptype) continue;

            $start = Carbon::parse($p->process_start_date);
            $diff = $start->diffInDays(Carbon::now());

            if ($diff > $limit) {
                $late_list[] = [
                    'job_card_no'       => $jc->job_card_no ?? 'N/A',
                    'job_card_name'     => $jc->name_of_job ?? 'N/A',
                    'customer'          => $jc->customer_agent->name ?? 'N/A',
                    'process'           => $ptype,
                    'current_stage'     => $pname,
                    'days_taken'        => $diff,
                    'limit'             => $limit,
                    'delay'             => $diff - $limit,
                    'is_hold'           => (int)$jc->is_hold,
                    'hold_reason'       => $jc->hold_reason ? $jc->hold_reason->name : null,
                    'hold_notes'        => $jc->hold_notes ?? null,
                    'held_by'           => $jc->heldByUser ? $jc->heldByUser->name : null,
                    'held_at'           => $jc->held_at ? $jc->held_at->format('d M Y, h:i A') : null,
                ];
            }
        }

        return response()->json($late_list);
    }

    public function getLedgerDetail(Request $request)
    {
        $customerId = $request->customer_id;
        $ledger = CustomerLedger::where('customer_id', $customerId)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function($l) {
                return [
                    'date' => Carbon::parse($l->transaction_date)->format('d-M-Y'),
                    'remarks' => $l->software_remarks ?: $l->remarks,
                    'dr' => $l->dr_cr == 'Dr' ? ($l->grand_total_amount ?: $l->total_amount) : 0,
                    'cr' => $l->dr_cr == 'Cr' ? ($l->grand_total_amount ?: $l->total_amount) : 0,
                ];
            });

        return response()->json($ledger);
    }

    public function getMachineDetail(Request $request)
    {
        $from_date = Carbon::parse($request->from_date);
        $to_date = Carbon::parse($request->to_date);
        $machine_id = $request->machine_id;

        $processes = JobCardProcess::where('machine_id', $machine_id)
            ->whereBetween('date', [$from_date->format('Y-m-d'), $to_date->format('Y-m-d')])
            ->with('job_card.customer_agent')
            ->get();

        $details = $processes->map(function($p) {
            return [
                'date' => Carbon::parse($p->date)->format('d-M-Y'),
                'job_card_no' => $p->job_card->job_card_no ?? 'N/A',
                'customer' => $p->job_card->customer_agent->name ?? 'N/A',
                'production' => $p->actual_order,
                'wastage' => $p->wastage,
                'blockage' => $p->blockage_time,
                'reason' => $p->block_reason->reason ?? 'N/A'
            ];
        });

        return response()->json($details);
    }

    private function getLateJobs($limits, $from_date, $to_date)
    {
        // Fetch ACTIVE jobs within the date range that are not completed/cancelled
        // Fetch ACTIVE jobs (Global - no date filter as requested)
        $active_jobs = JobCard::whereNull('complete_date')
            ->whereNull('cancel_date')
            ->with(['processes' => function($q) {
                $q->where('status', 1); // Get the current active process record
            }])
            ->get();

        $stats = [
            'total_late' => 0,
            'by_process' => [
                'Cylinder' => 0,
                'Printing' => 0,
                'Lamination' => 0,
                'Cutting' => 0,
            ],
            'total_by_process' => [
                'Cylinder' => 0,
                'Printing' => 0,
                'Lamination' => 0,
                'Cutting' => 0,
            ],
            'total_delay_days' => 0,
            'avg_delay_days' => 0
        ];

        $total_delay = 0;
        $count = 0;

        foreach ($active_jobs as $job) {
            $p = $job->processes->first();
            if (!$p) continue;

            $limit = 0;
            $key = '';
            $pname = $p->process_name;

            // Mapping active processes to dashboard categories
            if ($pname == 'Cylinder Come') {
                $limit = $limits['cylinder'];
                $key = 'Cylinder';
            } elseif ($pname == 'Schedule For Printing') {
                $limit = $limits['printing'];
                $key = 'Printing';
            } elseif ($pname == 'Schedule For Lamination') {
                $limit = $limits['lamination'];
                $key = 'Lamination';
            } elseif ($pname == 'Schedule For Box / Cutting') {
                $limit = $limits['cutting'];
                $key = 'Cutting';
            }

            if (!$key) continue;

            $stats['total_by_process'][$key]++;

            $start = Carbon::parse($p->process_start_date);
            $diff = $start->diffInDays(Carbon::now());

            if ($diff > $limit) {
                $stats['total_late']++;
                $stats['total_delay_days'] += $diff;
                $stats['by_process'][$key]++;
                $total_delay += ($diff - $limit);
                $count++;
            }
        }

        $stats['avg_delay_days'] = $count > 0 ? round($total_delay / $count, 1) : 0;
        return $stats;
    }

    private function getCylinderDelays($limit, $from_date, $to_date)
    {
        return CylinderJob::with('cylinder_agent')
            ->whereBetween('check_in_date', [$from_date, $to_date])
            ->get()
            ->groupBy('cylinder_agent_id')
            ->map(function ($jobs) use ($limit) {
                $first_job = $jobs->first();
                $agent_id = $first_job->cylinder_agent_id ?? 0;
                $agent_name = $first_job->cylinder_agent->name ?? 'Unknown';
                
                $late_count = $jobs->filter(function ($j) use ($limit) {
                    $start = Carbon::parse($j->check_in_date);
                    $end = $j->check_out_date ? Carbon::parse($j->check_out_date) : Carbon::now();
                    return $start->diffInDays($end) > $limit;
                })->count();

                return [
                    'id' => $agent_id,
                    'name' => $agent_name,
                    'total_jobs' => $jobs->count(),
                    'late_jobs' => $late_count,
                    'performance' => $jobs->count() > 0 ? round((($jobs->count() - $late_count) / $jobs->count()) * 100) : 0
                ];
            })->sortBy('performance')->take(5);
    }

    public function getCylinderAgentDetail(Request $request)
    {
        $agent_id = $request->agent_id;
        $limit = $request->limit ?? 7; // Default limit if not passed
        $from_date = Carbon::parse($request->from_date);
        $to_date = Carbon::parse($request->to_date);

        $jobs = CylinderJob::where('cylinder_agent_id', $agent_id)
            ->whereBetween('check_in_date', [$from_date->startOfDay(), $to_date->endOfDay()])
            ->with(['job_card', 'cylinder_agent'])
            ->get()
            ->filter(function ($j) use ($limit) {
                $start = Carbon::parse($j->check_in_date);
                $end = $j->check_out_date ? Carbon::parse($j->check_out_date) : Carbon::now();
                return $start->diffInDays($end) > $limit;
            });

        $details = $jobs->values()->map(function($j, $index) use ($limit) {
            $start = Carbon::parse($j->check_in_date);
            $end = $j->check_out_date ? Carbon::parse($j->check_out_date) : Carbon::now();
            return [
                'sr_no' => $index + 1,
                'job_card_no' => $j->job_card->job_card_no ?? ($j->job_card_id ?? 'N/A'),
                'job_name' => $j->name_of_job ?? 'N/A',
                'check_in' => $start->format('d-M-Y'),
                'check_out' => $j->check_out_date ? Carbon::parse($j->check_out_date)->format('d-M-Y') : 'In-Process',
                'days_taken' => $start->diffInDays($end),
                'late_by' => $start->diffInDays($end) - $limit
            ];
        });

        return response()->json($details->values());
    }

    private function getMachineStats($from_date, $to_date)
    {
        return Machine::where('status', 1)->get()->map(function($m) use ($from_date, $to_date) {
            $processes = JobCardProcess::where('machine_id', $m->id)
                ->whereBetween('date', [$from_date->format('Y-m-d'), $to_date->format('Y-m-d')])
                ->with('blockage_reason')
                ->get();

            $total_production = $processes->sum('actual_order');
            $total_blockage_min = $processes->sum('blockage_time');
            $total_wastage = $processes->sum('wastage');

            // Top blockage reason calculation
            $most_blockage_reason = "No Blockage";
            if ($total_blockage_min > 0) {
                $block_counts = $processes->where('blockage_time', '>', 0)
                    ->groupBy('blockage_reason_id')
                    ->map(function ($group) {
                        return [
                            'name' => $group->first()->blockage_reason->reason ?? 'Other',
                            'count' => $group->count()
                        ];
                    })
                    ->sortByDesc('count');

                if ($block_counts->count() > 0) $most_blockage_reason = $block_counts->first()['name'];
            }
            
            // Best/OK/Less based on production vs avg machine production
            $target = $m->avg_per_day_production ?? 5000;
            $days = $from_date->diffInDays($to_date) + 1;
            $period_target = $target * $days;

            $status = 'OK';
            if ($total_production > ($period_target * 1.2)) $status = 'Best';
            elseif ($total_production < ($period_target * 0.8)) $status = 'Less';

            return [
                'id' => $m->id,
                'name' => $m->name,
                'type' => $m->type,
                'production' => $total_production,
                'target' => $period_target,
                'blockage' => $total_blockage_min,
                'wastage' => $total_wastage,
                'status' => $status,
                'most_blockage_reason' => $most_blockage_reason
            ];
        });
    }

    private function getFinancialStats()
    {
        $overdue_threshold = Carbon::now()->subDays(45);
        $seven_days_warning = Carbon::now()->addDays(7);

        // This is a simplified version, should be adjusted based on CustomerLedger balance logic
        $customers = AgentCustomer::where('status', 1)->get();
        
        $stats = [
            'overdue_45' => [],
            'warning_7' => [],
        ];

        foreach ($customers as $c) {
            $balance = CustomerLedger::where('customer_id', $c->id)->sum(DB::raw('CASE WHEN dr_cr = "Dr" THEN grand_total_amount ELSE -grand_total_amount END'));
            if ($balance > 0) {
                $last_transaction = CustomerLedger::where('customer_id', $c->id)->latest('transaction_date')->first();
                if ($last_transaction) {
                    $date = Carbon::parse($last_transaction->transaction_date);
                    if ($date->lt($overdue_threshold)) {
                        $stats['overdue_45'][] = ['name' => $c->name, 'balance' => $balance, 'days' => $date->diffInDays(Carbon::now())];
                    }
                }
            }
        }

        return $stats;
    }

    private function getStockAlerts($filter_type = 'All', $filter_status = 'All')
    {
        $alerts = [];
        $stock_types = ['Fabric', 'Bopp', 'Ink', 'Dana', 'Loop'];
        
        // Map UI names to Model names if needed
        $type_map = [
            'Fabric' => 'Fabric',
            'BOPP' => 'Bopp',
            'Ink' => 'Ink',
            'Dana' => 'Dana',
            'Loop' => 'Loop'
        ];

        foreach ($stock_types as $type) {
            if ($filter_type != 'All' && strtolower($filter_type) != strtolower($type)) {
                continue;
            }

            $modelClass = "App\\Models\\" . $type;
            $items = $modelClass::where('status', 1)->get();

            foreach ($items as $item) {
                // RULE: Only show if min or max is set in MASTER (not both 0)
                $min = $item->alert_min_stock ?? 0;
                $max = $item->alert_max_stock ?? 0;
                
                if ($min == 0 && $max == 0) {
                    continue;
                }

                $stock_query = ManageStock::where('stock_name', strtolower($type))->where('stock_id', $item->id);
                $current = $stock_query->sum(DB::raw('CASE WHEN in_out = "in" THEN average ELSE -average END'));
                
                $last_out = $stock_query->where('in_out', 'out')->latest('date')->first();
                $last_out_date = $last_out ? Carbon::parse($last_out->date) : null;
                $last_out_qty = $last_out ? $last_out->average : 0;

                $status = '';
                if ($current == 0) {
                    $status = 'Zero Stock';
                } elseif ($min > 0 && $current <= $min) {
                    $status = 'Low Stock';
                } elseif ($max > 0 && $current >= $max) {
                    $status = 'Over Stock';
                }

                if ($status != '') {
                    // Apply Status Filter
                    if ($filter_status != 'All' && $filter_status != $status) {
                        continue;
                    }

                    $alerts[] = [
                        'name' => $item->name,
                        'type' => $type,
                        'status' => $status,
                        'current' => $current,
                        'min' => $min,
                        'max' => $max,
                        'unit' => $type == 'Ink' ? 'Kg' : ($type == 'Fabric' ? 'Kg' : 'Pcs'),
                        'last_out_date' => $last_out_date,
                        'last_out_qty' => $last_out_qty
                    ];
                }
            }
        }

        return $alerts;
    }

    public function getBlockageDetail(Request $request) {
        $reason_id = $request->reason_id;
        $from_date = Carbon::parse($request->from_date)->startOfDay();
        $to_date = Carbon::parse($request->to_date)->endOfDay();

        $blockages = JobCardProcess::where('blockage_reason_id', $reason_id)
            ->whereBetween('date', [$from_date, $to_date])
            ->with(['job_card', 'machine'])
            ->orderBy('date', 'desc')
            ->get();

        $details = $blockages->map(function($b, $index) {
            $jobCard = $b->job_card;
            return [
                'sr_no' => $index + 1,
                'date' => $b->date ? \Carbon\Carbon::parse($b->date)->format('d-M-Y') : 'N/A',
                'job_card_no' => $jobCard->job_card_no ?? 'N/A',
                'job_name' => $jobCard->name_of_job ?? 'N/A',
                'machine' => $b->machine->name ?? 'N/A',
                'duration' => $b->blockage_time . ' Min',
                'customer' => $jobCard->customer_agent->name ?? 'N/A'
            ];
        });

        return response()->json($details);
    }
    public function getCustomerPerformanceDetail(Request $request) {
        $from_date = Carbon::parse($request->from_date)->startOfDay();
        $to_date = Carbon::parse($request->to_date)->endOfDay();
        
        $jobs = JobCard::where('customer_agent_id', $request->id)
            ->whereBetween('job_card_date', [$from_date->format('Y-m-d'), $to_date->format('Y-m-d')])
            ->with(['customer_agent'])
            ->orderBy('job_card_date', 'desc')
            ->get();

        $details = $jobs->map(function($j, $index) {
            return [
                'sr_no' => $index + 1,
                'date' => $j->job_card_date ? Carbon::parse($j->job_card_date)->format('d-M-Y') : 'N/A',
                'job_card_no' => $j->job_card_no ?? 'N/A',
                'job_name' => $j->name_of_job ?? 'N/A',
                'pieces' => $j->no_of_pieces ?? '0',
                'customer' => $j->customer_agent->name ?? 'N/A',
                'status' => $j->status ?? 'N/A'
            ];
        });
        return response()->json($details);
    }

    public function getAgentPerformanceDetail(Request $request) {
        return $this->getCustomerPerformanceDetail($request); // Logic is same: filter by customer_agent_id
    }

    public function getExecutivePerformanceDetail(Request $request) {
        $from_date = Carbon::parse($request->from_date)->startOfDay();
        $to_date = Carbon::parse($request->to_date)->endOfDay();
        
        $jobs = JobCard::where('sale_executive_id', $request->id)
            ->whereBetween('job_card_date', [$from_date->format('Y-m-d'), $to_date->format('Y-m-d')])
            ->with(['customer_agent'])
            ->orderBy('job_card_date', 'desc')
            ->get();

        $details = $jobs->map(function($j, $index) {
            return [
                'sr_no' => $index + 1,
                'date' => $j->job_card_date ? Carbon::parse($j->job_card_date)->format('d-M-Y') : 'N/A',
                'job_card_no' => $j->job_card_no ?? 'N/A',
                'job_name' => $j->name_of_job ?? 'N/A',
                'pieces' => $j->no_of_pieces ?? '0',
                'customer' => $j->customer_agent->name ?? 'N/A',
                'status' => $j->status ?? 'N/A'
            ];
        });
        return response()->json($details);
    }
}
