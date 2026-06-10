@extends('company.layouts.app')

@section('content-header')
    @include('company.partials.content_header')
@endsection

@section('content')
    <style>
        /* ── Wrapper ── */
        .dashboard-wrapper {
            display: flex;
            height: calc(100vh - var(--tblr-navbar-height, 56px) - var(--tblr-content-header-height, 56px));
            overflow: hidden;
        }

        /* ── Dashboard main scroll ── */
        .dashboard-main {
            flex: 1 1 0;
            min-width: 0;
            overflow-y: auto;
        }

        /* ── Chat sidebar – desktop ── */
        .ai-chat-sidebar {
            width: 360px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            border-left: 1px solid var(--tblr-border-color);
            background: var(--tblr-bg-surface);
            transition: width .3s ease, opacity .3s ease;
            overflow: hidden;
        }
        .ai-chat-sidebar.chat-collapsed {
            width: 0;
            border-left: none;
            opacity: 0;
            pointer-events: none;
        }

        /* ── Chat sidebar – mobile: offcanvas over content, below navbar ── */
        @media (max-width: 991.98px) {
            .ai-chat-sidebar {
                position: fixed;
                top: var(--tblr-navbar-height, 56px);
                right: 0;
                bottom: 0;
                width: 100% !important;
                max-width: 420px;
                z-index: 1045;
                box-shadow: -4px 0 24px rgba(0,0,0,.15);
                opacity: 1;
                pointer-events: all;
                transition: transform .3s ease, opacity .3s ease;
            }
            .ai-chat-sidebar.chat-collapsed {
                transform: translateX(100%);
                opacity: 0;
                pointer-events: none;
                width: 100% !important;
                border-left: 1px solid var(--tblr-border-color);
            }
            /* Backdrop */
            .chat-backdrop {
                display: block !important;
            }
        }
        .chat-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1044;
            background: rgba(0,0,0,.4);
            top: var(--tblr-navbar-height, 56px);
        }
        .chat-backdrop.d-none { display: none !important; }

        /* ── Chat messages scroll ── */
        .chat-messages {
            flex: 1 1 0;
            overflow-y: auto;
            scroll-behavior: smooth;
        }
        .chat-messages::-webkit-scrollbar { width: 4px; }
        .chat-messages::-webkit-scrollbar-thumb {
            background: var(--tblr-border-color);
            border-radius: 4px;
        }

        /* ── Message bubbles ── */
        .msg-user .msg-bubble {
            background: var(--tblr-primary);
            color: #fff;
            border-radius: 14px 14px 4px 14px;
        }
        .msg-ai .msg-bubble {
            background: var(--tblr-bg-surface-secondary, #f1f5f9);
            border: 1px solid var(--tblr-border-color);
            border-radius: 14px 14px 14px 4px;
            color: var(--tblr-body-color);
        }
        .msg-bubble {
            max-width: 88%;
            padding: .55rem .85rem;
            font-size: .855rem;
            line-height: 1.55;
            word-break: break-word;
        }

        /* ── Typing dots ── */
        .typing-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--tblr-muted);
            animation: typingBounce 1.2s infinite;
        }
        .typing-dot:nth-child(2) { animation-delay: .2s; }
        .typing-dot:nth-child(3) { animation-delay: .4s; }
        @keyframes typingBounce {
            0%,80%,100% { transform: translateY(0); opacity: .4; }
            40%          { transform: translateY(-6px); opacity: 1; }
        }

        /* ── Online dot ── */
        .status-online {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #2fb344;
            animation: onlinePulse 2s infinite;
        }
        @keyframes onlinePulse {
            0%,100% { opacity: 1; }
            50%      { opacity: .35; }
        }

        /* ── Chat textarea ── */
        .chat-textarea {
            resize: none;
            overflow-y: hidden;
            max-height: 120px;
            field-sizing: content;
        }

        /* ── Suggestion chips ── */
        .suggestion-chip {
            cursor: pointer;
            transition: background .15s, border-color .15s, color .15s;
        }
        .suggestion-chip:hover {
            background: rgba(var(--tblr-primary-rgb, 6,111,209),.08) !important;
            border-color: var(--tblr-primary) !important;
            color: var(--tblr-primary) !important;
        }

        /* ── FAB toggle (mobile) ── */
        .chat-fab {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 1043;
            width: 52px; height: 52px;
            border-radius: 50%;
            border: none;
            background: var(--tblr-primary);
            color: #fff;
            box-shadow: 0 4px 20px rgba(6,111,209,.4);
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform .2s;
        }
        .chat-fab:hover { transform: scale(1.08); }
        @media (max-width: 991.98px) {
            .chat-fab { display: flex; }
        }
    </style>

    {{-- ════════════════════════════════════════════════════════ --}}
    {{--                   MAIN WRAPPER                         --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div class="dashboard-wrapper">

        {{-- ──────────────────────────────────────────────────── --}}
        {{--               DASHBOARD CONTENT                     --}}
        {{-- ──────────────────────────────────────────────────── --}}
        <div class="dashboard-main p-1" id="dashboardMain">
            {!! file_get_contents($path) !!}
        </div>


        {{-- ──────────────────────────────────────────────────── --}}
        {{--               AI CHAT SIDEBAR                       --}}
        {{-- ──────────────────────────────────────────────────── --}}
        <aside class="ai-chat-sidebar" id="aiChatSidebar" aria-label="AI Assistant">

            {{-- Header --}}
            <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom flex-shrink-0">
                <div class="avatar avatar-sm rounded-2 bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8a4 4 0 0 1 4 4"/><path d="M12 4a8 8 0 0 1 8 8"/><path d="M12 20a8 8 0 0 1 -8 -8"/><circle cx="12" cy="12" r="1"/></svg>
                </div>
                <div class="flex-fill overflow-hidden">
                    <div class="fw-semibold lh-1 small">AI Assistant</div>
                    <div class="text-secondary d-flex align-items-center gap-1 mt-1" style="font-size:.7rem">
                        <span class="status-online flex-shrink-0"></span>Ready to help
                    </div>
                </div>
                <div class="d-flex gap-1 ms-auto flex-shrink-0">
                    <button class="btn btn-sm btn-ghost-secondary px-2" onclick="clearChat()" title="Clear chat" aria-label="Clear chat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                    </button>
                    <button class="btn btn-sm btn-ghost-secondary px-2" onclick="toggleChat()" title="Close" aria-label="Close chat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="d-flex gap-2 px-3 py-2 border-bottom flex-shrink-0 flex-wrap bg-body-tertiary">
                <button class="btn btn-sm btn-outline-secondary px-2 py-1 d-flex align-items-center gap-1" onclick="quickAsk('Покажи статистику продаж')" style="font-size:.72rem">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 16l4 -7l4 4l4 -7"/></svg>
                    Продажи
                </button>
                <button class="btn btn-sm btn-outline-secondary px-2 py-1 d-flex align-items-center gap-1" onclick="quickAsk('Сколько активных пользователей?')" style="font-size:.72rem">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
                    Пользователи
                </button>
                <button class="btn btn-sm btn-outline-secondary px-2 py-1 d-flex align-items-center gap-1" onclick="quickAsk('Какой текущий доход?')" style="font-size:.72rem">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2"/><path d="M12 3v3m0 12v3"/></svg>
                    Доход
                </button>

            </div>

            {{-- Messages --}}
            <div class="chat-messages p-3 d-flex flex-column gap-3" id="chatMessages">
                {{-- Welcome --}}
                <div id="chatWelcome" class="d-flex flex-column align-items-center justify-content-center text-center py-4 px-2 flex-grow-1">
                    <div class="avatar avatar-lg rounded-3 bg-primary text-white mb-3 d-flex align-items-center justify-content-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8a4 4 0 0 1 4 4"/><path d="M12 4a8 8 0 0 1 8 8"/><path d="M12 20a8 8 0 0 1 -8 -8"/><circle cx="12" cy="12" r="1"/></svg>
                    </div>
                    <h5 class="fw-bold mb-1">AI Ассистент</h5>
                    <p class="text-secondary small mb-3">Задайте вопрос о данных вашего дашборда — анализ метрик, отчёты, тренды.</p>
                    <div class="d-flex flex-column gap-2 w-100">
                        <button class="btn btn-outline-secondary text-start suggestion-chip d-flex align-items-center gap-2 px-3 py-2" onclick="quickAsk('Проанализируй текущие показатели продаж и дай рекомендации')" style="font-size:.8rem">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 text-muted"><path d="M3 3v18h18"/><path d="M7 16l4 -7l4 4l4 -7"/></svg>
                            Анализ продаж и рекомендации
                        </button>
                        <button class="btn btn-outline-secondary text-start suggestion-chip d-flex align-items-center gap-2 px-3 py-2" onclick="quickAsk('Какие метрики показывают отрицательную динамику?')" style="font-size:.8rem">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 text-muted"><path d="M12 5l0 14"/><path d="M18 13l-6 6"/><path d="M6 13l6 6"/></svg>
                            Проблемные метрики
                        </button>
                        <button class="btn btn-outline-secondary text-start suggestion-chip d-flex align-items-center gap-2 px-3 py-2" onclick="quickAsk('Составь краткий отчёт по ключевым показателям дашборда')" style="font-size:.8rem">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 text-muted"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2"/><path d="M9 9l1 0"/><path d="M9 13l6 0"/><path d="M9 17l6 0"/></svg>
                            Сводный отчёт по KPI
                        </button>
                    </div>
                </div>
            </div>

            {{-- Input --}}
            <div class="p-3 border-top flex-shrink-0">
                <div class="input-group">
                <textarea
                    class="form-control chat-textarea"
                    id="chatInput"
                    placeholder="Спросите о ваших данных…"
                    rows="1"
                    onkeydown="handleChatKeydown(event)"
                    oninput="autoResizeTextarea(this)"
                    aria-label="Chat input"
                ></textarea>
                    <button class="btn btn-primary px-3" id="chatSendBtn" onclick="sendMessage()" title="Отправить (Enter)" aria-label="Send">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14l11 -11"/><path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5"/></svg>
                    </button>
                </div>
                <div class="text-center text-muted mt-1" style="font-size:.68rem">Enter — отправить &nbsp;·&nbsp; Shift+Enter — новая строка</div>
            </div>

        </aside>

    </div>{{-- /dashboard-wrapper --}}

    {{-- Backdrop (mobile) --}}
    <div class="chat-backdrop d-none" id="chatBackdrop" onclick="toggleChat()"></div>

    {{-- FAB (mobile only, shown via CSS) --}}
    <button class="chat-fab" id="chatFab" onclick="toggleChat()" aria-label="Open AI Assistant">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8a4 4 0 0 1 4 4"/><path d="M12 4a8 8 0 0 1 8 8"/><path d="M12 20a8 8 0 0 1 -8 -8"/><circle cx="12" cy="12" r="1"/></svg>
    </button>

    <script>
        // ════════════════════════════════════════════
        //  Dashboard context passed to AI
        // ════════════════════════════════════════════
        const DASHBOARD_CONTEXT = {
            todaySales: 6782, growthRate: '78.4%', growthDelta: '-1%',
            totalUsers: 75782, activeUsers: 25782,
            conversionRate: '75%', revenue: '$4,300',
            newClients: 6782, activeSubscriptions: 2986,
            socialTraffic: { Instagram: 3550, Twitter: 1798, Facebook: 1245, TikTok: 986, Pinterest: 854 },
            topPages: [
                { page: '/', visitors: 4896, bounceRate: '82.54%' },
                { page: '/form-elements.html', visitors: 3652, bounceRate: '76.29%' },
                { page: '/index.html', visitors: 3256, bounceRate: '72.65%' },
            ],
            tasks: [
                { name: 'Extend the data model',        done: 2, total: 7  },
                { name: 'Verify the event flow',         done: 0, total: 5  },
                { name: 'Database backup',               done: 0, total: 5  },
                { name: 'Identify implementation team',  done: 6, total: 10 },
                { name: 'Check Pull Requests',           done: 2, total: 9  },
            ],
            invoices: [
                { id: '001401', subject: 'Design Works',   client: 'Carlson Limited', status: 'Paid',    amount: 887  },
                { id: '001402', subject: 'UX Wireframes',  client: 'Adobe',           status: 'Pending', amount: 1200 },
                { id: '001403', subject: 'New Dashboard',  client: 'Bluewolf',        status: 'Pending', amount: 534  },
                { id: '001404', subject: 'Logo & Print',   client: 'Apple',           status: 'Paid',    amount: 2500 },
            ]
        };

        // ════════════════════════════════════════════
        //  State
        // ════════════════════════════════════════════
        let conversationHistory = [];
        let isSending = false;
        let chatOpen = true; // desktop default open

        // ════════════════════════════════════════════
        //  Toggle sidebar
        // ════════════════════════════════════════════
        function toggleChat() {
            const sidebar  = document.getElementById('aiChatSidebar');
            const backdrop = document.getElementById('chatBackdrop');
            const fab      = document.getElementById('chatFab');

            chatOpen = !chatOpen;
            sidebar.classList.toggle('chat-collapsed', !chatOpen);

            // backdrop only on mobile
            if (window.innerWidth < 992) {
                backdrop.classList.toggle('d-none', !chatOpen);
            }
            // FAB visible only on mobile when closed
            fab.style.display = (window.innerWidth < 992 && !chatOpen) ? 'flex' : '';
        }

        // keep FAB state in sync on resize
        window.addEventListener('resize', () => {
            const fab = document.getElementById('chatFab');
            fab.style.display = (window.innerWidth < 992 && !chatOpen) ? 'flex' : '';
        });

        // ════════════════════════════════════════════
        //  Helpers
        // ════════════════════════════════════════════
        function quickAsk(text) {
            const inp = document.getElementById('chatInput');
            inp.value = text;
            autoResizeTextarea(inp);
            sendMessage();
        }

        function handleChatKeydown(e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        }

        function autoResizeTextarea(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        }

        function clearChat() {
            conversationHistory = [];
            const c = document.getElementById('chatMessages');
            c.innerHTML = `
    <div id="chatWelcome" class="d-flex flex-column align-items-center justify-content-center text-center py-4 px-2 flex-grow-1">
        <div class="avatar avatar-lg rounded-3 bg-primary text-white mb-3 d-flex align-items-center justify-content-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8a4 4 0 0 1 4 4"/><path d="M12 4a8 8 0 0 1 8 8"/><path d="M12 20a8 8 0 0 1 -8 -8"/><circle cx="12" cy="12" r="1"/></svg>
        </div>
        <h5 class="fw-bold mb-1">AI Ассистент</h5>
        <p class="text-secondary small mb-3">Задайте вопрос о данных вашего дашборда — анализ метрик, отчёты, тренды.</p>
        <div class="d-flex flex-column gap-2 w-100">
            <button class="btn btn-outline-secondary text-start suggestion-chip d-flex align-items-center gap-2 px-3 py-2" onclick="quickAsk('Проанализируй текущие показатели продаж')" style="font-size:.8rem">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 text-muted"><path d="M3 3v18h18"/><path d="M7 16l4 -7l4 4l4 -7"/></svg>
                Анализ продаж
            </button>
            <button class="btn btn-outline-secondary text-start suggestion-chip d-flex align-items-center gap-2 px-3 py-2" onclick="quickAsk('Составь отчёт по KPI')" style="font-size:.8rem">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 text-muted"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2"/></svg>
                Сводный отчёт по KPI
            </button>
        </div>
    </div>`;
        }

        // ════════════════════════════════════════════
        //  Append message
        // ════════════════════════════════════════════
        function appendMessage(role, html) {
            const welcome = document.getElementById('chatWelcome');
            if (welcome) welcome.remove();

            const c   = document.getElementById('chatMessages');
            const now = new Date().toLocaleTimeString('ru', { hour: '2-digit', minute: '2-digit' });

            const group = document.createElement('div');
            group.className = `d-flex flex-column gap-1 ${role === 'user' ? 'align-items-end msg-user' : 'align-items-start msg-ai'}`;

            group.innerHTML = `
        <div class="text-muted px-1" style="font-size:.7rem">${role === 'user' ? 'Вы' : 'AI'} · ${now}</div>
        <div class="msg-bubble">${html.replace(/\n/g, '<br>')}</div>`;

            c.appendChild(group);
            c.scrollTop = c.scrollHeight;
            return group.querySelector('.msg-bubble');
        }

        // ════════════════════════════════════════════
        //  Typing indicator
        // ════════════════════════════════════════════
        function showTyping() {
            const welcome = document.getElementById('chatWelcome');
            if (welcome) welcome.remove();

            const c = document.getElementById('chatMessages');
            const el = document.createElement('div');
            el.id = 'typingIndicator';
            el.className = 'd-flex flex-column align-items-start gap-1 msg-ai';
            el.innerHTML = `<div class="msg-bubble d-flex align-items-center gap-1" style="padding:.65rem .9rem">
        <div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>
    </div>`;
            c.appendChild(el);
            c.scrollTop = c.scrollHeight;
        }

        function hideTyping() {
            const el = document.getElementById('typingIndicator');
            if (el) el.remove();
        }

        // ════════════════════════════════════════════
        //  Send message → Anthropic API
        // ════════════════════════════════════════════
        async function sendMessage() {
            if (isSending) return;
            const input   = document.getElementById('chatInput');
            const sendBtn = document.getElementById('chatSendBtn');
            const text    = input.value.trim();
            if (!text) return;

            input.value = '';
            input.style.height = 'auto';
            appendMessage('user', text);

            isSending = true;
            sendBtn.disabled = true;
            conversationHistory.push({ role: 'user', content: text });
            showTyping();



                const res = await fetch('{{route('company.chat.message')}}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'

                    },
                    body: JSON.stringify({
                        message:   text,
                    }),
                });

                const data  = await res.json();

                console.log(data);


        }
    </script>
@endsection
