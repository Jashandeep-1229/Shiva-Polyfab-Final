<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\AgentLead;
use App\Models\LeadAgent;
use App\Models\AgentDealsIn;
use App\Models\LeadStatus;
use App\Models\AgentLeadFollowup;
use App\Models\AgentLeadHistory;
use App\Models\LeadState;
use App\Models\User;
use App\Models\JobCard;
use App\Models\AgentOverallFollowup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentLeadController extends Controller
{
    public function index()
    {
        $users = User::whereIn('id', auth()->user()->getViewableUserIds())->where('status', 1)->get();
        $agents = LeadAgent::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        return view('lead.agent_leads.index', compact('users', 'agents', 'statuses'));
    }

    public function pendingIndex()
    {
        $users = User::whereIn('id', auth()->user()->getViewableUserIds())->where('status', 1)->get();
        $agents = LeadAgent::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $title = "Pending Agent Leads";
        $type = "pending";
        return view('lead.agent_leads.index', compact('users', 'agents', 'statuses', 'title', 'type'));
    }



    public function wonIndex()
    {
        $users = User::whereIn('id', auth()->user()->getViewableUserIds())->where('status', 1)->get();
        $agents = LeadAgent::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $title = "Won Agent Leads";
        $type = "won";
        return view('lead.agent_leads.index', compact('users', 'agents', 'statuses', 'title', 'type'));
    }

    public function lostIndex()
    {
        $users = User::whereIn('id', auth()->user()->getViewableUserIds())->where('status', 1)->get();
        $agents = LeadAgent::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $title = "Lost Agent Leads";
        $type = "lost";
        return view('lead.agent_leads.index', compact('users', 'agents', 'statuses', 'title', 'type'));
    }

    public function datatable(Request $request)
    {
        $limit = $request->value ?? 50;
        $query = AgentLead::with(['agent', 'status', 'assignedUser']);
        $this->applyUserFilter($query);
        
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

        if ($request->status_id) $query->where('status_id', $request->status_id);
        if ($request->agent_id) $query->where('agent_id', $request->agent_id);
        if ($request->assigned_user_id) $query->where('assigned_user_id', $request->assigned_user_id);

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
                $q->where('name_of_job', 'like', '%' . $search . '%')
                  ->orWhere('lead_no', 'like', '%' . $search . '%')
                  ->orWhereHas('agent', function($aq) use ($search) {
                      $aq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        $query->selectRaw('MAX(id) as id, agent_id, MAX(name_of_job) as name_of_job, MAX(status_id) as status_id, MAX(assigned_user_id) as assigned_user_id, MAX(lead_no) as lead_no, MAX(order_no) as order_no, COUNT(*) as lead_count')
            ->groupBy('agent_id');
        
        $leads = $query->with(['agent.dealsIn', 'status', 'assignedUser', 'agent.latestPendingOverallFollowup'])->latest('id')->paginate($limit);
        return view('lead.agent_leads.datatable', compact('leads'));
    }

    public function create()
    {
        $agents = LeadAgent::where('status', 1)->get();
        $users = User::where('status', 1)->get();
        $deals = AgentDealsIn::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)
            ->whereNotIn('slug', ['won', 'lost'])
            ->orderBy('sort_order')
            ->get();
        
        $states = [
            "Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar", "Chhattisgarh", "Goa", "Gujarat", 
            "Haryana", "Himachal Pradesh", "Jharkhand", "Karnataka", "Kerala", "Madhya Pradesh", 
            "Maharashtra", "Manipur", "Meghalaya", "Mizoram", "Nagaland", "Odisha", "Punjab", 
            "Rajasthan", "Sikkim", "Tamil Nadu", "Telangana", "Tripura", "Uttar Pradesh", 
            "Uttarakhand", "West Bengal", "Delhi", "Chandigarh", "Jammu & Kashmir", "Ladakh"
        ];

        $lastLead = AgentLead::withTrashed()->orderBy('id', 'desc')->first();
        $nextId = $lastLead ? ($lastLead->id + 1) : 1;
        $leadNo = 'ALEAD-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        
        return view('lead.agent_leads.add_edit', compact('agents', 'users', 'deals', 'leadNo', 'states', 'statuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required',
            'leads' => 'required|array|min:1',
            'leads.*.name_of_job' => 'required',
            'leads.*.status_id' => 'required',
        ]);

        $agentId = $request->agent_id;
        $assignedUserId = $request->assigned_user_id ?? auth()->id();
        $count = 0;

        foreach ($request->leads as $item) {
            $lastLead = AgentLead::withTrashed()->orderBy('id', 'desc')->first();
            $nextId = $lastLead ? ($lastLead->id + 1) : 1;
            $leadNo = 'ALEAD-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

            $lead = AgentLead::create([
                'lead_no' => $leadNo,
                'name_of_job' => $item['name_of_job'],
                'order_no' => $item['order_no'] ?? null,
                'agent_id' => $agentId,
                'status_id' => $item['status_id'],
                'assigned_user_id' => $assignedUserId,
                'remarks' => $item['remarks'] ?? null,
                'added_by' => auth()->id(),
            ]);

            // Initial History
            AgentLeadHistory::create([
                'agent_lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'type' => 'created',
                'description' => "Agent Lead $leadNo created via Bulk Entry"
            ]);

            // Initial Followup/Remarks
            if (!empty($item['remarks'])) {
                AgentLeadFollowup::create([
                    'agent_lead_id' => $lead->id,
                    'status_at_time_id' => $item['status_id'],
                    'type' => 'Call',
                    'followup_date' => now(),
                    'complete_date' => now(),
                    'remarks' => $item['remarks'],
                    'added_by' => auth()->id()
                ]);
            }

            // Sync to LeadAgentCustomer Master
            $lac = \App\Models\LeadAgentCustomer::where('phone_no', $lead->agent->phone)->first();
            if (!$lac) {
                $lac = \App\Models\LeadAgentCustomer::create([
                    'name' => $lead->agent->name,
                    'phone_no' => $lead->agent->phone,
                    'role' => 'Agent',
                    'type' => 'A',
                    'state' => $lead->agent->state,
                    'city' => $lead->agent->city,
                    'agent_lead_id' => $lead->id,
                    'status' => 1,
                    'user_id' => auth()->id()
                ]);
                $lac->code = 'SPFA' . $lac->id . rand(10000, 99999);
                $lac->save();
            } else {
                $lac->update(['agent_lead_id' => $lead->id]);
            }
            $lead->agent->update(['lead_agent_customer_id' => $lac->id]);

            $count++;
        }

        return redirect()->route('lead.agent_leads.index')->with('success', "$count Agent Jobs created successfully");
    }

    public function storeSingleJob(Request $request)
    {
        $request->validate([
            'agent_id' => 'required',
            'assigned_user_id' => 'required',
            'name_of_job' => 'required',
            'status_id' => 'required',
        ]);

        $lastLead = AgentLead::withTrashed()->orderBy('id', 'desc')->first();
        $nextId = $lastLead ? ($lastLead->id + 1) : 1;
        $leadNo = 'ALEAD-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        $lead = AgentLead::create([
            'lead_no' => $leadNo,
            'name_of_job' => $request->name_of_job,
            'agent_id' => $request->agent_id,
            'status_id' => $request->status_id,
            'assigned_user_id' => $request->assigned_user_id,
            'remarks' => $request->remarks ?? null,
            'added_by' => auth()->id(),
        ]);

        // Initial History
        AgentLeadHistory::create([
            'agent_lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'created',
            'description' => "Agent Job $leadNo created via Profile"
        ]);

        // Sync to LeadAgentCustomer Master
        $lac = \App\Models\LeadAgentCustomer::where('phone_no', $lead->agent->phone)->first();
        if (!$lac) {
            $lac = \App\Models\LeadAgentCustomer::create([
                'name' => $lead->agent->name,
                'phone_no' => $lead->agent->phone,
                'role' => 'Agent',
                'type' => 'A',
                'state' => $lead->agent->state,
                'city' => $lead->agent->city,
                'agent_lead_id' => $lead->id,
                'status' => 1,
                'user_id' => auth()->id()
            ]);
            $lac->code = 'SPFA' . $lac->id . rand(10000, 99999);
            $lac->save();
        } else {
            $lac->update(['agent_lead_id' => $lead->id]);
        }
        $lead->agent->update(['lead_agent_customer_id' => $lac->id]);

        return redirect()->route('lead.agent_leads.show', $lead->id)->with('success', "New Agent Job created successfully");
    }

    public function edit($id)
    {
        $lead = AgentLead::findOrFail($id);
        $agents = LeadAgent::where('status', 1)->get();
        $users = User::where('status', 1)->get();
        $deals = AgentDealsIn::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $states = LeadState::where('status', 1)->orderBy('name')->pluck('name')->toArray();
        
        return view('lead.agent_leads.add_edit', compact('lead', 'agents', 'users', 'deals', 'states', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_of_job' => 'required',
            'agent_id' => 'required',
        ]);

        $lead = AgentLead::findOrFail($id);
        $oldData = $lead->toArray();
        $lead->fill($request->all());
        $dirty = $lead->getDirty();

        if (count($dirty) > 0) {
            $changes = [];
            foreach ($dirty as $key => $newValue) {
                if ($key == 'updated_at') continue;
                $oldValue = $oldData[$key];
                $changes[] = ucfirst(str_replace('_', ' ', $key)) . " changed from '$oldValue' to '$newValue'";
            }

            if (!empty($changes)) {
                AgentLeadHistory::create([
                    'agent_lead_id' => $id,
                    'user_id' => auth()->id(),
                    'type' => 'manual_edit',
                    'description' => implode(', ', $changes)
                ]);
            }
        }

        $lead->save();

        return redirect()->route('lead.agent_leads.index')->with('success', "Agent Lead updated successfully");
    }

    public function show($id)
    {
        $lead = AgentLead::with(['agent', 'status', 'assignedUser', 'followups.adder', 'histories.user'])->findOrFail($id);
        
        // Fetch ALL enquiries for this same agent across the whole system, sorted by ID desc (latest first)
        $allLeads = collect();
        if ($lead->agent_id) {
            $allLeads = AgentLead::with('status')
                ->where('agent_id', $lead->agent_id)
                ->orderBy('id', 'desc')
                ->get();
        }

        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $overallFollowup = AgentOverallFollowup::where('agent_id', $lead->agent_id)->where('status', 0)->first();
        return view('lead.agent_leads.show', compact('lead', 'statuses', 'allLeads', 'overallFollowup'));
    }

    public function getProfileContent($id)
    {
        $lead = AgentLead::with(['agent', 'status', 'assignedUser', 'followups.adder', 'histories.user'])->findOrFail($id);
        
        // Fetch ALL sibling leads for the same agent in fixed order
        $allLeads = AgentLead::with('status')
            ->where('agent_id', $lead->agent_id)
            ->orderBy('id', 'desc')
            ->get();

        $statuses = LeadStatus::where('status', 1)->orderBy('sort_order')->get();
        $overallFollowup = AgentOverallFollowup::where('agent_id', $lead->agent_id)->where('status', 0)->first();
        
        $mainContent = view('lead.agent_leads.profile_main_content', compact('lead', 'allLeads', 'statuses', 'overallFollowup'))->render();
        $sidebarStatusName = ($lead->status && $lead->status->slug == 'won' ? '🥳 ' : ($lead->status && $lead->status->slug == 'lost' ? '😔 ' : '')) . ($lead->status->name ?? 'New');
        
        $showFollowup = ($lead->status && !in_array($lead->status->slug, ['won', 'lost']));
        $followupBtn = $showFollowup ? view('lead.agent_leads.sidebar_followup_btn', compact('lead'))->render() : '';
        
        $leadInfo = view('lead.agent_leads.sidebar_lead_info', compact('lead'))->render();
        $statusHistory = view('lead.agent_leads.sidebar_status_history', ['history' => $lead->histories->where('type', 'step_changed')])->render();

        $overallFollowupSidebar = view('lead.agent_leads.sidebar_overall_followup', compact('overallFollowup'))->render();

        return response()->json([
            'main_content' => $mainContent,
            'sidebar_status_name' => $sidebarStatusName,
            'sidebar_followup_btn' => $followupBtn,
            'sidebar_lead_info' => $leadInfo,
            'sidebar_status_history' => $statusHistory,
            'sidebar_overall_followup' => $overallFollowupSidebar,
            'lead_no' => $lead->lead_no
        ]);
    }

    public function followupStore(Request $request, $id)
    {
        $lead = AgentLead::findOrFail($id);
        $oldStatusName = $lead->status->name ?? 'None';
        $currentStatusId = $lead->status_id;

        $nextAction = $request->next_action; 
        $newStatusId = $currentStatusId;

        if (is_numeric($nextAction)) {
            $newStatusId = (int)$nextAction;
        } elseif ($nextAction == 'lost') {
            $lostStatus = LeadStatus::where('slug', 'lost')->first();
            $newStatusId = $lostStatus->id ?? $currentStatusId;
        } elseif ($nextAction == 'won') {
            $wonStatus = LeadStatus::where('slug', 'won')->first();
            $newStatusId = $wonStatus->id ?? $currentStatusId;
            if ($request->order_no) {
                $lead->update(['order_no' => $request->order_no]);
            }
        }

        if ($newStatusId != $currentStatusId) {
            $lead->update(['status_id' => $newStatusId]);
            $statusObj = LeadStatus::find($newStatusId);
            $newStatusName = $statusObj->name ?? $oldStatusName;
            
            AgentLeadHistory::create([
                'agent_lead_id' => $id,
                'user_id' => auth()->id(),
                'type' => 'step_changed',
                'description' => "Lead moved from $oldStatusName to $newStatusName"
            ]);
        }

        $pendingFollowup = AgentLeadFollowup::where('agent_lead_id', $id)->whereNull('complete_date')->orderBy('followup_date', 'desc')->first();
        
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
                'remarks' => $request->remarks ?? 'Action Completed',
                'completed_by' => auth()->id()
            ]);
        } else {
            // Create a completed followup immediately for the history
            AgentLeadFollowup::create([
                'agent_lead_id' => $id,
                'status_at_time_id' => $newStatusId,
                'followup_date' => now(), // The date of activity
                'complete_date' => now(),
                'remarks' => $request->remarks,
                'added_by' => auth()->id(),
                'completed_by' => auth()->id()
            ]);
        }

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

    public function rollbackStage(Request $request, $id)
    {
        $lead = AgentLead::findOrFail($id);
        $targetStatusId = $request->status_id;
        $targetStatus = \App\Models\LeadStatus::findOrFail($targetStatusId);
        $oldStatus = $lead->status->name ?? 'None';
        
        $lead->update(['status_id' => $targetStatusId]);
        
        \App\Models\AgentLeadHistory::create([
            'agent_lead_id' => $id,
            'user_id' => auth()->id(),
            'type' => 'manual_edit',
            'description' => "Stage rolled back from '$oldStatus' to '{$targetStatus->name}'."
        ]);

        // Add a system followup to mark the rollback in timeline nicely
        \App\Models\AgentLeadFollowup::create([
            'agent_lead_id' => $id,
            'status_at_time_id' => $targetStatusId,
            'type' => 'System',
            'followup_date' => now(),
            'complete_date' => now(),
            'remarks' => "🔄 <strong>Stage Rolled Back</strong>: This lead was moved back to <strong>{$targetStatus->name}</strong> stage from <strong>$oldStatus</strong>.",
            'added_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Agent Lead rolled back to {$targetStatus->name}"
        ]);
    }

    public function getTimeline($id)
    {
        $lead = AgentLead::with(['followups.adder', 'followups.completer', 'status'])->findOrFail($id);
        return view('lead.agent_leads.timeline', compact('lead'));
    }

    public function getStatusHistory($id)
    {
        $history = AgentLeadHistory::with('user')->where('agent_lead_id', $id)->where('type', 'step_changed')->latest()->get();
        return view('lead.agent_leads.sidebar_status_history', compact('history'));
    }

    public function followupModal($id)
    {
        $lead = AgentLead::with(['status'])->findOrFail($id);
        
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

        return view('lead.agent_leads.followup_modal', compact('lead', 'nextSteps', 'isLastStep', 'lostReasons'));
    }

    public function jobCardStatus(Request $request)
    {
        $query = JobCard::whereNotNull('agent_lead_id')
            ->with(['agentLead.agent', 'processes.user', 'processes.machine']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('job_card_no', 'like', '%' . $search . '%')
                  ->orWhereHas('agentLead', function($aq) use ($search) {
                      $aq->where('name_of_job', 'like', '%' . $search . '%')
                        ->orWhereHas('agent', function($ag) use ($search) {
                            $ag->where('name', 'like', '%' . $search . '%');
                        });
                  });
            });
        }

        $jobCards = $query->latest()->paginate(15);
        
        if ($request->ajax()) {
            return view('lead.agent_leads.order_process_table', compact('jobCards'));
        }

        return view('lead.agent_leads.order_process', compact('jobCards'));
    }

    public function repeatSuggestions(Request $request)
    {
        $tenDaysAgo = now()->subDays(10);
        $query = JobCard::whereNotNull('agent_lead_id')
            ->whereNotNull('complete_date')
            ->where('complete_date', '<=', $tenDaysAgo)
            ->with(['agentLead.agent', 'agentLead.status']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('job_card_no', 'like', '%' . $search . '%')
                  ->orWhereHas('agentLead', function($aq) use ($search) {
                      $aq->where('name_of_job', 'like', '%' . $search . '%')
                        ->orWhereHas('agent', function($ag) use ($search) {
                            $ag->where('name', 'like', '%' . $search . '%');
                        });
                  });
            });
        }

        $jobCards = $query->latest('complete_date')->paginate(20);

        if ($request->ajax()) {
            return view('lead.agent_leads.repeat_suggestions_table', compact('jobCards'));
        }

        return view('lead.agent_leads.repeat_suggestions', compact('jobCards'));
    }

    public function checkJobCardNo(Request $request)
    {
        $jcNo = $request->job_card_no;
        $leadId = $request->lead_id;

        $jc = JobCard::where('job_card_no', $jcNo)->first();

        if (!$jc) {
            return response()->json(['status' => 'error', 'message' => 'Job Card Number not found in production.']);
        }

        if ($jc->agent_lead_id && $jc->agent_lead_id != $leadId) {
            return response()->json(['status' => 'error', 'message' => 'This Job Card is already linked to another Agent Lead.']);
        }

        if ($jc->lead_id) {
            return response()->json(['status' => 'error', 'message' => 'This Job Card is already linked to a Customer Lead.']);
        }

        return response()->json(['status' => 'success', 'message' => 'Valid Job Card. Linkable to this lead.']);
    }

    public function updateJobCardNo(Request $request, $id)
    {
        $request->validate(['order_no' => 'nullable']);
        $lead = AgentLead::findOrFail($id);
        $newNo = $request->order_no;
        $user = auth()->user();

        if (auth()->user()->role == 'admin') {
            $oldNo = $lead->order_no;

            if ($oldNo) {
                JobCard::where('job_card_no', $oldNo)->update(['agent_lead_id' => null]);
            }

            if ($newNo) {
                $jc = JobCard::where('job_card_no', $newNo)->first();
                if ($jc) {
                    $jc->update(['agent_lead_id' => $lead->id]);
                    $lead->update(['order_no' => $newNo]);
                }
            } else {
                $lead->update(['order_no' => null]);
            }

            AgentLeadHistory::create([
                'agent_lead_id' => $id,
                'user_id' => $user->id,
                'type' => 'manual_edit',
                'description' => "Job Card REPLACED by Admin from '$oldNo' to '$newNo'"
            ]);

            return back()->with('success', "Production link updated successfully.");
        }

        return back()->with('error', "Only Admin can link or modify a Production ID.");
    }

    public function checkAgent(Request $request)
    {
        $agentId = $request->agent_id;
        $exclude_id = $request->exclude_id;

        $query = AgentLead::with(['agent', 'status', 'assignedUser'])
            ->where('agent_id', $agentId);

        if ($exclude_id) {
            $query->where('id', '!=', $exclude_id);
        }

        $existingLeads = $query->orderBy('created_at', 'desc')->get();
        if ($existingLeads->isEmpty()) {
            return response()->json(['status' => 'clear']);
        }

        $activeEnquiries = $existingLeads->filter(function($l) {
            return $l->status && !in_array($l->status->slug, ['won', 'lost']);
        });

        if ($activeEnquiries->count() > 0) {
            $lead = $activeEnquiries->first();
            $managedBy = $lead->assignedUser->name ?? 'Unassigned';
            $isOwn = $lead->assigned_user_id == auth()->id();

            return response()->json([
                'status' => 'exists',
                'lead_no' => $lead->lead_no,
                'managed_by' => $managedBy,
                'is_own' => $isOwn,
                'link' => route('lead.agent_leads.show', $lead->id)
            ]);
        }

        $wonLeads = $existingLeads->filter(function($l) {
            return $l->status && $l->status->slug == 'won';
        });
        if ($wonLeads->count() > 0) {
            return response()->json(['status' => 'repeat']);
        }

        $lostLeads = $existingLeads->filter(function($l) {
            return $l->status && $l->status->slug == 'lost';
        });
        if ($lostLeads->count() > 0) {
            return response()->json(['status' => 'recover']);
        }

        return response()->json(['status' => 'clear']);
    }

    private function applyUserFilter($query)
    {
        $user = auth()->user();
        if (strtolower($user->role) != 'admin') {
            $userIds = $user->getViewableUserIds();
            $query->whereIn('assigned_user_id', $userIds);
        }
    }

    public function storeOverallFollowup(Request $request, $agentId)
    {
        $pending = AgentOverallFollowup::where('agent_id', $agentId)->where('status', 0)->first();
        $now = now();
        
        if ($pending) {
            // 1. COMPLETE the existing pending interaction with today's outcome
            $scheduledDate = \Carbon\Carbon::parse($pending->followup_date);
            $delay = $now->greaterThan($scheduledDate) ? (int)$now->diffInDays($scheduledDate) : 0;
            
            $pending->update([
                'complete_date' => $now,
                'complete_remarks' => $request->has_next == 'no' ? $request->remarks : 'Continued to next interaction',
                'delay_days' => $delay,
                'completed_by' => auth()->id(),
                'status' => 1
            ]);

            // 2. Schedule the NEXT pending interaction if "Continue" is chosen
            if ($request->has_next == 'yes' && $request->next_followup_days) {
                AgentOverallFollowup::create([
                    'agent_id' => $agentId,
                    'followup_date' => now()->addDays($request->next_followup_days)->setTime(12, 0, 0),
                    'remarks' => $request->remarks, // Current remarks become the NEXT task's topic
                    'added_by' => auth()->id(),
                    'status' => 0
                ]);
            }
        } else {
            // NO pending exists (First time or manual entry)
            if ($request->has_next == 'yes') {
                // Create a single PENDING entry for the first time
                AgentOverallFollowup::create([
                    'agent_id' => $agentId,
                    'followup_date' => now()->addDays($request->next_followup_days)->setTime(12, 0, 0),
                    'remarks' => $request->remarks, // Use user's remarks as the upcoming topic
                    'added_by' => auth()->id(),
                    'status' => 0
                ]);
            } else {
                // Just log a completed activity directly
                AgentOverallFollowup::create([
                    'agent_id' => $agentId,
                    'followup_date' => $now,
                    'complete_date' => $now,
                    'remarks' => 'Direct Discussion',
                    'complete_remarks' => $request->remarks,
                    'added_by' => auth()->id(),
                    'completed_by' => auth()->id(),
                    'status' => 1
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Agent interaction recorded successfully'
        ]);
    }

    public function getOverallFollowupHistory($id)
    {
        // Show BOTH pending and completed records in history for a full timeline view
        $history = AgentOverallFollowup::with(['addedBy', 'completedBy'])
            ->where('agent_id', $id)
            ->orderByRaw('status ASC, id DESC') // Show Pending first, then Completed
            ->get();
        return view('lead.agent_leads.overall_followup_history', compact('history'));
    }

    public function overallFollowupModal($agent_id)
    {
        $agent = LeadAgent::findOrFail($agent_id);
        $pendingFollowup = AgentOverallFollowup::where('agent_id', $agent_id)
            ->where('status', 0)
            ->first();
        return view('lead.agent_leads.overall_followup_modal_ajax', compact('agent', 'pendingFollowup'));
    }
}
