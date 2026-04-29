<?php

namespace App\Jobs;

use App\Models\Bill;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBillWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $billId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($billId)
    {
        $this->billId = $billId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WhatsAppService $service)
    {
        // 1. Fetch the Bill with its customer
        // We use withTrashed() if the model uses SoftDeletes, but find() won't return it if trashed.
        $bill = Bill::with('customer')->find($this->billId);

        // 2. CHECK: If 1 hour passes and bill is deleted or status changed to inactive, do not send
        if (!$bill || $bill->status == 0) {
            Log::info("WhatsApp Job: Bill #{$this->billId} was deleted or deactivated. Skipping notification.");
            return;
        }

        if (!$bill->customer || empty($bill->customer->phone_no)) {
            Log::warning("WhatsApp Job: Bill #{$bill->id} has no customer phone number.");
            return;
        }

        // 3. Clean phone number
        $to = preg_replace('/[^0-9]/', '', $bill->customer->phone_no);
        if (strlen($to) === 10) {
            $to = '91' . $to;
        }

        // 4. Trigger the service
        Log::info("WhatsApp Job: Sending notification for Bill #{$bill->bill_no} to {$to}");
        $service->sendBillNotification($to, $bill);
    }
}
