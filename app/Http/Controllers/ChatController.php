<?php

namespace App\Http\Controllers;

use App\Models\AgentCustomer;
use App\Models\ChatMessage;
use App\Services\WhatsAppService;
use App\Events\ChatMessageReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChatController extends Controller
{
    protected $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function index()
    {
        if (auth()->user()->role_as != 'Admin') {
            abort(403, 'Unauthorized access to WhatsApp Chat.');
        }
        return view('admin.chat.index');
    }

    public function getConversations(Request $request)
    {
        $query = AgentCustomer::whereNotNull('phone_no')
            ->whereNotNull('last_message_at');

        if ($request->tab == 'unseen') {
            $query->where('unseen_count', '>', 0);
        }

        $conversations = $query->orderByDesc('last_message_at')
            ->get(['id', 'name', 'phone_no', 'last_message_at', 'unseen_count']);

        // Get last message for each conversation
        foreach ($conversations as $conv) {
            $lastMsg = ChatMessage::where('phone_no', $conv->phone_no)->latest()->first();
            $conv->last_message = $lastMsg ? $lastMsg->message : '';
            $conv->last_message_type = $lastMsg ? $lastMsg->message_type : 'text';
            $conv->time_ago = $conv->last_message_at ? Carbon::parse($conv->last_message_at)->diffForHumans() : '';
        }

        return response()->json($conversations);
    }

    public function getMessages($customerId)
    {
        $customer = AgentCustomer::findOrFail($customerId);
        
        // Reset unseen count
        $customer->unseen_count = 0;
        $customer->save();

        $messages = ChatMessage::where('customer_id', $customerId)
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'customer' => $customer,
            'messages' => $messages
        ]);
    }

    public function startNewChat(Request $request)
    {
        $phone = $request->phone;
        // Clean phone: Get last 10 digits to match database reliably
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $searchPhone = strlen($cleanPhone) > 10 ? substr($cleanPhone, -10) : $cleanPhone;
        
        // Find existing or create dummy
        $customer = AgentCustomer::where('phone_no', 'LIKE', "%$searchPhone%")->first();
        
        if (!$customer) {
            $customer = new AgentCustomer();
            $customer->name = "New: " . $phone;
            $customer->phone_no = $phone;
            $customer->last_message_at = now();
            $customer->save();
        } else {
            if (!$customer->last_message_at) {
                $customer->last_message_at = now();
                $customer->save();
            }
        }
        
        return response()->json($customer);
    }

    public function markSeen($customerId)
    {
        $customer = AgentCustomer::findOrFail($customerId);
        $customer->unseen_count = 0;
        $customer->save();
        return response()->json(['status' => 'success']);
    }

    public function deleteConversation($id)
    {
        $customer = AgentCustomer::findOrFail($id);
        ChatMessage::where('customer_id', $id)->delete();
        $customer->delete();
        return response()->json(['status' => 'success']);
    }

    public function deleteMessage($id)
    {
        $message = ChatMessage::findOrFail($id);
        $message->delete();
        return response()->json(['status' => 'success']);
    }

    public function clearChat($customerId)
    {
        ChatMessage::where('customer_id', $customerId)->delete();
        return response()->json(['status' => 'success']);
    }

    public function retryMessage($id)
    {
        $message = ChatMessage::findOrFail($id);
        $customer = AgentCustomer::find($message->customer_id);
        $to = preg_replace('/[^0-9]/', '', $customer->phone_no);
        if (strlen($to) === 10) { $to = '91' . $to; }

        try {
            if ($message->message_type == 'text') {
                $this->whatsapp->sendTextMessage($to, $message->message);
                $message->status = 'sent';
            } elseif ($message->media_id) {
                $this->whatsapp->sendMediaMessage($to, $message->media_id, $message->message_type);
                $message->status = 'sent';
            }
            $message->save();
            broadcast(new ChatMessageReceived($message));
            return response()->json(['status' => 'success', 'message' => $message]);
        } catch (\Exception $e) {
            \Log::error("Chat Retry Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function sendWelcomeTemplate(Request $request)
    {
        $customer = AgentCustomer::findOrFail($request->customer_id);
        $to = preg_replace('/[^0-9]/', '', $customer->phone_no);
        if (strlen($to) === 10) { $to = '91' . $to; }

        try {
            $this->whatsapp->sendWelcomeGreeting($to);
            
            $message = new ChatMessage();
            $message->customer_id = $customer->id;
            $message->phone_no = $customer->phone_no;
            $message->direction = 'outbound';
            $message->message_type = 'template';
            $message->message = "Hello 👋 Greetings from *Shiva Polyfab*.\n\nWe’d like to connect with you regarding your requirements.\nPlease let us know how we can assist you.";
            $message->status = 'sent';
            $message->save();

            broadcast(new ChatMessageReceived($message));
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            \Log::error("Chat Template Error: " . $errorMsg);
            
            // Try to extract readable error if it's JSON
            $decoded = json_decode($errorMsg, true);
            $reason = ($decoded && isset($decoded['error']['message'])) ? $decoded['error']['message'] : $errorMsg;
            
            return response()->json(['status' => 'error', 'message' => 'WhatsApp Error: ' . $reason], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:agent_customers,id',
            'message' => 'required_without:media|nullable|string',
            'media' => 'nullable|file|max:10240', // 10MB limit
        ]);

        $customer = AgentCustomer::find($request->customer_id);
        $to = preg_replace('/[^0-9]/', '', $customer->phone_no);
        if (strlen($to) === 10) { $to = '91' . $to; }

        $message = new ChatMessage();
        $message->customer_id = $customer->id;
        $message->phone_no = $customer->phone_no;
        $message->direction = 'outbound';
        $message->status = 'pending';

        // Window check disabled per user request (User wants to try sending regardless)
        /*
        $lastInbound = ChatMessage::where('customer_id', $customer->id)
            ->where('direction', 'inbound')
            ->latest()
            ->first();
            
        if ($lastInbound) {
            $hoursSinceLast = now()->diffInHours($lastInbound->created_at);
            if ($hoursSinceLast > 24) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'The 24-hour window has expired. You must send a template message to resume the conversation.'
                ], 403);
            }
        } elseif (ChatMessage::where('customer_id', $customer->id)->count() > 0) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Customer has not replied yet. You can only send another template message.'
            ], 403);
        }
        */

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filename = time() . '_' . $file->getClientOriginalName();
            $mime = $file->getMimeType();
            $fileType = str_contains($mime, 'video') ? 'video' : (str_contains($mime, 'image') ? 'image' : 'document');
            
            // Move file to public directory
            $file->move(public_path('chat_media'), $filename);
            $fullPath = public_path('chat_media/' . $filename);
            
            $message->media_path = 'chat_media/' . $filename;
            $message->message_type = $fileType;
            $message->message = $request->input('message') ?: 'Sent a ' . $fileType;
            
            // Upload to Meta using the new public path
            $media_id = $this->whatsapp->uploadMedia($fullPath);
            $message->media_id = $media_id;
        } else {
            $message->message = $request->message;
            $message->message_type = 'text';
        }

        $message->save();

        $customer->last_message_at = now();
        $customer->unseen_count = 0;
        $customer->save();

        try {
            if ($message->message_type == 'text') {
                $this->whatsapp->sendTextMessage($to, $message->message);
                $message->status = 'sent';
            } elseif ($message->media_id) {
                $this->whatsapp->sendMediaMessage($to, $message->media_id, $message->message_type);
                $message->status = 'sent';
            }
        } catch (\Exception $e) {
            $message->status = 'failed';
            Log::error("Chat Send Error: " . $e->getMessage());
        }

        $message->save();
        broadcast(new ChatMessageReceived($message));

        return response()->json($message);
    }

    /**
     * Webhook for WhatsApp Cloud API
     */
    public function webhook(Request $request)
    {
        if ($request->isMethod('get')) {
            $verifyToken = env('WHATSAPP_VERIFY_TOKEN', 'shiva_polyfab_chat');
            if ($request->input('hub_verify_token') === $verifyToken) {
                return response($request->input('hub_challenge'), 200);
            }
            return response('Forbidden', 403);
        }

        $entry = $request->input('entry.0.changes.0.value');
        if (isset($entry['messages'][0])) {
            $msgData = $entry['messages'][0];
            $waId = $msgData['from'];
            $phone = substr($waId, 0, 2) == '91' ? substr($waId, 2) : $waId;
            
            $customer = AgentCustomer::where('phone_no', 'LIKE', "%$phone%")->first();

            $message = new ChatMessage();
            $message->customer_id = $customer ? $customer->id : null;
            $message->phone_no = $customer ? $customer->phone_no : $waId;
            $message->direction = 'inbound';
            $message->wa_message_id = $msgData['id'];
            $message->status = 'received';

            if ($msgData['type'] == 'text') {
                $message->message = $msgData['text']['body'];
                $message->message_type = 'text';
            } elseif (in_array($msgData['type'], ['image', 'video', 'document'])) {
                $type = $msgData['type'];
                $mediaData = $msgData[$type];
                $message->message_type = $type;
                $message->media_id = $mediaData['id'];
                $message->message = isset($mediaData['caption']) ? $mediaData['caption'] : "Received a $type";
                
                // Download Media
                $mime = isset($mediaData['mime_type']) ? $mediaData['mime_type'] : '';
                $ext = 'jpg';
                if (str_contains($mime, 'video')) $ext = 'mp4';
                elseif (str_contains($mime, 'pdf') || str_contains($mime, 'document')) $ext = 'pdf';
                elseif (str_contains($mime, 'png')) $ext = 'png';
                elseif (str_contains($mime, 'gif')) $ext = 'gif';
                elseif (str_contains($mime, 'webp')) $ext = 'webp';
                $filename = "media_{$message->media_id}.{$ext}";
                $savePath = public_path("chat_media/{$filename}");
                
                // High priority fix: Use URL from webhook if available
                $directUrl = isset($mediaData['url']) ? $mediaData['url'] : null;
                
                if ($this->whatsapp->downloadMediaToFile($message->media_id, $savePath, $directUrl)) {
                    $message->media_path = "chat_media/{$filename}";
                }
            }

            $message->save();

            if ($customer) {
                $customer->last_message_at = now();
                $customer->unseen_count += 1;
                $customer->save();
            }

            broadcast(new ChatMessageReceived($message));
        }

        return response('OK', 200);
    }
}
