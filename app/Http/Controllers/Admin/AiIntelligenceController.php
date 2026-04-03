<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiIntelligenceDesign;
use App\Models\AiChatHistory;
use App\Models\JobCard;
use App\Models\SizeMaster;
use App\Models\ColorMaster;
use App\Models\Bopp;
use App\Models\Fabric;
use App\Models\AgentCustomer;
use App\Models\User;
use App\Services\AiIntelligenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiIntelligenceController extends Controller
{
    protected $aiService;

    public function __construct(AiIntelligenceService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Main AI Chat page
     */
    public function chat(Request $request)
    {
        return view('admin.ai_studio.chat');
    }

    /**
     * POST: user sends a message — AI scans DB and responds
     */
    public function askAi(Request $request)
    {
        $request->validate([
            'message'    => 'nullable|string|max:500',
            'attachment' => 'nullable|file|max:5120' // Max 5MB attachment
        ]);

        $message   = $request->message ?? '';
        $sessionId = $request->session()->get('ai_session_id', Str::uuid());
        $request->session()->put('ai_session_id', $sessionId);

        $fileBase64 = null;
        $mimeType   = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $mimeType = $file->getMimeType();
            $fileBase64 = base64_encode(file_get_contents($file->getRealPath()));
        }

        $startTime = microtime(true);
        $response  = $this->aiService->askAi(auth()->user(), $message, $fileBase64, $mimeType);
        $ms        = (int) round((microtime(true) - $startTime) * 1000);
        $modelUsed = $this->aiService->lastModelUsed;

        // --- ACTION EXECUTION ENGINE ---
        // Look for JSON blocks in the AI's response indicating an action
        if (preg_match('/```json\s*(\[.*?\])\s*```/s', $response, $matches)) {
            try {
                $actionsArray = json_decode($matches[1], true);
                if (is_array($actionsArray)) {
                    $actionReports = [];
                    foreach ($actionsArray as $actionData) {
                        if (isset($actionData['action']) && $actionData['action'] === 'add_stock') {
                            $categoryName = ucfirst($actionData['stock_category'] ?? 'fabric');
                            $modelClass   = "\\App\\Models\\" . $categoryName;
                            
                            if (class_exists($modelClass)) {
                                $item = $modelClass::where('name', 'like', '%' . $actionData['item_name'] . '%')->first();
                                
                                if ($item) {
                                    $categoryLower = strtolower($categoryName);
                                    // Determine Base Average factor
                                    $baseAvg = 0;
                                    if ($categoryLower === 'fabric') { $baseAvg = 80; }
                                    elseif ($categoryLower === 'bopp') { $baseAvg = 255; }
                                    elseif ($categoryLower === 'loop') { $baseAvg = 40; }
                                    elseif ($categoryLower === 'ink') { $baseAvg = 20; }
                                    elseif ($categoryLower === 'dana') { $baseAvg = 25; }

                                    $totalAverage = floatval($actionData['quantity']) * $baseAvg;

                                    \App\Models\ManageStock::create([
                                        'user_id'    => auth()->id(),
                                        'from'       => $actionData['stock_category'],
                                        'from_id'    => $item->id,
                                        'stock_name' => $categoryLower,
                                        'stock_id'   => $item->id,
                                        'date'       => date('Y-m-d'),
                                        'unit'       => null, // Set to null to avoid 'Rolls' displaying
                                        'quantity'   => $actionData['quantity'],
                                        'average'    => $totalAverage, // Calculated based on base multiplier
                                        'in_out'     => $actionData['in_out'] ?? 'in',
                                        'remarks'    => 'Auto-added by AI Agent',
                                        'status'     => 1,
                                    ]);

                                    $actionReports[] = "✅ Successfully added " . $actionData['quantity'] . " to " . $item->name . " (Total Avg: " . number_format($totalAverage, 2) . " kg)!";
                                } else {
                                    $actionReports[] = "⚠️ Could not find exactly '{$actionData['item_name']}' in {$categoryName} table.";
                                }
                            }
                        } elseif (isset($actionData['action']) && $actionData['action'] === 'add_ledger') {
                            $customer = \App\Models\AgentCustomer::where('party_name', 'like', '%' . $actionData['customer_name'] . '%')->first();
                            
                            if ($customer) {
                                \App\Models\CustomerLedger::create([
                                    'user_id'            => auth()->id(),
                                    'customer_id'        => $customer->id,
                                    'amount'             => $actionData['amount'],
                                    'total_amount'       => $actionData['amount'],
                                    'grand_total_amount' => $actionData['amount'],
                                    'dr_cr'              => $actionData['dr_cr'],
                                    'transaction_date'   => $actionData['date'] ?? date('Y-m-d'),
                                    'remarks'            => $actionData['remarks'] ?? 'Auto-added by AI Agent',
                                    'software_remarks'   => 'AI Generated'
                                ]);
                                
                                $type = strtoupper($actionData['dr_cr'] === 'dr' ? 'Debit' : 'Credit');
                                $actionReports[] = "✅ Successfully added " . $type . " of ₹" . number_format($actionData['amount'], 2) . " to " . $customer->party_name . "!";
                            } else {
                                $actionReports[] = "⚠️ Could not find customer '{$actionData['customer_name']}'.";
                            }
                        }
                    }
                    
                    if (count($actionReports) > 0) {
                        // Strip the JSON block from response and add success tags
                        $response = preg_replace('/```json\s*\[.*?\]\s*```/s', '', $response);
                        $response .= "\n\n**Action Results:**\n" . implode("\n", $actionReports);
                    }
                }
            } catch (\Exception $e) {
                // Ignore parsing errors, just show original message
            }
        }

        // Save to history
        AiChatHistory::create([
            'user_id'          => auth()->id(),
            'session_id'       => $sessionId,
            'user_message'     => $message,
            'ai_response'      => $response,
            'model_used'       => $modelUsed,
            'response_time_ms' => $ms,
        ]);

        return response()->json([
            'status'      => 'success',
            'response'    => $response,
            'model'       => $modelUsed,
            'response_ms' => $ms,
        ]);
    }

    /**
     * Get Current Session History
     */
    public function getHistory(Request $request)
    {
        $sessionId = $request->session()->get('ai_session_id');
        if (!$sessionId) {
            return response()->json(['history' => []]);
        }

        $history = AiChatHistory::where('user_id', auth()->id())
            ->where('session_id', $sessionId)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json(['history' => $history]);
    }

    /**
     * Clear / Start New Chat Session
     */
    public function clearHistory(Request $request)
    {
        $request->session()->put('ai_session_id', Str::uuid());
        return response()->json(['status' => 'success']);
    }

    /**
     * POST: Generate AI Premium Design descriptions
     */
    public function generateAIPremuimDesigns(Request $request)
    {
        $requirement = $request->requirement ?? 'standard woven bag';
        $result      = $this->aiService->generatePremiumDesigns($requirement);

        return response()->json([
            'status'  => 'success',
            'designs' => is_array($result) ? ($result['preview_urls'] ?? $result) : [],
            'ai_description' => is_array($result) ? ($result['description'] ?? '') : '',
        ]);
    }

    /**
     * AI Studio index — list of saved AI designs
     */
    public function index(Request $request)
    {
        $designs = AiIntelligenceDesign::with('user', 'job_card')->latest()->paginate(20);
        return view('admin.ai_studio.index', compact('designs'));
    }

    /**
     * Smart Parse — convert natural text to Job Card fields
     */
    public function smartParse(Request $request)
    {
        $text   = strtolower($request->text ?? '');
        $result = [
            'job_type'    => 'new',
            'no_of_pieces'=> null,
            'size_id'     => null,
            'color_id'    => null,
            'bopp_id'     => null,
            'fabric_id'   => null,
            'customer_name'=> '',
            'found_items' => [],
        ];

        // Job type detection
        if (str_contains($text, 'common'))  $result['job_type'] = 'Common';
        elseif (str_contains($text, 'repeat')) $result['job_type'] = 'repeat';

        // Piece count
        preg_match('/(\d{1,3}(?:,\d{3})*|\d+)\s*(?:pcs|pieces|pcs\.|qty)/i', $text, $matches);
        if (isset($matches[1])) {
            $result['no_of_pieces'] = (int)str_replace(',', '', $matches[1]);
        }

        // Match Size
        foreach (SizeMaster::where('status', 1)->get() as $size) {
            if (str_contains($text, strtolower($size->name))) {
                $result['size_id']      = $size->id;
                $result['found_items'][]= "Size: " . $size->name;
                break;
            }
        }

        // Match Color
        foreach (ColorMaster::where('status', 1)->get() as $color) {
            if (str_contains($text, strtolower($color->name))) {
                $result['color_id']     = $color->id;
                $result['found_items'][]= "Color: " . $color->name;
                break;
            }
        }

        // Match BOPP
        foreach (Bopp::where('status', 1)->get() as $bopp) {
            if (str_contains($text, strtolower($bopp->name))) {
                $result['bopp_id']      = $bopp->id;
                $result['found_items'][]= "BOPP: " . $bopp->name;
                break;
            }
        }

        // Match Fabric
        foreach (Fabric::where('status', 1)->get() as $fabric) {
            if (str_contains($text, strtolower($fabric->name))) {
                $result['fabric_id']    = $fabric->id;
                $result['found_items'][]= "Fabric: " . $fabric->name;
                break;
            }
        }

        return response()->json(['status' => 'success', 'data' => $result]);
    }

    /**
     * Store AI Design record
     */
    public function store(Request $request)
    {
        $input              = $request->all();
        $input['user_id']   = auth()->id();
        $mockups            = [];

        if ($request->hasFile('design_mockups')) {
            foreach ($request->file('design_mockups') as $file) {
                $filename  = 'ai_design_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/ai_designs'), $filename);
                $mockups[] = 'uploads/ai_designs/' . $filename;
            }
        }
        $input['design_mockups'] = $mockups;

        AiIntelligenceDesign::create($input);
        return response()->json(['result' => 1, 'message' => 'Design saved successfully!']);
    }

    /**
     * Show design detail
     */
    public function showDesign($id)
    {
        $design = AiIntelligenceDesign::findOrFail($id);
        return view('admin.ai_studio.show', compact('design'));
    }

    /**
     * Approve design
     */
    public function approveDesign(Request $request, $id)
    {
        $design                = AiIntelligenceDesign::findOrFail($id);
        $design->status        = 'Approved';
        $design->approval_date = now();
        $design->save();
        return response()->json(['result' => 1, 'message' => 'Design approved!']);
    }

    /**
     * Convert approved design to Job Card
     */
    public function convertToJobCard($id)
    {
        $design = AiIntelligenceDesign::findOrFail($id);
        if ($design->status != 'Approved') {
            return back()->with('error', 'Design must be approved before conversion.');
        }

        $data    = $design->ai_parsed_data ?? [];
        $jobCard = new JobCard();
        $jobCard->job_type       = $data['job_type'] ?? 'new';
        $jobCard->size_id        = $data['size_id'] ?? null;
        $jobCard->color_id       = $data['color_id'] ?? null;
        $jobCard->bopp_id        = $data['bopp_id'] ?? null;
        $jobCard->fabric_id      = $data['fabric_id'] ?? null;
        $jobCard->no_of_pieces   = $data['no_of_pieces'] ?? 0;
        $jobCard->actual_pieces  = $data['no_of_pieces'] ?? 0;
        $jobCard->name_of_job    = $design->customer_name . " - AI Design #" . $design->id;
        $jobCard->user_id        = auth()->id();
        $jobCard->job_card_date  = now();
        $jobCard->status         = 'Pending';
        $jobCard->job_card_process = ($jobCard->job_type == 'new') ? 'Cylinder Come' : 'Order List';

        // Auto-generate JC number
        $now           = now();
        $startYear     = $now->month >= 4 ? $now->year : $now->year - 1;
        $endYear       = str_pad(($startYear + 1) % 100, 2, '0', STR_PAD_LEFT);
        $startYearShort= str_pad($startYear % 100, 2, '0', STR_PAD_LEFT);
        $fy            = $startYearShort . '-' . $endYear;
        $prefix        = ($jobCard->job_type == 'Common' ? "JC-C-" : "JC-") . $fy . '-';
        $latest        = JobCard::withTrashed()->where('job_card_no', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        $nextNo        = ($latest) ? intval(substr($latest->job_card_no, strlen($prefix))) + 1 : 1;
        $jobCard->job_card_no = $prefix . str_pad($nextNo, 2, '0', STR_PAD_LEFT);

        if (isset($design->design_mockups[0])) {
            $jobCard->file_upload = basename($design->design_mockups[0]);
            $source = public_path($design->design_mockups[0]);
            $dest   = public_path('uploads/job_card/' . $jobCard->file_upload);
            if (file_exists($source)) copy($source, $dest);
        }

        $jobCard->save();
        $design->status      = 'Converted';
        $design->job_card_id = $jobCard->id;
        $design->save();

        return redirect()->route('job_card.index')->with('success', 'AI Design converted to Job Card #' . $jobCard->job_card_no);
    }
}
