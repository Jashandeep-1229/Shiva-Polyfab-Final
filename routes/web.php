<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FabricController;
use App\Http\Controllers\BoppController;
use App\Http\Controllers\InkController;
use App\Http\Controllers\DanaController;
use App\Http\Controllers\LoopController;
use App\Http\Controllers\AgentCustomerController;
use App\Http\Controllers\SaleExecutiveController;
use App\Http\Controllers\JobCardController;
use App\Http\Controllers\CommonOrderController;
use App\Http\Controllers\CylinderAgentController;
use App\Http\Controllers\CylinderJobController;
use App\Http\Controllers\Admin\WebsiteController;
use App\Http\Controllers\ManageStockController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\BlockageReasonController;
use App\Http\Controllers\PackingSlipController;
use App\Http\Controllers\ColorMasterController;
use App\Http\Controllers\SizeMasterController;
use App\Http\Controllers\MasterVendorController;
use App\Http\Controllers\CommonManageStockController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\OldDataController;
use App\Http\Controllers\CommonPackingSlipController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CustomerLedgerController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\MachineReportController;
use App\Http\Controllers\Lead\Auth\LoginController as LeadLoginController;
use App\Http\Controllers\Lead\DashboardController as LeadDashboardController;
use App\Http\Controllers\Lead\AgentDashboardController;
use App\Http\Controllers\Lead\LeadController;
use App\Http\Controllers\Lead\LeadMasterController;
use App\Http\Controllers\Lead\AgentLeadController;
use App\Http\Controllers\Lead\AgentMasterController;
use App\Http\Controllers\Lead\AgentReportController;
use App\Http\Controllers\Lead\TeamController as LeadTeamController;
use App\Http\Controllers\LeadAgentCustomerController;


use App\Http\Controllers\Admin\AiIntelligenceController;
use App\Http\Controllers\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', [HomeController::class, 'login'])->name('new_login');

Auth::routes();

// ___________________________ Admin Route ____________________________
Route::group(['middleware' => ['auth']], function () {
    Route::prefix('admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('overall_dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard.overall');
        Route::get('overall_dashboard/late_detail', [AdminDashboardController::class, 'getLateJobsDetail'])->name('admin.dashboard.late_detail');
        Route::get('overall_dashboard/machine_detail', [AdminDashboardController::class, 'getMachineDetail'])->name('admin.dashboard.machine_detail');
        Route::get('overall_dashboard/overdue_dispatches', [AdminDashboardController::class, 'getOverdueDispatchesDetail'])->name('admin.dashboard.overdue_dispatches_detail');
        Route::get('overall_dashboard/account_pending', [AdminDashboardController::class, 'getAccountPendingDetail'])->name('admin.dashboard.account_pending_detail');
        Route::get('overall_dashboard/cylinder_agent_detail', [AdminDashboardController::class, 'getCylinderAgentDetail'])->name('admin.dashboard.cylinder_agent_detail');
        Route::get('overall_dashboard/blockage_detail', [AdminDashboardController::class, 'getBlockageDetail'])->name('admin.dashboard.blockage_detail');
        Route::get('overall_dashboard/customer_performance_detail', [AdminDashboardController::class, 'getCustomerPerformanceDetail'])->name('admin.dashboard.customer_performance_detail');
        Route::get('overall_dashboard/agent_performance_detail', [AdminDashboardController::class, 'getAgentPerformanceDetail'])->name('admin.dashboard.agent_performance_detail');
        Route::get('overall_dashboard/executive_performance_detail', [AdminDashboardController::class, 'getExecutivePerformanceDetail'])->name('admin.dashboard.executive_performance_detail');
        Route::get('overall_dashboard/ledger_detail', [AdminDashboardController::class, 'getLedgerDetail'])->name('admin.dashboard.ledger_detail');

        // AI Intelligence Studio (Independent Module)
        Route::get('ai-studio', [AiIntelligenceController::class, 'index'])->name('ai_studio.index');
        Route::get('ai-studio/chat', [AiIntelligenceController::class, 'chat'])->name('ai_studio.chat');
        Route::post('ai-studio/ask-ai', [AiIntelligenceController::class, 'askAi'])->name('ai_studio.ask_ai');
        Route::get('ai-studio/get-history', [AiIntelligenceController::class, 'getHistory'])->name('ai_studio.get_history');
        Route::post('ai-studio/clear-history', [AiIntelligenceController::class, 'clearHistory'])->name('ai_studio.clear_history');
        Route::post('ai-studio/generate-designs', [AiIntelligenceController::class, 'generateAIPremuimDesigns'])->name('ai_studio.generate_designs');
        Route::post('ai-studio/smart-parse', [AiIntelligenceController::class, 'smartParse'])->name('ai_studio.smart_parse');
        Route::post('ai-studio/store', [AiIntelligenceController::class, 'store'])->name('ai_studio.store');
        Route::get('ai-studio/design/{id}', [AiIntelligenceController::class, 'showDesign'])->name('ai_studio.show_design');
        Route::post('ai-studio/approve/{id}', [AiIntelligenceController::class, 'approveDesign'])->name('ai_studio.approve');
        Route::get('ai-studio/convert/{id}', [AiIntelligenceController::class, 'convertToJobCard'])->name('ai_studio.convert');

        // Master Shared Routes (Job Card, Stock, etc.)
        Route::resource('job_card', JobCardController::class);
        Route::get('job_cards/datatable', [JobCardController::class, 'datatable'])->name('job_card.datatable');
        Route::get('job_cards/process', [JobCardController::class, 'process'])->name('job_card.process');
        Route::get('job_cards/process_datatable', [JobCardController::class, 'process_datatable'])->name('job_card.process_datatable');
        Route::get('job_cards/edit_modal/{id}', [JobCardController::class, 'edit_modal'])->name('job_card.edit_modal');
        Route::get('job_cards/delete/{id}', [JobCardController::class, 'delete'])->name('job_card.delete');
        Route::get('job_cards/change_status/{id}', [JobCardController::class, 'change_status'])->name('job_card.change_status');
        Route::get('job_cards/list/', [JobCardController::class, 'index'])->name('job_card.list');
        Route::get('job_card/next_process/{id}', [JobCardController::class, 'next_process'])->name('job_card.next_process');
        Route::get('job_card/change_hold_status/{id}', [JobCardController::class, 'change_hold_status'])->name('job_card.change_hold_status');
        Route::get('job_card/hold_reasons', [JobCardController::class, 'hold_reasons'])->name('job_card.hold_reasons');
        Route::get('job_card/hold_modal/{id}', [JobCardController::class, 'holdModal'])->name('job_card.hold_modal');
        Route::post('job_card/hold/{id}', [JobCardController::class, 'holdJobCard'])->name('job_card.hold');
        Route::get('job_card/unhold/{id}', [JobCardController::class, 'unholdJobCard'])->name('job_card.unhold');
        Route::post('job_card/update_process/{id}', [JobCardController::class, 'update_process'])->name('job_card.update_process');
        Route::post('job_card/packing_store/{id}', [JobCardController::class, 'packing_store'])->name('job_card.packing_store');
        
        Route::get('job_card/common_roll_out_modal/{id}', [JobCardController::class, 'common_roll_out_modal'])->name('job_card.common_roll_out_modal');
        Route::post('job_card/store_roll_out/{id}', [JobCardController::class, 'store_roll_out'])->name('job_card.store_roll_out');
        
        // Report Routes
        Route::get('job_cards/report', [JobCardController::class, 'report'])->name('job_card.report');
        Route::get('job_cards/report_datatable', [JobCardController::class, 'report_datatable'])->name('job_card.report_datatable');
        Route::get('job_cards/view_timeline/{id}', [JobCardController::class, 'view_timeline'])->name('job_card.view_timeline');
        Route::get('job_cards/view_billing_details/{id}', [JobCardController::class, 'view_billing_details'])->name('job_card.view_billing_details');
        Route::get('job_cards/view_packing_details/{id}', [JobCardController::class, 'view_packing_details'])->name('job_card.view_packing_details');
        
        // Machine Wise Report
        Route::get('report/machine_wise', [MachineReportController::class, 'index'])->name('report.machine_wise');
        Route::get('report/machine_wise/get_machines', [MachineReportController::class, 'get_machines'])->name('report.machine_wise.get_machines');
        Route::get('report/machine_wise/report_data', [MachineReportController::class, 'report_data'])->name('report.machine_wise.report_data');

        Route::resource('cylinder_job', CylinderJobController::class);
        Route::get('cylinder_jobs/datatable', [CylinderJobController::class, 'datatable'])->name('cylinder_job.datatable');
        Route::get('cylinder_jobs/edit_modal/{id}', [CylinderJobController::class, 'edit_modal'])->name('cylinder_job.edit_modal');
        Route::get('cylinder_jobs/delete/{id}', [CylinderJobController::class, 'delete'])->name('cylinder_job.delete');
        Route::get('cylinder_jobs/change_status/{id}', [CylinderJobController::class, 'change_status'])->name('cylinder_job.change_status');
        Route::get('cylinder_jobs/list/', [CylinderJobController::class, 'cylinder_job_master_list'])->name('cylinder_job.list');
        Route::get('cylinder_jobs/report/', [CylinderJobController::class, 'cylinder_job_master_report'])->name('cylinder_job.report');
        Route::get('cylinder_jobs/agent_report/', [CylinderJobController::class, 'agent_report'])->name('cylinder_job.agent_report');
        Route::get('cylinder_jobs/agent_report_datatable/', [CylinderJobController::class, 'agent_report_datatable'])->name('cylinder_job.agent_report_datatable');
        Route::post('cylinder_jobs/import', [CylinderJobController::class, 'import'])->name('cylinder_job.import');

        Route::resource('manage_stock', ManageStockController::class);
        Route::get('manage_stocks/datatable', [ManageStockController::class, 'datatable'])->name('manage_stock.datatable');
        Route::get('manage_stocks/edit_modal/{id}', [ManageStockController::class, 'edit_modal'])->name('manage_stock.edit_modal');
        Route::get('manage_stocks/delete/{id}', [ManageStockController::class, 'delete'])->name('manage_stock.delete');
        Route::get('manage_stocks/get_current_stock/{id}', [ManageStockController::class, 'get_current_stock'])->name('manage_stock.get_current_stock');
        Route::get('manage_stocks/average_index/', [ManageStockController::class, 'average_index'])->name('manage_stock.average_index');
        Route::get('manage_stocks/average_datatable/', [ManageStockController::class, 'average_datatable'])->name('manage_stock.average_datatable');
        Route::get('manage_stocks/history/', [ManageStockController::class, 'history_index'])->name('manage_stock.history');
        Route::get('manage_stocks/history_datatable/', [ManageStockController::class, 'history_datatable'])->name('manage_stock.history_datatable');

        Route::resource('packing_slip', PackingSlipController::class);
        Route::get('packing_slips/datatable', [PackingSlipController::class, 'datatable'])->name('packing_slip.datatable');
        Route::get('packing_slips/view_modal/{id}', [PackingSlipController::class, 'view_modal'])->name('packing_slip.view_modal');
        Route::get('packing_slips/complete_detail/{id}', [PackingSlipController::class, 'complete_detail'])->name('packing_slip.complete_detail');
        Route::get('packing_slips/undo_detail/{id}', [PackingSlipController::class, 'undo_detail'])->name('packing_slip.undo_detail');
        Route::get('packing_slips/complete/{id}', [PackingSlipController::class, 'complete'])->name('packing_slip.complete');
        Route::get('packing_slips/pdf/{id}', [PackingSlipController::class, 'pdf'])->name('packing_slip.pdf');
        Route::get('packing_slips/list/', [PackingSlipController::class, 'packing_slip_master_list'])->name('packing_slip.list');

        Route::get('common_order/create', [CommonOrderController::class, 'create'])->name('common_order.create');
        Route::post('common_order/store', [CommonOrderController::class, 'store'])->name('common_order.store');

        Route::get('packing_slip_common/create', [CommonPackingSlipController::class, 'create'])->name('packing_slip_common.create');
        Route::post('packing_slip_common/store', [CommonPackingSlipController::class, 'store'])->name('packing_slip_common.store');
        Route::get('packing_slip_common/index', [CommonPackingSlipController::class, 'index'])->name('packing_slip_common.index');
        Route::get('packing_slip_common/datatable', [CommonPackingSlipController::class, 'datatable'])->name('packing_slip_common.datatable');
        Route::get('packing_slip_common/pdf/{id}', [CommonPackingSlipController::class, 'pdf'])->name('packing_slip_common.pdf');
        Route::get('packing_slip_common/edit/{id}', [CommonPackingSlipController::class, 'edit'])->name('packing_slip_common.edit');
        Route::post('packing_slip_common/update/{id}', [CommonPackingSlipController::class, 'update'])->name('packing_slip_common.update');
        Route::get('packing_slip_common/delete/{id}', [CommonPackingSlipController::class, 'delete'])->name('packing_slip_common.delete');

        // Payment Method Master
        Route::get('payment_methods', [PaymentMethodController::class, 'index'])->name('payment_method.index');
        Route::get('payment_methods/datatable', [PaymentMethodController::class, 'datatable'])->name('payment_method.datatable');
        Route::post('payment_methods/store', [PaymentMethodController::class, 'store'])->name('payment_method.store');
        Route::get('payment_methods/edit_modal/{id}', [PaymentMethodController::class, 'edit_modal'])->name('payment_method.edit_modal');
        Route::get('payment_methods/delete/{id}', [PaymentMethodController::class, 'delete'])->name('payment_method.delete');

        // Customer Ledger
        Route::get('customer_ledgers', [CustomerLedgerController::class, 'index'])->name('customer_ledger.index');
        Route::get('customer_ledgers/datatable', [CustomerLedgerController::class, 'datatable'])->name('customer_ledger.datatable');
        Route::get('customer_ledgers/view/{id}', [CustomerLedgerController::class, 'view_ledger'])->name('customer_ledger.view');
        Route::get('customer_ledgers/detail_datatable/{id}', [CustomerLedgerController::class, 'detail_datatable'])->name('customer_ledger.detail_datatable');
        Route::get('customer_ledgers/transactions', [CustomerLedgerController::class, 'transactions_index'])->name('customer_ledger.transactions');
        Route::get('customer_ledgers/transactions_datatable', [CustomerLedgerController::class, 'transactions_datatable'])->name('customer_ledger.transactions_datatable');
        Route::get('customer_ledgers/report', [CustomerLedgerController::class, 'report'])->name('customer_ledger.report');
        Route::get('customer_ledgers/report_datatable', [CustomerLedgerController::class, 'report_datatable'])->name('customer_ledger.report_datatable');
        Route::get('customer_ledgers/export_pdf', [CustomerLedgerController::class, 'export_pdf'])->name('customer_ledger.export_pdf');
        Route::get('customer_ledgers/export_excel', [CustomerLedgerController::class, 'export_excel'])->name('customer_ledger.export_excel');
        Route::get('customer_ledgers/delete/{id}', [CustomerLedgerController::class, 'delete'])->name('customer_ledger.delete');
        Route::get('customer_ledgers/logs', [CustomerLedgerController::class, 'logs_index'])->name('customer_ledger.logs');
        Route::get('customer_ledgers/logs_datatable', [CustomerLedgerController::class, 'logs_datatable'])->name('customer_ledger.logs_datatable');
        Route::get('customer_ledgers/individual_pdf/{id}', [CustomerLedgerController::class, 'export_individual_ledger_pdf'])->name('customer_ledger.individual_pdf');

        // Ledger Followups
        Route::get('ledger_followups', [\App\Http\Controllers\LedgerFollowupController::class, 'index'])->name('ledger_followup.index');
        Route::get('ledger_followups/datatable', [\App\Http\Controllers\LedgerFollowupController::class, 'datatable'])->name('ledger_followup.datatable');
        Route::get('ledger_followups/pending_today', [\App\Http\Controllers\LedgerFollowupController::class, 'pending_today'])->name('ledger_followup.pending_today');
        Route::get('ledger_followups/pending_today_datatable', [\App\Http\Controllers\LedgerFollowupController::class, 'pending_today_datatable'])->name('ledger_followup.pending_today_datatable');
        Route::get('ledger_followups/history_all', [\App\Http\Controllers\LedgerFollowupController::class, 'history_all'])->name('ledger_followup.history_all');
        Route::get('ledger_followups/history_all_datatable', [\App\Http\Controllers\LedgerFollowupController::class, 'history_all_datatable'])->name('ledger_followup.history_all_datatable');
        Route::get('ledger_followups/report', [\App\Http\Controllers\LedgerFollowupController::class, 'report'])->name('ledger_followup.report');
        Route::get('ledger_followups/customer_report', [\App\Http\Controllers\LedgerFollowupController::class, 'customer_report'])->name('ledger_followup.customer_report');
        Route::post('ledger_followups/store', [\App\Http\Controllers\LedgerFollowupController::class, 'store'])->name('ledger_followup.store');
        Route::post('ledger_followups/update_thread', [\App\Http\Controllers\LedgerFollowupController::class, 'update_thread'])->name('ledger_followup.update_thread');
        Route::get('ledger_followups/history/{id}', [\App\Http\Controllers\LedgerFollowupController::class, 'get_history'])->name('ledger_followup.history');

        Route::post('customer_ledgers/store_payment', [CustomerLedgerController::class, 'store_payment'])->name('customer_ledger.store_payment');
        Route::post('customer_ledgers/store_multi_payment', [CustomerLedgerController::class, 'store_multi_payment'])->name('customer_ledger.store_multi_payment');
        Route::get('customer_ledgers/edit_modal/{id}', [CustomerLedgerController::class, 'edit_modal'])->name('customer_ledger.edit_modal');
        Route::get('customer_ledgers/delete/{id}', [CustomerLedgerController::class, 'delete'])->name('customer_ledger.delete');

        // Bill Management
        Route::get('bills', [BillController::class, 'index'])->name('bill.index');
        Route::get('bills/datatable', [BillController::class, 'datatable'])->name('bill.datatable');
        Route::get('bills/create', [BillController::class, 'create'])->name('bill.create');
        Route::post('bills/store', [BillController::class, 'store'])->name('bill.store');
        Route::get('bills/{id}/edit', [BillController::class, 'edit'])->name('bill.edit');
        Route::post('bills/update/{id}', [BillController::class, 'update'])->name('bill.update');
        Route::get('bills/{id}/pdf', [BillController::class, 'show'])->name('bill.pdf');
        Route::get('bills/delete/{id}', [BillController::class, 'destroy'])->name('bill.delete');

        // Lists accessible to all authorized roles
        Route::get('agent_customers/list/', [AgentCustomerController::class, 'agent_customer_list'])->name('agent_customer.list');
        Route::get('sale_executives/list/', [TeamController::class, 'member_list'])->name('sale_executive.list');
        Route::get('fabrics/list/', [FabricController::class, 'fabric_master_list'])->name('fabric.list');
        Route::get('bopps/list/', [BoppController::class, 'bopp_master_list'])->name('bopp.list');
        Route::get('loops/list/', [LoopController::class, 'loop_master_list'])->name('loop.list');
        Route::get('cylinder_agents/list/', [CylinderAgentController::class, 'cylinder_agent_master_list'])->name('cylinder_agent.list');
        Route::get('machines/list/', [MachineController::class, 'machine_master_list'])->name('machine.list');
        Route::get('blockage_reasons/list/', [BlockageReasonController::class, 'blockage_reason_master_list'])->name('blockage_reason.list');
        Route::post('agent_customers/store', [AgentCustomerController::class, 'store'])->name('agent_customer.store');
        Route::get('agent_customers/edit_modal/{id}', [AgentCustomerController::class, 'edit_modal'])->name('agent_customer.edit_modal');

            Route::resource('fabric', FabricController::class);
            Route::get('fabrics/datatable', [FabricController::class, 'datatable'])->name('fabric.datatable');
            Route::get('fabrics/edit_modal/{id}', [FabricController::class, 'edit_modal'])->name('fabric.edit_modal');
            Route::get('fabrics/delete/{id}', [FabricController::class, 'delete'])->name('fabric.delete');
            Route::get('fabrics/change_status/{id}', [FabricController::class, 'change_status'])->name('fabric.change_status');

            Route::resource('bopp', BoppController::class);
            Route::get('bopps/datatable', [BoppController::class, 'datatable'])->name('bopp.datatable');
            Route::get('bopps/edit_modal/{id}', [BoppController::class, 'edit_modal'])->name('bopp.edit_modal');
            Route::get('bopps/delete/{id}', [BoppController::class, 'delete'])->name('bopp.delete');
            Route::get('bopps/change_status/{id}', [BoppController::class, 'change_status'])->name('bopp.change_status');

            Route::resource('ink', InkController::class);
            Route::get('inks/datatable', [InkController::class, 'datatable'])->name('ink.datatable');
            Route::get('inks/edit_modal/{id}', [InkController::class, 'edit_modal'])->name('ink.edit_modal');
            Route::get('inks/delete/{id}', [InkController::class, 'delete'])->name('ink.delete');
            Route::get('inks/change_status/{id}', [InkController::class, 'change_status'])->name('ink.change_status');
            Route::get('inks/list/', [InkController::class, 'ink_master_list'])->name('ink.list');

            Route::resource('dana', DanaController::class);
            Route::get('danas/datatable', [DanaController::class, 'datatable'])->name('dana.datatable');
            Route::get('danas/edit_modal/{id}', [DanaController::class, 'edit_modal'])->name('dana.edit_modal');
            Route::get('danas/delete/{id}', [DanaController::class, 'delete'])->name('dana.delete');
            Route::get('danas/change_status/{id}', [DanaController::class, 'change_status'])->name('dana.change_status');
            Route::get('danas/list/', [DanaController::class, 'dana_master_list'])->name('dana.list');

            Route::resource('loop', LoopController::class);
            Route::get('loops/datatable', [LoopController::class, 'datatable'])->name('loop.datatable');
            Route::get('loops/edit_modal/{id}', [LoopController::class, 'edit_modal'])->name('loop.edit_modal');
            Route::get('loops/delete/{id}', [LoopController::class, 'delete'])->name('loop.delete');
            Route::get('loops/change_status/{id}', [LoopController::class, 'change_status'])->name('loop.change_status');

            Route::resource('agent_customer', AgentCustomerController::class);
            Route::get('agent_customers/datatable', [AgentCustomerController::class, 'datatable'])->name('agent_customer.datatable');
            Route::get('agent_customers/change_status/{id}', [AgentCustomerController::class, 'change_status'])->name('agent_customer.change_status');
            Route::post('agent_customers/upload', [AgentCustomerController::class, 'upload'])->name('agent_customer.upload');

            // Lead Agent Customer Unified Master
            Route::get('lead_agent_customer', [LeadAgentCustomerController::class, 'index'])->name('lead_agent_customer.index');
            Route::get('lead_agent_customer/datatable', [LeadAgentCustomerController::class, 'datatable'])->name('lead_agent_customer.datatable');
            Route::get('lead_agent_customer/delete/{id}', [LeadAgentCustomerController::class, 'delete'])->name('lead_agent_customer.delete');
            Route::get('lead_agent_customer/change_status/{id}', [LeadAgentCustomerController::class, 'change_status'])->name('lead_agent_customer.change_status');
            Route::get('agent_customers/delete/{id}', [AgentCustomerController::class, 'delete'])->name('agent_customer.delete');
            Route::get('agent_customers/check_lead', [AgentCustomerController::class, 'check_lead'])->name('agent_customer.check_lead');
            Route::get('agent_customers/convert/{id}', [AgentCustomerController::class, 'convert'])->name('agent_customer.convert');

            Route::resource('sale_executive', SaleExecutiveController::class);
            Route::get('sale_executives/datatable', [SaleExecutiveController::class, 'datatable'])->name('sale_executive.datatable');
            Route::get('sale_executives/edit_modal/{id}', [SaleExecutiveController::class, 'edit_modal'])->name('sale_executive.edit_modal');
            Route::get('sale_executives/delete/{id}', [SaleExecutiveController::class, 'delete'])->name('sale_executive.delete');
            Route::get('sale_executives/change_status/{id}', [SaleExecutiveController::class, 'change_status'])->name('sale_executive.change_status');

            Route::resource('cylinder_agent', CylinderAgentController::class);
            Route::get('cylinder_agents/datatable', [CylinderAgentController::class, 'datatable'])->name('cylinder_agent.datatable');
            Route::get('cylinder_agents/edit_modal/{id}', [CylinderAgentController::class, 'edit_modal'])->name('cylinder_agent.edit_modal');
            Route::get('cylinder_agents/delete/{id}', [CylinderAgentController::class, 'delete'])->name('cylinder_agent.delete');
            Route::get('cylinder_agents/change_status/{id}', [CylinderAgentController::class, 'change_status'])->name('cylinder_agent.change_status');

            Route::resource('machine', MachineController::class);
            Route::get('machines/datatable', [MachineController::class, 'datatable'])->name('machine.datatable');
            Route::get('machines/edit_modal/{id}', [MachineController::class, 'edit_modal'])->name('machine.edit_modal');
            Route::get('machines/delete/{id}', [MachineController::class, 'delete'])->name('machine.delete');
            Route::get('machines/change_status/{id}', [MachineController::class, 'change_status'])->name('machine.change_status');

            Route::resource('blockage_reason', BlockageReasonController::class);
            Route::get('blockage_reasons/datatable', [BlockageReasonController::class, 'datatable'])->name('blockage_reason.datatable');
            Route::get('blockage_reasons/edit_modal/{id}', [BlockageReasonController::class, 'edit_modal'])->name('blockage_reason.edit_modal');
            Route::get('blockage_reasons/delete/{id}', [BlockageReasonController::class, 'delete'])->name('blockage_reason.delete');
            Route::get('blockage_reasons/change_status/{id}', [BlockageReasonController::class, 'change_status'])->name('blockage_reason.change_status');
            
            // Common Stock Management
            Route::get('common_stock', [CommonManageStockController::class, 'index'])->name('common_stock.index');
            Route::get('common_stock/remaining', [CommonManageStockController::class, 'remaining'])->name('common_stock.remaining');
            Route::get('common_stock/remaining_list', [CommonManageStockController::class, 'remaining_list'])->name('common_stock.remaining_list');
            Route::get('common_stock/remaining_list_datatable', [CommonManageStockController::class, 'remaining_list_datatable'])->name('common_stock.remaining_list_datatable');
            Route::get('common_stock/datatable', [CommonManageStockController::class, 'datatable'])->name('common_stock.datatable');
            Route::post('common_stock/store', [CommonManageStockController::class, 'store'])->name('common_stock.store');
            Route::get('common_stock/edit_modal/{id}', [CommonManageStockController::class, 'edit_modal'])->name('common_stock.edit_modal');
            Route::post('common_stock/update/{id}', [CommonManageStockController::class, 'update'])->name('common_stock.update');
            Route::get('common_stock/delete/{id}', [CommonManageStockController::class, 'delete'])->name('common_stock.delete');
            Route::get('common_stock/get_current_stock', [CommonManageStockController::class, 'get_current_stock'])->name('common_stock.get_current_stock');
            Route::get('common_stock/history', [CommonManageStockController::class, 'history'])->name('common_stock.history');
            Route::get('common_stock/history_datatable', [CommonManageStockController::class, 'history_datatable'])->name('common_stock.history_datatable');
            Route::get('common_stock/report', [CommonManageStockController::class, 'report'])->name('common_stock.report');

            Route::get('size_color', [ColorMasterController::class, 'index'])->name('size_color.index');
            Route::get('color_master/datatable', [ColorMasterController::class, 'datatable'])->name('color_master.datatable');
            Route::get('color_master/edit_modal/{id}', [ColorMasterController::class, 'edit_modal'])->name('color_master.edit_modal');
            Route::get('color_master/delete/{id}', [ColorMasterController::class, 'delete'])->name('color_master.delete');
            Route::get('color_master/change_status/{id}', [ColorMasterController::class, 'change_status'])->name('color_master.change_status');
            Route::post('color_master/store', [ColorMasterController::class, 'store'])->name('color_master.store');

            Route::get('size_master/datatable', [SizeMasterController::class, 'datatable'])->name('size_master.datatable');
            Route::get('size_master/edit_modal/{id}', [SizeMasterController::class, 'edit_modal'])->name('size_master.edit_modal');
            Route::get('size_master/delete/{id}', [SizeMasterController::class, 'delete'])->name('size_master.delete');
            Route::get('size_master/change_status/{id}', [SizeMasterController::class, 'change_status'])->name('size_master.change_status');
            Route::post('size_master/store', [SizeMasterController::class, 'store'])->name('size_master.store');

            // Team Management Routes
            Route::get('team', [TeamController::class, 'index'])->name('team.index');
            Route::get('team/datatable', [TeamController::class, 'datatable'])->name('team.datatable');
            Route::get('team/edit_modal/{id}', [TeamController::class, 'edit_modal'])->name('team.edit_modal');
            Route::get('team/delete/{id}', [TeamController::class, 'delete'])->name('team.delete');
            Route::post('team/store', [TeamController::class, 'store'])->name('team.store');

            // Role Permissions
            Route::get('role_permissions', [RolePermissionController::class, 'index'])->name('role_permission.index');
            Route::get('role_permissions/get', [RolePermissionController::class, 'get_permissions'])->name('role_permission.get');
            Route::post('role_permissions/store', [RolePermissionController::class, 'store'])->name('role_permission.store');

            // Master Vendor Routes
            Route::get('master_vendor/add_modal/{type}/{id}', [MasterVendorController::class, 'add_vendor_modal'])->name('master_vendor.add_modal');
            Route::post('master_vendor/store', [MasterVendorController::class, 'store_vendor'])->name('master_vendor.store');
            Route::get('master_vendor/list_modal/{type}/{id}', [MasterVendorController::class, 'list_vendors_modal'])->name('master_vendor.list_modal');
            Route::get('master_vendor/delete/{id}', [MasterVendorController::class, 'delete_vendor'])->name('master_vendor.delete');

            // Old Data Routes
            Route::get('old_data', [OldDataController::class, 'index'])->name('old_data.index');
            Route::get('old_data/datatable', [OldDataController::class, 'datatable'])->name('old_data.datatable');
            Route::post('old_data/import', [OldDataController::class, 'import'])->name('old_data.import');
            Route::get('old_data/delete/{id}', [OldDataController::class, 'delete'])->name('old_data.delete');
            Route::get('old_data/search', [OldDataController::class, 'search'])->name('old_data.search');
            Route::get('old_data/get_details/{id}', [OldDataController::class, 'get_details'])->name('old_data.get_details');

        // Restricted Routes (Admin Only)
        Route::group(['middleware' => ['is_Admin']], function () {
            Route::get('admin/device-verifications', [\App\Http\Controllers\OtpController::class, 'admin_index'])->name('admin.device_verifications');
            Route::get('website-setting', [WebsiteController::class, 'index'])->name('website.setting');
            Route::post('website-setting/insert', [WebsiteController::class, 'insert'])->name('website.setting.insert');
        });
    });
    
    // OTP Routes for device verification
    Route::post('otp/verify', [\App\Http\Controllers\OtpController::class, 'verify'])->name('otp.verify');
    Route::post('otp/resend', [\App\Http\Controllers\OtpController::class, 'resend'])->name('otp.resend');
});

// ___________________________ Admin & User Route ____________________________
Route::group(['middleware' => ['auth','is_User']], function () {
   

});

// ___________________________ Lead Management Routes ____________________________
Route::prefix('lead')->group(function () {
    Route::get('login', function() { return redirect('/login'); })->name('lead.login');
    Route::post('login', [LeadLoginController::class, 'login'])->name('lead.login.submit');
    Route::post('logout', [LeadLoginController::class, 'logout'])->name('lead.logout');

    Route::group(['middleware' => ['auth']], function () {
        Route::get('dashboard', fn() => redirect()->route('dashboard'))->name('lead.dashboard');
        Route::get('dashboard/widgets', [LeadDashboardController::class, 'widgets'])->name('lead.dashboard.widgets');

        Route::get('agent-dashboard', [AgentDashboardController::class, 'index'])->name('lead.agent_dashboard');
        Route::get('agent-dashboard/widgets', [AgentDashboardController::class, 'widgets'])->name('lead.agent_dashboard.widgets');

        // Masters
        Route::prefix('master')->group(function () {
            Route::get('source', [LeadMasterController::class, 'sourceIndex'])->name('lead.source.index');
            Route::post('source', [LeadMasterController::class, 'sourceStore'])->name('lead.source.store');
            Route::get('source/delete/{id}', [LeadMasterController::class, 'sourceDelete'])->name('lead.source.delete');

            Route::get('tag', [LeadMasterController::class, 'tagIndex'])->name('lead.tag.index');
            Route::post('tag', [LeadMasterController::class, 'tagStore'])->name('lead.tag.store');
            Route::get('tag/delete/{id}', [LeadMasterController::class, 'tagDelete'])->name('lead.tag.delete');

            Route::get('status', [LeadMasterController::class, 'statusIndex'])->name('lead.status.index');
            Route::post('status', [LeadMasterController::class, 'statusStore'])->name('lead.status.store');
            Route::post('status/update-order', [LeadMasterController::class, 'statusUpdateOrder'])->name('lead.status.update-order');
            Route::post('status/update-field', [LeadMasterController::class, 'statusUpdateField'])->name('lead.status.update-field');
            Route::get('status/delete/{id}', [LeadMasterController::class, 'statusDelete'])->name('lead.status.delete');

            // Team Management
            Route::get('team', [LeadTeamController::class, 'index'])->name('lead.team.index');
            Route::post('team', [LeadTeamController::class, 'store'])->name('lead.team.store');
            Route::get('team/delete/{id}', [LeadTeamController::class, 'delete'])->name('lead.team.delete');
        });

        // Leads CRUD
        Route::get('leads/datatable', [LeadController::class, 'datatable'])->name('lead.datatable');
        Route::get('leads/pending', [LeadController::class, 'pendingIndex'])->name('lead.pending');
        Route::get('leads/won', [LeadController::class, 'wonIndex'])->name('lead.won');
        Route::get('leads/lost', [LeadController::class, 'lostIndex'])->name('lead.lost');
        Route::get('leads/report', [LeadController::class, 'reportIndex'])->name('lead.report');
        Route::get('leads/report-data', [LeadController::class, 'reportData'])->name('lead.report.data');
        Route::get('leads/report-pdf', [LeadController::class, 'reportPdf'])->name('lead.report.pdf');
        Route::get('leads/report-pdf-simple', [LeadController::class, 'simpleReportPdf'])->name('lead.report.pdf.simple');
        Route::get('leads/report-charts', [LeadController::class, 'reportCharts'])->name('lead.report.charts');
        
        Route::get('leads/job-card-status', [LeadController::class, 'jobCardStatus'])->name('lead.job_card_status');
        Route::get('leads/repeat-suggestions', [LeadController::class, 'repeatSuggestionsIndex'])->name('lead.repeat_suggestions');

        // Location Management
        Route::get('locations/states', [\App\Http\Controllers\Lead\LeadLocationController::class, 'index'])->name('lead.locations.states');
        Route::post('locations/states', [\App\Http\Controllers\Lead\LeadLocationController::class, 'storeState'])->name('lead.locations.states.store');
        Route::put('locations/states/{id}', [\App\Http\Controllers\Lead\LeadLocationController::class, 'updateState'])->name('lead.locations.states.update');
        Route::delete('locations/states/{id}', [\App\Http\Controllers\Lead\LeadLocationController::class, 'destroyState'])->name('lead.locations.states.destroy');
        
        Route::get('leads/report', [LeadController::class, 'reportIndex'])->name('lead.report');
        Route::get('leads/report-data', [LeadController::class, 'reportData'])->name('lead.report.data');
        Route::get('leads/report-charts', [LeadController::class, 'reportCharts'])->name('lead.report.charts');
        Route::get('leads/report-pdf', [LeadController::class, 'reportPdf'])->name('lead.report.pdf');
        Route::get('leads/report-pdf-simple', [LeadController::class, 'simpleReportPdf'])->name('lead.report.pdf.simple');
        
        Route::get('locations/cities', [\App\Http\Controllers\Lead\LeadLocationController::class, 'cityIndex'])->name('lead.locations.cities');
        Route::post('locations/cities', [\App\Http\Controllers\Lead\LeadLocationController::class, 'storeCity'])->name('lead.locations.cities.store');
        Route::post('locations/cities/quick-store', [\App\Http\Controllers\Lead\LeadLocationController::class, 'quickStoreCity'])->name('lead.locations.cities.quick_store');
        Route::put('locations/cities/{id}', [\App\Http\Controllers\Lead\LeadLocationController::class, 'updateCity'])->name('lead.locations.cities.update');
        Route::delete('locations/cities/{id}', [\App\Http\Controllers\Lead\LeadLocationController::class, 'destroyCity'])->name('lead.locations.cities.destroy');
        Route::get('locations/get-cities/{state_id}', [\App\Http\Controllers\Lead\LeadLocationController::class, 'getCitiesByState'])->name('lead.locations.get-cities');
                Route::get('leads/history_modal/{id}', [LeadController::class, 'history_modal'])->name('lead.history_modal');
        Route::get('leads/followup-modal/{id}', [LeadController::class, 'followup_modal'])->name('lead.followup_modal');
        Route::get('leads/get-timeline/{id}', [LeadController::class, 'getTimeline'])->name('lead.get-timeline');
        Route::get('leads/get-profile-content/{id}', [LeadController::class, 'getProfileContent'])->name('lead.get-profile-content');
        Route::get('leads/get-status-history/{id}', [LeadController::class, 'getStatusHistory'])->name('lead.get-status-history');
        Route::post('leads/check-phone', [LeadController::class, 'checkPhone'])->name('lead.check-phone');
        Route::post('leads/check-job-card-no', [LeadController::class, 'checkJobCardNo'])->name('lead.check-job-card-no');
        Route::get('leads/show/{id}', [LeadController::class, 'show'])->name('lead.leads.show');
        Route::post('leads/update-step/{id}', [LeadController::class, 'updateStepData'])->name('lead.leads.update-step');
        Route::post('leads/update-job-card-no/{id}', [LeadController::class, 'updateJobCardNo'])->name('lead.leads.update-job-card-no');
        Route::post('leads/mark-lost/{id}', [LeadController::class, 'markLost'])->name('lead.leads.mark-lost');
        Route::post('leads/rollback-stage/{id}', [LeadController::class, 'rollbackStage'])->name('lead.leads.rollback-stage');
        Route::get('leads/datatable', [LeadController::class, 'datatable'])->name('lead.datatable');
        Route::post('leads/transfer/{id}', [LeadController::class, 'transfer'])->name('lead.transfer');
        Route::delete('leads/destroy-all-for-client/{id}', [LeadController::class, 'destroyAllForClient'])->name('lead.destroy-all-for-client');

        Route::resource('leads', LeadController::class)->names([
            'index' => 'lead.index',
            'create' => 'lead.create',
            'store' => 'lead.store',
            'edit' => 'lead.edit',
            'update' => 'lead.update',
            'destroy' => 'lead.destroy',
        ]);
        
        Route::get('leads-won', [LeadController::class, 'wonIndex'])->name('lead.won');
        Route::get('leads-lost', [LeadController::class, 'lostIndex'])->name('lead.lost');
        Route::get('leads-pending', [LeadController::class, 'pendingIndex'])->name('lead.pending');
        Route::get('leads-repeat-suggestions', [LeadController::class, 'repeatSuggestionsIndex'])->name('lead.repeat_suggestions');
        

        // Followups
        Route::get('followup', [LeadController::class, 'followupList'])->name('lead.followup.index');
        Route::get('followup/pending', fn() => redirect()->route('lead.followup.index', ['filter' => 'pending']))->name('lead.followup.pending');
        Route::get('followup/today', fn() => redirect()->route('lead.followup.index', ['filter' => 'pending']))->name('lead.followup.today');
        Route::get('followup/upcoming', fn() => redirect()->route('lead.followup.index', ['filter' => 'upcoming']))->name('lead.followup.upcoming');
        Route::post('followup/store/{id}', [LeadController::class, 'followupStore'])->name('lead.followup.store');

        // Agent Lead System
        Route::prefix('agent-master')->group(function () {
            Route::get('deals-in', [AgentMasterController::class, 'dealsInIndex'])->name('lead.agent.deals_in.index');
            Route::post('deals-in', [AgentMasterController::class, 'dealsInStore'])->name('lead.agent.deals_in.store');
            Route::get('deals-in/delete/{id}', [AgentMasterController::class, 'dealsInDelete'])->name('lead.agent.deals_in.delete');

            Route::get('agent', [AgentMasterController::class, 'agentIndex'])->name('lead.agent.agent.index');
            Route::post('agent', [AgentMasterController::class, 'agentStore'])->name('lead.agent.agent.store');
            Route::get('agent/delete/{id}', [AgentMasterController::class, 'agentDelete'])->name('lead.agent.agent.delete');
            Route::get('agents-json', [AgentMasterController::class, 'getAgentsJson'])->name('lead.agent.agents_json');
            Route::get('agent/check-phone', [AgentMasterController::class, 'checkAgentPhone'])->name('lead.agent.check_phone');
        });

        Route::prefix('agent-leads')->group(function () {
            Route::get('datatable', [AgentLeadController::class, 'datatable'])->name('lead.agent_leads.datatable');
            Route::get('pending', [AgentLeadController::class, 'pendingIndex'])->name('lead.agent_leads.pending');
            Route::get('won', [AgentLeadController::class, 'wonIndex'])->name('lead.agent_leads.won');
            Route::get('lost', [AgentLeadController::class, 'lostIndex'])->name('lead.agent_leads.lost');
            
            Route::get('order-process', [AgentLeadController::class, 'jobCardStatus'])->name('lead.agent_leads.order_process');
            Route::get('repeat-suggestions', [AgentLeadController::class, 'repeatSuggestions'])->name('lead.agent_leads.repeat_suggestions');
            Route::post('check-job-card-no', [AgentLeadController::class, 'checkJobCardNo'])->name('lead.agent_leads.check_job_card_no');
            Route::post('update-job-card-no/{id}', [AgentLeadController::class, 'updateJobCardNo'])->name('lead.agent_leads.update_job_card_no');

            Route::get('show/{id}', [AgentLeadController::class, 'show'])->name('lead.agent_leads.show');
            Route::get('get-profile-content/{id}', [AgentLeadController::class, 'getProfileContent'])->name('lead.agent_leads.get-profile-content');
            Route::get('get-timeline/{id}', [AgentLeadController::class, 'getTimeline'])->name('lead.agent_leads.get-timeline');
            Route::get('get-status-history/{id}', [AgentLeadController::class, 'getStatusHistory'])->name('lead.agent_leads.get-status-history');
            Route::get('followup-modal/{id}', [AgentLeadController::class, 'followupModal'])->name('lead.agent_leads.followup_modal');
            Route::get('overall-followup-modal/{agent_id}', [AgentLeadController::class, 'overallFollowupModal'])->name('lead.agent_leads.overall_followup_modal');
            Route::post('followup/store/{id}', [AgentLeadController::class, 'followupStore'])->name('lead.agent_leads.followup.store');
            Route::post('rollback-stage/{id}', [AgentLeadController::class, 'rollbackStage'])->name('lead.agent_leads.rollback-stage');
            Route::post('check-agent', [AgentLeadController::class, 'checkAgent'])->name('lead.agent_leads.check-agent');
            
            // Overall Followup
            Route::post('overall-followup/store/{id}', [AgentLeadController::class, 'storeOverallFollowup'])->name('lead.agent_leads.overall_followup.store');
            Route::get('overall-followup/history/{id}', [AgentLeadController::class, 'getOverallFollowupHistory'])->name('lead.agent_leads.overall_followup.history');

            Route::post('store-single-job', [AgentLeadController::class, 'storeSingleJob'])->name('lead.agent_leads.store_single_job');

            Route::get('report', [AgentReportController::class, 'reportIndex'])->name('lead.agent_leads.report');
            Route::get('report-data', [AgentReportController::class, 'reportData'])->name('lead.agent_leads.report.data');
            Route::get('report-charts', [AgentReportController::class, 'reportCharts'])->name('lead.agent_leads.report.charts');
        });

        Route::resource('agent-leads', AgentLeadController::class)->names([
            'index' => 'lead.agent_leads.index',
            'create' => 'lead.agent_leads.create',
            'store' => 'lead.agent_leads.store',
            'edit' => 'lead.agent_leads.edit',
            'update' => 'lead.agent_leads.update',
            'destroy' => 'lead.agent_leads.destroy',
        ]);
    });
});