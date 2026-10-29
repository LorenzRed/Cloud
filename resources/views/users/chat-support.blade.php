<!DOCTYPE html>
<html class="light" lang="en">
<head>
    @include('partials.favicon')
    <meta charset="utf-8" />
    @include('partials.users-viewport')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Bantay Kalusugan - Chat Support</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <style>
        html, body { height: 100%; overflow: hidden; }
        #usersSidebar { transition: width 0.25s ease; }
        @media (min-width: 1024px) {
            #usersSidebar.is-collapsed { width: 5rem; }
            #usersSidebar.is-collapsed .sidebar-label, #usersSidebar.is-collapsed .sidebar-brand-text { display: none; }
            #usersSidebar.is-collapsed .sidebar-link, #usersSidebar.is-collapsed .sidebar-logout { justify-content: center; }
            #usersSidebar.is-collapsed .sidebar-header-inner { justify-content: center; }
        }

        .users-messenger-shell {
            min-height: 0;
            background: #fff;
        }
        .dark .users-messenger-shell {
            background: rgb(17 33 23);
        }
        .users-messenger-header {
            box-shadow: 0 1px 0 rgb(0 0 0 / 0.06);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.92);
        }
        .dark .users-messenger-header {
            box-shadow: 0 1px 0 rgb(255 255 255 / 0.06);
            background: rgba(17, 33, 23, 0.92);
        }
        .users-messenger-chat-body .chat-bg {
            background-color: #eef2ef;
            background-image:
                radial-gradient(circle at 12% 18%, rgba(25, 230, 107, 0.08), transparent 28%),
                radial-gradient(circle at 88% 12%, rgba(31, 127, 78, 0.06), transparent 24%),
                radial-gradient(circle at 50% 100%, rgba(25, 230, 107, 0.05), transparent 36%);
        }
        .dark .users-messenger-chat-body .chat-bg {
            background-color: rgb(15 28 20);
            background-image:
                radial-gradient(circle at 12% 18%, rgba(25, 230, 107, 0.08), transparent 28%),
                radial-gradient(circle at 88% 12%, rgba(25, 230, 107, 0.05), transparent 24%);
        }
        .users-messenger-chat-body .rbhw-chat-scroll {
            padding-top: 1.25rem !important;
            padding-bottom: 0.75rem !important;
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        .users-messenger-chat-body > .shrink-0 {
            background: #fff;
            border-top-color: #e7ede9 !important;
            padding: 0.5rem 0.625rem 0.625rem !important;
        }
        .dark .users-messenger-chat-body > .shrink-0 {
            background: rgb(17 33 23);
            border-top-color: rgb(55 65 81) !important;
        }
        .users-messenger-chat-body form {
            align-items: flex-end;
            gap: 0.375rem;
            background: #f0f4f1;
            border-radius: 1.5rem;
            padding: 0.3125rem 0.375rem 0.3125rem 0.5rem;
            box-shadow: inset 0 0 0 1px rgba(31, 127, 78, 0.06);
        }
        .dark .users-messenger-chat-body form {
            background: rgb(31 41 55);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.04);
        }
        .users-messenger-chat-body textarea {
            min-height: 2.25rem !important;
            max-height: 6.5rem;
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
            white-space: pre-wrap !important;
            overflow-wrap: anywhere;
            word-break: break-word;
            overflow-x: hidden;
            overflow-y: hidden;
            transition: none;
            scrollbar-width: none;
        }
        .users-messenger-chat-body textarea::-webkit-scrollbar {
            display: none;
        }
        .users-messenger-chat-body textarea:focus {
            outline: none !important;
        }
        .users-messenger-chat-body button[type="submit"] {
            height: 2.375rem;
            width: 2.375rem;
            border-radius: 9999px;
            flex-shrink: 0;
            box-shadow: 0 6px 14px rgba(25, 230, 107, 0.28);
        }

        .ai-intro {
            width: min(100%, 22rem);
            margin: 0.75rem auto 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            animation: aiFadeUp 0.45s ease both;
        }
        .ai-intro-card {
            width: 100%;
            border-radius: 1.5rem;
            padding: 1.25rem 1.15rem 1.1rem;
            text-align: center;
            background: linear-gradient(180deg, rgba(255,255,255,0.96) 0%, rgba(247,251,248,0.96) 100%);
            border: 1px solid rgba(208, 231, 217, 0.9);
            box-shadow: 0 12px 28px rgba(15, 40, 25, 0.06);
        }
        .dark .ai-intro-card {
            background: linear-gradient(180deg, rgba(31, 41, 55, 0.95) 0%, rgba(17, 33, 23, 0.95) 100%);
            border-color: rgba(75, 85, 99, 0.7);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.25);
        }
        .ai-intro-avatar {
            width: 3.5rem;
            height: 3.5rem;
            margin: 0 auto 0.85rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1f7f4e;
            background: linear-gradient(145deg, rgba(25, 230, 107, 0.38) 0%, rgba(25, 230, 107, 0.12) 100%);
            border: 2px solid rgba(255, 255, 255, 0.95);
            box-shadow:
                0 8px 20px rgba(15, 118, 55, 0.22),
                0 0 0 1px rgba(25, 230, 107, 0.18);
        }
        .dark .ai-intro-avatar {
            color: #19e66b;
            background: linear-gradient(145deg, rgba(25, 230, 107, 0.28) 0%, rgba(25, 230, 107, 0.1) 100%);
            border-color: rgb(17, 33, 23);
            box-shadow:
                0 8px 20px rgba(0, 0, 0, 0.35),
                0 0 0 1px rgba(25, 230, 107, 0.22);
        }
        .ai-intro-avatar .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }
        .chat-support-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            flex-shrink: 0;
            color: #1f7f4e;
            background: linear-gradient(145deg, rgba(25, 230, 107, 0.38) 0%, rgba(25, 230, 107, 0.12) 100%);
            border: 2px solid rgba(255, 255, 255, 0.95);
            box-shadow:
                0 8px 20px rgba(15, 118, 55, 0.22),
                0 0 0 1px rgba(25, 230, 107, 0.18);
        }
        .dark .chat-support-avatar {
            color: #19e66b;
            background: linear-gradient(145deg, rgba(25, 230, 107, 0.28) 0%, rgba(25, 230, 107, 0.1) 100%);
            border-color: rgb(17, 33, 23);
            box-shadow:
                0 8px 20px rgba(0, 0, 0, 0.35),
                0 0 0 1px rgba(25, 230, 107, 0.22);
        }
        .chat-support-avatar .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
            line-height: 1;
        }
        .ai-quick-actions {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }
        .ai-quick-btn {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            width: 100%;
            padding: 0.9rem 0.95rem;
            border-radius: 1.15rem;
            text-align: left;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(208, 231, 217, 0.95);
            box-shadow: 0 8px 18px rgba(15, 40, 25, 0.04);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease;
        }
        .dark .ai-quick-btn {
            background: rgba(31, 41, 55, 0.9);
            border-color: rgba(75, 85, 99, 0.7);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.18);
        }
        .ai-quick-btn:hover {
            transform: translateY(-1px);
            border-color: rgba(25, 230, 107, 0.55);
            box-shadow: 0 12px 24px rgba(25, 230, 107, 0.12);
            background: #fff;
        }
        .dark .ai-quick-btn:hover {
            background: rgb(38, 50, 43);
        }
        .ai-quick-btn.is-primary {
            background: linear-gradient(135deg, rgba(25, 230, 107, 0.16), rgba(255,255,255,0.95));
            border-color: rgba(25, 230, 107, 0.45);
        }
        .dark .ai-quick-btn.is-primary {
            background: linear-gradient(135deg, rgba(25, 230, 107, 0.18), rgba(31, 41, 55, 0.95));
        }
        .ai-quick-icon {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(25, 230, 107, 0.14);
            color: #1f7f4e;
        }
        .dark .ai-quick-icon {
            color: #8cefb5;
        }
        .ai-quick-btn .ai-quick-arrow {
            margin-left: auto;
            align-self: center;
            color: #4e976b;
            opacity: 0.55;
            transition: transform 0.18s ease, opacity 0.18s ease;
        }
        .ai-quick-btn:hover .ai-quick-arrow {
            opacity: 1;
            transform: translateX(2px);
        }

        .ai-bubble-in,
        .ai-bubble-out {
            animation: aiFadeUp 0.28s ease both;
            max-width: 100%;
            min-width: 0;
        }
        .ai-chat-bubble {
            max-width: min(85%, 100%);
            min-width: 0;
            overflow-wrap: anywhere;
            word-break: break-word;
            white-space: pre-wrap;
        }
        .ai-typing {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            min-height: 1.25rem;
        }
        .ai-typing span {
            width: 0.4rem;
            height: 0.4rem;
            border-radius: 9999px;
            background: #4e976b;
            animation: aiTyping 1.1s infinite ease-in-out;
        }
        .ai-typing span:nth-child(2) { animation-delay: 0.15s; }
        .ai-typing span:nth-child(3) { animation-delay: 0.3s; }
        .dark .ai-typing span { background: #8cefb5; }

        @keyframes aiFadeUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes aiTyping {
            0%, 80%, 100% { opacity: 0.35; transform: translateY(0); }
            40% { opacity: 1; transform: translateY(-2px); }
        }

        @media (max-width: 1023px) {
            .users-bhw-chat-page #usersSidebar { display: none; }
            .users-bhw-chat-page #usersMobileNav { display: none !important; }
            .users-bhw-chat-page #usersContent {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .users-bhw-chat-page .users-bhw-chat-main {
                padding: 0 !important;
            }
            .users-messenger-shell {
                min-height: 0;
                height: 100%;
            }
            .users-messenger-header {
                padding-top: max(0.5rem, env(safe-area-inset-top, 0px));
            }
            .users-messenger-chat-body > .shrink-0 {
                padding-bottom: calc(0.625rem + env(safe-area-inset-bottom, 0px)) !important;
            }
        }

        @media (min-width: 1024px) {
            #usersSidebarToggle {
                z-index: 100;
            }
            .users-bhw-chat-page {
                background: #fff;
            }
            .dark .users-bhw-chat-page {
                background: rgb(17 33 23);
            }
            .users-bhw-chat-page #usersContent {
                background: #fff;
            }
            .dark .users-bhw-chat-page #usersContent {
                background: rgb(17 33 23);
            }
            .users-bhw-chat-page .users-bhw-chat-main {
                padding: 0 !important;
            }
            .users-messenger-shell {
                flex: 1;
                width: 100%;
                max-width: none;
                margin: 0;
                border: none;
                border-radius: 0;
                box-shadow: none;
                min-height: 100%;
            }
            .users-messenger-header {
                padding: 0.875rem 1.25rem;
            }
            .users-messenger-chat-body .rbhw-chat-scroll {
                padding-left: 1.25rem !important;
                padding-right: 1.25rem !important;
                padding-top: 2rem !important;
            }
            .users-messenger-chat-body > .shrink-0 {
                padding: 0.75rem 1.25rem 1rem !important;
            }
            .users-messenger-chat-body form {
                padding: 0.375rem 0.5rem 0.375rem 0.625rem;
            }
            .users-messenger-chat-body button[type="submit"] {
                height: 2.5rem;
                width: 2.5rem;
            }
            .users-messenger-chat-body textarea {
                min-height: 2.5rem !important;
                font-size: 0.9375rem;
            }
            .ai-intro {
                width: min(100%, 24rem);
                margin-top: 1.5rem;
            }
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#19e66b", "background-light": "#f6f8f7", "background-dark": "#112117" },
                    fontFamily: { "display": ["Public Sans", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="users-bhw-chat-page font-display bg-background-light dark:bg-background-dark text-[#0e1b13] dark:text-gray-200">
@php
    $previousUrl = url()->previous();
    $currentUrl = url()->current();
    $chatSupportBackUrl = route('users.main');
    if ($previousUrl && $previousUrl !== $currentUrl) {
        $previousPath = (string) (parse_url($previousUrl, PHP_URL_PATH) ?? '');
        if (str_starts_with($previousPath, '/users') && ! str_contains($previousPath, '/chat-support')) {
            $chatSupportBackUrl = $previousUrl;
        }
    }
    @endphp
    @include('sidebars.users')

<div id="usersContent" data-chat-keyboard-shell class="flex h-[100dvh] h-[100svh] lg:h-screen flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] transition-all duration-200 overflow-hidden">
    <main class="users-bhw-chat-main flex-1 overflow-hidden flex flex-col min-h-0">
        <section class="users-messenger-shell flex-1 min-h-0 flex flex-col overflow-hidden">
            <header class="users-messenger-header shrink-0 flex items-center gap-2.5 px-2 lg:px-5 pb-2.5 lg:py-3 border-b border-[#e7f3ec] dark:border-gray-800">
                <a href="{{ $chatSupportBackUrl }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-[#0e1b13] dark:text-white hover:bg-[#f0f4f1] dark:hover:bg-gray-800 transition shrink-0" aria-label="Go back">
                    <span class="material-symbols-outlined text-[24px]">arrow_back</span>
                </a>
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="chat-support-avatar h-10 w-10 lg:h-11 lg:w-11">
                        <span class="material-symbols-outlined text-[22px] lg:text-[24px]">support_agent</span>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-[15px] lg:text-base leading-tight truncate text-[#0e1b13] dark:text-white">Chat Support</p>
                        <p class="text-xs lg:text-sm text-[#4e976b] dark:text-gray-400 truncate flex items-center gap-1.5 mt-0.5">
                            <span class="inline-block h-2 w-2 rounded-full bg-primary shrink-0 animate-pulse"></span>
                            <span>Online · AI assistant</span>
                        </p>
                    </div>
                </div>
            </header>

            <div class="users-messenger-chat-body flex flex-col flex-1 min-h-0">
                <div id="chatSupportMessages" class="rbhw-chat-scroll chat-bg flex-1 overflow-y-auto space-y-3">
                    <div id="chatSupportIntro" class="ai-intro">
                        <div class="ai-intro-card">
                            <div class="ai-intro-avatar">
                                <span class="material-symbols-outlined text-[28px]">support_agent</span>
                            </div>
                            <p class="text-base font-bold text-[#0e1b13] dark:text-white">Bantay Kalusugan Assistant</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-[#4e976b] dark:text-gray-400">
                                Ask about appointments, records, registration, or general health questions.
                            </p>
                        </div>
                        
                        <div id="chatSupportOptions" class="ai-quick-actions">
                            <button type="button" id="btnHowItWorks" class="ai-quick-btn">
                                <span class="ai-quick-icon">
                                    <span class="material-symbols-outlined text-[20px]">help_outline</span>
                                </span>
                                <span class="min-w-0">
                                    <span class="block font-semibold text-[#1f7f4e] dark:text-[#8cefb5] text-sm">How it works</span>
                                    <span class="block text-[#376f50] dark:text-gray-400 text-xs mt-0.5">Quick guide to using this assistant</span>
                                </span>
                                <span class="material-symbols-outlined text-[18px] ai-quick-arrow">chevron_right</span>
                            </button>
                            <button type="button" id="btnAskHealth" class="ai-quick-btn is-primary">
                                <span class="ai-quick-icon">
                                    <span class="material-symbols-outlined text-[20px]">health_and_safety</span>
                                </span>
                                <span class="min-w-0">
                                    <span class="block font-semibold text-primary text-sm">Ask health related</span>
                                    <span class="block text-[#376f50] dark:text-gray-400 text-xs mt-0.5">Start chatting about your concerns</span>
                                </span>
                                <span class="material-symbols-outlined text-[18px] ai-quick-arrow">chevron_right</span>
                            </button>
                        </div>
                        </div>
                    </div>

                <div class="shrink-0 border-t border-[#e7ede9] dark:border-gray-700">
                    <p id="chatSupportStatus" class="px-3 pt-2 text-xs text-[#4e976b] dark:text-gray-400 empty:hidden"></p>
                    <form id="chatSupportForm" class="m-0 flex items-center">
                        @csrf
                            <label class="sr-only" for="chatSupportInput">Your message</label>
                        <textarea id="chatSupportInput" rows="1" maxlength="1000" wrap="soft" class="flex-1 min-h-[36px] max-h-32 rounded-2xl px-2 py-2 text-sm resize-none text-[#0e1b13] dark:text-gray-100" placeholder="Ask anything about health or the app..." required></textarea>
                        <button type="submit" class="inline-flex items-center justify-center bg-primary text-[#0e1b13] hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition" aria-label="Send">
                            <span class="material-symbols-outlined text-[20px]">send</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
        </main>
    @include('partials.users-mobile-nav')
</div>

    <script>
        (function () {
            const form = document.getElementById('chatSupportForm');
            const input = document.getElementById('chatSupportInput');
            const messages = document.getElementById('chatSupportMessages');
            const status = document.getElementById('chatSupportStatus');
        const intro = document.getElementById('chatSupportIntro');
            const submitButton = form?.querySelector('button[type="submit"]');
        const csrfToken = form?.querySelector('input[name="_token"]')?.value
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const optionsContainer = document.getElementById('chatSupportOptions');
            const btnHowItWorks = document.getElementById('btnHowItWorks');
            const btnAskHealth = document.getElementById('btnAskHealth');

            if (!form || !input || !messages || !submitButton || !csrfToken) {
                return;
            }

        function hideIntro() {
            if (intro && !intro.classList.contains('hidden')) {
                intro.classList.add('hidden');
            }
            if (optionsContainer) {
                optionsContainer.style.display = 'none';
            }
            }

            function appendBubble(text, fromUser = false) {
                const container = document.createElement('div');
            container.className = fromUser
                ? 'flex items-end gap-2 justify-end ai-bubble-out'
                : 'flex items-end gap-2 justify-start ai-bubble-in';
                
                if (!fromUser) {
                    const avatar = document.createElement('div');
                    avatar.className = 'chat-support-avatar w-8 h-8';
                    avatar.innerHTML = '<span class="material-symbols-outlined text-base">support_agent</span>';
                    container.appendChild(avatar);
                }
                
                const bubble = document.createElement('div');
            bubble.className = fromUser
                ? 'ai-chat-bubble rounded-2xl rounded-br-md bg-primary px-4 py-2.5 text-sm text-[#0e1b13] leading-relaxed shadow-sm'
                : 'ai-chat-bubble rounded-2xl rounded-bl-md border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-[#0e1b13] dark:text-gray-200 leading-relaxed shadow-sm';
                bubble.textContent = text;
                container.appendChild(bubble);
                messages.appendChild(container);
                messages.scrollTop = messages.scrollHeight;
            return container;
        }

        function appendThinkingSkeleton() {
            const container = document.createElement('div');
            container.id = 'chatSupportThinking';
            container.className = 'flex items-end gap-2 justify-start ai-bubble-in';
            container.setAttribute('aria-label', 'Assistant is responding');

            const avatar = document.createElement('div');
            avatar.className = 'chat-support-avatar w-8 h-8';
            avatar.innerHTML = '<span class="material-symbols-outlined text-base">support_agent</span>';

            const bubble = document.createElement('div');
            bubble.className = 'rounded-2xl rounded-bl-md border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 shadow-sm';
            bubble.innerHTML = '<div class="ai-typing"><span></span><span></span><span></span></div>';

            container.appendChild(avatar);
            container.appendChild(bubble);
            messages.appendChild(container);
            messages.scrollTop = messages.scrollHeight;
            return container;
        }

        function removeThinkingSkeleton() {
            document.getElementById('chatSupportThinking')?.remove();
        }

        function getComposerMinHeight() {
            const minHeight = parseFloat(window.getComputedStyle(input).minHeight);
            return Number.isFinite(minHeight) && minHeight > 0 ? minHeight : 36;
        }

        function autoResizeInput() {
            const minHeight = getComposerMinHeight();
            const maxHeight = 128;
            input.style.removeProperty('height');
            input.style.height = 'auto';
            const next = Math.min(Math.max(input.scrollHeight, minHeight), maxHeight);
            input.style.setProperty('height', `${next}px`, 'important');
            input.style.overflowY = next >= maxHeight ? 'auto' : 'hidden';
        }

        function resetComposerHeight() {
            const minHeight = getComposerMinHeight();
            input.style.setProperty('height', `${minHeight}px`, 'important');
            input.style.overflowY = 'hidden';
            input.scrollTop = 0;
        }

        function collapseComposerPreview() {
            const minHeight = getComposerMinHeight();
            input.style.setProperty('height', `${minHeight}px`, 'important');
            input.style.overflowY = 'auto';
            input.scrollTop = input.scrollHeight;
            window.requestAnimationFrame(() => {
                input.scrollTop = input.scrollHeight;
            });
        }

        function expandComposerEditing() {
            if (!(input.value || '').trim()) {
                resetComposerHeight();
                return;
            }
            autoResizeInput();
            input.scrollTop = 0;
        }

        function scrollMessagesToBottom() {
            if (window.ChatKeyboard?.scrollToBottom) {
                window.ChatKeyboard.scrollToBottom();
            }
            messages.scrollTop = messages.scrollHeight;
            window.requestAnimationFrame(() => {
                messages.scrollTop = messages.scrollHeight;
            });
        }

        input.addEventListener('input', () => {
            autoResizeInput();
            input.scrollTop = input.scrollHeight;
            window.ChatKeyboard?.sync?.();
            scrollMessagesToBottom();
        });

        input.addEventListener('focus', () => {
            window.ChatKeyboard?.sync?.();
            window.requestAnimationFrame(() => {
                expandComposerEditing();
                scrollMessagesToBottom();
            });
            window.setTimeout(scrollMessagesToBottom, 80);
            window.setTimeout(scrollMessagesToBottom, 250);
            window.setTimeout(scrollMessagesToBottom, 400);
        });

        input.addEventListener('blur', () => {
            if ((input.value || '').trim()) {
                collapseComposerPreview();
            } else {
                resetComposerHeight();
            }
            scrollMessagesToBottom();
            window.setTimeout(() => {
                if ((input.value || '').trim()) {
                    collapseComposerPreview();
                } else {
                    resetComposerHeight();
                }
                window.ChatKeyboard?.sync?.();
                scrollMessagesToBottom();
            }, 150);
        });

        let lastViewportHeight = window.visualViewport?.height ?? window.innerHeight;
        const onViewportChange = () => {
            const currentHeight = window.visualViewport?.height ?? window.innerHeight;
            const keyboardClosed = currentHeight > lastViewportHeight + 60;
            const keyboardOpened = currentHeight < lastViewportHeight - 60;
            lastViewportHeight = currentHeight;

            window.ChatKeyboard?.sync?.();

            if (document.activeElement === input && keyboardOpened) {
                expandComposerEditing();
            }
            if (keyboardClosed && document.activeElement !== input) {
                if ((input.value || '').trim()) {
                    collapseComposerPreview();
                } else {
                    resetComposerHeight();
                }
            }
            scrollMessagesToBottom();
        };

        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', onViewportChange);
            window.visualViewport.addEventListener('scroll', onViewportChange);
        } else {
            window.addEventListener('resize', onViewportChange);
        }

        resetComposerHeight();

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                form.requestSubmit();
            }
        });

            btnHowItWorks?.addEventListener('click', (e) => {
                e.preventDefault();
                appendBubble('How it works?', true);
            hideIntro();
            appendBubble(
                'Step 1: Ask anything about health-related issues, appointments, registration, notifications, or medical records\n'
                + 'Step 2: Review the response and suggested next steps\n'
                + 'Step 3: Follow up with your barangay health worker if you need personal care or urgent help\n\n'
                + 'Note: This assistant is for general health and app guidance only. It is not a doctor and cannot diagnose, prescribe, or handle emergencies.',
                false
            );
            input.placeholder = 'Aa';
            input.focus();
            });

            btnAskHealth?.addEventListener('click', (e) => {
                e.preventDefault();
            appendBubble('Ask health related', true);
            hideIntro();
            appendBubble(
                'You can ask about general health concerns, appointments, registration, notifications, and medical records.\n\n'
                + 'Please keep questions within health and barangay health app topics only. '
                + 'I cannot diagnose illnesses, prescribe medicine, or replace emergency care. '
                + 'For severe symptoms or emergencies, contact local emergency services or your barangay health worker right away.\n\n'
                + 'What would you like to ask?',
                false
            );
            input.placeholder = 'Aa';
            input.focus();
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

            const message = input.value.trim().slice(0, 1000);
                if (!message) {
                    return;
                }

            if (/ignore (all |any )?(previous|prior|above) (instructions|rules|prompts)/i.test(message)
                || /system prompt:/i.test(message)
                || /reveal (your )?(system|hidden|developer) (prompt|instructions)/i.test(message)) {
                status.textContent = 'That message looks invalid. Please ask a normal health or app question.';
                return;
            }

            hideIntro();
            input.placeholder = 'Aa';

                appendBubble(message, true);
                input.value = '';
            resetComposerHeight();
            status.textContent = '';
            removeThinkingSkeleton();
            appendThinkingSkeleton();
                submitButton.disabled = true;
                input.disabled = true;

                try {
                    const response = await fetch('{{ route('users.profile.chat-support.send') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ message }),
                    });

                    const data = await response.json().catch(() => ({}));
                removeThinkingSkeleton();

                    if (!response.ok) {
                        const errorMessage = data?.message || data?.errors?.message?.[0] || 'Unable to send message right now.';
                        appendBubble(errorMessage, false);
                        return;
                    }

                    appendBubble(data.message || 'No reply received.', false);
                } catch (error) {
                removeThinkingSkeleton();
                    appendBubble('The chat service is unavailable right now.', false);
                } finally {
                    submitButton.disabled = false;
                    input.disabled = false;
                resetComposerHeight();
                if (window.matchMedia('(max-width: 1023px)').matches) {
                    input.blur();
                    scrollMessagesToBottom();
                } else {
                    input.focus({ preventScroll: true });
                }
                }
            });
        })();
    </script>

    <script>
        (function () {
            const sidebar = document.getElementById('usersSidebar');
            const content = document.getElementById('usersContent');
            const toggleButton = document.getElementById('usersSidebarToggle');
            const hamburgerIcon = document.getElementById('hamburgerIcon');
            const closeIcon = document.getElementById('closeIcon');

            if (!sidebar || !content || !toggleButton || !hamburgerIcon || !closeIcon) {
                return;
            }

            const storageKey = 'usersSidebarCollapsed';

            function applySidebarState(collapsed) {
                if (collapsed) {
                    sidebar.classList.add('is-collapsed');
                    content.classList.remove('lg:ml-64');
                    content.classList.remove('lg:w-[calc(100%-16rem)]');
                    content.classList.add('lg:ml-20');
                    content.classList.add('lg:w-[calc(100%-5rem)]');
                    toggleButton.style.left = '5rem';
                    hamburgerIcon.style.transform = 'scale(1) rotate(0deg)';
                    hamburgerIcon.style.opacity = '1';
                    closeIcon.style.transform = 'scale(0) rotate(-90deg)';
                    closeIcon.style.opacity = '0';
                } else {
                    sidebar.classList.remove('is-collapsed');
                    content.classList.remove('lg:ml-20');
                    content.classList.remove('lg:w-[calc(100%-5rem)]');
                    content.classList.add('lg:ml-64');
                    content.classList.add('lg:w-[calc(100%-16rem)]');
                    toggleButton.style.left = '16rem';
                    hamburgerIcon.style.transform = 'scale(0) rotate(90deg)';
                    hamburgerIcon.style.opacity = '0';
                    closeIcon.style.transform = 'scale(1) rotate(0deg)';
                    closeIcon.style.opacity = '1';
                }
            }

        applySidebarState(localStorage.getItem(storageKey) === '1');

            toggleButton.addEventListener('click', () => {
                const nextCollapsed = !sidebar.classList.contains('is-collapsed');
                applySidebarState(nextCollapsed);
                localStorage.setItem(storageKey, nextCollapsed ? '1' : '0');
            });
        })();
    </script>
@include('partials.mobile-chat-keyboard')
</body>
</html>
