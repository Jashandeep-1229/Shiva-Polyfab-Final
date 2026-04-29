<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bill;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class SendDueBillReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bills:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Scan for bills due today and send WhatsApp reminders';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $service)
    {
        $today = Carbon::today();
        
        // --- 1. SCAN FOR BILLS DUE TODAY ---
        $dueToday = Bill::with('customer')
            ->whereDate('due_date', $today->toDateString())
            ->where('status', 1)
            ->get();

        $this->info("Scanning " . $dueToday->count() . " bills due today...");
        foreach ($dueToday as $bill) {
            $this->sendNotification($service, $bill, 'due');
        }

        // --- 2. SCAN FOR OVERDUE BILLS (Every 3 Days) ---
        // Expression: Unpaid bills where Due Date is in the past AND (Today - Due Date) is a multiple of 3
        $overdueBills = Bill::with('customer')
            ->where('status', 1)
            ->whereDate('due_date', '<', $today->toDateString())
            ->get()
            ->filter(function($bill) use ($today) {
                $daysOverdue = $today->diffInDays(Carbon::parse($bill->due_date));
                return $daysOverdue > 0 && ($daysOverdue % 3 == 0);
            });

        $this->info("Scanning " . $overdueBills->count() . " bills overdue by multiples of 3 days...");
        foreach ($overdueBills as $bill) {
            $this->sendNotification($service, $bill, 'overdue');
        }

        // --- 3. SCAN FOR BILLS DUE IN 2 DAYS ---
        $dueInTwoDays = Bill::with('customer')
            ->whereDate('due_date', Carbon::today()->addDays(2)->toDateString())
            ->where('status', 1)
            ->get();

        $this->info("Scanning " . $dueInTwoDays->count() . " bills due in 2 days...");
        foreach ($dueInTwoDays as $bill) {
            $this->sendNotification($service, $bill, 'before');
        }

        $this->info("All reminders processed.");
        return Command::SUCCESS;
    }

    protected function sendNotification($service, $bill, $type)
    {
        if ($bill->customer && !empty($bill->customer->phone_no)) {
            
            // Clean phone number
            $to = preg_replace('/[^0-9]/', '', $bill->customer->phone_no);
            if (strlen($to) === 10) {
                $to = '91' . $to;
            }

            if ($type === 'due') {
                $this->info("Sending TODAY reminder to {$to} for Bill #{$bill->bill_no}");
                $service->sendDueReminder($to, $bill);
            } elseif ($type === 'before') {
                $this->info("Sending 2-DAY BEFORE reminder to {$to} for Bill #{$bill->bill_no}");
                $service->sendBeforeReminder($to, $bill);
            } else {
                $this->info("Sending OVERDUE reminder to {$to} for Bill #{$bill->bill_no}");
                $service->sendOverdueReminder($to, $bill);
            }
        }
    }
}
