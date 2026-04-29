<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\User;

class EmployeeLogController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name', 'asc')->get();
        return view('admin.employee_log.index', compact('users'));
    }

    public function datatable(Request $request)
    {
        $limit = $request->limit ?? 50;
        $query = Activity::with(['causer', 'subject']);

        if (!$request->log_name || $request->log_name !== 'User') {
             $query->whereNotIn('log_name', ['JobCardProcess', 'PackingDetail', 'User']);
        } else {
             $query->where('log_name', 'User');
        }

        $query->where(function($q) use ($request) {
                if ($request->log_name) {
                    // Skip grouping if we are looking for a specific module's history
                    return;
                }
                $q->whereIn('id', function($sub) {
                    $sub->selectRaw('max(id)')
                        ->from('activity_log')
                        ->whereNotNull('subject_id')
                        ->whereNotIn('log_name', ['JobCardProcess', 'PackingDetail', 'User'])
                        ->where(function($sq) {
                            $sq->where('event', '!=', 'updated')
                               ->orWhere('log_name', '!=', 'LedgerFollowupHistory');
                        })
                        ->groupBy('subject_id', 'subject_type');
                })
                ->orWhereNull('subject_id');
            })
            ->latest();

        if ($request->user_id) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', 'App\Models\User');
        }

        if ($request->log_name) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->event) {
            $query->where('event', $request->event);
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('log_name', 'like', '%' . $request->search . '%')
                  ->orWhere('properties', 'like', '%' . $request->search . '%');
            });
        }

        $logs = $query->paginate($limit);

        return view('admin.employee_log.datatable', compact('logs'));
    }

    public function details($id)
    {
        $log = Activity::with(['causer', 'subject'])->findOrFail($id);
        
        // Fetch full history for PackingSlip, Bill, and Followup subjects to show in a timeline
        $history = [];
        if (in_array($log->log_name, ['PackingSlip', 'Bill']) && $log->subject_id) {
            $history = Activity::with('causer')
                ->where('log_name', $log->log_name)
                ->where('subject_id', $log->subject_id)
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif (in_array($log->log_name, ['LedgerFollowup', 'LedgerFollowupHistory'])) {
            $subject = $log->subject;
            $fId = ($log->log_name == 'LedgerFollowup') ? $log->subject_id : ($subject->followup_id ?? null);
            if ($fId) {
                $history = Activity::with('causer')
                    ->where(function($q) use ($fId) {
                        $q->where(function($sq) use ($fId) {
                            $sq->where('log_name', 'LedgerFollowup')->where('subject_id', $fId);
                        })->orWhere(function($sq) use ($fId) {
                            $sq->where('log_name', 'LedgerFollowupHistory')->whereIn('subject_id', function($sub) use ($fId) {
                                $sub->select('id')->from('ledger_followup_histories')->where('followup_id', $fId);
                            });
                        });
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } elseif (in_array($log->log_name, ['ManageStock', 'CommonManageStock']) && $log->event == 'created') {
            // Fetch the whole batch of stock entries created together
            $history = Activity::with(['causer', 'subject'])
                ->where('log_name', $log->log_name)
                ->where('causer_id', $log->causer_id)
                ->where('event', 'created')
                ->whereBetween('created_at', [
                    $log->created_at->copy()->subSeconds(3),
                    $log->created_at->copy()->addSeconds(3)
                ])
                ->orderBy('id', 'asc')
                ->get();
        }

        $eventClass = [
            'created' => 'badge-created',
            'updated' => 'badge-updated',
            'deleted' => 'badge-deleted',
        ][$log->event] ?? 'bg-info';

        $properties = $log->properties;
        $oldValue = $properties['old'] ?? null;
        $newValue = $properties['attributes'] ?? null;

        $subject = null;
        if($log->subject_type == 'App\Models\JobCard' && $log->subject_id) {
            $subject = \App\Models\JobCard::with(['fabric', 'bopp', 'cylinder_agent', 'customer_agent', 'sale_executive'])->find($log->subject_id);
        }

        return view('admin.employee_log.modal_content', compact('log', 'eventClass', 'oldValue', 'newValue', 'subject', 'history'));
    }

    public function softDeleteAll(Request $request)
    {
        $query = Activity::query()
                ->where('log_name', '!=', 'JobCardProcess');

        if ($request->user_id) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', 'App\Models\User');
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('log_name', 'like', '%' . $request->search . '%')
                  ->orWhere('properties', 'like', '%' . $request->search . '%');
            });
        }

        $query->delete();

        return response()->json([
            'result' => 1,
            'message' => 'Logs cleared successfully (Soft Deleted)'
        ]);
    }

    public function performance(Request $request)
    {
        $fromDate = $request->from_date ?: date('Y-m-01');
        $toDate = $request->to_date ?: date('Y-m-d');
        $userId = $request->user_id;

        $users = User::where('status', 1)
            ->orderBy('role_as', 'asc')
            ->orderBy('name', 'asc')->get();

        $query = Activity::whereBetween(\DB::raw('DATE(created_at)'), [$fromDate, $toDate]);
            
        if ($userId) {
            $query->where('causer_id', $userId);
        }
        
        $rawLogs = $query->get();

        $leadStatuses = \DB::table('lead_statuses')->get();
        $wonId = $leadStatuses->where('slug', 'won')->first()->id ?? 0;
        $lostId = $leadStatuses->where('slug', 'lost')->first()->id ?? 0;

        $reportStats = [];
        $targetUsers = $userId ? $users->where('id', $userId) : $users;

        foreach ($targetUsers as $user) {
            $uLogs = $rawLogs->where('causer_id', $user->id);
            
            $reportStats[$user->id] = [
                'user' => $user,
                'logins' => $uLogs->where('log_name', 'User')->filter(fn($l) => strpos($l->description, 'login') !== false)->count(),
                'job_card_created' => $uLogs->where('log_name', 'JobCard')->where('event', 'created')->count(),
                'job_card_moved' => $uLogs->where('log_name', 'JobCard')->where('event', 'updated')->count(),
                'bill_created' => $uLogs->where('log_name', 'Bill')->where('event', 'created')->count(),
                'stock_in' => $uLogs->where('log_name', 'ManageStock')->where('event', 'created')->filter(function($l) {
                    $props = $l->properties;
                    return ($props['attributes']['in_out'] ?? '') == 'in';
                })->count(),
                'stock_out' => $uLogs->where('log_name', 'ManageStock')->where('event', 'created')->filter(function($l) {
                    $props = $l->properties;
                    return ($props['attributes']['in_out'] ?? '') == 'out';
                })->count(),
                'common_stock_in' => $uLogs->where('log_name', 'CommonManageStock')->where('event', 'created')->filter(function($l) {
                    $props = $l->properties;
                    return ($props['attributes']['in_out'] ?? '') == 'in';
                })->count(),
                'common_stock_out' => $uLogs->where('log_name', 'CommonManageStock')->where('event', 'created')->filter(function($l) {
                    $props = $l->properties;
                    return ($props['attributes']['in_out'] ?? '') == 'out';
                })->count(),
                'lead_added' => $uLogs->where('log_name', 'Lead')->where('event', 'created')->count(),
                'converted' => $uLogs->where('log_name', 'Lead')->where('event', 'updated')->filter(function($l) use ($wonId) {
                    $props = $l->properties;
                    return ($props['attributes']['status_id'] ?? null) == $wonId;
                })->count(),
                'lost' => $uLogs->where('log_name', 'Lead')->where('event', 'updated')->filter(function($l) use ($lostId) {
                    $props = $l->properties;
                    return ($props['attributes']['status_id'] ?? null) == $lostId;
                })->count(),

                // Advanced Followup Stats
                'followup_pending' => \App\Models\LedgerFollowupHistory::where('user_id', $user->id)
                    ->where('status', 1)
                    ->count(),
                'followup_on_time' => \App\Models\LedgerFollowupHistory::where('complete_by', $user->id)
                    ->where('status', 0)
                    ->where('total_no_of_days', '<=', 0)
                    ->whereBetween('complete_date_time', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                    ->count(),
                'followup_late' => \App\Models\LedgerFollowupHistory::where('complete_by', $user->id)
                    ->where('status', 0)
                    ->where('total_no_of_days', '>', 0)
                    ->whereBetween('complete_date_time', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                    ->count(),
                'followup_missing' => \App\Models\LedgerFollowupHistory::where('user_id', $user->id)
                    ->where('status', 1)
                    ->where('followup_date_time', '<', now())
                    ->count(),

                'roto_packing_slip' => $uLogs->where('log_name', 'PackingSlip')->where('event', 'created')->count(),
                'common_packing_slip' => $uLogs->where('log_name', 'CommonPackingSlip')->where('event', 'created')->count(),
                'credit_vouchers' => $uLogs->where('log_name', 'CustomerLedger')->where('event', 'created')->filter(function($l) {
                    $props = $l->properties;
                    return ($props['attributes']['dr_cr'] ?? '') == 'Cr' && ($props['attributes']['is_bad_debt'] ?? 0) == 0;
                })->count(),
                'debit_vouchers' => $uLogs->where('log_name', 'CustomerLedger')->where('event', 'created')->filter(function($l) {
                    $props = $l->properties;
                    return ($props['attributes']['dr_cr'] ?? '') == 'Dr';
                })->count(),
                'bad_debt_vouchers' => $uLogs->where('log_name', 'CustomerLedger')->where('event', 'created')->filter(function($l) {
                    $props = $l->properties;
                    return ($props['attributes']['is_bad_debt'] ?? 0) == 1;
                })->count(),
                'payment_followup' => $uLogs->where('log_name', 'LedgerFollowupHistory')->where('event', 'created')->count(),
            ];
        }

        return view('admin.employee_log.performance', compact('reportStats', 'users', 'fromDate', 'toDate', 'userId'));
    }
}
