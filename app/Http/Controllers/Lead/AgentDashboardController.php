<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\AgentLead;
use App\Models\AgentLeadFollowup;
use App\Models\LeadAgent;
use App\Models\LeadStatus;
use App\Models\JobCard;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AgentDashboardController extends Controller
{
    public function index()
    {
        $today = date('Y-m-d');
        $viewableIds = auth()->user()->getViewableUserIds();
        $isAdmin = auth()->user()->role == 'Admin';

        $totalLeads          = AgentLead::whereIn('assigned_user_id', $viewableIds)->count();
        $totalFollowups      = AgentLeadFollowup::whereIn('agent_lead_id', function($q) use ($viewableIds) {
                                    $q->select('id')->from('agent_leads')->whereIn('assigned_user_id', $viewableIds);
                                })->whereNull('complete_date')->count();
        $todayNew            = AgentLead::whereIn('assigned_user_id', $viewableIds)->whereDate('created_at', $today)->count();
        $todayFollowupPending = AgentLeadFollowup::whereIn('agent_lead_id', function($q) use ($viewableIds) {
                                    $q->select('id')->from('agent_leads')->whereIn('assigned_user_id', $viewableIds);
                                })->whereNull('complete_date')
                                ->whereDate('followup_date', '<=', $today)->count();

        $repeatSuggestionCount = Cache::remember($isAdmin ? 'agent_lead_repeat_count' : 'agent_lead_repeat_count_'.auth()->id(), 300, function () use ($viewableIds) {
            return JobCard::whereNotNull('agent_lead_id')
                ->whereNotNull('complete_date')
                ->where('complete_date', '<=', now()->subDays(10))
                ->whereHas('agentLead', fn($q) => $q->whereIn('assigned_user_id', $viewableIds))
                ->count();
        });

        return view('lead.agent_dashboard', compact(
            'totalLeads', 'totalFollowups',
            'todayNew', 'todayFollowupPending',
            'repeatSuggestionCount'
        ));
    }

    public function widgets()
    {
        $today = date('Y-m-d');
        $viewableIds = auth()->user()->getViewableUserIds();

        // Neglected leads
        $threeDaysAgo = now()->subDays(3);
        $neglectedCount = AgentLead::whereIn('assigned_user_id', $viewableIds)
            ->whereHas('status', fn($q) => $q->whereNotIn('slug', ['won', 'lost']))
            ->where(fn($q) => $q->where('updated_at', '<', $threeDaysAgo)->orWhereDoesntHave('followups'))
            ->count();

        // Today won / lost
        $todayWon  = AgentLead::whereIn('assigned_user_id', $viewableIds)->whereDate('updated_at', $today)
                         ->whereHas('status', fn($q) => $q->where('slug', 'won'))->count();
        $todayLost = AgentLead::whereIn('assigned_user_id', $viewableIds)->whereDate('updated_at', $today)
                         ->whereHas('status', fn($q) => $q->where('slug', 'lost'))->count();

        // Weekly chart
        $wonByDate = AgentLead::whereIn('assigned_user_id', $viewableIds)->whereHas('status', fn($q) => $q->where('slug', 'won'))
            ->whereDate('updated_at', '>=', now()->subDays(6)->toDateString())
            ->selectRaw('DATE(updated_at) as date, COUNT(*) as cnt')
            ->groupBy('date')
            ->pluck('cnt', 'date');

        $newByDate = AgentLead::whereIn('assigned_user_id', $viewableIds)->whereDate('created_at', '>=', now()->subDays(6)->toDateString())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as cnt')
            ->groupBy('date')
            ->pluck('cnt', 'date');

        $weeklyChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $weeklyChart[] = [
                'day' => now()->subDays($i)->format('D'),
                'won' => $wonByDate[$d] ?? 0,
                'new' => $newByDate[$d] ?? 0,
            ];
        }

        $monthStart  = now()->startOfMonth()->toDateString();
        $monthName   = now()->format('F Y');
        $leaderboard = \App\Models\User::whereIn('id', $viewableIds)->withCount(['agentLeads' => fn($q) =>
            $q->whereHas('status', fn($sq) => $sq->where('slug', 'won'))
              ->whereDate('updated_at', '>=', $monthStart)
        ])->orderBy('agent_leads_count', 'desc')->where('status', 1)->get()->filter(function($u) {
            return $u->agent_leads_count > 0;
        })->take(5);

        // Pending followups
        $pendingFollowups = AgentLeadFollowup::whereIn('agent_lead_id', function($q) use ($viewableIds) {
                $q->select('id')->from('agent_leads')->whereIn('assigned_user_id', $viewableIds);
            })->whereNull('complete_date')
            ->whereDate('followup_date', '<=', $today)
            ->with(['agentLead.status', 'agentLead.assignedUser'])
            ->latest('followup_date')
            ->take(8)->get();

        // Repeat suggestions (mini list)
        $tenDaysAgo = now()->subDays(10);
        $repeatSuggestions = JobCard::whereNotNull('agent_lead_id')
            ->whereNotNull('complete_date')
            ->where('complete_date', '<=', $tenDaysAgo)
            ->whereHas('agentLead', fn($q) => $q->whereIn('assigned_user_id', $viewableIds))
            ->with(['agentLead.status'])
            ->latest('complete_date')
            ->take(5)->get();

        return response()->json([
            'neglectedCount'   => $neglectedCount,
            'todayWon'         => $todayWon,
            'todayLost'        => $todayLost,
            'weeklyChart'      => $weeklyChart,
            'leaderboard'      => $leaderboard,
            'leaderboardMonth' => $monthName,
            'pendingFollowups' => $pendingFollowups,
            'repeatSuggestions'=> $repeatSuggestions,
        ]);
    }
}
