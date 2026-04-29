<?php

namespace App\Services;

use App\Models\JobCard;
use App\Models\Lead;
use App\Models\AgentLead;
use App\Models\Bill;
use App\Models\User;
use App\Models\Fabric;
use App\Models\Dana;
use App\Models\Bopp;
use App\Models\Ink;
use App\Models\Loop;
use App\Models\ManageStock;
use App\Models\CommonManageStock;
use App\Models\AgentCustomer;
use App\Models\CustomerLedger;
use App\Models\SizeMaster;
use App\Models\ColorMaster;
use App\Models\PackingSlip;
use App\Models\CylinderJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AiIntelligenceService
{
    protected $apiKey;

    protected $models = [
        'gemini-3-flash'        => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash:generateContent',
        'gemini-2.5-flash'      => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
        'gemini-2.5-flash-lite' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent',
    ];

    // Track which model actually responded
    public string $lastModelUsed = 'unknown';

    public function __construct()
    {
        $this->apiKey = trim(env('AI_API_KEY'));
    }

    /**
     * Main entry point — receives user message, scans DB, calls Gemini
     */
    public function askAi(User $user, string $message, ?string $fileBase64 = null, ?string $mimeType = null, $history = null): string
    {
        // Hard limit: prevent absurdly long inputs
        $message = mb_substr(trim($message), 0, 500);
        if (empty($message) && empty($fileBase64)) return 'Please type a message or upload a file.';

        $context = $this->buildFullDatabaseContext($user, $message, $history);
        $systemPrompt  = $this->buildSystemPrompt($user, $message, $context);
        return $this->callGemini($systemPrompt, $message, $fileBase64, $mimeType, $history);
    }

    // -------------------------------------------------------------------------
    // DATABASE CONTEXT BUILDER — scans every important table
    // -------------------------------------------------------------------------

    private function buildFullDatabaseContext(User $user, string $message, $history = null): string
    {
        $lines = [];

        // Determine all text the user recently said to extract context keywords
        $allText = $message;
        if ($history) {
            foreach ($history as $h) {
                if (!empty($h->user_message)) {
                    $allText .= ' ' . $h->user_message;
                }
            }
        }
        
        $words = collect(explode(' ', preg_replace('/[^a-zA-Z0-9\s]/', '', $allText)))
            ->filter(fn($w) => strlen($w) > 3)
            ->unique()
            ->toArray();

        // 1. FACTORY OVERVIEW
        $totalJCs      = JobCard::count();
        $activeJCs     = JobCard::where('status', 'In Production')->count();
        $pendingJCs    = JobCard::where('status', 'Pending')->count();
        $completedJCs  = JobCard::where('status', 'Completed')->count();
        $holdJCs       = JobCard::where('is_hold', 1)->count();
        $todayJCs      = JobCard::whereDate('created_at', Carbon::today())->count();

        $lines[] = "=== FACTORY OVERVIEW ===";
        $lines[] = "Total Job Cards: $totalJCs | Active (In Production): $activeJCs | Pending: $pendingJCs | Completed: $completedJCs | On Hold: $holdJCs | Created Today: $todayJCs";

        // 2. RECENT JOB CARDS (Extended & Contextual)
        $lines[] = "\n=== JOB CARDS (Last 100 + Matched Keywords) ===";
        
        $jcQuery = JobCard::with(['customer_agent', 'fabric', 'bopp', 'size', 'color'])->latest()->take(100);
        $recentJCs = $jcQuery->get();
        
        $matchedJCs = collect();
        if (count($words) > 0) {
            $matchQuery = JobCard::with(['customer_agent', 'fabric', 'bopp', 'size', 'color']);
            $matchQuery->whereHas('customer_agent', function($q) use ($words) {
                $q->where(function($q2) use ($words) {
                    foreach($words as $w) {
                        $q2->orWhere('name', 'like', "%{$w}%");
                    }
                });
            })->orWhere(function($q) use ($words) {
                foreach($words as $w) {
                     $q->orWhere('name_of_job', 'like', "%{$w}%");
                     $q->orWhere('job_card_no', 'like', "%{$w}%");
                }
            });
            $matchedJCs = $matchQuery->take(50)->get();
        }
        
        $allJCs = $recentJCs->merge($matchedJCs)->unique('id');

        foreach ($allJCs as $jc) {
            $customer = optional($jc->customer_agent)->name ?? $jc->name_of_job ?? 'N/A';
            $fabric   = optional($jc->fabric)->name ?? 'N/A';
            $viewUrl  = route('job_card.show', $jc->id);
            $processUrl = route('job_card.next_process', $jc->id);
            $lines[]  = "JC#{$jc->job_card_no} | ViewLink: {$viewUrl} | ProcessLink: {$processUrl} | Customer: $customer | Process: {$jc->job_card_process} | Status: {$jc->status} | Pieces: " . number_format($jc->no_of_pieces) . " | Fabric: $fabric | Hold: " . ($jc->is_hold ? 'YES - ' . $jc->hold_notes : 'No') . " | Date: " . Carbon::parse($jc->job_card_date)->format('d-M-Y');
        }

        // 3. INVENTORY / RAW MATERIAL STOCK
        $lines[] = "\n=== RAW MATERIAL STOCK (Live Calculated) ===";
        $stockMapping = [
            'fabric' => Fabric::class,
            'dana'   => Dana::class,
            'bopp'   => Bopp::class,
            'ink'    => Ink::class,
            'loop'   => Loop::class,
        ];
        
        $stockLink = route('manage_stock.index');
        $lines[] = "Raw Material Stock Manager Link: $stockLink";

        foreach ($stockMapping as $key => $modelClass) {
            $items = $modelClass::where('status', 1)->get();
            foreach ($items as $item) {
                $in  = ManageStock::where('stock_name', $key)->where('stock_id', $item->id)->where('in_out', 'in')->sum('quantity');
                $out = ManageStock::where('stock_name', $key)->where('stock_id', $item->id)->where('in_out', 'out')->sum('quantity');
                $gsm = isset($item->gsm) ? " | GSM: {$item->gsm}" : '';
                $rem = round($in - $out, 2);
                $alert = $rem < 50 ? ' ⚠️ LOW STOCK' : '';
                $lines[] = ucfirst($key) . ": {$item->name}{$gsm} | In: {$in}kg | Out: {$out}kg | Remaining: {$rem}kg{$alert}";
            }
        }

        // 4. COMMON STOCK (Finished Bags)
        $lines[] = "\n=== COMMON FINISHED BAG STOCK ===";
        $sizes  = SizeMaster::where('status', 1)->get();
        $colors = ColorMaster::where('status', 1)->get();
        foreach ($sizes as $size) {
            foreach ($colors as $color) {
                $in  = CommonManageStock::where('size_id', $size->id)->where('color_id', $color->id)->where('in_out', 'in')->sum('quantity');
                $out = CommonManageStock::where('size_id', $size->id)->where('color_id', $color->id)->where('in_out', 'out')->sum('quantity');
                $rem = $in - $out;
                if ($rem > 0 || $in > 0) {
                    $lines[] = "Size: {$size->name} | Color: {$color->name} | In: {$in} | Out: {$out} | Remaining: {$rem} pcs";
                }
            }
        }

        // 5. CUSTOMER / AGENT DATA
        $lines[] = "\n=== CUSTOMERS & AGENTS (Live Contextual Match) ===";
        $totalCustomers = AgentCustomer::count();
        $recentCustomers = AgentCustomer::latest()->take(50)->get();
        
        $matchedCustomers = collect();
        if (count($words) > 0) {
            $custQuery = AgentCustomer::query();
            $custQuery->where(function($q) use ($words) {
                foreach($words as $w) {
                    $q->orWhere('name', 'like', "%{$w}%");
                    $q->orWhere('phone_no', 'like', "%{$w}%");
                }
            });
            $matchedCustomers = $custQuery->take(20)->get();
        }
        
        $allCustomers = $recentCustomers->merge($matchedCustomers)->unique('id');
        
        $lines[] = "Total Customers/Agents in DB: $totalCustomers";
        foreach ($allCustomers as $c) {
            $ledgerLink = url("admin/customer_ledgers?customer_slug={$c->id}");
            $lines[] = "Customer: {$c->name} | Phone: {$c->phone_no} | City: {$c->city} | LedgerLink: $ledgerLink";
        }

        // 6. LEADS SUMMARY
        $lines[] = "\n=== LEADS SUMMARY ===";
        try {
            $totalLeads   = Lead::count();
            $pendingLeads = Lead::where('status', '!=', 'Won')->where('status', '!=', 'Lost')->count();
            $wonLeads     = Lead::where('status', 'Won')->count();
            $lostLeads    = Lead::where('status', 'Lost')->count();
            $todayLeads   = Lead::whereDate('created_at', Carbon::today())->count();
            $lines[] = "Total Leads: $totalLeads | Pending: $pendingLeads | Won: $wonLeads | Lost: $lostLeads | New Today: $todayLeads";
        } catch (\Exception $e) {
            $lines[] = "Leads data not available.";
        }

        // 7. BILLING SUMMARY
        $lines[] = "\n=== BILLING SUMMARY (This Month) ===";
        try {
            $monthBills  = Bill::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->latest()->get();
            // try multiple common column names for the total
            $totalBilled = $monthBills->sum(fn($b) =>
                $b->total_amount ?? $b->grand_total ?? $b->net_amount ?? $b->amount ?? 0
            );
            $lines[] = "Bills This Month: " . $monthBills->count() . " | Total Billed: ₹" . number_format($totalBilled, 2);
            foreach ($monthBills->take(10) as $b) {
                $pdfUrl = url("/admin/bills/{$b->id}/pdf");
                $lines[] = "Bill#{$b->bill_no} | Amount: ₹" . ($b->total_amount ?? $b->grand_total ?? 0) . " | Date: {$b->bill_date} | PDFLink: $pdfUrl";
            }
        } catch (\Exception $e) {
            $lines[] = "Billing data not available.";
        }

        // 8. PACKING SLIPS
        $lines[] = "\n=== PACKING SLIPS (Recent) ===";
        try {
            $pending  = PackingSlip::where('status', 'pending')->count();
            $complete = PackingSlip::where('status', 'complete')->count();
            $lines[]  = "Packing Slips — Pending: $pending | Completed: $complete";
        } catch (\Exception $e) {
            $lines[] = "Packing data not available.";
        }

        // 9. TEAM MEMBERS
        $lines[] = "\n=== TEAM MEMBERS ===";
        $team = User::all();
        foreach ($team as $member) {
            $lines[] = "Name: {$member->name} | Role: {$member->role_as} | Phone: {$member->phone}";
        }

        // 10. ON-HOLD JOB CARDS DETAIL
        $holdingJCs = JobCard::where('is_hold', 1)->with('customer_agent')->get();
        if ($holdingJCs->count() > 0) {
            $lines[] = "\n=== JOB CARDS CURRENTLY ON HOLD ===";
            foreach ($holdingJCs as $jc) {
                $customer = optional($jc->customer_agent)->name ?? $jc->name_of_job;
                $viewUrl  = route('job_card.show', $jc->id);
                $processUrl = route('job_card.next_process', $jc->id);
                $lines[] = "JC#{$jc->job_card_no} | ViewLink: {$viewUrl} | ProcessLink: {$processUrl} | Customer: $customer | Hold Reason: {$jc->hold_notes} | Since: " . (optional($jc->held_at)?->format('d-M-Y H:i') ?? 'unknown');
            }
        }

        return implode("\n", $lines);
    }

    // -------------------------------------------------------------------------
    // SYSTEM PROMPT BUILDER
    // -------------------------------------------------------------------------

    private function buildSystemPrompt(User $user, string $userMessage, string $context): string
    {
        return <<<PROMPT
You are "Factory AI" — the intelligent assistant for **Shiva Polyfab** (Carrybag Industries), a polypropylene woven bag manufacturing company in India.

Your capabilities:
- Answer questions in ANY LANGUAGE the user writes in (Hindi, Gujarati, Punjabi, English — respond in the same language)
- Scan the factory database context provided below
- Answer questions about stock, inventory, job cards, customers, leads, billing, packing, team
- Explain industry terms like GSM, BOPP, PP fabric, D-cut, Loop bags, etc.
- Help rephrase customer messages professionally
- Give business advice and insights
- Answer questions about remaining stock, specific job card status, customer details
- Do NOT make up data — only use what is provided in the [LIVE DATABASE CONTEXT]

Industry Knowledge:
- GSM (Grams per Square Meter) = measure of fabric weight/density. Higher GSM = thicker, stronger bag. Common: 70GSM, 80GSM, 90GSM, 100GSM
- BOPP = Biaxially Oriented Polypropylene — laminated film coating on bags for printing/glossy finish
- PP Fabric = Polypropylene woven fabric (the main raw material)
- D-Cut bags = Non-woven bags with a D-shaped handle cut
- Loop bags = Bags with fabric loop handles
- Dana = Raw PP granules used to make fabric
- Cylinder = Printing cylinder used for gravure printing on bags
- Job Card = Production order for a batch of bags (JC-XX-YY format)
- Packing Slip = Dispatch document when bags leave factory
- Common Stock = Ready-made finished bags in standard sizes

Current User: {$user->name} (Role: {$user->role_as})
Current Date/Time: " . now()->format('d M Y, h:i A') . "

[LIVE DATABASE CONTEXT]
$context
[END OF DATABASE CONTEXT]

User's Question: "{$userMessage}"

Instructions:
1. Answer specifically using the database context above.
2. If asking about stock/remaining quantity — calculate and state clearly.
3. If asking about GSM or industry term — explain it clearly.
4. If asking to rephrase something professionally — do it.
5. If message is in Hindi/other language — respond in that language.
6. Use emojis for readability but keep it professional.
7. For data not found in context, say "I don't have that data in the current scan".
8. Format numbers with commas (Indian format).
9. Be concise but complete.
10. VERY IMPORTANT: Whenever you talk about a Job Card, Customer Ledger, or Bill, check the context for "ViewLink", "ProcessLink", "LedgerLink", or "PDFLink". You MUST provide these links as clickable markdown text (e.g. [Process Job Card](/admin/job_card/next_process/123)) so the user can easily navigate!

ACTION CAPABILITY (CRITICAL):
If the user explicitly asks you to ADD, ENTER, or UPDATE data, you MUST output a JSON block containing an ARRAY of actions at the very end of your message.

1. For Stock (e.g., "enter stock in 38/55 fabric - 20"):
```json
[ { "action": "add_stock", "stock_category": "fabric", "item_name": "38/55", "quantity": 20, "in_out": "in" } ]
```

2. FOR ANY OTHER MODULE (Leads, Customers, Job Cards, Bills, Ledgers/Debits/Credits):
You have "God Mode" access to all Eloquent Models in the system. Use the `universal_insert` action.
Available Models: AgentCustomer, Bill, BillItem, CustomerLedger, CylinderJob, JobCard, Lead, LeadAgentCustomer, LeadFollowup, PackingSlip, PaymentMethod, User, etc.

Example: Adding a Debit to Customer Ledger for "Partap Sons":
```json
[
  {
    "action": "universal_insert",
    "model": "CustomerLedger",
    "data": { "dr_cr": "dr", "amount": 20000, "total_amount": 20000, "grand_total_amount": 20000, "remarks": "To Bill Test" },
    "relations": { "customer_id": { "model": "AgentCustomer", "search_column": "party_name", "search_value": "partap sons" } }
  }
]
```

Example 3: Creating a nested Document (Like a Bill with Items) and opening it in PDF:
```json
[
  {
    "action": "universal_insert",
    "model": "Bill",
    "trigger_url": "/admin/bills/ID/pdf",
    "data": { "bill_no": "B-101", "bill_date": "2026-04-06", "total_amount": 6000, "grand_total": 6300 },
    "relations": { "customer_id": { "model": "AgentCustomer", "search_column": "party_name", "search_value": "abaan packaging" } },
    "children": [
      {
         "model": "BillItem",
         "foreign_key": "bill_id",
         "records": [
           { "description": "Test 1", "qty": 20, "rate": 150, "amount": 3000, "gst_percent": 5, "gst_amount": 150, "total_amount": 3150 },
           { "description": "Test 2", "qty": 20, "rate": 150, "amount": 3000, "gst_percent": 5, "gst_amount": 150, "total_amount": 3150 }
         ]
      }
    ]
  }
]
```

If the action requires an ID (like `customer_id` or `job_card_id`), DO NOT GUESS ID NUMBERS. Use the `relations` object instead to instruct the backend to search for the ID automatically. The backend will inject the found ID into your `data` object before saving. Make sure you calculate the totals!

IMAGE GENERATION CAPABILITY:
If the user asks you to "generate an image" or "give me a design idea for a bag", you MUST respond with a markdown image combining their prompt with the pollinations.ai URL. 
Format: `![Design Concept](https://image.pollinations.ai/prompt/detailed_description_of_the_image_with_underscores)`
Example: `![Bag](https://image.pollinations.ai/prompt/polypropylene_woven_bag_mockup_vibrant_colors)`
PROMPT;
    }

    // -------------------------------------------------------------------------
    // GEMINI API CALL — using gemini-2.5-pro etc
    // -------------------------------------------------------------------------

    private function callGemini(string $systemPrompt, string $userMessage, ?string $fileBase64 = null, ?string $mimeType = null, $history = null): string
    {
        if (empty($this->apiKey)) {
            return "⚠️ AI API Key missing. Please add AI_API_KEY to your .env file. Get a free key from: https://aistudio.google.com/app/apikey";
        }

        $contents = [];

        // 1. Initial System Context mapped as the first user message
        $contents[] = [
            'role' => 'user', 
            'parts' => [['text' => $systemPrompt]]
        ];
        $contents[] = [
            'role' => 'model', 
            'parts' => [['text' => 'Acknowledged. I am ready.']]
        ];

        // 2. Hydrate History
        if ($history && count($history) > 0) {
            foreach($history as $msg) {
                if (!empty($msg->user_message)) {
                    $contents[] = ['role' => 'user', 'parts' => [['text' => $msg->user_message]]];
                }
                if (!empty($msg->ai_response)) {
                    $contents[] = ['role' => 'model', 'parts' => [['text' => $msg->ai_response]]];
                }
            }
        }

        // 3. New Active Message
        $activeParts = [];
        if (!empty($userMessage)) $activeParts[] = ['text' => $userMessage];
        
        // Attach Multimodal File Data if provided
        if ($fileBase64 && $mimeType) {
            $activeParts[] = [
                'inlineData' => [
                    'mimeType' => $mimeType,
                    'data'     => $fileBase64
                ]
            ];
        }

        if (count($activeParts) > 0) {
            $contents[] = ['role' => 'user', 'parts' => $activeParts];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature'     => 0.4,
                'topK'            => 40,
                'topP'            => 0.95,
                'maxOutputTokens' => 2048,
            ],
        ];

        $lastError = 'No model responded.';

        foreach ($this->models as $modelName => $url) {
            try {
                $this->lastModelUsed = $modelName; // Track the model we are attempting
                $response = Http::timeout(45)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url . '?key=' . $this->apiKey, $payload);

                if ($response->successful()) {
                    $json = $response->json();
                    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($text) {
                        return $text;
                    }
                } else {
                    $errorText = $response->json()['error']['message'] ?? $response->body();
                    $statusCode = $response->status();

                    if ($statusCode === 403 || $statusCode === 401) {
                        // Critical Authentication / Leaked Key Error - Halt completely
                        return "❌ API Key Error: " . $errorText . "\n\nPlease replace your AI_API_KEY in the .env file.";
                    } elseif ($statusCode === 429) {
                        $lastError = "⏳ Free tier rate limits reached on " . $modelName . ". Auto-shifting...";
                    } elseif (strpos($errorText, 'limit: 0') !== false || strpos($errorText, 'is not found') !== false) {
                        continue;
                    } else {
                        $lastError = $errorText;
                    }
                }
            } catch (\Exception $e) {
                // Ignore silent timeouts inside the loop unless all fail
            }
        }

        return "❌ Google Cloud Error: You have completely exhausted your daily or minute quota across all models. Please check https://ai.dev/rate-limit\n\nLast trace: " . $lastError;
    }

    // -------------------------------------------------------------------------
    // GENERATE PREMIUM DESIGNS (kept for compatibility)
    // -------------------------------------------------------------------------

    public function generatePremiumDesigns(string $requirement): array
    {
        if (empty($this->apiKey)) {
            $description = "AI Design Consultant for Shiva Polyfab: Generating professional woven bag designs based on: '$requirement'.";
        } else {
            $prompt = "You are a lead packaging designer for Shiva Polyfab, a premium woven bag manufacturer. 
            Requirement: '$requirement'
            Create 4 distinct premium bag design concepts. For each, describe:
            1. Theme/Aesthetic (e.g. Minimalist, Industrial, Luxury)
            2. Color palette (mention fabric and print colors)
            3. Detailed visual prompt for an AI image generator.
            Format as a list. Be professional.";

            $description = $this->callGemini($prompt);
        }

        // Generate 4-5 random-ish but relevant image URLs using Pollinations.ai
        $keywords = collect(explode(' ', preg_replace('/[^a-z0-9]/', ' ', strtolower($requirement))))
            ->filter(fn($w) => strlen($w) > 3)
            ->take(5)
            ->implode('_');
        
        if (empty($keywords)) $keywords = "polypropylene_woven_bag_packaging";

        $previews = [
            "https://image.pollinations.ai/prompt/premium_polypropylene_woven_bag_design_{$keywords}_modern_luxury?width=1024&height=1024&nologo=true&seed=1",
            "https://image.pollinations.ai/prompt/industrial_style_woven_sack_packaging_design_{$keywords}?width=1024&height=1024&nologo=true&seed=2",
            "https://image.pollinations.ai/prompt/shiva_polyfab_elegant_bag_design_{$keywords}?width=1024&height=1024&nologo=true&seed=3",
            "https://image.pollinations.ai/prompt/vibrant_colorful_carry_bag_design_{$keywords}_realistic_mockup?width=1024&height=1024&nologo=true&seed=4",
        ];

        return [
            'description'  => $description,
            'preview_urls' => $previews,
        ];
    }
}
