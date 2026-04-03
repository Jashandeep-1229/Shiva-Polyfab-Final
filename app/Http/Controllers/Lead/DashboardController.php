<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\AgentLead;
use App\Models\AgentOverallFollowup;
use App\Models\JobCard;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $today       = date('Y-m-d');
        $user        = auth()->user();
        $viewableIds = $user->getViewableUserIds();
        $isAdmin     = strtolower($user->role) === 'admin';

        // Customer Lead counts
        $custTotalLeads       = Lead::whereIn('assigned_user_id', $viewableIds)->count();
        $custTodayNew         = Lead::whereIn('assigned_user_id', $viewableIds)->whereDate('created_at', $today)->count();
        $custPendingFollowups = LeadFollowup::whereIn('lead_id', function($q) use ($viewableIds) {
            $q->select('id')->from('leads')->whereIn('assigned_user_id', $viewableIds);
        })->whereNull('complete_date')->whereDate('followup_date', '<=', $today)->count();

        // Agent Lead counts
        $agentTotalLeads  = AgentLead::whereIn('assigned_user_id', $viewableIds)->count();
        $agentTodayNew    = AgentLead::whereIn('assigned_user_id', $viewableIds)->whereDate('created_at', $today)->count();
        $agentPFQuery     = AgentOverallFollowup::where('status', 0)->where('followup_date', '<=', now()->endOfDay());
        if (!$isAdmin) {
            $agentPFQuery->whereHas('agent.leads', fn($q) => $q->whereIn('assigned_user_id', $viewableIds));
        }
        $agentPendingFollowupsCount = $agentPFQuery->count();

        // Repeat candidates (combined)
        $repeatSuggestionCount = Cache::remember(
            $isAdmin ? 'unified_repeat_count' : 'unified_repeat_count_' . $user->id, 300,
            function () use ($viewableIds) {
                $cust = JobCard::whereNotNull('lead_id')->whereNotNull('complete_date')
                    ->where('complete_date', '<=', now()->subDays(10))
                    ->whereHas('lead', fn($q) => $q->where('is_repeat', 0)->whereIn('assigned_user_id', $viewableIds))
                    ->count();
                $agent = JobCard::whereNotNull('agent_lead_id')->whereNotNull('complete_date')
                    ->where('complete_date', '<=', now()->subDays(10))
                    ->whereHas('agentLead', fn($q) => $q->whereIn('assigned_user_id', $viewableIds))
                    ->count();
                return $cust + $agent;
            }
        );

        return view('lead.dashboard', compact(
            'custTotalLeads', 'custTodayNew', 'custPendingFollowups',
            'agentTotalLeads', 'agentTodayNew', 'agentPendingFollowupsCount',
            'repeatSuggestionCount'
        ));
    }

    public function widgets()
    {
        $today       = date('Y-m-d');
        $user        = auth()->user();
        $viewableIds = $user->getViewableUserIds();
        $isAdmin     = strtolower($user->role) === 'admin';

        // Neglected leads (customer only)
        $threeDaysAgo   = now()->subDays(3);
        $neglectedCount = Lead::whereIn('assigned_user_id', $viewableIds)
            ->whereHas('status', fn($q) => $q->whereNotIn('slug', ['won', 'lost']))
            ->where(fn($q) => $q->where('updated_at', '<', $threeDaysAgo)->orWhereDoesntHave('followups'))
            ->count();

        // Today won / lost (combined)
        $todayWon = Lead::whereIn('assigned_user_id', $viewableIds)->whereDate('updated_at', $today)
                        ->whereHas('status', fn($q) => $q->where('slug', 'won'))->count()
                  + AgentLead::whereIn('assigned_user_id', $viewableIds)->whereDate('updated_at', $today)
                        ->whereHas('status', fn($q) => $q->where('slug', 'won'))->count();

        $todayLost = Lead::whereIn('assigned_user_id', $viewableIds)->whereDate('updated_at', $today)
                        ->whereHas('status', fn($q) => $q->where('slug', 'lost'))->count()
                   + AgentLead::whereIn('assigned_user_id', $viewableIds)->whereDate('updated_at', $today)
                        ->whereHas('status', fn($q) => $q->where('slug', 'lost'))->count();

        // Weekly chart (combined)
        $weeklyChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $weeklyChart[] = [
                'day' => now()->subDays($i)->format('D'),
                'new' => Lead::whereIn('assigned_user_id', $viewableIds)->whereDate('created_at', $d)->count()
                       + AgentLead::whereIn('assigned_user_id', $viewableIds)->whereDate('created_at', $d)->count(),
                'won' => Lead::whereIn('assigned_user_id', $viewableIds)->whereDate('updated_at', $d)
                              ->whereHas('status', fn($q) => $q->where('slug', 'won'))->count()
                       + AgentLead::whereIn('assigned_user_id', $viewableIds)->whereDate('updated_at', $d)
                              ->whereHas('status', fn($q) => $q->where('slug', 'won'))->count(),
            ];
        }

        // Leaderboard (combined conversions this month)
        $monthStart  = now()->startOfMonth()->toDateString();
        $monthName   = now()->format('F Y');
        $leaderboard = User::whereIn('id', $viewableIds)->where('status', 1)
            ->withCount([
                'leads as cust_won'       => fn($q) => $q->whereHas('status', fn($s) => $s->where('slug', 'won'))->whereDate('updated_at', '>=', $monthStart),
                'agentLeads as agent_won' => fn($q) => $q->whereHas('status', fn($s) => $s->where('slug', 'won'))->whereDate('updated_at', '>=', $monthStart),
            ])
            ->get()
            ->map(function($u) {
                $u->leads_count = ($u->cust_won ?? 0) + ($u->agent_won ?? 0);
                return $u;
            })
            ->filter(fn($u) => $u->leads_count > 0)
            ->sortByDesc('leads_count')
            ->take(5)
            ->values();

        // Pending customer followups
        $pendingFollowups = LeadFollowup::whereIn('lead_id', function($q) use ($viewableIds) {
            $q->select('id')->from('leads')->whereIn('assigned_user_id', $viewableIds);
        })->whereNull('complete_date')
          ->whereDate('followup_date', '<=', $today)
          ->with(['lead.status'])
          ->latest('followup_date')
          ->take(8)->get();

        // Repeat suggestions (combined)
        $tenDaysAgo  = now()->subDays(10);
        $custRepeat  = JobCard::whereNotNull('lead_id')->whereNotNull('complete_date')
            ->where('complete_date', '<=', $tenDaysAgo)
            ->whereHas('lead', fn($q) => $q->where('is_repeat', 0)->whereIn('assigned_user_id', $viewableIds))
            ->with(['lead'])->latest('complete_date')->take(5)->get()
            ->map(fn($jc) => ['type' => 'customer', 'name' => $jc->lead->name ?? '-', 'lead_id' => $jc->lead_id, 'job_card_no' => $jc->job_card_no, 'complete_date' => $jc->complete_date]);

        $agentRepeat = JobCard::whereNotNull('agent_lead_id')->whereNotNull('complete_date')
            ->where('complete_date', '<=', $tenDaysAgo)
            ->whereHas('agentLead', fn($q) => $q->whereIn('assigned_user_id', $viewableIds))
            ->with(['agentLead.agent'])->latest('complete_date')->take(5)->get()
            ->map(fn($jc) => ['type' => 'agent', 'name' => $jc->agentLead->agent->name ?? '-', 'lead_id' => $jc->agent_lead_id, 'job_card_no' => $jc->job_card_no, 'complete_date' => $jc->complete_date]);

        $repeatSuggestions = $custRepeat->concat($agentRepeat)->sortByDesc('complete_date')->take(5)->values();

        return response()->json([
            'neglectedCount'    => $neglectedCount,
            'todayWon'          => $todayWon,
            'todayLost'         => $todayLost,
            'weeklyChart'       => $weeklyChart,
            'leaderboard'       => $leaderboard,
            'leaderboardMonth'  => $monthName,
            'pendingFollowups'  => $pendingFollowups,
            'repeatSuggestions' => $repeatSuggestions,
        ]);
    }
}
