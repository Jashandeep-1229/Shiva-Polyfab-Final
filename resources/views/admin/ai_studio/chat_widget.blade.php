<!-- AI Floating Chat Widget -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<style>
    /* Floating Button */
    #ai-floating-button {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 65px;
        height: 65px;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        cursor: pointer;
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4), 0 4px 10px rgba(0,0,0,0.1);
        z-index: 9999;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
    }
    #ai-floating-button:hover {
        transform: scale(1.1) translateY(-5px);
        box-shadow: 0 15px 30px rgba(79, 70, 229, 0.5), 0 6px 15px rgba(0,0,0,0.15);
    }
    #ai-floating-button.pulse {
        animation: ai-pulse 2s infinite;
    }
    @keyframes ai-pulse {
        0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.7); }
        70% { box-shadow: 0 0 0 15px rgba(79, 70, 229, 0); }
        100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
    }

    /* Main Chat Window */
    #ai-chat-window {
        position: fixed;
        bottom: 110px;
        right: 30px;
        width: 400px;
        height: 600px;
        max-height: calc(100vh - 140px);
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05);
        display: none;
        flex-direction: column;
        z-index: 9999;
        overflow: hidden;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    /* Header */
    #ai-chat-header {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(0,0,0,0.08);
        z-index: 10;
    }
    .ai-header-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .ai-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
    }
    .ai-title {
        margin: 0;
        font-weight: 700;
        color: #1e293b;
        font-size: 16px;
        line-height: 1.2;
    }
    .ai-subtitle {
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .ai-status-dot {
        width: 6px;
        height: 6px;
        background: #10b981;
        border-radius: 50%;
        display: inline-block;
    }
    .ai-header-actions {
        display: flex;
        gap: 8px;
    }
    .ai-action-btn {
        background: #f1f5f9;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: #64748b;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .ai-action-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* Messages Area */
    #ai-chat-messages {
        flex-grow: 1;
        padding: 24px 20px;
        overflow-y: auto;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    
    /* Scrollbar */
    #ai-chat-messages::-webkit-scrollbar {
        width: 6px;
    }
    #ai-chat-messages::-webkit-scrollbar-track {
        background: transparent;
    }
    #ai-chat-messages::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.1);
        border-radius: 10px;
    }

    /* Bubbles */
    .ai-msg {
        max-width: 85%;
        padding: 14px 18px;
        font-size: 14px;
        line-height: 1.5;
        position: relative;
        word-wrap: break-word;
    }
    
    .ai-msg-bot {
        background: white;
        color: #334155;
        align-self: flex-start;
        border-radius: 20px 20px 20px 4px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03), 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.02);
    }
    
    .ai-msg-user {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        align-self: flex-end;
        border-radius: 20px 20px 4px 20px;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
    }

    /* Markdown Styling */
    .markdown-body pre {
        background: #1e293b;
        color: #f8fafc;
        padding: 12px;
        border-radius: 12px;
        overflow-x: auto;
        margin: 10px 0;
        font-size: 13px;
    }
    .markdown-body code {
        background: rgba(0,0,0,0.05);
        padding: 2px 6px;
        border-radius: 4px;
        font-family: monospace;
    }
    .markdown-body pre code {
        background: transparent;
        padding: 0;
    }
    .markdown-body p:last-child {
        margin-bottom: 0;
    }
    .markdown-body ul, .markdown-body ol {
        margin-bottom: 0;
        padding-left: 20px;
    }
    .markdown-body img {
        max-width: 100%;
        border-radius: 12px;
        margin-top: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    /* Meta Info */
    .msg-meta {
        font-size: 10px;
        color: #94a3b8;
        display: block;
        margin-top: 8px;
        text-align: right;
        font-weight: 500;
    }
    .ai-msg-user .msg-meta {
        color: rgba(255,255,255,0.7);
    }

    /* Input Area */
    #ai-chat-input-area {
        padding: 16px 20px;
        background: white;
        border-top: 1px solid rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
    }
    .ai-input-wrapper {
        flex-grow: 1;
        position: relative;
        background: #f1f5f9;
        border-radius: 24px;
        display: flex;
        align-items: center;
        padding: 4px;
        transition: all 0.2s;
    }
    .ai-input-wrapper:focus-within {
        background: #fff;
        box-shadow: 0 0 0 2px #c7d2fe;
    }
    #ai-chat-msg-input {
        border: none;
        background: transparent;
        padding: 10px 16px;
        width: 100%;
        font-size: 14px;
        outline: none;
        color: #334155;
    }
    #ai-chat-msg-input::placeholder {
        color: #94a3b8;
    }
    .ai-upload-btn {
        background: transparent;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .ai-upload-btn:hover {
        background: #e2e8f0;
        color: #4f46e5;
    }
    .ai-send-btn {
        background: #4f46e5;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s, background 0.2s;
    }
    .ai-send-btn:hover {
        background: #4338ca;
        transform: scale(1.05);
    }

    /* Loading indicator */
    .typing-indicator {
        display: flex;
        gap: 4px;
        padding: 4px 8px;
    }
    .typing-dot {
        width: 6px;
        height: 6px;
        background: #94a3b8;
        border-radius: 50%;
        animation: typing 1.4s infinite ease-in-out both;
    }
    .typing-dot:nth-child(1) { animation-delay: -0.32s; }
    .typing-dot:nth-child(2) { animation-delay: -0.16s; }
    @keyframes typing {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }
    #ai-file-preview {
        display: none;
        font-size: 11px;
        color: #4f46e5;
        background: #e0e7ff;
        padding: 4px 8px;
        border-radius: 12px;
        margin-right: 8px;
        white-space: nowrap;
        max-width: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<div id="ai-floating-button" onclick="toggleAiChat()">
    <i class="fa fa-sparkles"></i>
</div>

<div id="ai-chat-window">
    <div id="ai-chat-header">
        <div class="ai-header-info">
            <div class="ai-avatar">🤖</div>
            <div>
                <h6 class="ai-title">Factory AI Assistant</h6>
                <div class="ai-subtitle"><span class="ai-status-dot"></span> Online & Scanning</div>
            </div>
        </div>
        <div class="ai-header-actions">
            <!-- New Chat Button -->
            <button class="ai-action-btn" onclick="startNewChat()" title="New Chat">
                <i class="fa fa-edit"></i>
            </button>
            <button class="ai-action-btn" onclick="toggleAiChat()" title="Close">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>

    <div id="ai-chat-messages">
        <!-- Initial Message -->
        <div class="ai-msg ai-msg-bot animate__animated animate__fadeIn" id="welcome-msg">
            <div class="markdown-body">
                Hello! I am connected to the live database. ✨<br><br>
                Ask me about <b>Stock</b>, <b>Job Cards</b>, <b>Stats</b>, or tell me to <b>Add Stock</b>!
            </div>
            <span class="msg-meta">System</span>
        </div>
    </div>

    <div id="ai-chat-input-area">
        <input type="file" id="ai-attachment" accept="image/*,.pdf,.csv,.xlsx,.xls,.txt" style="display:none;" onchange="handleAiFileSelect(event)">
        <div class="ai-input-wrapper">
            <button class="ai-upload-btn" onclick="document.getElementById('ai-attachment').click()" title="Attach File">
                <i class="fa fa-paperclip"></i>
            </button>
            <span id="ai-file-preview"></span>
            <input type="text" id="ai-chat-msg-input" placeholder="Message AI... " maxlength="500" onkeypress="if(event.key === 'Enter') sendAiMsg()">
            <button class="ai-send-btn" onclick="sendAiMsg()"><i class="fa fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<script>
    let chatHistoryLoaded = false;

    function toggleAiChat() {
        const win = document.getElementById('ai-chat-window');
        const btn = document.getElementById('ai-floating-button');
        
        if (win.style.display === 'none' || win.style.display === '') {
            win.style.display = 'flex';
            btn.style.transform = 'scale(0.8)';
            btn.style.opacity = '0';
            
            // Fetch history on first open
            if(!chatHistoryLoaded) {
                fetchChatHistory();
            } else {
                scrollToBottom();
            }
        } else {
            win.style.display = 'none';
            btn.style.transform = 'scale(1)';
            btn.style.opacity = '1';
        }
    }

    function scrollToBottom() {
        const msgBox = document.getElementById('ai-chat-messages');
        msgBox.scrollTop = msgBox.scrollHeight;
    }

    // --- history handling ---
    function fetchChatHistory() {
        chatHistoryLoaded = true;
        $.ajax({
            url: "{{ route('ai_studio.get_history') }}",
            method: "GET",
            success: function(res) {
                if(res.history && res.history.length > 0) {
                    // We hide the welcome msg since we have history
                    document.getElementById('welcome-msg').style.display = 'none';
                    
                    res.history.forEach(function(chat){
                        appendAiMsg('user', chat.user_message, null, null, false);
                        appendAiMsg('bot', chat.ai_response, chat.model_used, chat.response_time_ms, false);
                    });
                    scrollToBottom();
                }
            }
        });
    }

    function startNewChat() {
        $.ajax({
            url: "{{ route('ai_studio.clear_history') }}",
            method: "POST",
            data: { _token: "{{ csrf_token() }}" },
            success: function() {
                // Clear the DOM
                const msgBox = document.getElementById('ai-chat-messages');
                msgBox.innerHTML = `
                    <div class="ai-msg ai-msg-bot animate__animated animate__fadeIn">
                        <div class="markdown-body">
                            Started a new chat session! ✨ How can I help?
                        </div>
                        <span class="msg-meta">System</span>
                    </div>
                `;
            }
        });
    }

    function handleAiFileSelect(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('ai-file-preview');
        if(file) {
            preview.style.display = 'inline-block';
            preview.innerText = file.name;
        } else {
            preview.style.display = 'none';
            preview.innerText = '';
        }
    }

    function sendAiMsg() {
        const input = document.getElementById('ai-chat-msg-input');
        const fileInput = document.getElementById('ai-attachment');
        const file = fileInput.files[0];
        const msg = input.value.trim();
        
        if(!msg && !file) return;

        let displayMsg = msg;
        if (file) {
            displayMsg = msg + `<br><small>📎 <i>Attached: ${file.name}</i></small>`;
        }
        
        appendAiMsg('user', displayMsg);
        
        // Reset Inputs
        input.value = '';
        fileInput.value = '';
        document.getElementById('ai-file-preview').style.display = 'none';

        // Show thinking bubble
        const thinkingId = 'thinking-' + Date.now();
        const msgBox = document.getElementById('ai-chat-messages');
        msgBox.innerHTML += `
            <div id="${thinkingId}" class="ai-msg ai-msg-bot animate__animated animate__fadeInUp">
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>`;
        scrollToBottom();

        // Build FormData
        let formData = new FormData();
        formData.append('_token', "{{ csrf_token() }}");
        formData.append('message', msg);
        if (file) formData.append('attachment', file);

        $.ajax({
            url: "{{ route('ai_studio.ask_ai') }}",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                document.getElementById(thinkingId).remove();
                if(response.status === 'success') {
                    appendAiMsg('bot', response.response, response.model, response.response_ms);
                } else {
                    appendAiMsg('bot', "❌ Something went wrong.");
                }
            },
            error: function() {
                document.getElementById(thinkingId).remove();
                appendAiMsg('bot', "Connection error. Please try again later.");
            }
        });
    }

    function appendAiMsg(sender, text, model = null, ms = null, animate = true) {
        const msgBox = document.getElementById('ai-chat-messages');
        const div = document.createElement('div');
        div.className = `ai-msg ai-msg-${sender} ${animate ? 'animate__animated animate__fadeInUp' : ''}`;
        
        let content = text;
        if(sender === 'bot') {
            // Use marked.js for bot responses completely
            content = marked.parse(text);
        } else {
            // User message escape and replace newlines
            content = text.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, '<br>');
        }
        
        div.innerHTML = `<div class="${sender === 'bot' ? 'markdown-body' : ''}">${content}</div>`;
        
        if(model && ms) {
            div.innerHTML += `<span class="msg-meta">${model} • ${ms}ms</span>`;
        }

        msgBox.appendChild(div);
        scrollToBottom();
    }
</script>
