<?php

namespace App\Http\Controllers;

use App\Models\JobCard;
use App\Models\Fabric;
use App\Models\Bopp;
use App\Models\ManageStock;
use App\Models\LedgerFollowup;
use App\Models\CustomerLedger;
use App\Models\AgentCustomer;
use App\Models\Lead;
use App\Models\LeadStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {   
        if (!\App\Helpers\PermissionHelper::check('dashboard')) {
            return view('errors.unauthorized_dashboard');
        }

        $user = Auth::user();
        $role = strtolower(trim($user->role_as));
        
        // Check if user is Admin OR has the special Full Access permission
        $hasFullAccess = ($role == 'admin' || \App\Helpers\PermissionHelper::check('sales_dashboard_full_access'));
        $isAdmin = $hasFullAccess; // Treat Full Access users as Admins for dashboard purposes
        
        $is_sales_data_user = in_array($role, ['sale executive', 'senior sale executive']) || $hasFullAccess;
        $is_under_development = !$is_sales_data_user;

        $total_pending_followup = 0;
        $today_payment_followup = 0;
        $se_job_cards_count = 0;
        $incoming_payment_val = 0;
        $incoming_payment_formatted = '₹0.00';
        $new_leads_count = 0;
        $conversion_rate = 0;
        $customer_orders = 0;
        $direct_orders = 0;
        $total_job_cards = 0;
        $pending_job_cards = 0;
        $progress_job_cards = 0;
        $completed_job_cards = 0;
        $low_stock_fabric = 0;
        $executives = [];
        $from_date = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now()->subDays(7);
        $to_date = $request->to_date ? Carbon::parse($request->to_date) : Carbon::now();
        $target_user_id = $request->executive_id;

        if ($is_sales_data_user) {
            // If Admin selects an executive, we show the sales dashboard for that executive
            $target_user = null;
            if ($isAdmin && $target_user_id) {
                $target_user = \App\Models\User::find($target_user_id);
            }

            // ACTOR decides whose data we see
            $actor = $target_user ?? $user;
            $managed_ids = $actor->getPermittedUserIds('customer_ledger');
            $today = Carbon::today();

            // 1. Pending Followups
            $pending_cust_query = LedgerFollowup::whereIn('user_id', $managed_ids)
                ->where('status', 'Pending')
                ->whereHas('activeHistory', function($q) use ($today, $from_date, $to_date) {
                    if ($from_date && $to_date) {
                        $q->whereBetween('followup_date_time', [$from_date->startOfDay(), $to_date->endOfDay()]);
                    } else {
                        $q->whereDate('followup_date_time', '<=', $today);
                    }
                });
            $pending_cust_followups = $pending_cust_query->count();
            
            $pending_lead_query = Lead::whereIn('assigned_user_id', $managed_ids)
                ->whereHas('latestFollowup', function($q) use ($today, $from_date, $to_date) {
                    $q->whereNull('complete_date');
                    if ($from_date && $to_date) {
                        $q->whereBetween('followup_date', [$from_date->startOfDay(), $to_date->endOfDay()]);
                    } else {
                        $q->whereDate('followup_date', '<=', $today);
                    }
                });
            $pending_lead_followups = $pending_lead_query->count();
            
            $total_pending_followup = $pending_lead_followups;

            // 2. Payment Followup
            $payment_followup_query = LedgerFollowup::whereIn('user_id', $managed_ids)
                ->where('status', 'Pending')
                ->whereHas('activeHistory', function($q) use ($today, $from_date, $to_date) {
                    if ($from_date && $to_date) {
                        $q->whereBetween('followup_date_time', [$from_date->startOfDay(), $to_date->endOfDay()]);
                    } else {
                        $q->whereDate('followup_date_time', $today);
                    }
                });
            $today_payment_followup = $payment_followup_query->count();

            // 3. Total Job Cards (Pipeline - Pending Only, Non-Common) - Show ALL, ignore dates
            // Strictly check sale_executive_id per user's count of 16 vs 42
            $se_job_cards_query = JobCard::whereIn('sale_executive_id', $managed_ids)
                ->where('job_type', '!=', 'Common')
                ->where('status', '!=', 'Completed');
            // Deliberately skipped date filter here per user request
            $se_job_cards_count = $se_job_cards_query->count();

            // 4. Incoming Payment
            $incoming_payment_query = CustomerLedger::where('dr_cr', 'Cr')
                ->where('is_bad_debt', 0)
                ->whereHas('customer', function($q) use ($managed_ids) {
                    $q->whereIn('sale_executive_id', $managed_ids);
                });
            if ($from_date && $to_date) {
                $incoming_payment_query->whereBetween('transaction_date', [$from_date->startOfDay(), $to_date->endOfDay()]);
            } else {
                $incoming_payment_query->whereDate('transaction_date', '>=', Carbon::now()->subDays(7));
            }
            $incoming_payment_val = $incoming_payment_query->sum('grand_total_amount');

            $parts = explode('.', number_format($incoming_payment_val, 2, '.', ''));
            $last_three = substr($parts[0], -3);
            $rest = substr($parts[0], 0, -3);
            if(strlen($rest) > 0) {
                $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',';
            }
            $incoming_payment_formatted = '₹' . $rest . $last_three . '.' . $parts[1];

            // 5. Total New Lead
            $new_leads_query = Lead::whereIn('assigned_user_id', $managed_ids);
            if ($from_date && $to_date) {
                $new_leads_query->whereBetween('created_at', [$from_date->startOfDay(), $to_date->endOfDay()]);
            } else {
                $new_leads_query->whereMonth('created_at', Carbon::now()->month)
                                 ->whereYear('created_at', Carbon::now()->year);
            }
            $new_leads_count = $new_leads_query->count();

            // 6. Conversion Rate
            $conversion_leads_base = Lead::whereIn('assigned_user_id', $managed_ids);
            if ($from_date && $to_date) {
                $conversion_leads_base->whereBetween('created_at', [$from_date->startOfDay(), $to_date->endOfDay()]);
            }
            $total_leads_conv = (clone $conversion_leads_base)->count();
            $won_leads_conv = (clone $conversion_leads_base)
                ->whereHas('status', function($q){ $q->where('slug', 'won'); })
                ->count();
            $conversion_rate = $total_leads_conv > 0 ? round(($won_leads_conv / $total_leads_conv) * 100, 1) : 0;

            // 7. Orders (Customer vs Agent) - Order Distribution
            // Must match the se_job_cards base query so they sum perfectly
            $customer_orders = (clone $se_job_cards_query)->whereHas('customer_agent', function($q) {
                $q->where('role', 'Agent');
            })->count();
            
            $direct_orders = (clone $se_job_cards_query)->whereHas('customer_agent', function($q) {
                $q->where('role', 'Customer');
            })->count();

            if ($request->ajax()) {
                return response()->json([
                    'total_pending_followup' => $total_pending_followup,
                    'today_payment_followup' => $today_payment_followup,
                    'se_job_cards_count' => $se_job_cards_count,
                    'incoming_payment_val' => $incoming_payment_val,
                    'incoming_payment_formatted' => $incoming_payment_formatted,
                    'new_leads_count' => $new_leads_count,
                    'conversion_rate' => $conversion_rate,
                    'customer_orders' => $customer_orders,
                    'direct_orders' => $direct_orders,
                    'is_range' => ($from_date && $to_date)
                ]);
            }

            if ($isAdmin) {
                $total_job_cards = JobCard::count();
                $pending_job_cards = JobCard::where('status', 'Pending')->count();
                $progress_job_cards = JobCard::where('status', 'Progress')->count();
                $completed_job_cards = JobCard::where('status', 'Completed')->count();
                $low_stock_fabric = Fabric::whereRaw('alert_min_stock >= (SELECT SUM(IF(in_out="in", quantity, -quantity)) FROM manage_stocks WHERE stock_name="fabric" AND stock_id=fabrics.id)')->count();
                $executives = \App\Models\User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive'])->where('status', 1)->get();
            }
        } else {
            // Other authorized roles (EA, Data Entry, etc.)
            if ($request->ajax()) {
                return response()->json([
                    'total_pending_followup' => 0,
                    'today_payment_followup' => 0,
                    'se_job_cards_count' => 0,
                    'incoming_payment_val' => 0,
                    'incoming_payment_formatted' => '₹0.00',
                    'new_leads_count' => 0,
                    'conversion_rate' => 0,
                    'customer_orders' => 0,
                    'direct_orders' => 0,
                    'is_range' => true
                ]);
            }
        }

        return view('admin.dashboard_sales', [
            'total_pending_followup' => $total_pending_followup,
            'today_payment_followup' => $today_payment_followup,
            'se_job_cards_count' => $se_job_cards_count,
            'incoming_payment_val' => $incoming_payment_val,
            'incoming_payment_formatted' => $incoming_payment_formatted,
            'new_leads_count' => $new_leads_count,
            'conversion_rate' => $conversion_rate,
            'customer_orders' => $customer_orders,
            'direct_orders' => $direct_orders,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'isAdmin' => $isAdmin,
            'executives' => $executives,
            'total_job_cards' => $total_job_cards,
            'pending_job_cards' => $pending_job_cards,
            'progress_job_cards' => $progress_job_cards,
            'completed_job_cards' => $completed_job_cards,
            'low_stock_fabric' => $low_stock_fabric,
            'target_user_id' => $target_user_id,
            'is_under_development' => $is_under_development
        ]);
    }
}
