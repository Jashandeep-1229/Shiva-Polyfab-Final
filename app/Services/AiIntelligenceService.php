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

    // Models: Gemini 2.5 Pro first (most advanced), then fallbacks
    protected $models = [
        'gemini-2.5-pro'       => 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-pro:generateContent',
        'gemini-2.5-flash'     => 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent',
        'gemini-2.0-flash'     => 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent',
        'gemini-2.0-flash-lite'=> 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash-lite:generateContent',
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
    public function askAi(User $user, string $message, ?string $fileBase64 = null, ?string $mimeType = null): string
    {
        // Hard limit: prevent absurdly long inputs
        $message = mb_substr(trim($message), 0, 500);
        if (empty($message) && empty($fileBase64)) return 'Please type a message or upload a file.';

        $context = $this->buildFullDatabaseContext($user);
        $prompt  = $this->buildSystemPrompt($user, $message, $context);
        return $this->callGemini($prompt, $fileBase64, $mimeType);
    }

    // -------------------------------------------------------------------------
    // DATABASE CONTEXT BUILDER — scans every important table
    // -------------------------------------------------------------------------

    private function buildFullDatabaseContext(User $user): string
    {
        $lines = [];

        // 1. FACTORY OVERVIEW
        $totalJCs      = JobCard::count();
        $activeJCs     = JobCard::where('status', 'In Production')->count();
        $pendingJCs    = JobCard::where('status', 'Pending')->count();
        $completedJCs  = JobCard::where('status', 'Completed')->count();
        $holdJCs       = JobCard::where('is_hold', 1)->count();
        $todayJCs      = JobCard::whereDate('created_at', Carbon::today())->count();

        $lines[] = "=== FACTORY OVERVIEW ===";
        $lines[] = "Total Job Cards: $totalJCs | Active (In Production): $activeJCs | Pending: $pendingJCs | Completed: $completedJCs | On Hold: $holdJCs | Created Today: $todayJCs";

        // 2. RECENT JOB CARDS (last 20)
        $lines[] = "\n=== RECENT JOB CARDS (Last 20) ===";
        $recentJCs = JobCard::with(['customer_agent', 'fabric', 'bopp', 'size', 'color'])
            ->latest()->take(20)->get();
        foreach ($recentJCs as $jc) {
            $customer = optional($jc->customer_agent)->party_name ?? $jc->name_of_job ?? 'N/A';
            $fabric   = optional($jc->fabric)->name ?? 'N/A';
            $lines[]  = "JC#{$jc->job_card_no} | Customer: $customer | Process: {$jc->job_card_process} | Status: {$jc->status} | Pieces: " . number_format($jc->no_of_pieces) . " | Fabric: $fabric | Hold: " . ($jc->is_hold ? 'YES - ' . $jc->hold_notes : 'No') . " | Date: " . Carbon::parse($jc->job_card_date)->format('d-M-Y');
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
        $lines[] = "\n=== CUSTOMERS & AGENTS ===";
        $totalCustomers = AgentCustomer::count();
        $recentCustomers = AgentCustomer::latest()->take(10)->get();
        $lines[] = "Total Customers/Agents: $totalCustomers";
        foreach ($recentCustomers as $c) {
            $lines[] = "Customer: {$c->party_name} | Phone: {$c->phone} | City: {$c->city}";
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
            $monthBills  = Bill::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->get();
            // try multiple common column names for the total
            $totalBilled = $monthBills->sum(fn($b) =>
                $b->total_amount ?? $b->grand_total ?? $b->net_amount ?? $b->amount ?? 0
            );
            $lines[] = "Bills This Month: " . $monthBills->count() . " | Total Billed: ₹" . number_format($totalBilled, 2);
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
                $customer = optional($jc->customer_agent)->party_name ?? $jc->name_of_job;
                $lines[] = "JC#{$jc->job_card_no} | Customer: $customer | Hold Reason: {$jc->hold_notes} | Since: " . (optional($jc->held_at)?->format('d-M-Y H:i') ?? 'unknown');
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
9. Be concise but complete — don't pad with unnecessary text.

ACTION CAPABILITY (CRITICAL):
If the user explicitly asks you to ADD, ENTER, or UPDATE data, you MUST output a JSON block containing an ARRAY of actions at the very end of your message.
For Stock (e.g., "enter stock in 38/55 fabric - 20"):
```json
[ { "action": "add_stock", "stock_category": "fabric", "item_name": "38/55", "quantity": 20, "in_out": "in" } ]
```
For Customer Ledger / Debit / Credit (e.g., "add debit 20,000 to partap sons"):
```json
[ { "action": "add_ledger", "customer_name": "partap sons", "amount": 20000, "dr_cr": "dr", "remarks": "To Bill TEst", "date": "2026-04-06" } ]
```
(Set dr_cr to "dr" for debit, "cr" for credit. If date is not given, use today's date).
Only output the JSON block if the user expressly requested an update or insert.

IMAGE GENERATION CAPABILITY:
If the user asks you to "generate an image" or "give me a design idea for a bag", you MUST respond with a markdown image combining their prompt with the pollinations.ai URL. 
Format: `![Design Concept](https://image.pollinations.ai/prompt/detailed_description_of_the_image_with_underscores)`
Example: `![Bag](https://image.pollinations.ai/prompt/polypropylene_woven_bag_mockup_vibrant_colors)`
PROMPT;
    }

    // -------------------------------------------------------------------------
    // GEMINI API CALL — using gemini-2.5-pro etc
    // -------------------------------------------------------------------------

    private function callGemini(string $prompt, ?string $fileBase64 = null, ?string $mimeType = null): string
    {
        if (empty($this->apiKey)) {
            return "⚠️ AI API Key missing. Please add AI_API_KEY to your .env file. Get a free key from: https://aistudio.google.com/app/apikey";
        }

        $parts = [
            ['text' => $prompt]
        ];

        // Attach Multimodal File Data if provided
        if ($fileBase64 && $mimeType) {
            $parts[] = [
                'inlineData' => [
                    'mimeType' => $mimeType,
                    'data'     => $fileBase64
                ]
            ];
        }

        $payload = [
            'contents' => [
                ['parts' => $parts]
            ],
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
                $response = Http::timeout(45)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url . '?key=' . $this->apiKey, $payload);

                if ($response->successful()) {
                    $json = $response->json();
                    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($text) {
                        $this->lastModelUsed = $modelName;
                        return $text;
                    }
                }

                $lastError = $response->json()['error']['message']
                    ?? 'HTTP ' . $response->status();

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        return "❌ Gemini Error: " . $lastError . "\n\nAll models failed. Please check your AI_API_KEY quota at https://ai.dev/rate-limit";
    }

    // -------------------------------------------------------------------------
    // GENERATE PREMIUM DESIGNS (kept for compatibility)
    // -------------------------------------------------------------------------

    public function generatePremiumDesigns(string $requirement): array
    {
        if (empty($this->apiKey)) {
            return [
                'https://placehold.co/600x400/6366f1/ffffff?text=Premium+D-Cut+Bag',
                'https://placehold.co/600x400/10b981/ffffff?text=Eco+Woven+Bag',
                'https://placehold.co/600x400/f59e0b/ffffff?text=Loop+Handle+Bag',
            ];
        }

        $prompt = "You are a premium packaging design consultant for Shiva Polyfab, a woven bag manufacturer. 
        Customer requirement: '$requirement'
        Describe 3 premium bag design concepts with: material, GSM, size recommendation, print style, and finish type.
        Format as numbered list. Be specific and professional.";

        $description = $this->callGemini($prompt);

        return [
            'description'  => $description,
            'preview_urls' => [
                'https://placehold.co/600x400/6366f1/ffffff?text=Design+Concept+1',
                'https://placehold.co/600x400/10b981/ffffff?text=Design+Concept+2',
                'https://placehold.co/600x400/f59e0b/000000?text=Design+Concept+3',
            ],
        ];
    }
}
