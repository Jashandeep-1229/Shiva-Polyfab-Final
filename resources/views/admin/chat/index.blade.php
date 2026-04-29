@extends('layouts.admin.app')

@section('title', 'WhatsApp Chat')

@section('css')
<style>
    .chat-container {
        height: calc(100vh - 180px);
        display: flex;
        background: #fff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    /* Sidebar Styles */
    .chat-sidebar {
        width: 350px;
        border-right: 1px solid #f0f0f0;
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    .sidebar-header {
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #f0f0f0;
    }

    .sidebar-header h5 {
        margin: 0;
        font-weight: 700;
        color: #25D366;
    }

    .chat-search {
        padding: 10px 20px;
    }

    .chat-search input {
        width: 100%;
        padding: 10px 15px;
        border-radius: 20px;
        border: 1px solid #eee;
        background: #fdfdfd;
        outline: none;
    }

    .chat-tabs {
        display: flex;
        padding: 0 10px;
        border-bottom: 1px solid #f0f0f0;
    }

    .chat-tab {
        flex: 1;
        padding: 12px;
        text-align: center;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        color: #666;
        transition: all 0.3s;
        border-bottom: 3px solid transparent;
    }

    .chat-tab.active {
        color: #25D366;
        border-bottom-color: #25D366;
    }

    .conversation-list {
        flex: 1;
        overflow-y: auto;
    }

    .conversation-item {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        cursor: pointer;
        transition: background 0.2s;
        border-bottom: 1px solid #f9f9f9;
        position: relative;
    }

    .conversation-item:hover .conversation-options { opacity: 1 !important; }
    .message-bubble:hover .message-options { opacity: 1 !important; }
    .dropdown-item { cursor: pointer; }
    .conversation-item:hover {
        background: #f5f5f5;
    }

    .conversation-item.active {
        background: #e9edef;
    }

    .avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #ddd;
        margin-right: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #fff;
        font-size: 18px;
    }

    .conv-info {
        flex: 1;
        min-width: 0;
    }

    .conv-info h6 {
        margin: 0 0 5px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .conv-info p {
        margin: 0;
        font-size: 13px;
        color: #888;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .conv-meta {
        text-align: right;
        font-size: 11px;
        color: #999;
    }

    .unseen-badge {
        background: #25D366;
        color: #fff;
        border-radius: 50%;
        min-width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
        margin-top: 5px;
    }

    /* Main Chat Styles */
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #e5ddd5 url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
        background-blend-mode: overlay;
    }

    .chat-header {
        padding: 12px 25px;
        background: #fff;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #eee;
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px 40px;
        display: flex;
        flex-direction: column-reverse; /* Browser handles bottom-pinning natively */
        background: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
        background-repeat: repeat;
        background-color: #e5ddd5;
    }

    .message-bubble {
        max-width: 65%;
        padding: 8px 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14.5px;
        position: relative;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        line-height: 1.5;
    }

    .message-inbound {
        align-self: flex-start;
        background: #fff;
        border-top-left-radius: 0;
    }

    .message-outbound {
        align-self: flex-end;
        background: #dcf8c6;
        border-top-right-radius: 0;
    }

    .message-time {
        font-size: 10px;
        color: #999;
        text-align: right;
        margin-top: 4px;
    }

    .message-status {
        font-size: 12px;
        margin-left: 5px;
    }

    .chat-footer {
        padding: 15px 25px;
        background: #f0f2f5;
        display: flex;
        align-items: center;
    }

    .chat-input-wrapper {
        flex: 1;
        background: #fff;
        border-radius: 25px;
        padding: 10px 20px;
        margin: 0 15px;
        display: flex;
        align-items: center;
    }

    .chat-input-wrapper input {
        border: none;
        outline: none;
        width: 100%;
        background: transparent;
    }

    .attach-btn, .send-btn {
        color: #54656f;
        cursor: pointer;
        font-size: 20px;
        transition: color 0.3s;
    }

    .send-btn {
        color: #25D366;
    }

    .send-btn:hover {
        color: #128c7e;
    }

    /* Media Styles */
    .message-media {
        max-width: 300px;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 8px;
        cursor: pointer;
        position: relative;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .message-media img, .message-media video {
        width: 100%;
        max-height: 350px;
        object-fit: cover;
        display: block;
    }
    .message-media:hover {
        filter: brightness(0.9);
    }

    .no-chat-selected {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        color: #888;
    }

    .no-chat-selected i {
        font-size: 80px;
        color: #eee;
        margin-bottom: 20px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .chat-sidebar {
            width: 100%;
            display: block;
        }
        .chat-main {
            display: none;
        }
    .conversation-item:hover .conversation-options { opacity: 1 !important; }
    .conversation-options { opacity: 0.3; transition: 0.2s; }
    .message-bubble:hover .message-options { opacity: 1 !important; }
    .dropdown-item { cursor: pointer; }
    .dropdown-menu { z-index: 1060 !important; }
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">WhatsApp Chat</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="chat-sidebar" id="chatSidebar">
            <div class="sidebar-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5><i class="fa fa-whatsapp"></i> Chat</h5>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-info me-1" onclick="playTing()" title="Enable/Test Sound">
                            <i class="fa fa-volume-up"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success me-1" onclick="showNewChatModal()" title="New Chat">
                            <i class="fa fa-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="loadConversations()">
                            <i class="fa fa-refresh"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="chat-tabs">
                <div class="chat-tab active" data-tab="all" onclick="changeTab('all')">All Messages</div>
                <div class="chat-tab" data-tab="unseen" onclick="changeTab('unseen')">Unseen <span id="unseenCountSidebar" class="badge bg-danger ms-1 d-none">0</span></div>
            </div>

            <div class="chat-search">
                <input type="text" id="convSearch" placeholder="Search customer..." onkeyup="filterConversations()">
            </div>

            <div class="conversation-list" id="conversationList">
                <div class="text-center p-5">
                    <div class="spinner-border text-success" role="status"></div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main" id="chatMain">
            <div class="no-chat-selected" id="noChatSelected">
                <i class="fa fa-whatsapp"></i>
                <h4>WhatsApp Real-time Chat</h4>
                <p>Select a customer from the left to start chatting</p>
            </div>

            <div class="d-none flex-column h-100" id="chatActiveArea">
                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="avatar" id="activeAvatar">U</div>
                    <div class="active-info">
                        <h6 class="mb-0" id="activeName">Customer Name</h6>
                        <small class="text-success" id="activeStatus">Online</small>
                    </div>
                    <div class="ms-auto pe-3 dropdown">
                        <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v" style="font-size: 20px;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmClearChat()">
                                    <i class="fa fa-eraser me-2"></i> Clear Messages
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmDeleteConversation()">
                                    <i class="fa fa-trash-o me-2"></i> Delete Conversation
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Messages -->
                <div class="chat-messages" id="chatMessages">
                    <!-- Messages will be loaded here via column-reverse -->
                </div>

                <!-- Welcome Template Button (only for new chats) -->
                <div id="welcomeTemplateArea" class="d-none justify-content-center p-3">
                    <div class="text-center bg-light p-4 rounded shadow-sm" style="max-width: 400px;">
                        <p class="text-muted small mb-3">Starting a new conversation? WhatsApp requires a Template message first.</p>
                        <button class="btn btn-success" onclick="sendWelcomeTemplate()">
                            <i class="fa fa-send"></i> Send Welcome Greeting
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="chat-footer">
                    <div class="attach-btn" onclick="document.getElementById('mediaInput').click()">
                        <i class="fa fa-paperclip"></i>
                        <input type="file" id="mediaInput" class="d-none" onchange="handleMediaSelect(this)">
                    </div>
                    <div class="chat-input-wrapper">
                        <input type="text" id="chatInput" placeholder="Type a message" onkeypress="handleKeyPress(event)">
                    </div>
                    <div class="send-btn" onclick="sendMessage()">
                        <i class="fa fa-send"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Chat Modal -->
<div class="modal fade" id="newChatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Start New Chat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Phone Number (with country code)</label>
                    <input type="text" id="newChatPhone" class="form-control" placeholder="e.g. 919876543210">
                </div>
                <div id="newChatResult" class="text-muted small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnSubmitNewChat" class="btn btn-success">Start Chat</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fslightbox/3.4.1/index.min.js"></script>
<script>
    let currentCustomerId = null;
    let currentTab = 'all';
    let conversations = [];

    function getRandomColor(str) {
        if (!str) return '#10b981';
        const colors = ['#007bff', '#6610f2', '#6f42c1', '#e83e8c', '#dc3545', '#fd7e14', '#ffc107', '#28a745', '#20c997', '#17a2b8'];
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        return colors[Math.abs(hash) % colors.length];
    }

    // Initialize Pusher
    Pusher.logToConsole = false;
    const pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
        cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
        forceTLS: true
    });

    const channel = pusher.subscribe('chat');
    channel.bind('message.received', function(data) {
        if (data.message.customer_id == currentCustomerId) {
            loadMessages(currentCustomerId);
            fetch(`{{ url('admin/chat/seen') }}/${currentCustomerId}`);
        }
        loadConversations();
        
        // Play sound for all incoming messages
        if (data.message.direction === 'inbound') {
            playTing();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        loadConversations();
    });

    function loadConversations() {
        fetch(`{{ route('chat.conversations') }}?tab=${currentTab}`)
            .then(res => res.json())
            .then(data => {
                conversations = data;
                renderConversations();
                updateUnseenTotal();
            });
    }

    function renderConversations() {
        const list = document.getElementById('conversationList');
        if (conversations.length === 0) {
            list.innerHTML = '<div class="text-center p-5 text-muted">No conversations found</div>';
            return;
        }

        list.innerHTML = conversations.map(c => {
            const safeName = c.name ? c.name.replace(/'/g, "\\'") : 'Unknown';
            const isActive = c.id == currentCustomerId;
            return `
                <div class="conversation-item ${isActive ? 'active' : ''} position-relative pr-4" onclick="selectConversation(${c.id}, '${safeName}')">
                    <div class="avatar" style="background: ${c.avatar_color || getRandomColor(c.name)}">
                        ${(c.name || 'New').charAt(0).toUpperCase()}
                    </div>
                    <div class="conv-info">
                        <div class="d-flex justify-content-between">
                            <h6>${c.name || 'New: ' + c.phone_no}</h6>
                            <span class="conv-meta">${c.time_ago || ''}</span>
                        </div>
                        <p class="mb-0">${c.last_message || 'No messages yet'}</p>
                    </div>
                    ${c.unseen_count > 0 ? `<div class="unseen-badge animate__animated animate__bounceIn">${c.unseen_count}</div>` : ''}
                    
                    <div class="conversation-options position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); transition: 0.2s; z-index: 10;">
                        <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown" aria-expanded="false" onclick="event.stopPropagation();">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmClearChat(${c.id})">
                                    <i class="fa fa-eraser me-2"></i> Clear Messages
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmDeleteConversation(${c.id})">
                                    <i class="fa fa-trash-o me-2"></i> Delete Conversation
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            `;
        }).join('');
    }

    function changeTab(tab) {
        currentTab = tab;
        document.querySelectorAll('.chat-tab').forEach(el => el.classList.remove('active'));
        document.querySelector(`.chat-tab[data-tab="${tab}"]`).classList.add('active');
        loadConversations();
    }

    function selectConversation(id, name) {
        currentCustomerId = id;
        
        document.getElementById('noChatSelected').classList.add('d-none');
        document.getElementById('chatActiveArea').classList.remove('d-none');
        document.getElementById('chatActiveArea').classList.add('d-flex');
        
        const nameEl = document.getElementById('activeName');
        const avatarEl = document.getElementById('activeAvatar');
        
        if (nameEl) nameEl.innerText = name;
        if (avatarEl) {
            avatarEl.innerText = name ? name.charAt(0) : '?';
            avatarEl.style.background = getRandomColor(name);
        }
        
        renderConversations();
        loadMessages(id);
        
        setTimeout(() => {
            const input = document.getElementById('chatInput');
            if (input) input.focus();
        }, 100);
    }

    function loadMessages(customerId) {
        if (!customerId) return;
        
        fetch(`{{ url('admin/chat/messages') }}/${customerId}`)
            .then(res => res.json())
            .then(data => {
                const chatMessages = document.getElementById('chatMessages');
                chatMessages.innerHTML = data.messages.map(m => renderMessage(m)).join('');
                if (typeof refreshFsLightbox === 'function') refreshFsLightbox();
                
                const welcomeArea = document.getElementById('welcomeTemplateArea');
                const lastInbound = data.messages.find(m => m.direction === 'inbound');
                const now = new Date();
                const diffHours = lastInbound ? (now - new Date(lastInbound.created_at)) / (1000 * 60 * 60) : 999;
                
                const isWindowClosed = (data.messages.length === 0 || diffHours > 24);
                const isLastMessageTemplate = data.messages.length > 0 && data.messages[0].message_type === 'template';
                
                // Keep input ALWAYS enabled per user request (User wants to "start chat" without waiting)
                const chatInput = document.getElementById('chatInput');
                if (chatInput) chatInput.disabled = false;
                const chatFooter = document.querySelector('.chat-footer');
                if (chatFooter) {
                    chatFooter.style.opacity = '1';
                    chatFooter.style.pointerEvents = 'auto';
                }

                if (isWindowClosed && !isLastMessageTemplate && welcomeArea) {
                    welcomeArea.classList.remove('d-none');
                    welcomeArea.classList.add('d-flex');
                    
                    if (diffHours > 24 && data.messages.length > 0) {
                        welcomeArea.querySelector('p').innerText = 'The 24-hour window has expired. You must send a template to resume chatting.';
                        welcomeArea.querySelector('button').innerHTML = '<i class="fa fa-send"></i> Send Welcome Greeting';
                    } else {
                        welcomeArea.querySelector('p').innerText = 'Starting a new conversation? WhatsApp requires a Template message first.';
                        welcomeArea.querySelector('button').innerHTML = '<i class="fa fa-send"></i> Send Welcome Greeting';
                    }
                } else if (welcomeArea) {
                    welcomeArea.classList.add('d-none');
                    welcomeArea.classList.remove('d-flex');
                }
                
                if (typeof refreshFsLightbox === 'function') refreshFsLightbox();
                
                const conv = conversations.find(c => c.id == customerId);
                if (conv) conv.unseen_count = 0;
                renderConversations();
                updateUnseenTotal();
            });
    }

    function renderMessage(m) {
        const isOutbound = m.direction === 'outbound';
        const time = new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        let content = m.message || '';
        
        // Robust media path handling to fix "localhost" issues or relative paths
        let mediaUrl = m.media_path;
        if (mediaUrl && mediaUrl !== 'null') {
            try {
                if (mediaUrl.startsWith('http')) {
                    const url = new URL(mediaUrl);
                    if (url.hostname === 'localhost' || url.hostname === '127.0.0.1') {
                        // Replace localhost with current browser host/port to ensure it loads
                        mediaUrl = window.location.origin + url.pathname;
                    }
                } else {
                    // Prepend origin if it's a relative path
                    mediaUrl = window.location.origin + (mediaUrl.startsWith('/') ? '' : '/') + mediaUrl;
                }
            } catch(e) {}
        }

        if (m.message_type === 'image' && mediaUrl && mediaUrl !== 'null') {
            content = `<div class="message-media"><a data-fslightbox="gallery" href="${mediaUrl}"><img src="${mediaUrl}" onerror="this.src='https://placehold.co/300x200?text=Image+Not+Found'"></a><br>${m.message || ''}</div>`;
        } else if (m.message_type === 'video' && mediaUrl && mediaUrl !== 'null') {
            content = `<div class="message-media"><video src="${mediaUrl}" controls></video><br>${m.message || ''}</div>`;
        }

        const statusIcon = m.status === 'read' ? '✔✔' : (m.status === 'delivered' ? '✔✔' : (m.status === 'sent' ? '✔' : '🕒'));
        const statusColor = m.status === 'read' ? 'color: #34b7f1;' : '';
        
        const retryBtn = m.status === 'failed' ? `<button class="btn btn-xs btn-danger p-0 px-1 mt-1" style="font-size: 10px;" onclick="retryMessage(${m.id})">Retry</button>` : '';
        const templateBadge = m.message_type === 'template' ? '<span class="badge bg-info" style="font-size: 8px; vertical-align: middle;">TEMPLATE</span><br>' : '';

        return `
            <div class="message-bubble ${isOutbound ? 'message-outbound' : 'message-inbound'} position-relative group">
                <div class="message-options position-absolute" style="top: 2px; right: 8px; opacity: 0; transition: 0.2s; cursor: pointer;">
                    <span class="text-muted" data-bs-toggle="dropdown" style="font-size: 10px;"><i class="fa fa-chevron-down"></i></span>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 12px; z-index: 1050;">
                        <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteSpecificMessage(${m.id})">Delete Message</a></li>
                    </ul>
                </div>
                ${templateBadge}
                ${content}
                ${retryBtn}
                <div class="message-time">
                    ${time}
                    ${isOutbound ? `<span class="message-status" style="${statusColor}">${statusIcon}</span>` : ''}
                </div>
            </div>
        `;
    }

    function sendMessage() {
        const input = document.getElementById('chatInput');
        const text = input.value.trim();
        const mediaInput = document.getElementById('mediaInput');
        
        if (!text && !mediaInput.files[0]) return;

        const formData = new FormData();
        formData.append('customer_id', currentCustomerId);
        formData.append('message', text);
        if (mediaInput.files[0]) {
            formData.append('media', mediaInput.files[0]);
        }

        const optimisticMsg = {
            direction: 'outbound',
            message: text || 'Sending file...',
            message_type: mediaInput.files[0] ? 'image' : 'text',
            status: 'pending',
            created_at: new Date().toISOString()
        };
        appendMessage(optimisticMsg);
        
        input.value = '';
        mediaInput.value = '';

        fetch(`{{ route('chat.send') }}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw err; });
            }
            return res.json();
        })
        .then(msg => {
            loadMessages(currentCustomerId);
            loadConversations();
        })
        .catch(err => {
            const msg = (err && err.message) ? err.message : 'Could not send message. Please check your connection.';
            swal('Send Error', msg, 'error');
            loadMessages(currentCustomerId); // Refresh to show failure icons
        });
    }

    function appendMessage(msg) {
        const chatMessages = document.getElementById('chatMessages');
        const div = document.createElement('div');
        div.innerHTML = renderMessage(msg);
        chatMessages.insertBefore(div.firstElementChild, chatMessages.firstChild);
        if (typeof refreshFsLightbox === 'function') refreshFsLightbox();
    }

    function handleKeyPress(e) {
        if (e.key === 'Enter') sendMessage();
    }

    function deleteSpecificMessage(id) {
        swal({
            title: "Delete Message?",
            text: "This message will be hidden from your chat.",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                fetch(`{{ url('admin/chat/delete-message') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadMessages(currentCustomerId);
                    }
                });
            }
        });
    }

    function handleMediaSelect(input) {
        if (input.files[0]) {
            sendMessage();
        }
    }

    function sendWelcomeTemplate() {
        if (!currentCustomerId) return;
        const btn = document.querySelector('#welcomeTemplateArea button');
        const welcomeArea = document.getElementById('welcomeTemplateArea');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending Template...';

        const formData = new FormData();
        formData.append('customer_id', currentCustomerId);

        fetch(`{{ route('chat.send_template') }}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                welcomeArea.classList.add('d-none');
                welcomeArea.classList.remove('d-flex');
                loadMessages(currentCustomerId);
                loadConversations();
            } else {
                swal('Template Error', data.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-send"></i> Send Welcome Greeting';
            }
        })
        .catch(err => {
            swal('Network Error', 'Failed to reach server. Please try again.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-send"></i> Send Welcome Greeting';
        });
    }

    function retryMessage(id) {
        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Retrying...';

        fetch(`{{ url('admin/chat/retry') }}/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    loadMessages(currentCustomerId);
                } else {
                    Swal.fire('Error', data.message, 'error');
                    btn.disabled = false;
                    btn.innerText = 'Retry';
                }
            })
            .catch(err => {
                Swal.fire('Network Error', 'Please check your connection.', 'error');
                btn.disabled = false;
                btn.innerText = 'Retry';
            });
    }

    function filterConversations() {
        const term = document.getElementById('convSearch').value.toLowerCase();
        const items = document.querySelectorAll('.conversation-item');
        items.forEach(item => {
            const name = item.querySelector('h6').innerText.toLowerCase();
            item.style.display = name.includes(term) ? 'flex' : 'none';
        });
    }

    function updateUnseenTotal() {
        const total = conversations.reduce((acc, c) => acc + (c.unseen_count || 0), 0);
        const badge = document.getElementById('unseenCountSidebar');
        if (total > 0) {
            badge.innerText = total;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    function confirmClearChat(customerId = null) {
        const id = customerId || currentCustomerId;
        if (!id) return;
        
        swal({
            title: "Clear Chat History?",
            text: "All messages with this customer will be hidden.",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                fetch(`{{ url('admin/chat/clear') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (id == currentCustomerId) loadMessages(id);
                        loadConversations();
                        $.notify({ title: 'Success', message: 'Chat history cleared' }, { type: 'success' });
                    }
                });
            }
        });
    }

    function confirmDeleteConversation(customerId = null) {
        const id = customerId || currentCustomerId;
        if (!id) return;

        swal({
            title: "Delete Conversation?",
            text: "This will remove the customer from your list and delete all messages.",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                fetch(`{{ url('admin/chat/delete-conversation') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (id == currentCustomerId) {
                            currentCustomerId = null;
                            document.getElementById('chatMessages').innerHTML = '';
                            document.getElementById('noChatSelected').classList.remove('d-none');
                            document.getElementById('chatActiveArea').classList.add('d-none');
                        }
                        loadConversations();
                        $.notify({ title: 'Deleted', message: 'Conversation removed' }, { type: 'success' });
                    }
                });
            }
        });
    }

    function showNewChatModal() {
        const modalEl = document.getElementById('newChatModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'btnSubmitNewChat') {
            const phoneInput = document.getElementById('newChatPhone');
            const phone = phoneInput.value;
            if (!phone) {
                Swal.fire('Oops!', 'Please enter a phone number', 'warning');
                return;
            }

            e.target.disabled = true;
            e.target.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
            
            fetch(`{{ url('admin/chat/start') }}?phone=${phone}`)
                .then(res => res.json())
                .then(data => {
                    const modalEl = document.getElementById('newChatModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    phoneInput.value = '';
                    
                    if (data && data.id) {
                        selectConversation(data.id, data.name || data.phone_no);
                        
                        const templateBtn = document.querySelector('#welcomeTemplateArea button');
                        if (templateBtn) templateBtn.click();
                        
                        loadConversations();
                    }
                })
                .finally(() => {
                    const btn = document.getElementById('btnSubmitNewChat');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerText = 'Start Chat';
                    }
                });
        }
    });

    window.addEventListener('load', () => {
        loadConversations();
    });

    function playTing() {
        const sound = document.getElementById('msgSound');
        if (sound) {
            sound.currentTime = 0;
            sound.play().catch(e => {});
        }
    }
</script>

<audio id="msgSound" preload="auto" class="d-none">
    <source src="https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3" type="audio/mpeg">
</audio>
@endsection
