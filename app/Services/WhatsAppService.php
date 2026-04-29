<?php

namespace App\Services;

use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use Netflie\WhatsAppCloudApi\Message\Template\Component;
use Netflie\WhatsAppCloudApi\Message\Media\MediaObjectID;

class WhatsAppService
{
    protected $whatsapp;

    public function __construct()
    {
        $this->whatsapp = new WhatsAppCloudApi([
            'from_phone_number_id' => env('WHATSAPP_FROM_PHONE_NUMBER_ID'),
            'access_token' => env('WHATSAPP_TOKEN'),
        ]);
    }

    public function sendBillNotification($to, $bill)
    {
        $to = $this->getRecipient($to);
        try {
            // 1. Generate the public PDF URL (Meta fetcher needs this)
            $pdf_url = route('bill.share_pdf', $bill->id);
            
            // 2. Format variables to match template preview
            $bill_no = $bill->bill_no; // SP-20
            $amount = '₹ ' . number_format($bill->grand_total, 2); // ₹ 20,000
            $due_date = $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('d F, Y') : 'NA'; // 20 May, 2026
            
            // 3. Setup Components
            // Header: Document PDF
            $component_header = [
                [
                    'type' => 'document',
                    'document' => [
                        'link' => $pdf_url,
                        'filename' => "Bill_{$bill->bill_no}.pdf"
                    ]
                ]
            ];
            
            // Body: 3 Variables
            $component_body = [
                ['type' => 'text', 'text' => $bill_no],
                ['type' => 'text', 'text' => $amount],
                ['type' => 'text', 'text' => $due_date],
            ];
            
            $components = new Component($component_header, $component_body);
            
            // 4. Dispatch the template
            $response = $this->whatsapp->sendTemplate(
                $to,
                'bill_send_pdf_1', 
                'en', // Make sure it is 'en' or 'en_US' as registered
                $components
            );
            
            \Log::info("WhatsApp Bill SENT to {$to}: " . json_encode($response->decodedBody()));
            return true;
        } catch (\Exception $e) {
            \Log::error("WhatsApp Bill Error: " . $e->getMessage());
            if (method_exists($e, 'getResponse')) {
                \Log::error("WhatsApp Bill Error Full Body: " . $e->getResponse()->getBody());
            }
            return false;
        }
    }

    public function sendPaymentReceivedNotification($to, $ledger)
    {
        $to = $this->getRecipient($to);
        try {
            $customer = $ledger->customer;
            
            $component_header = [];
            $component_body = [
                ['type' => 'text', 'text' => '₹ ' . number_format($ledger->grand_total_amount, 2)],
            ];

            $components = new Component($component_header, $component_body);

            $response = $this->whatsapp->sendTemplate(
                $to,
                'payment_recd_thank_you_1', 
                'en', 
                $components
            );

            \Log::info("WhatsApp Payment SENT to {$to}: " . json_encode($response->decodedBody()));
            return true;
        } catch (\Exception $e) {
            \Log::error("WhatsApp Payment Error: " . $e->getMessage());
            if (method_exists($e, 'getResponse')) {
                \Log::error("WhatsApp Payment Full Error Body: " . $e->getResponse()->getBody());
            }
            return false;
        }
    }
    public function sendDueReminder($to, $bill)
    {
        $to = $this->getRecipient($to);
        try {
            $amount = number_format($bill->grand_total, 2);
            $bill_no = $bill->bill_no;
            
            $component_header = [];
            $component_body = [
                ['type' => 'text', 'text' => $amount],
                ['type' => 'text', 'text' => $bill_no],
            ];
            
            $components = new Component($component_header, $component_body);
            
            $response = $this->whatsapp->sendTemplate(
                $to,
                'bill_due_reminder_today_1', 
                'en',
                $components
            );
            
            \Log::info("WhatsApp Due Reminder SENT to {$to}: " . json_encode($response->decodedBody()));
            return true;
        } catch (\Exception $e) {
            \Log::error("WhatsApp Due Reminder Error: " . $e->getMessage());
            return false;
        }
    }
    public function sendOverdueReminder($to, $bill)
    {
        $to = $this->getRecipient($to);
        try {
            $amount = number_format($bill->grand_total, 2);
            $bill_no = $bill->bill_no;
            
            $component_header = [];
            $component_body = [
                ['type' => 'text', 'text' => $amount],
                ['type' => 'text', 'text' => $bill_no],
            ];
            
            $components = new Component($component_header, $component_body);
            
            $response = $this->whatsapp->sendTemplate(
                $to,
                'bill_over_due_1', 
                'en',
                $components
            );
            
            \Log::info("WhatsApp Overdue Reminder SENT to {$to}: " . json_encode($response->decodedBody()));
            return true;
        } catch (\Exception $e) {
            \Log::error("WhatsApp Overdue Reminder Error: " . $e->getMessage());
            return false;
        }
    }
    public function sendBeforeReminder($to, $bill)
    {
        $to = $this->getRecipient($to);
        try {
            $amount = number_format($bill->grand_total, 2);
            $bill_no = $bill->bill_no;
            $due_date = $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('d F, Y') : 'NA';
            
            $component_header = [];
            $component_body = [
                ['type' => 'text', 'text' => $amount],
                ['type' => 'text', 'text' => $bill_no],
                ['type' => 'text', 'text' => $due_date],
            ];
            
            $components = new Component($component_header, $component_body);
            
            $response = $this->whatsapp->sendTemplate(
                $to,
                'bill_reminder_before_1', 
                'en',
                $components
            );
            
            \Log::info("WhatsApp Before Reminder SENT to {$to}: " . json_encode($response->decodedBody()));
            return true;
        } catch (\Exception $e) {
            \Log::error("WhatsApp Before Reminder Error: " . $e->getMessage());
            return false;
        }
    }
    public function sendLedgerSummary($to, $customer, $pendingAmount)
    {
        $to = $this->getRecipient($to);
        try {
            // 1. Generate the public PDF URL (Meta fetcher needs this)
            $pdf_url = route('customer_ledger.share_pdf', $customer->id);
            
            // 2. Setup Components
            // Header: Document PDF
            $component_header = [
                [
                    'type' => 'document',
                    'document' => [
                        'link' => $pdf_url,
                        'filename' => "Ledger_{$customer->name}.pdf"
                    ]
                ]
            ];
            
            // Body: 1 Variable (Total Outstanding)
            $component_body = [
                ['type' => 'text', 'text' => '₹ ' . number_format($pendingAmount, 2)],
            ];
            
            $components = new Component($component_header, $component_body);
            
            $response = $this->whatsapp->sendTemplate(
                $to,
                'ledger_summary_reminder_1', 
                'en',
                $components
            );
            
            \Log::info("WhatsApp Ledger Summary SENT to {$to}: " . json_encode($response->decodedBody()));
            return true;
        } catch (\Exception $e) {
            \Log::error("WhatsApp Ledger Summary Error: " . $e->getMessage());
            return false;
        }
    }

    private function getRecipient($to)
    {
        return $to;
    }

    public function sendTextMessage($to, $message)
    {
        try {
            $to = $this->getRecipient($to);
            $response = $this->whatsapp->sendTextMessage($to, $message);
            return $response;
        } catch (\Exception $e) {
            \Log::error("WhatsApp Text Send Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function sendMediaMessage($to, $media_id, $type = 'image')
    {
        try {
            $to = $this->getRecipient($to);
            if ($type == 'image') {
                return $this->whatsapp->sendImage($to, new \Netflie\WhatsAppCloudApi\Message\Media\MediaObjectID($media_id));
            } elseif ($type == 'video') {
                return $this->whatsapp->sendVideo($to, new \Netflie\WhatsAppCloudApi\Message\Media\MediaObjectID($media_id));
            } elseif ($type == 'document') {
                return $this->whatsapp->sendDocument($to, new \Netflie\WhatsAppCloudApi\Message\Media\MediaObjectID($media_id));
            }
        } catch (\Exception $e) {
            \Log::error("WhatsApp Media Send Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function uploadMedia($file_path, $type = 'image')
    {
        try {
            $response = $this->whatsapp->uploadMedia($file_path);
            return $response->decodedBody()['id'] ?? null;
        } catch (\Exception $e) {
            \Log::error("WhatsApp Media Upload Error: " . $e->getMessage());
            return null;
        }
    }

    public function downloadMediaToFile($media_id, $save_path, $directUrl = null)
    {
        try {
            $token = env('WHATSAPP_TOKEN');
            $downloadUrl = $directUrl;
            
            if (!$downloadUrl) {
                // Get media details from Meta API if no direct URL provided
                $url = "https://graph.facebook.com/v19.0/{$media_id}";
                $response = \Illuminate\Support\Facades\Http::withToken($token)->get($url);
                
                if (!$response->successful()) {
                    \Log::error("WhatsApp Media Detail Error: " . $response->body());
                    return false;
                }
                $downloadUrl = $response->json()['url'] ?? null;
            }
            
            if (!$downloadUrl) {
                \Log::error("No download URL found for Media ID: " . $media_id);
                return false;
            }
            
            // 2. Download binary data using the authenticated URL
            $mediaResponse = \Illuminate\Support\Facades\Http::withToken($token)->get($downloadUrl);
            
            if ($mediaResponse->successful()) {
                // Ensure directory exists
                $dir = dirname($save_path);
                if (!file_exists($dir)) mkdir($dir, 0777, true);
                
                file_put_contents($save_path, $mediaResponse->body());
                \Log::info("WhatsApp Media Downloaded Successfully to Public: {$save_path}");
                return true;
            }
            
            \Log::error("WhatsApp Binary Download Failed for ID: {$media_id}");
            return false;
        } catch (\Exception $e) {
            \Log::error("WhatsApp Media Download Exception: " . $e->getMessage());
            return false;
        }
    }
    public function sendWelcomeGreeting($to)
    {
        return $this->whatsapp->sendTemplate(
            $to,
            'start_message', 
            'en'
        );
    }
}
