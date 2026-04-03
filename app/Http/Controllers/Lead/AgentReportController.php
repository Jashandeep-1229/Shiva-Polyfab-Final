<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\AgentLead;
use App\Models\AgentLeadFollowup;
use App\Models\LeadAgent;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PermissionHelper;

class AgentReportController extends Controller
{
    public function reportIndex()
    {
        if (!PermissionHelper::check('agent_report_lead')) abort(403);
        Log::channel('lead')->info("Agent Report skeleton loaded by user ".auth()->id());
        $users = User::where('status', 1)->get();
        $agents = LeadAgent::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        
        $states = [
            "Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar", "Chhattisgarh", "Goa", "Gujarat", 
            "Haryana", "Himachal Pradesh", "Jharkhand", "Karnataka", "Kerala", "Madhya Pradesh", 
            "Maharashtra", "Manipur", "Meghalaya", "Mizoram", "Nagaland", "Odisha", "Punjab", 
            "Rajasthan", "Sikkim", "Tamil Nadu", "Telangana", "Tripura", "Uttar Pradesh", 
            "Uttarakhand", "West Bengal", "Delhi", "Chandigarh", "Jammu & Kashmir", "Ladakh"
        ];

        return view('lead.agent_leads.report', compact('users', 'agents', 'statuses', 'states'));
    }

    public function reportData(Request $request)
    {
        if (!PermissionHelper::check('agent_report_lead')) abort(403);
        $base = AgentLead::query();

        if ($request->from_date)        $base->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date)          $base->whereDate('created_at', '<=', $request->to_date);
        if ($request->status_id)        $base->where('status_id', $request->status_id);
        if ($request->agent_id)         $base->where('agent_id', $request->agent_id);
        if ($request->assigned_user_id) $base->where('assigned_user_id', $request->assigned_user_id);
        if ($request->added_by)         $base->where('added_by', $request->added_by);
        
        if ($request->state || $request->city) {
            $base->whereHas('agent', function($q) use ($request) {
                if ($request->state) $q->where('state', $request->state);
                if ($request->city)  $q->where('city', $request->city);
            });
        }

        // ── KPI Metrics ──
        $total        = (clone $base)->count();
        $wonCount     = (clone $base)->whereHas('status', fn($q) => $q->where('slug', 'won'))->count();
        $lostCount    = (clone $base)->whereHas('status', fn($q) => $q->where('slug', 'lost'))->count();
        $pendingCount = $total - $wonCount - $lostCount;

        // ── Followup Stats ──
        $fStats = DB::table('agent_lead_followups')
            ->whereNotNull('complete_date')
            ->whereIn('agent_lead_id', (clone $base)->select('agent_leads.id'))
            ->selectRaw('
                SUM(CASE WHEN delay_days <= 0 THEN 1 ELSE 0 END) as done_in_time,
                SUM(CASE WHEN delay_days > 0  THEN 1 ELSE 0 END) as done_late
            ')->first();
        $doneInTime     = $fStats->done_in_time ?? 0;
        $doneLate       = $fStats->done_late ?? 0;
        $totalFollowups = $doneInTime + $doneLate;

        // ── Breakdown Distributions ──
        $stepDistrib  = (clone $base)->selectRaw('status_id, COUNT(*) as cnt')
            ->groupBy('status_id')->pluck('cnt', 'status_id');

        $agentDistrib = (clone $base)->selectRaw('agent_id, COUNT(*) as cnt')
            ->groupBy('agent_id')->pluck('cnt', 'agent_id');

        $userDistrib = (clone $base)->selectRaw('assigned_user_id, COUNT(*) as cnt')
            ->groupBy('assigned_user_id')->pluck('cnt', 'assigned_user_id');

        // ── Star Performers ──
        $leaderboard = User::withCount(['agentLeads' => fn($q) =>
            $q->whereHas('status', fn($sq) => $sq->where('slug', 'won'))
              ->when($request->from_date, fn($q) => $q->whereDate('agent_leads.updated_at', '>=', $request->from_date))
              ->when($request->to_date,   fn($q) => $q->whereDate('agent_leads.updated_at', '<=', $request->to_date))
        ])->orderBy('agent_leads_count', 'desc')->where('status', 1)->get()->filter(fn($u) => $u->agent_leads_count > 0);

        $perfLabel = $request->from_date
            ? Carbon::parse($request->from_date)->format('d M Y') . ' – ' . ($request->to_date ? Carbon::parse($request->to_date)->format('d M Y') : 'Now')
            : 'All Time';

        // ── Paginated Lead List ──
        $leads = (clone $base)->with(['agent', 'status', 'assignedUser'])->latest()->paginate(25);

        // Metadata
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $agents   = LeadAgent::where('status', 1)->get();
        $users    = User::where('status', 1)->get();

        $filterQuery = http_build_query(array_filter($request->only([
            'from_date','to_date','status_id','agent_id','assigned_user_id','added_by','state','city'
        ])));

        return view('lead.agent_leads.report_data', compact(
            'leads', 'total', 'wonCount', 'lostCount', 'pendingCount',
            'doneInTime', 'doneLate', 'totalFollowups',
            'statuses', 'agents', 'users',
            'stepDistrib', 'agentDistrib', 'userDistrib',
            'leaderboard', 'perfLabel', 'filterQuery'
        ));
    }

    public function reportCharts(Request $request)
    {
        if (!PermissionHelper::check('agent_report_lead')) abort(403);
        $base = AgentLead::query();
        if ($request->from_date)        $base->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date)          $base->whereDate('created_at', '<=', $request->to_date);
        if ($request->status_id)        $base->where('status_id', $request->status_id);
        if ($request->agent_id)         $base->where('agent_id', $request->agent_id);
        if ($request->assigned_user_id) $base->where('assigned_user_id', $request->assigned_user_id);
        if ($request->added_by)         $base->where('added_by', $request->added_by);
        
        if ($request->state || $request->city) {
            $base->whereHas('agent', function($q) use ($request) {
                if ($request->state) $q->where('state', $request->state);
                if ($request->city)  $q->where('city', $request->city);
            });
        }

        if (!$request->from_date && !$request->to_date) {
            $base->where('created_at', '>=', now()->subMonths(6));
        }

        $monthlyStats = (clone $base)
            ->selectRaw('COUNT(*) as count, MONTHNAME(created_at) as month, MONTH(created_at) as m, YEAR(created_at) as y')
            ->groupBy('y', 'm', 'month')
            ->orderBy('y', 'asc')
            ->orderBy('m', 'asc')
            ->get();

        $wonStats = (clone $base)->whereHas('status', fn($q) => $q->where('slug', 'won'))
            ->selectRaw('COUNT(*) as count, MONTHNAME(updated_at) as month')
            ->groupBy('month')
            ->pluck('count', 'month');

        $lostStats = (clone $base)->whereHas('status', fn($q) => $q->where('slug', 'lost'))
            ->selectRaw('COUNT(*) as count, MONTHNAME(updated_at) as month')
            ->groupBy('month')
            ->pluck('count', 'month');

        $months = [];
        $newLeads = [];
        $wonLeads = [];
        $lostLeads = [];
        $pendingLeads = [];

        foreach ($monthlyStats as $stat) {
            $m = $stat->month;
            $months[] = $m . ' ' . $stat->y;
            $newLeads[] = $stat->count;
            $w = $wonStats[$m] ?? 0;
            $l = $lostStats[$m] ?? 0;
            $wonLeads[] = $w;
            $lostLeads[] = $l;
            $pendingLeads[] = max(0, $stat->count - $w - $l);
        }

        return response()->json([
            'months' => $months,
            'newLeads' => $newLeads,
            'wonLeads' => $wonLeads,
            'lostLeads' => $lostLeads,
            'pendingLeads' => $pendingLeads
        ]);
    }
}
