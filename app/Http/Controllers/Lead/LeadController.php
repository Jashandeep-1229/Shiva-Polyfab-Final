<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadTag;
use App\Models\LeadState;
use App\Models\LeadStatus;
use App\Models\LeadFollowup;
use App\Models\LeadHistory;
use App\Models\User;
use App\Models\LeadStepDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\PermissionHelper;

class LeadController extends Controller
{
    public function index()
    {
        $users = User::whereIn('id', auth()->user()->getViewableUserIds())->where('status', 1)->get();
        $sources = LeadSource::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $tags = LeadTag::where('status', 1)->get();
        return view('lead.leads.index', compact('users', 'sources', 'statuses', 'tags'));
    }

    public function pendingIndex()
    {
        $users = User::whereIn('id', auth()->user()->getViewableUserIds())->where('status', 1)->get();
        $sources = LeadSource::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $tags = LeadTag::where('status', 1)->get();
        $title = "Pending Leads";
        $type = "pending";
        return view('lead.leads.index', compact('users', 'sources', 'statuses', 'tags', 'title', 'type'));
    }


    public function wonIndex()
    {
        $users = User::whereIn('id', auth()->user()->getViewableUserIds())->where('status', 1)->get();
        $sources = LeadSource::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $tags = LeadTag::where('status', 1)->get();
        $title = "Converted Won Leads";
        $type = "won";
        return view('lead.leads.index', compact('users', 'sources', 'statuses', 'tags', 'title', 'type'));
    }

    public function lostIndex()
    {
        $users = User::whereIn('id', auth()->user()->getViewableUserIds())->where('status', 1)->get();
        $sources = LeadSource::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $tags = LeadTag::where('status', 1)->get();
        $title = "Lost Leads Archive";
        $type = "lost";
        return view('lead.leads.index', compact('users', 'sources', 'statuses', 'tags', 'title', 'type'));
    }

    public function checkPhone(Request $request)
    {
        $phone = $request->phone;
        $exclude_id = $request->exclude_id;
        
        $query = Lead::with(['status', 'assignedUser'])->where('phone', $phone);
        if ($exclude_id) {
            $query->where('id', '!=', $exclude_id);
        }
        
        $existingLeads = $query->orderBy('created_at', 'desc')->get();
        
        // Check AgentCustomer Master for prefill data if not already found in leads
        $masterCustomer = \App\Models\AgentCustomer::where('phone_no', $phone)
            ->whereRaw('LOWER(role) = ?', ['customer'])
            ->first();

        // Base prefill data
        $prefill = null;
        if ($masterCustomer) {
            $prefill = [
                'name' => $masterCustomer->name,
                'state' => $masterCustomer->state,
                'city' => $masterCustomer->city,
                'is_master' => true
            ];
        }

        if ($existingLeads->isEmpty()) {
            if ($prefill) {
                return response()->json(array_merge(['status' => 'clear'], $prefill));
            }
            return response()->json(['status' => 'clear']);
        }

        $latestLead = $existingLeads->first();
        $commonData = [
            'name' => $latestLead->name,
            'state' => $latestLead->state,
            'city' => $latestLead->city,
            'is_master' => false
        ];
        
        $activeEnquiries = $existingLeads->filter(function($l) {
            return $l->status && !in_array($l->status->slug, ['won', 'lost']);
        });
        
        if ($activeEnquiries->count() > 0) {
            $lead = $activeEnquiries->first();
            $managedBy = $lead->assignedUser->name ?? 'Unassigned';
            $isOwn = $lead->assigned_user_id == auth()->id();
            
            return response()->json(array_merge([
                'status' => 'exists',
                'lead_no' => $lead->lead_no,
                'managed_by' => $managedBy,
                'is_own' => $isOwn,
                'link' => route('lead.leads.show', $lead->id)
            ], $commonData));
        }
        
        $wonLeads = $existingLeads->filter(function($l) {
            return $l->status && $l->status->slug == 'won';
        });
        if ($wonLeads->count() > 0) {
            return response()->json(array_merge(['status' => 'repeat'], $commonData));
        }
        
        $lostLeads = $existingLeads->filter(function($l) {
            return $l->status && $l->status->slug == 'lost';
        });
        if ($lostLeads->count() > 0) {
            return response()->json(array_merge(['status' => 'recover'], $commonData));
        }
        
        return response()->json(array_merge(['status' => 'clear'], $commonData));
    }

    public function reportIndex()
    {
        if (!PermissionHelper::check('lead_report')) abort(403);
        Log::channel('lead')->info("Lead Report skeleton loaded by user ".auth()->id());
        $users = User::whereIn('id', auth()->user()->getViewableUserIds())->where('status', 1)->get();
        $sources = LeadSource::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $tags = LeadTag::where('status', 1)->get();

        return view('lead.leads.report', compact('users', 'sources', 'statuses', 'tags'));
    }

    public function reportData(Request $request)
    {
        if (!PermissionHelper::check('lead_report')) abort(403);
        // ── Build filtered base query (no eager loading — metrics via SQL) ──
        $base = Lead::query();
        $this->applyUserFilter($base);

        if ($request->from_date)        $base->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date)          $base->whereDate('created_at', '<=', $request->to_date);
        if ($request->status_id)        $base->where('status_id', $request->status_id);
        if ($request->source_id)        $base->where('source_id', $request->source_id);
        if ($request->assigned_user_id) $base->where('assigned_user_id', $request->assigned_user_id);
        if ($request->added_by)         $base->where('added_by', $request->added_by);
        if ($request->state) $base->where('state', $request->state);
        if ($request->city)  $base->where('city', $request->city);
        if ($request->tag_id) {
            $base->whereHas('tags', fn($q) => $q->where('lead_tags.id', $request->tag_id));
        }

        // ── KPI Metrics — pure SQL COUNT (no collection load!) ──
        $total        = (clone $base)->count();
        $wonCount     = (clone $base)->whereHas('status', fn($q) => $q->where('slug', 'won'))->count();
        $lostCount    = (clone $base)->whereHas('status', fn($q) => $q->where('slug', 'lost'))->count();
        $pendingCount = $total - $wonCount - $lostCount;

        $repeatLead = (clone $base)->where('is_repeat', 1)->count();
        $repeatWon  = (clone $base)->where('is_repeat', 1)->whereHas('status', fn($q) => $q->where('slug', 'won'))->count();
        $recoverLead = (clone $base)->where('is_returning_lost', 1)->count();
        $recoverWon  = (clone $base)->where('is_returning_lost', 1)->whereHas('status', fn($q) => $q->where('slug', 'won'))->count();

        // ── Followup Stats — single raw SQL query (no PHP array) ──
        $fStats = \DB::table('lead_followups')
            ->whereNotNull('complete_date')
            ->whereIn('lead_id', (clone $base)->select('leads.id'))
            ->selectRaw('
                SUM(CASE WHEN delay_days <= 0 THEN 1 ELSE 0 END) as done_in_time,
                SUM(CASE WHEN delay_days > 0  THEN 1 ELSE 0 END) as done_late
            ')->first();
        $doneInTime     = $fStats->done_in_time ?? 0;
        $doneLate       = $fStats->done_late ?? 0;
        $totalFollowups = $doneInTime + $doneLate;

        // ── Breakdown Distributions — GROUP BY (no full load!) ──
        $stageDistrib  = (clone $base)->selectRaw('status_id, COUNT(*) as cnt')
            ->groupBy('status_id')->pluck('cnt', 'status_id');

        $sourceDistrib = (clone $base)->selectRaw('source_id, COUNT(*) as cnt')
            ->groupBy('source_id')->pluck('cnt', 'source_id');

        $sourceWon     = (clone $base)->whereHas('status', fn($q) => $q->where('slug', 'won'))
            ->selectRaw('source_id, COUNT(*) as cnt')
            ->groupBy('source_id')->pluck('cnt', 'source_id');

        $addedDistrib  = (clone $base)->selectRaw('added_by, COUNT(*) as cnt')
            ->groupBy('added_by')->pluck('cnt', 'added_by');

        $assignDistrib = (clone $base)->selectRaw('assigned_user_id, COUNT(*) as cnt')
            ->groupBy('assigned_user_id')->pluck('cnt', 'assigned_user_id');

        // ── Star Performers — date range ONLY (ignores all other filters) ──
        // If no dates selected → All Time
        $leaderboard = User::withCount(['leads' => fn($q) =>
            $q->whereHas('status', fn($sq) => $sq->where('slug', 'won'))
              ->when($request->from_date, fn($q) => $q->whereDate('leads.updated_at', '>=', $request->from_date))
              ->when($request->to_date,   fn($q) => $q->whereDate('leads.updated_at', '<=', $request->to_date))
        ])->orderBy('leads_count', 'desc')->where('status', 1)->get()->filter(fn($u) => $u->leads_count > 0);

        $perfLabel = $request->from_date
            ? \Carbon\Carbon::parse($request->from_date)->format('d M Y')
              . ' \u2013 '
              . ($request->to_date ? \Carbon\Carbon::parse($request->to_date)->format('d M Y') : 'Now')
            : 'All Time';

        // ── Paginated Lead List — only 25 rows with eager loading ──
        $leads = (clone $base)->with(['source', 'status', 'assignedUser'])->latest()->paginate(25);

        // ── Filter Metadata ──
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $sources  = LeadSource::where('status', 1)->get();
        $users    = User::whereIn('id', auth()->user()->getViewableUserIds())->where('status', 1)->get();

        // Pass serialized filters so the view can build the chart AJAX URL
        $filterQuery = http_build_query(array_filter($request->only([
            'from_date','to_date','status_id','source_id','assigned_user_id','added_by','tag_id','state','city'
        ])));

        return view('lead.leads.report_data', compact(
            'leads', 'total', 'wonCount', 'lostCount', 'pendingCount',
            'repeatLead', 'repeatWon', 'recoverLead', 'recoverWon',
            'doneInTime', 'doneLate', 'totalFollowups',
            'statuses', 'sources', 'users',
            'stageDistrib', 'sourceDistrib', 'sourceWon',
            'addedDistrib', 'assignDistrib', 'filterQuery',
            'leaderboard', 'perfLabel'
        ));
    }

    public function reportCharts(Request $request)
    {
        if (!PermissionHelper::check('lead_report')) abort(403);
        // Apply ALL the same filters as reportData so chart matches the report exactly
        $base = Lead::query();
        $this->applyUserFilter($base);
        if ($request->from_date)        $base->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date)          $base->whereDate('created_at', '<=', $request->to_date);
        if ($request->status_id)        $base->where('status_id', $request->status_id);
        if ($request->source_id)        $base->where('source_id', $request->source_id);
        if ($request->assigned_user_id) $base->where('assigned_user_id', $request->assigned_user_id);
        if ($request->added_by)         $base->where('added_by', $request->added_by);
        if ($request->state)            $base->where('state', $request->state);
        if ($request->city)             $base->where('city', $request->city);
        if ($request->tag_id)           $base->whereHas('tags', fn($q) => $q->where('lead_tags.id', $request->tag_id));

        // If no date filter, default to last 6 months
        if (!$request->from_date && !$request->to_date) {
            $base->where('created_at', '>=', now()->subMonths(6));
        }

        $monthlyStats = (clone $base)
            ->selectRaw('COUNT(*) as count, MONTHNAME(created_at) as month, MONTH(created_at) as m, YEAR(created_at) as y')
            ->groupBy('y', 'm', 'month')
            ->orderBy('y')->orderBy('m')
            ->get();

        $wonMonthly = (clone $base)
            ->whereHas('status', fn($q) => $q->where('slug', 'won'))
            ->selectRaw('COUNT(*) as count, MONTHNAME(created_at) as month, MONTH(created_at) as m, YEAR(created_at) as y')
            ->groupBy('y', 'm', 'month')
            ->orderBy('y')->orderBy('m')
            ->get();

        $lostMonthly = (clone $base)
            ->whereHas('status', fn($q) => $q->where('slug', 'lost'))
            ->selectRaw('COUNT(*) as count, MONTHNAME(created_at) as month, MONTH(created_at) as m, YEAR(created_at) as y')
            ->groupBy('y', 'm', 'month')->orderBy('y')->orderBy('m')->get();

        $pendingMonthly = (clone $base)
            ->whereHas('status', fn($q) => $q->whereNotIn('slug', ['won','lost']))
            ->selectRaw('COUNT(*) as count, MONTHNAME(created_at) as month, MONTH(created_at) as m, YEAR(created_at) as y')
            ->groupBy('y', 'm', 'month')->orderBy('y')->orderBy('m')->get();

        $doneInTime = \DB::table('lead_followups')->whereNotNull('complete_date')->where('delay_days', '<=', 0)->count();
        $doneLate   = \DB::table('lead_followups')->whereNotNull('complete_date')->where('delay_days', '>',  0)->count();
        $onTimePct  = ($doneInTime + $doneLate) > 0 ? round(($doneInTime / ($doneInTime + $doneLate)) * 100, 1) : 100;

        $wonLeads = $monthlyStats->map(fn($m) => (int)($wonMonthly->where('m',$m->m)->where('y',$m->y)->first()->count ?? 0))->values();
        $lostLeads = $monthlyStats->map(fn($m) => (int)($lostMonthly->where('m',$m->m)->where('y',$m->y)->first()->count ?? 0))->values();
        $pendingLeads = $monthlyStats->map(fn($m) => (int)($pendingMonthly->where('m',$m->m)->where('y',$m->y)->first()->count ?? 0))->values();

        return response()->json([
            'months'       => $monthlyStats->pluck('month')->values(),
            'newLeads'     => $monthlyStats->pluck('count')->map(fn($v) => (int)$v)->values(),
            'wonLeads'     => $wonLeads,
            'lostLeads'    => $lostLeads,
            'pendingLeads' => $pendingLeads,
            'onTimePct'    => $onTimePct,
        ]);
    }

    public function reportPdf(Request $request)
    {
        $query = Lead::with(['source', 'status', 'assignedUser', 'tags']);
        $this->applyUserFilter($query);

        if ($request->from_date)        $query->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date)          $query->whereDate('created_at', '<=', $request->to_date);
        if ($request->status_id)        $query->where('status_id', $request->status_id);
        if ($request->source_id)        $query->where('source_id', $request->source_id);
        if ($request->assigned_user_id) $query->where('assigned_user_id', $request->assigned_user_id);
        if ($request->added_by)         $query->where('added_by', $request->added_by);
        if ($request->state)            $query->where('state', 'like', '%'.$request->state.'%');
        if ($request->city)             $query->where('city', 'like', '%'.$request->city.'%');
        if ($request->tag_id) {
            $query->whereHas('tags', fn($q) => $q->where('lead_tag_id', $request->tag_id));
        }

        $allResults = $query->latest()->get();

        $total        = $allResults->count();
        $wonCount     = $allResults->where('status.slug', 'won')->count();
        $lostCount    = $allResults->where('status.slug', 'lost')->count();
        $pendingCount = $total - $wonCount - $lostCount;
        $repeatLead   = $allResults->where('is_repeat', 1)->count();
        $repeatWon    = $allResults->where('is_repeat', 1)->where('status.slug', 'won')->count();
        $recoverLead  = $allResults->where('is_returning_lost', 1)->count();
        $recoverWon   = $allResults->where('is_returning_lost', 1)->where('status.slug', 'won')->count();

        $followups   = LeadFollowup::whereIn('lead_id', $allResults->pluck('id'))->get();
        $doneInTime  = $followups->whereNotNull('complete_date')->where('delay_days', '<=', 0)->count();
        $doneLate    = $followups->whereNotNull('complete_date')->where('delay_days', '>', 0)->count();

        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $sources  = LeadSource::where('status', 1)->get();
        $users    = User::where('status', 1)->get();

        // Star Performers — date range ONLY, no other filters, All Time if no dates
        $leaderboard = User::withCount(['leads' => fn($q) =>
            $q->whereHas('status', fn($sq) => $sq->where('slug', 'won'))
              ->when($request->from_date, fn($q) => $q->whereDate('leads.updated_at', '>=', $request->from_date))
              ->when($request->to_date,   fn($q) => $q->whereDate('leads.updated_at', '<=', $request->to_date))
        ])->orderBy('leads_count', 'desc')->where('status', 1)->get()->filter(fn($u) => $u->leads_count > 0);

        $perfLabel = $request->from_date
            ? \Carbon\Carbon::parse($request->from_date)->format('d M Y')
              . ' – '
              . ($request->to_date ? \Carbon\Carbon::parse($request->to_date)->format('d M Y') : 'Now')
            : 'All Time';

        $filters = [
            'from_date'    => $request->from_date,
            'to_date'      => $request->to_date,
            'generated_at' => now()->format('d M Y, h:i A'),
            'generated_by' => auth()->user()->name ?? 'Admin',
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('lead.leads.report_pdf', compact(
            'allResults', 'total', 'wonCount', 'lostCount', 'pendingCount',
            'repeatLead', 'repeatWon', 'recoverLead', 'recoverWon',
            'doneInTime', 'doneLate', 'statuses', 'sources', 'users', 'filters',
            'leaderboard', 'perfLabel'
        ))->setPaper('a4', 'landscape');

        $filename = 'lead-report-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->stream($filename);
    }

    public function simpleReportPdf(Request $request)
    {
        $query = Lead::with(['source', 'status', 'assignedUser', 'tags']);
        $this->applyUserFilter($query);

        if ($request->from_date)        $query->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date)          $query->whereDate('created_at', '<=', $request->to_date);
        if ($request->status_id)        $query->where('status_id', $request->status_id);
        if ($request->source_id)        $query->where('source_id', $request->source_id);
        if ($request->assigned_user_id) $query->where('assigned_user_id', $request->assigned_user_id);
        if ($request->added_by)         $query->where('added_by', $request->added_by);
        if ($request->state)            $query->where('state', 'like', '%'.$request->state.'%');
        if ($request->city)             $query->where('city', 'like', '%'.$request->city.'%');
        if ($request->tag_id) {
            $query->whereHas('tags', fn($q) => $q->where('lead_tag_id', $request->tag_id));
        }

        $allResults = $query->latest()->get();

        $filters = [
            'from_date'    => $request->from_date,
            'to_date'      => $request->to_date,
            'generated_at' => now()->format('d M Y, h:i A'),
            'generated_by' => auth()->user()->name ?? 'Admin',
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('lead.leads.simple_report_pdf', compact('allResults', 'filters'))
            ->setPaper('a4', 'landscape');

        $filename = 'table-lead-report-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->stream($filename);
    }

    public function datatable(Request $request)
    {
        $limit = $request->value ?? 50;
        $query = Lead::with(['source', 'status', 'assignedUser', 'tags', 'histories.user']);
        
        $this->applyUserFilter($query);
        
        // Custom Queue Types
        if ($request->type == 'pending') {
            $query->whereHas('status', function($q) {
                $q->whereNotIn('slug', ['won', 'lost']);
            });
        } elseif ($request->type == 'won') {
            $query->whereHas('status', function($q) {
                $q->where('slug', 'won');
            });
        } elseif ($request->type == 'lost') {
            $query->whereHas('status', function($q) {
                $q->where('slug', 'lost');
            });
        }

        // Filters
        if ($request->status_id) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->source_id) {
            $query->where('source_id', $request->source_id);
        }
        if ($request->assigned_user_id) {
            $query->where('assigned_user_id', $request->assigned_user_id);
        }
        if ($request->tag_id) {
            $query->whereHas('tags', function($q) use ($request) {
                $q->where('lead_tags.id', $request->tag_id);
            });
        }

        if ($request->date == 'today') {
            $query->whereDate('created_at', now()->toDateString());
        } else {
            if ($request->from_date) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->to_date) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('lead_no', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('city', 'like', '%' . $search . '%')
                  ->orWhere('state', 'like', '%' . $search . '%')
                  ->orWhere('regarding', 'like', '%' . $search . '%');
            });
        }
        
        $query->selectRaw('MAX(id) as id, phone, MAX(name) as name, MAX(state) as state, MAX(city) as city, MAX(source_id) as source_id, MAX(assigned_user_id) as assigned_user_id, MAX(status_id) as status_id, MAX(lead_no) as lead_no, MAX(regarding) as regarding, COUNT(*) as lead_count')
            ->groupBy('phone');

        $leads = $query->latest('id')->paginate($limit);
        $users = User::whereIn('id', auth()->user()->getViewableUserIds())->where('status', 1)->get();
        return view('lead.leads.datatable', compact('leads', 'users'));
    }

    public function create()
    {
        $users = User::whereIn('id', auth()->user()->getViewableUserIds())->where('status', 1)->get();
        $sources = LeadSource::where('status', 1)->get();
        $tags = LeadTag::where('status', 1)->get();
        
        // Dynamic States from DB
        $states = LeadState::where('status', 1)->orderBy('name')->pluck('name')->toArray();
        
        // Generate Next Lead No
        $lastLead = Lead::withTrashed()->orderBy('id', 'desc')->first();
        $nextId = $lastLead ? ($lastLead->id + 1) : 1;
        $leadNo = 'LEAD-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        
        return view('lead.leads.add_edit', compact('sources', 'tags', 'users', 'states', 'leadNo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|digits:10',
            'state' => 'required',
        ], [
            'phone.digits' => 'Phone number must be exactly 10 digits.',
        ]);

        $existingLeads = Lead::with('status')->where('phone', $request->phone)->get();
        $isRepeat = 0;
        $isReturningLost = 0;

        if ($existingLeads->count() > 0) {
            $activeEnquiries = $existingLeads->filter(function($l) {
                return $l->status && !in_array($l->status->slug, ['won', 'lost']);
            });
            
            if ($activeEnquiries->count() > 0) {
                return back()->withErrors(['phone' => 'An active lead with this phone number already exists. Please close the active pipeline first.'])->withInput();
            }
            
            $wonLeads = $existingLeads->filter(function($l) {
                return $l->status && $l->status->slug == 'won';
            });
            
            if ($wonLeads->count() > 0) {
                $isRepeat = 1;
            } else {
                $isReturningLost = 1;
            }
        }

        // Auto Generate Lead No if duplicate check or sequence
        $lastLead = Lead::withTrashed()->orderBy('id', 'desc')->first();
        $nextId = $lastLead ? ($lastLead->id + 1) : 1;
        $leadNo = 'LEAD-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        // Get First Status
        $firstStatus = LeadStatus::orderBy('sort_order')->first();

        $lead = Lead::create([
            'lead_no' => $leadNo,
            'name' => $request->name,
            'phone' => $request->phone,
            'state' => $request->state,
            'city' => $request->city,
            'source_id' => $request->source_id,
            'regarding' => $request->regarding,
            'assigned_user_id' => $request->assigned_user_id ?? auth()->id(),
            'status_id' => $firstStatus->id ?? null,
            'added_by' => auth()->id(),
            'remarks' => $request->remarks,
            'lead_remarks' => $request->lead_remarks,
            'is_repeat' => $isRepeat,
            'is_returning_lost' => $isReturningLost
        ]);

        // Sync to LeadAgentCustomer Master
        $lac = \App\Models\LeadAgentCustomer::where('phone_no', $request->phone)->first();
        if (!$lac) {
            $lac = \App\Models\LeadAgentCustomer::create([
                'name' => $request->name,
                'phone_no' => $request->phone,
                'role' => 'Customer',
                'type' => 'A',
                'state' => $request->state,
                'city' => $request->city,
                'lead_id' => $lead->id,
                'status' => 1,
                'user_id' => auth()->id()
            ]);
            $lac->code = 'SPFC' . $lac->id . rand(10000, 99999);
            $lac->save();
        } else {
            $lac->update(['lead_id' => $lead->id]);
        }
        $lead->update(['lead_agent_customer_id' => $lac->id]);

        if ($request->has('tags')) {
            $lead->tags()->attach($request->tags);
        }

        // Initial First Communication (Completed)
        if ($request->remarks) {
            LeadFollowup::create([
                'lead_id' => $lead->id,
                'status_at_time_id' => $firstStatus->id ?? null,
                'type' => 'Call',
                'followup_date' => now(),
                'complete_date' => now(),
                'remarks' => $request->remarks,
                'added_by' => auth()->id()
            ]);
        }

        // Future Planned Followup (Pending)
        if ($request->next_followup) {
            $days = (int)$request->next_followup;
            $scheduledDate = now()->addDays($days)->setTime(12, 0, 0);

            LeadFollowup::create([
                'lead_id' => $lead->id,
                'status_at_time_id' => $firstStatus->id ?? null,
                'followup_date' => $scheduledDate,
                'added_by' => auth()->id()
            ]);
        }

        // Log History
        LeadHistory::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'created',
            'description' => "Lead $leadNo created and assigned to " . ($lead->assignedUser->name ?? 'None')
        ]);

        return redirect()->route('lead.index')->with('success', "Lead $leadNo added successfully");
    }

    public function edit($id)
    {
        $lead = Lead::findOrFail($id);
        $sources = LeadSource::where('status', 1)->get();
        $tags = LeadTag::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $users = User::where('status', 1)->get();
        $states = LeadState::where('status', 1)->orderBy('name')->pluck('name')->toArray();
        
        return view('lead.leads.add_edit', compact('lead', 'sources', 'tags', 'statuses', 'users', 'states'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|digits:10',
            'state' => 'required',
        ], [
            'phone.digits' => 'Phone number must be exactly 10 digits.',
        ]);

        $lead = Lead::findOrFail($id);
        
        if ($lead->phone !== $request->phone) {
            $existingLeads = Lead::with('status')->where('phone', $request->phone)->where('id', '!=', $id)->get();
            if ($existingLeads->count() > 0) {
                $activeEnquiries = $existingLeads->filter(function($l) {
                    return $l->status && !in_array($l->status->slug, ['won', 'lost']);
                });
                if ($activeEnquiries->count() > 0) {
                    return back()->withErrors(['phone' => 'An active lead with this phone number already exists. Please close the active pipeline first.'])->withInput();
                }
                
                $wonLeads = $existingLeads->filter(function($l) {
                    return $l->status && $l->status->slug == 'won';
                });
                
                $lead->is_repeat = $wonLeads->count() > 0 ? 1 : 0;
                $lead->is_returning_lost = $wonLeads->count() == 0 ? 1 : 0;
            } else {
                $lead->is_repeat = 0;
                $lead->is_returning_lost = 0;
            }
        }

        $old_data = $lead->toArray(); // Capture old values for history logging
        
        $lead->fill($request->all());
        $dirty = $lead->getDirty(); // See what changed

        if ($request->has('tags')) {
            $lead->tags()->sync($request->tags);
        }

        $lead->save();

        // Log History with details of what exactly changed
        if (count($dirty) > 0) {
            $changes = [];
            foreach ($dirty as $key => $newValue) {
                if ($key == 'updated_at') continue;
                $oldValue = $old_data[$key] ?? 'None';
                $label = ucwords(str_replace('_', ' ', $key));
                $changes[] = "<strong>$label</strong> changed from <i>'$oldValue'</i> to <i>'$newValue'</i>";
            }

            if (count($changes) > 0) {
                LeadHistory::create([
                    'lead_id' => $id,
                    'user_id' => auth()->id(),
                    'type' => 'manual_edit',
                    'description' => "Profile updated: " . implode(', ', $changes)
                ]);
            }
        }

        return redirect()->route('lead.index')->with('success', 'Lead updated successfully');
    }

    public function followupList(Request $request)
    {
        $user    = auth()->user();
        $userIds = $user->getViewableUserIds();
        $now     = now()->endOfDay();
        $filter  = $request->filter ?? 'pending'; // all, pending, upcoming

        // 1. Customer Followups
        $custQuery = LeadFollowup::whereHas('lead', function($q) use ($userIds) {
                $q->whereIn('assigned_user_id', $userIds);
            })
            ->with(['lead.status'])
            ->whereNull('complete_date');

        if ($filter === 'pending') {
            $custQuery->where('followup_date', '<=', $now);
        } elseif ($filter === 'upcoming') {
            $custQuery->where('followup_date', '>', $now);
        }

        $customerFollowups = $custQuery->get()->map(function($f) {
            $f->model_type = 'customer';
            return $f;
        });

        // 2. Agent Followups
        $agentQuery = \App\Models\AgentOverallFollowup::with(['agent.latestLead'])
            ->where('status', 0);

        if ($filter === 'pending') {
            $agentQuery->where('followup_date', '<=', $now);
        } elseif ($filter === 'upcoming') {
            $agentQuery->where('followup_date', '>', $now);
        }

        if (strtolower($user->role) !== 'admin') {
            $agentQuery->whereHas('agent.leads', function($q) use ($userIds) {
                $q->whereIn('assigned_user_id', $userIds);
            });
        }

        $agentFollowups = $agentQuery->get()->map(function($f) {
            $f->model_type = 'agent';
            return $f;
        });

        $followups = $customerFollowups->concat($agentFollowups)->sortBy('followup_date');
        
        $titleMap = [
            'all'      => 'All Followups',
            'pending'  => 'Today & Pending Followup',
            'upcoming' => 'Upcoming Followup'
        ];
        $title = $titleMap[$filter] ?? 'Followup Management';
        
        return view('lead.followup.index', compact('followups', 'title', 'filter'));
    }

    public function followupStore(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $oldStatusName = $lead->status->name ?? 'None';
        $currentStatusId = $lead->status_id;

        // 2. Determine Next Action
        $nextAction = $request->next_action; // continue, status_id, lost, won
        $newStatusId = $currentStatusId;

        if (is_numeric($nextAction)) {
            $newStatusId = (int)$nextAction;
        } elseif ($nextAction == 'next_step') {
            // Keep old logic for backward compatibility if needed
            $nextStatus = LeadStatus::where('sort_order', '>', $lead->status->sort_order ?? 0)
                ->whereNotIn('slug', ['lost', 'won'])
                ->orderBy('sort_order')
                ->first();
            if ($nextStatus) {
                $newStatusId = $nextStatus->id;
            }
        } elseif ($nextAction == 'lost') {
            $lostStatus = LeadStatus::where('slug', 'lost')->first();
            $newStatusId = $lostStatus->id ?? $currentStatusId;
            
            $lostReason = $request->lost_reason;
            if ($lostReason == 'Other' && $request->other_lost_reason) {
                $lostReason = $request->other_lost_reason;
            }

            if ($lostReason) {
                \App\Models\LeadStepDetail::updateOrCreate(
                    ['lead_id' => $id, 'status_id' => $newStatusId, 'field_key' => 'lost_reason'],
                    ['field_value' => $lostReason]
                );
            }
        } elseif ($nextAction == 'won') {
            $wonStatus = LeadStatus::where('slug', 'won')->first();
            $newStatusId = $wonStatus->id ?? $currentStatusId;
            if ($request->order_no) {
                $lead->update(['order_no' => $request->order_no]);
            }
            
            // Auto-convert to AgentCustomer
            if ($lead->phone) {
                $existingCustomer = \App\Models\AgentCustomer::where('phone_no', $lead->phone)->first();
                if (!$existingCustomer) {
                    $agentCustomer = new \App\Models\AgentCustomer();
                    $agentCustomer->name = $lead->name;
                    $agentCustomer->phone_no = $lead->phone;
                    $agentCustomer->role = 'Customer';
                    $agentCustomer->user_id = auth()->id() ?? 1;
                    $agentCustomer->status = 1;
                    $agentCustomer->save();
                    
                    do {
                        $code = 'SPFC' . $agentCustomer->id . rand(10000, 99999);
                    } while (\App\Models\AgentCustomer::where('code', $code)->exists());
                    
                    $agentCustomer->code = $code;
                    $agentCustomer->save();
                }
                
                // Cleanup LeadAgentCustomer upon successful win
                \App\Models\LeadAgentCustomer::where('phone_no', $lead->phone)->delete();
            }
        }

        // 2.5 Save any dynamic step fields provided
        $standardFields = ['_token', '_method', 'next_action', 'order_no', 'lost_reason', 'followup_date', 'remarks', 'next_followup_date'];
        foreach ($request->except($standardFields) as $key => $value) {
            if ($value !== null && $value !== '') {
                \App\Models\LeadStepDetail::updateOrCreate(
                    ['lead_id' => $id, 'status_id' => $newStatusId, 'field_key' => $key],
                    ['field_value' => is_array($value) ? json_encode($value) : $value]
                );
            }
        }

        $newStatusName = $oldStatusName;

        // 3. Update Lead Status if changed
        if ($newStatusId != $currentStatusId) {
            $lead->update(['status_id' => $newStatusId]);
            $statusObj = LeadStatus::find($newStatusId);
            $newStatusName = $statusObj->name ?? $oldStatusName;
            
            // Log History for Switch
            LeadHistory::create([
                'lead_id' => $id,
                'user_id' => auth()->id(),
                'type' => 'step_changed',
                'description' => "Lead moved from $oldStatusName to $newStatusName"
            ]);
        }

        // 4. Find AND Complete the most recent pending followup OR create new
        $pendingFollowup = LeadFollowup::where('lead_id', $id)->whereNull('complete_date')->orderBy('followup_date', 'desc')->first();
        
        if ($pendingFollowup) {
            $now = now();
            $scheduledDate = \Carbon\Carbon::parse($pendingFollowup->followup_date);
            $delay = 0;
            if ($now->greaterThan($scheduledDate)) {
                $delay = (int)$now->diffInDays($scheduledDate);
            }

            $pendingFollowup->update([
                'complete_date' => $now,
                'delay_days' => $delay,
                'remarks' => $nextAction == 'lost' ? ($request->remarks ?? 'Lead marked as Lost') : 'Action Completed',
                'completed_by' => auth()->id()
            ]);
        } else {
            LeadFollowup::create([
                'lead_id' => $id,
                'status_at_time_id' => $currentStatusId,
                'followup_date' => now(), // Instant followup
                'remarks' => $nextAction == 'lost' ? ($request->remarks ?? 'Lead marked as Lost') : 'Action Completed',
                'complete_date' => now(),
                'delay_days' => 0,
                'completed_by' => auth()->id(),
                'added_by' => auth()->id()
            ]);
        }

        // 5. Schedule Next PENDING Followup if provided (Days)
        if ($request->next_followup_date) {
            $days = (int)$request->next_followup_date;
            $scheduledDate = now()->addDays($days)->setTime(12, 0, 0);

            LeadFollowup::create([
                'lead_id' => $id,
                'status_at_time_id' => $newStatusId, // Planned touchpoint for the NEW status
                'followup_date' => $scheduledDate,
                'remarks' => $request->remarks ?? 'Planned next followup',
                'complete_date' => null, // Pending status
                'added_by' => auth()->id()
            ]);
        }

        // 6. Log a generic History entry for the touchpoint so it shows in the History Modal
        $followupDateTime = \Carbon\Carbon::parse($request->followup_date)->format('d M h:i A');
        $remarksText = $request->remarks ? "Notes: " . $request->remarks : ($nextAction == 'lost' ? "Reason: " . $request->lost_reason : "No notes");
        
        LeadHistory::create([
            'lead_id' => $id,
            'user_id' => auth()->id(),
            'type' => 'followup',
            'description' => "Followup Logged ($followupDateTime). Status: $oldStatusName. $remarksText"
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Followup action recorded successfully',
                'lead_id' => $id,
                'new_status_name' => $newStatusName ?? $lead->status->name
            ]);
        }

        return back()->with('success', 'Followup action recorded successfully');
    }

    public function getTimeline($id)
    {
        $lead = Lead::with(['followups', 'status', 'assignedUser'])->findOrFail($id);
        return view('lead.leads.timeline', compact('lead'))->render();
    }

    public function getStatusHistory($id)
    {
        $lead = Lead::with('histories.user')->findOrFail($id);
        return view('lead.leads.sidebar_status_history', compact('lead'))->render();
    }

    public function getLeadCard($id)
    {
        $lead = Lead::with(['source', 'status', 'assignedUser', 'addedBy', 'histories'])->findOrFail($id);
        return view('lead.leads.sidebar_lead_card', compact('lead'))->render();
    }

    public function history_modal($id)
    {
        $lead = Lead::with(['histories' => function($q) {
            $q->whereIn('type', ['created', 'manual_edit', 'transferred'])->with('user');
        }])->findOrFail($id);
        
        return view('lead.leads.history_modal', compact('lead'));
    }

    public function followup_modal($id)
    {
        $lead = Lead::with(['status', 'stepDetails'])->findOrFail($id);
        
        // Optimize: Calculate next steps in controller
        $nextSteps = [];
        $allNextSteps = LeadStatus::where('sort_order', '>', $lead->status->sort_order ?? 0)
            ->where(function($q) {
                $q->whereNotIn('slug', ['lost', 'won'])->orWhereNull('slug');
            })
            ->orderBy('sort_order', 'asc')
            ->get();

        foreach($allNextSteps as $step) {
            $nextSteps[] = $step;
            if($step->is_required) break; 
        }

        $isLastStep = !LeadStatus::where('sort_order', '>', $lead->status->sort_order ?? 0)
            ->where('is_required', 1)
            ->where(function($q) {
                $q->whereNotIn('slug', ['lost', 'won'])->orWhereNull('slug');
            })
            ->exists();

        $lostReasons = [
            'Requirement Cancelled',
            'Purchased from Competitor',
            'Price Too High',
            'No Response / Ghosted',
            'Budget Issues',
            'Other'
        ];

        return view('lead.leads.followup_modal', compact('lead', 'nextSteps', 'isLastStep', 'lostReasons'));
    }

    public function transfer(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $old_user = $lead->assignedUser->name ?? 'None';
        
        $lead->update([
            'assigned_user_id' => $request->assigned_user_id
        ]);

        $new_user = User::find($request->assigned_user_id)->name;

        // Log History
        LeadHistory::create([
            'lead_id' => $id,
            'user_id' => auth()->id(),
            'type' => 'transferred',
            'description' => "Lead transferred from $old_user to $new_user. Remarks: " . $request->transfer_remarks
        ]);

        return back()->with('success', "Lead transferred to $new_user");
    }

    public function show($id)
    {
        $lead = Lead::with(['source', 'status', 'assignedUser', 'tags', 'followups', 'stepDetails'])->findOrFail($id);
        
        // Find sibling leads for the same phone
        $allLeads = Lead::with(['status', 'assignedUser'])
            ->where('phone', $lead->phone)
            ->latest('id')
            ->get();

        $statuses = LeadStatus::orderBy('sort_order')->get();
        $stepData = $lead->stepDetails->pluck('field_value', 'field_key')->toArray();
        
        return view('lead.leads.show', compact('lead', 'statuses', 'stepData', 'allLeads'));
    }

    public function updateStepData(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $status_id = $lead->status_id;

        foreach ($request->except(['_token', '_method', 'next_status_id', 'move_to_next']) as $key => $value) {
            LeadStepDetail::updateOrCreate(
                ['lead_id' => $id, 'status_id' => $status_id, 'field_key' => $key],
                ['field_value' => is_array($value) ? json_encode($value) : $value]
            );
        }

        if ($request->move_to_next && $request->next_status_id) {
            $oldStatus = $lead->status->name ?? 'None';
            $lead->update(['status_id' => $request->next_status_id]);
            $newStatus = LeadStatus::find($request->next_status_id)->name;

            LeadHistory::create([
                'lead_id' => $id,
                'user_id' => auth()->id(),
                'type' => 'step_changed',
                'description' => "Lead moved from $oldStatus to $newStatus"
            ]);

            return redirect()->route('lead.leads.show', $id)->with('success', "Lead moved to $newStatus");
        }

        // Log generic history for step technical data update
        LeadHistory::create([
            'lead_id' => $id,
            'user_id' => auth()->id(),
            'type' => 'manual_edit',
            'description' => "Technical step details updated"
        ]);

        return redirect()->route('lead.leads.show', $id)->with('success', "Technical attributes saved successfully");
    }

    public function checkJobCardNo(Request $request)
    {
        $jobCardNo = trim($request->job_card_no);
        $leadId = $request->lead_id;

        $jobCard = \App\Models\JobCard::where('job_card_no', $jobCardNo)->first();

        if (!$jobCard) {
            return response()->json([
                'status' => 'error',
                'message' => 'Job Card No does not exist in production database.'
            ]);
        }

        if ($jobCard->lead_id && $jobCard->lead_id != $leadId) {
            $linkedLead = Lead::find($jobCard->lead_id);
            return response()->json([
                'status' => 'error',
                'message' => 'This Job Card is already linked to Lead No: ' . ($linkedLead->lead_no ?? 'ID:'.$jobCard->lead_id)
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Job Card found and available to link.'
        ]);
    }

    public function updateJobCardNo(Request $request, $id)
    {
        $user = auth()->user();
        $lead = Lead::findOrFail($id);
        
        // ADMIN LOGIC: Only Admin can edit or link
        if (strtolower($user->role) == 'admin') {
            $newNo = trim($request->order_no ?? '');
            $oldNo = $lead->order_no;
            
            // 1. Unlink previous Job Card if exists and changed
            if ($oldNo && $oldNo != $newNo) {
                \App\Models\JobCard::where('job_card_no', $oldNo)->update(['lead_id' => null]);
            }
            
            // 2. Clear case
            if (empty($newNo)) {
                $lead->update(['order_no' => null]);
                LeadHistory::create([
                    'lead_id' => $id,
                    'user_id' => $user->id,
                    'type' => 'manual_edit',
                    'description' => "Job Card link REMOVED by Admin. Previous: '$oldNo'"
                ]);
                return back()->with('success', "Production ID unlinked by Admin. Now open for re-entry.");
            }

            // 3. New link check
            $jobCard = \App\Models\JobCard::where('job_card_no', $newNo)->first();
            if (!$jobCard) {
                return back()->with('error', "Production verification failed: '$newNo' not found.");
            }

            if ($jobCard->lead_id && $jobCard->lead_id != $id) {
                $linkedTo = Lead::find($jobCard->lead_id);
                return back()->with('error', "Already linked to " . ($linkedTo->lead_no ?? 'ID:'.$jobCard->lead_id));
            }

            // 4. Update
            $lead->update(['order_no' => $newNo]);
            $jobCard->update(['lead_id' => $id]);

            LeadHistory::create([
                'lead_id' => $id,
                'user_id' => $user->id,
                'type' => 'manual_edit',
                'description' => "Job Card REPLACED by Admin from '$oldNo' to '$newNo'"
            ]);

            return back()->with('success', "Production link updated successfully by Admin.");

        } else {
            return back()->with('error', "Access Denied: Only Admin can link or modify a Production ID.");
        }
    }

    public function markLost(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $lostStatus = LeadStatus::where('slug', 'lost')->first();
        
        if ($lostStatus) {
            $oldStatus = $lead->status->name ?? 'None';
            $lead->update(['status_id' => $lostStatus->id]);
            
            \App\Models\LeadHistory::create([
                'lead_id' => $id,
                'user_id' => auth()->id(),
                'type' => 'lost',
                'description' => "Lead marked as LOST. Reason: " . $request->lost_reason
            ]);

            \App\Models\LeadStepDetail::updateOrCreate(
                ['lead_id' => $id, 'status_id' => $lostStatus->id, 'field_key' => 'lost_reason'],
                ['field_value' => $request->lost_reason]
            );

            return redirect()->route('lead.index')->with('danger', 'Lead marked as LOST');
        }

        return back();
    }

    public function rollbackStage(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $targetStatusId = $request->status_id;
        $targetStatus = \App\Models\LeadStatus::findOrFail($targetStatusId);
        $oldStatus = $lead->status->name ?? 'None';
        
        $lead->update(['status_id' => $targetStatusId]);
        
        \App\Models\LeadHistory::create([
            'lead_id' => $id,
            'user_id' => auth()->id(),
            'type' => 'rollback',
            'description' => "Stage rolled back from '$oldStatus' to '{$targetStatus->name}'."
        ]);

        // Add a system followup to mark the rollback in timeline nicely
        \App\Models\LeadFollowup::create([
            'lead_id' => $id,
            'status_at_time_id' => $targetStatusId,
            'type' => 'System',
            'followup_date' => now(),
            'complete_date' => now(),
            'remarks' => "🔄 <strong>Stage Rolled Back</strong>: This lead was moved back to <strong>{$targetStatus->name}</strong> stage from <strong>$oldStatus</strong>.",
            'added_by' => auth()->id(),
            'completed_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Lead rolled back to {$targetStatus->name}"
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $leadNo = $lead->lead_no;
        
        // Soft Deleting Related Records
        $lead->followups()->delete();
        $lead->histories()->delete();
        $lead->stepDetails()->delete();
        $lead->tags()->detach();
        $lead->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Lead $leadNo deleted."
            ]);
        }

        return redirect()->route('lead.index')->with('danger', 'Lead deleted successfully');
    }

    public function destroyAllForClient(Request $request, $id)
    {
        $currentLead = Lead::findOrFail($id);
        $phone = $currentLead->phone;
        
        $allLeads = Lead::where('phone', $phone)->get();
        $count = $allLeads->count();

        foreach ($allLeads as $lead) {
            $lead->followups()->delete();
            $lead->histories()->delete();
            $lead->stepDetails()->delete();
            $lead->tags()->detach();
            $lead->delete();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "All $count enquiries for phone $phone have been deleted."
            ]);
        }

        return redirect()->route('lead.index')->with('danger', "All $count enquiries deleted successfully");
    }
    public function jobCardStatus(Request $request)
    {
        $query = \App\Models\JobCard::whereNotNull('lead_id')
            ->with(['lead', 'processes.user', 'processes.machine']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('job_card_no', 'like', '%' . $search . '%')
                  ->orWhereHas('lead', function($lq) use ($search) {
                      $lq->where('name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('product', 'like', '%' . $search . '%');
                  });
            });
        }

        $jobCards = $query->latest()->paginate(15);
        
        if ($request->ajax()) {
            return view('lead.job_card.status_table', compact('jobCards'));
        }

        return view('lead.job_card.status', compact('jobCards'));
    }

    public function repeatSuggestionsIndex(Request $request)
    {
        $tenDaysAgo = now()->subDays(10);
        $query = \App\Models\JobCard::whereNotNull('lead_id')
            ->whereNotNull('complete_date')
            ->where('complete_date', '<=', $tenDaysAgo)
            ->whereHas('lead', function($q) {
                $q->where('is_repeat', 0);
            })
            ->with(['lead.status', 'lead.assignedUser', 'lead.source']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('job_card_no', 'like', '%' . $search . '%')
                  ->orWhereHas('lead', function($lq) use ($search) {
                      $lq->where('name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                  });
            });
        }

        $leads = $query->latest('complete_date')->paginate(20);

        if ($request->ajax()) {
            return view('lead.leads.repeat_suggestions_table', compact('leads'));
        }

        return view('lead.leads.repeat_suggestions', compact('leads'));
    }

    private function applyUserFilter($query)
    {
        $user = auth()->user();
        if (strtolower($user->role) != 'admin') {
            $userIds = $user->getViewableUserIds();
            $query->whereIn('assigned_user_id', $userIds);
        }
        return $query;
    }
    public function getFollowupModal($id)
    {
        $lead = Lead::with(['status', 'stepDetails'])->findOrFail($id);
        
        // Logical Next Steps (Higher Sort Order)
        $nextSteps = LeadStatus::where('sort_order', '>', $lead->status->sort_order ?? 0)
            ->where(function($q) {
                $q->whereNotIn('slug', ['lost', 'won'])->orWhereNull('slug');
            })
            ->orderBy('sort_order')
            ->get();
            
        // Stop at first required step
        $filteredSteps = [];
        foreach($nextSteps as $step) {
            $filteredSteps[] = $step;
            if($step->is_required) break; 
        }
        $nextSteps = collect($filteredSteps);

        $isLastStep = !LeadStatus::where('sort_order', '>', $lead->status->sort_order ?? 0)
            ->where('is_required', 1)
            ->where(function($q) {
                $q->whereNotIn('slug', ['lost', 'won'])->orWhereNull('slug');
            })
            ->exists();

        $lostReasons = [
            'Requirement Cancelled',
            'Purchased from Competitor',
            'Price Too High',
            'No Response / Ghosted',
            'Budget Issues',
            'Other'
        ];

        return view('lead.leads.followup_modal', compact('lead', 'nextSteps', 'isLastStep', 'lostReasons'));
    }
    public function getProfileContent($id)
    {
        $lead = Lead::with(['source', 'status', 'assignedUser', 'tags', 'followups', 'stepDetails'])->findOrFail($id);
        
        $allLeads = Lead::with(['status', 'assignedUser'])
            ->where('phone', $lead->phone)
            ->latest('id')
            ->get();

        $statuses = LeadStatus::orderBy('sort_order')->get();
        $stepData = $lead->stepDetails->pluck('field_value', 'field_key')->toArray();

        return response()->json([
            'main_content' => view('lead.leads.profile_main_content', compact('lead', 'statuses', 'stepData', 'allLeads'))->render(),
            'sidebar_status_name' => ($lead->status && $lead->status->slug == 'won') ? '🥳 ' . $lead->status->name : (($lead->status && $lead->status->slug == 'lost') ? '😔 ' . $lead->status->name : $lead->status->name),
            'sidebar_followup_btn' => view('lead.leads.sidebar_followup_btn', compact('lead'))->render(),
            'sidebar_lead_info' => view('lead.leads.sidebar_lead_info', compact('lead', 'stepData'))->render(),
            'sidebar_status_history' => view('lead.leads.sidebar_status_history', compact('lead'))->render(),
            'lead_no' => $lead->lead_no
        ]);
    }
}
