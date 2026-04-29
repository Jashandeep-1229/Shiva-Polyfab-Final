@extends('layouts.admin.app')
@section('title', 'AI Factory Agent — Shiva Polyfab')

@section('custom_css')
<style>
/* ── Google Font ──────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

#ai-chat-page * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

/* ── Page Shell ───────────────────────────────── */
#ai-chat-page {
    background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
    min-height: 100vh;
    padding: 24px;
}

/* ── Header Banner ────────────────────────────── */
.ai-header {
    background: linear-gradient(120deg, rgba(99,102,241,.15) 0%, rgba(16,185,129,.12) 100%);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 20px;
    padding: 20px 28px;
    margin-bottom: 20px;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ai-header-left { display: flex; align-items: center; gap: 16px; }
.ai-logo {
    width: 54px; height: 54px; border-radius: 14px;
    background: linear-gradient(135deg, #6366f1, #10b981);
    display: flex; align-items: center; justify-content: center;
    font-size: 26px;
    box-shadow: 0 0 24px rgba(99,102,241,.5);
    animation: pulse-glow 2.5s ease-in-out infinite;
}
@keyframes pulse-glow {
    0%,100%{ box-shadow: 0 0 24px rgba(99,102,241,.5); }
    50%    { box-shadow: 0 0 40px rgba(99,102,241,.9), 0 0 60px rgba(16,185,129,.3); }
}
.ai-header h1 { color: #fff; font-size: 22px; font-weight: 700; margin: 0; }
.ai-header p  { color: rgba(255,255,255,.6); font-size: 13px; margin: 2px 0 0; }
.status-badge {
    display: flex; align-items: center; gap: 8px;
    background: rgba(16,185,129,.15); border: 1px solid rgba(16,185,129,.4);
    border-radius: 50px; padding: 8px 18px; color: #10b981; font-size: 13px; font-weight: 600;
}
.status-dot {
    width: 8px; height: 8px; border-radius: 50%; background: #10b981;
    animation: blink 1.4s ease-in-out infinite;
}
@keyframes blink { 0%,100%{ opacity:1; } 50%{ opacity:.3; } }

/* ── Main Layout ──────────────────────────────── */
.ai-main-grid { display: grid; grid-template-columns: 1fr 300px; gap: 20px; }

/* ── Chat Window ──────────────────────────────── */
.chat-window {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 20px;
    display: flex; flex-direction: column;
    height: calc(100vh - 200px);
    min-height: 500px;
    overflow: hidden;
    backdrop-filter: blur(16px);
}
.chat-messages {
    flex: 1; overflow-y: auto; padding: 24px;
    scroll-behavior: smooth;
}
.chat-messages::-webkit-scrollbar { width: 5px; }
.chat-messages::-webkit-scrollbar-track { background: transparent; }
.chat-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 10px; }

/* ── Messages ─────────────────────────────────── */
.msg-row {
    display: flex; gap: 12px; margin-bottom: 20px;
    animation: slideIn .3s ease-out;
}
@keyframes slideIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
.msg-row.user-row { flex-direction: row-reverse; }

.msg-avatar {
    width: 38px; height: 38px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.ai-avatar   { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.user-avatar { background: linear-gradient(135deg, #0ea5e9, #10b981); }

.msg-bubble {
    max-width: 72%; padding: 14px 18px; border-radius: 16px;
    font-size: 14px; line-height: 1.65; word-break: break-word;
}
.ai-bubble {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.1);
    color: rgba(255,255,255,.92);
    border-radius: 4px 16px 16px 16px;
}
.user-bubble {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    border-radius: 16px 4px 16px 16px;
}
.msg-bubble strong { color: #10b981; }
.msg-bubble ul, .msg-bubble ol { padding-left: 18px; margin: 6px 0; }
.msg-bubble li { margin-bottom: 3px; }
.msg-bubble code { background: rgba(255,255,255,.12); padding: 2px 6px; border-radius: 5px; font-size: 12px; }

/* Typing animation */
.typing-bubble { display: flex; align-items: center; gap: 5px; padding: 14px 18px; }
.typing-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: rgba(255,255,255,.5); animation: bounce .8s ease-in-out infinite;
}
.typing-dot:nth-child(2) { animation-delay: .15s; }
.typing-dot:nth-child(3) { animation-delay: .30s; }
@keyframes bounce { 0%,100%{ transform:translateY(0); } 50%{ transform:translateY(-6px); } }

/* ── Chat Input Bar ───────────────────────────── */
.chat-input-bar {
    padding: 16px 20px;
    border-top: 1px solid rgba(255,255,255,.08);
    background: rgba(0,0,0,.2);
}
.input-wrap {
    display: flex; gap: 12px; align-items: flex-end;
}
#ai-input {
    flex: 1; background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 14px; color: #fff;
    padding: 13px 18px; font-size: 14px; font-family: 'Inter', sans-serif;
    resize: none; outline: none; transition: border-color .2s;
    max-height: 120px; min-height: 48px;
}
#ai-input::placeholder { color: rgba(255,255,255,.35); }
#ai-input:focus { border-color: #6366f1; }
#send-btn {
    width: 48px; height: 48px; border-radius: 14px; border: none;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff; cursor: pointer; font-size: 18px;
    display: flex; align-items: center; justify-content: center;
    transition: transform .2s, box-shadow .2s; flex-shrink: 0;
}
#send-btn:hover { transform: scale(1.05); box-shadow: 0 0 20px rgba(99,102,241,.5); }
#send-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* ── Sidebar ──────────────────────────────────── */
.ai-sidebar { display: flex; flex-direction: column; gap: 16px; }
.sidebar-card {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 16px; padding: 18px;
    backdrop-filter: blur(16px);
}
.sidebar-card h6 {
    color: rgba(255,255,255,.9); font-size: 13px; font-weight: 600;
    margin: 0 0 14px; display: flex; align-items: center; gap: 8px;
}
.sidebar-card h6 .icon { font-size: 16px; }

/* Suggestion chips */
.chip {
    display: block; width: 100%; text-align: left;
    background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
    border-radius: 10px; padding: 10px 14px; color: rgba(255,255,255,.8);
    font-size: 12.5px; cursor: pointer; margin-bottom: 8px;
    transition: background .2s, border-color .2s, transform .15s;
}
.chip:hover { background: rgba(99,102,241,.25); border-color: #6366f1; transform: translateX(4px); }
.chip:last-child { margin-bottom: 0; }

/* Quick stats */
.stat-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 9px 0; border-bottom: 1px solid rgba(255,255,255,.07);
}
.stat-row:last-child { border: none; padding-bottom: 0; }
.stat-label { color: rgba(255,255,255,.5); font-size: 12px; }
.stat-val { color: #fff; font-size: 13px; font-weight: 600; }

/* Language tags */
.lang-tags { display: flex; flex-wrap: wrap; gap: 6px; }
.lang-tag {
    background: rgba(16,185,129,.15); border: 1px solid rgba(16,185,129,.3);
    border-radius: 6px; padding: 4px 10px; color: #10b981; font-size: 11px; font-weight: 500;
}

/* Breadcrumb */
.ai-breadcrumb { color: rgba(255,255,255,.4); font-size: 13px; margin-bottom: 16px; }
.ai-breadcrumb a { color: rgba(255,255,255,.5); text-decoration: none; }
.ai-breadcrumb a:hover { color: #6366f1; }

/* Markdown-style render */
.ai-bubble p { margin: 0 0 8px; }
.ai-bubble p:last-child { margin-bottom: 0; }
.ai-bubble h1,.ai-bubble h2,.ai-bubble h3 { color: #10b981; font-size: 15px; margin: 8px 0 4px; }
.ai-link { color: #10b981; font-weight: 600; text-decoration: none; border-bottom: 1px dashed rgba(16,185,129,0.5); transition: all .2s; }
.ai-link:hover { color: #059669; background: rgba(16,185,129,0.1); border-bottom-style: solid; border-bottom-color: #10b981; text-decoration: none; }

@media (max-width: 900px) {
    .ai-main-grid { grid-template-columns: 1fr; }
    .ai-sidebar { display: none; }
}
</style>
@endsection

@section('content')
<div id="ai-chat-page">

    {{-- Breadcrumb --}}
    <div class="ai-breadcrumb">
        <a href="{{ route('dashboard') }}">🏠 Home</a> &rsaquo;
        <a href="{{ route('ai_studio.index') }}">AI Studio</a> &rsaquo;
        <span style="color:rgba(255,255,255,.7)">Factory AI Agent</span>
    </div>

    {{-- Header --}}
    <div class="ai-header">
        <div class="ai-header-left">
            <div class="ai-logo">🤖</div>
            <div>
                <h1>Shiva Polyfab · Factory AI Agent</h1>
                <p>Powered by Gemini 1.5 Flash · Scans your live database · Speaks any language</p>
            </div>
        </div>
        <div class="status-badge">
            <div class="status-dot"></div>
            AI Online
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="ai-main-grid">

        {{-- Chat Window --}}
        <div class="chat-window">
            <div class="chat-messages" id="chat-messages">

                {{-- Welcome message --}}
                <div class="msg-row">
                    <div class="msg-avatar ai-avatar">🤖</div>
                    <div class="msg-bubble ai-bubble">
                        <p>Namaste <strong>{{ auth()->user()->name }}</strong>! 🙏</p>
                        <p>I am your <strong>Factory AI Agent</strong> for <strong>Shiva Polyfab</strong>. I scan your <strong>live database</strong> and can answer anything:</p>
                        <ul>
                            <li>📦 Remaining stock of fabric, BOPP, Dana, Ink, Loop</li>
                            <li>🏭 Job card status &amp; production process</li>
                            <li>👥 Customer details &amp; leads</li>
                            <li>🔤 "What is GSM?" — industry terms explained</li>
                            <li>✍️ Rephrase customer messages professionally</li>
                            <li>🌐 Ask in <strong>Hindi, Punjabi, Gujarati, English</strong> — I reply in the same language</li>
                        </ul>
                        <p style="color:rgba(255,255,255,.5); font-size:12px; margin-top:8px;">Type anything below ↓</p>
                    </div>
                </div>

            </div>

            {{-- Input Bar --}}
            <div class="chat-input-bar">
                <div class="input-wrap">
                    <textarea id="ai-input" rows="1" placeholder="Ask anything... e.g. 'fabric ka remaining stock kitna hai?' or 'what is GSM?'"></textarea>
                    <button id="send-btn" title="Send (Enter)">➤</button>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="ai-sidebar">

            {{-- Suggestion Chips --}}
            <div class="sidebar-card">
                <h6><span class="icon">💡</span> Quick Questions</h6>
                <button class="chip" data-q="Fabric ka remaining stock kitna hai?">Fabric remaining stock?</button>
                <button class="chip" data-q="BOPP remaining stock show karo">BOPP remaining stock</button>
                <button class="chip" data-q="Kitne job cards abhi in production hain?">Job cards in production?</button>
                <button class="chip" data-q="What is GSM in polypropylene bags?">What is GSM?</button>
                <button class="chip" data-q="Today's leads and conversions stats">Today's leads stats</button>
                <button class="chip" data-q="Konse job cards hold par hain aur kyun?">Which JCs on hold?</button>
                <button class="chip" data-q="Customer ne pucha delivery late kyun hui, iska professional reply likho">Write professional delay reply</button>
                <button class="chip" data-q="Common stock mein kitne bags hain?">Common bag stock</button>
            </div>

            {{-- Languages --}}
            <div class="sidebar-card">
                <h6><span class="icon">🌐</span> Languages Supported</h6>
                <div class="lang-tags">
                    <span class="lang-tag">English</span>
                    <span class="lang-tag">हिंदी</span>
                    <span class="lang-tag">ਪੰਜਾਬੀ</span>
                    <span class="lang-tag">ગુજરાતી</span>
                    <span class="lang-tag">मराठी</span>
                </div>
            </div>

            {{-- What AI can do --}}
            <div class="sidebar-card">
                <h6><span class="icon">⚡</span> AI Capabilities</h6>
                @php
                    $jobCount = \App\Models\JobCard::where('status','In Production')->count();
                    $holdCount = \App\Models\JobCard::where('is_hold',1)->count();
                    $custCount = \App\Models\AgentCustomer::count();
                @endphp
                <div class="stat-row">
                    <span class="stat-label">🏭 In Production</span>
                    <span class="stat-val">{{ $jobCount }} JCs</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">🛑 On Hold</span>
                    <span class="stat-val">{{ $holdCount }} JCs</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">👥 Customers</span>
                    <span class="stat-val">{{ $custCount }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">🧠 AI Model</span>
                    <span class="stat-val" style="color:#10b981;">Gemini 1.5 Flash</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">📡 Data Scan</span>
                    <span class="stat-val" style="color:#10b981;">Live DB</span>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

@section('custom_javascript')
<script>
$(document).ready(function () {

    const $messages = $('#chat-messages');
    const $input    = $('#ai-input');
    const $sendBtn  = $('#send-btn');
    let   isLoading = false;

    // ── Auto-resize textarea ──────────────────────
    $input.on('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // ── Scroll to bottom ──────────────────────────
    function scrollBottom() {
        $messages.stop(true).animate({ scrollTop: $messages[0].scrollHeight }, 300);
    }

    // ── Render markdown-ish text ──────────────────
    function renderText(text) {
        return text
            .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" class="ai-link" target="_blank">$1</a>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g,     '<em>$1</em>')
            .replace(/`(.*?)`/g,       '<code>$1</code>')
            .replace(/\n\/g,           '<br>');
    }

    // ── Intercept Link Clicks ──────────────────────
    // Aggressively catch ALL links within the bubble, not just .ai-link
    $(document).on('click', '.ai-bubble a', function(e) {
        const url = $(this).attr('href');
        if (!url || url === '#' || url.startsWith('javascript:')) return;
        
        // If it's a PDF link or external, let it open normally
        if (url.includes('/pdf') || url.includes('http') && !url.includes(window.location.hostname)) {
            return true;
        }

        e.preventDefault();
        
        // Ensure modal exists
        if ($('#ai-modal').length === 0) {
            $('body').append(`
                <div class="modal fade" id="ai-modal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content" id="ai-modal-content" style="background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
                            <div class="modal-body text-center py-5">
                                <div class="loader-box"><div class="loader-37"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
        
        $('#ai-modal').modal('show');
        $('#ai-modal-content').html('<div class="modal-body text-center py-5"><div class="loader-box"><div class="loader-37"></div></div></div>');
        
        $.get(url, function(data) {
            if (typeof data === 'string' && data.includes('</body>')) {
                $('#ai-modal-content').html(`
                    <div class="modal-header">
                        <h4 class="modal-title">System View</h4>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0" style="height: 80vh;">
                        <iframe src="${url}" style="width:100%; height:100%; border:none;"></iframe>
                    </div>
                `);
            } else {
                $('#ai-modal-content').html(data);
                $('.js-example-basic-single').select2({ dropdownParent: $('#ai-modal') });
            }
        }).fail(function() {
             $('#ai-modal-content').html(`
                <div class="modal-header">
                    <h5 class="modal-title">Error</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-5 text-center text-danger">
                    <i class="fa fa-exclamation-triangle fa-3x mb-3"></i>
                    <p>Failed to load the requested content.</p>
                </div>
             `);
        });
    });

    // ── Add a message bubble ──────────────────────
    function addMsg(role, text) {
        const isUser   = role === 'user';
        const rowClass = isUser ? 'msg-row user-row' : 'msg-row';
        const avatar   = isUser ? '👤' : '🤖';
        const avCls    = isUser ? 'user-avatar' : 'ai-avatar';
        const bubCls   = isUser ? 'user-bubble' : 'ai-bubble';

        const html = `
        <div class="${rowClass} msg-anim">
            <div class="msg-avatar ${avCls}">${avatar}</div>
            <div class="msg-bubble ${bubCls}">${isUser ? text : renderText(text)}</div>
        </div>`;

        $messages.append(html);
        scrollBottom();
    }

    // ── Typing indicator ──────────────────────────
    function showTyping() {
        const html = `
        <div id="typing-indicator" class="msg-row">
            <div class="msg-avatar ai-avatar">🤖</div>
            <div class="msg-bubble ai-bubble typing-bubble">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        </div>`;
        $messages.append(html);
        scrollBottom();
    }
    function hideTyping() { $('#typing-indicator').remove(); }

    // ── Send message ──────────────────────────────
    function sendMessage() {
        const msg = $input.val().trim();
        if (!msg || isLoading) return;

        addMsg('user', msg);
        $input.val('').css('height', 'auto');
        isLoading = true;
        $sendBtn.prop('disabled', true);
        showTyping();

        $.ajax({
            url    : '{{ route("ai_studio.ask_ai") }}',
            method : 'POST',
            data   : { _token: '{{ csrf_token() }}', message: msg },
            timeout: 60000,
            success: function (res) {
                hideTyping();
                addMsg('ai', res.response || '⚠️ No response returned.');
            },
            error: function (xhr) {
                hideTyping();
                const errMsg = xhr.responseJSON?.message || 'Server error. Please try again.';
                addMsg('ai', '❌ Error: ' + errMsg);
            },
            complete: function () {
                isLoading = false;
                $sendBtn.prop('disabled', false);
                $input.focus();
            }
        });
    }

    // ── Events ────────────────────────────────────
    $sendBtn.on('click', sendMessage);

    $input.on('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Suggestion chips
    $(document).on('click', '.chip', function () {
        const q = $(this).data('q');
        $input.val(q).trigger('input');
        sendMessage();
    });

    $input.focus();
});
</script>
@endsection
