<?php

namespace App\Jobs;

use App\Models\CustomerLedger;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCreditWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ledgerId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ledgerId)
    {
        $this->ledgerId = $ledgerId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WhatsAppService $service)
    {
        // 1. Fetch Ledger entry with customer and payment method
        $ledger = CustomerLedger::with(['customer', 'payment_method'])->find($this->ledgerId);

        // 2. CHECK: If entry deleted or modified to Dr, skip
        if (!$ledger || $ledger->dr_cr !== 'Cr') {
            Log::info("WhatsApp Credit Job: Ledger entry #{$this->ledgerId} not eligible. Skipping.");
            return;
        }

        if (!$ledger->customer || empty($ledger->customer->phone_no)) {
            Log::warning("WhatsApp Credit Job: Ledger #{$ledger->id} customer has no phone number.");
            return;
        }

        // 3. Clean phone number
        $to = preg_replace('/[^0-9]/', '', $ledger->customer->phone_no);
        
        if (strlen($to) === 10) {
            $to = '91' . $to;
        }
        
        // 4. Trigger the service
        Log::info("WhatsApp Credit Job: Sending payment confirmation for Ledger #{$ledger->id} to {$to}");
        $service->sendPaymentReceivedNotification($to, $ledger);
    }
}
