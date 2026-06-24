<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Bantay Kalusugan - Chat Support</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <style>
        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        #usersSidebar {
            transition: width 0.25s ease;
        }

        @media (min-width: 1024px) {
            #usersSidebar.is-collapsed {
                width: 5rem;
            }

            #usersSidebar.is-collapsed .sidebar-label,
            #usersSidebar.is-collapsed .sidebar-brand-text {
                display: none;
            }

            #usersSidebar.is-collapsed .sidebar-link,
            #usersSidebar.is-collapsed .sidebar-logout {
                justify-content: center;
            }

            #usersSidebar.is-collapsed .sidebar-header-inner {
                justify-content: center;
            }
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#19e66b",
                        "background-light": "#f6f8f7",
                        "background-dark": "#112117",
                    },
                    fontFamily: {
                        "display": ["Public Sans", "sans-serif"]
                    },
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-[#0e1b13] dark:text-gray-200">
    @php
        $isDashboard = request()->routeIs('users.main');
        $isEvents = request()->routeIs('users.events');
        $isAnnouncements = request()->routeIs('users.announcements');
        $isProfile = request()->routeIs('users.profile*');
        $isNotifications = request()->routeIs('users.notifications');
        $isMedicalRecords = request()->routeIs('users.medical-records');
    @endphp
    @include('sidebars.users')

    <div id="usersContent" class="flex h-screen flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] transition-all duration-200 overflow-hidden">
        <main class="flex-1 px-4 sm:px-5 md:px-6 lg:px-7 pt-4 md:pt-6 pb-24 md:pb-6 overflow-hidden">
            <div class="w-full max-w-none space-y-6">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-1">
                        <h1 class="mt-1 text-2xl font-bold text-[#0e1b13] dark:text-white">Chat Support</h1>
                        <span class="text-sm font-bold text-primary bg-primary/10 px-2 py-1 rounded">AI</span>
                    </div>
                    <a href="{{ route('users.profile') }}" class="inline-flex items-center gap-1 rounded-lg border border-[#d0e7d9] dark:border-gray-700 px-3 py-2 text-sm font-semibold text-[#1f7f4e] dark:text-gray-200">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Back
                    </a>
                </div>

                <section class="rounded-2xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm p-5 md:p-6 flex flex-col h-[calc(100vh-8.5rem)] overflow-hidden">
                    <div id="chatSupportMessages" class="mt-4 h-[420px] overflow-y-auto rounded-xl border border-[#e7f3ec] dark:border-gray-700 bg-white dark:bg-gray-900/40 p-4 space-y-3">
                        <div class="flex items-start gap-2">
                            <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="material-symbols-outlined text-base text-primary">smart_toy</span>
                            </div>
                            <div class="max-w-[85%] rounded-2xl rounded-tl-sm border border-[#d0e7d9] bg-[#f7fbf8] px-4 py-3 text-sm text-[#376f50] dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                Hi, I'm your Bantay Kalusugan assistant. How can I help you today?
                            </div>
                        </div>
                        
                        <div id="chatSupportOptions" class="flex flex-col gap-2 mt-4 pt-2">
                            <button type="button" id="btnHowItWorks" class="flex items-start gap-3 p-3 rounded-lg border border-[#d0e7d9] dark:border-gray-700 hover:bg-[#f0f7f2] dark:hover:bg-gray-700/50 text-left transition">
                                <span class="material-symbols-outlined text-[20px] text-primary flex-shrink-0 mt-0.5">help_outline</span>
                                <div>
                                    <p class="font-semibold text-[#1f7f4e] dark:text-[#8cefb5] text-sm">How it works?</p>
                                    <p class="text-[#376f50] dark:text-gray-400 text-xs">Learn how to use this assistant</p>
                                </div>
                            </button>
                            <button type="button" id="btnEmergency" class="flex items-start gap-3 p-3 rounded-lg border border-[#d0e7d9] dark:border-gray-700 hover:bg-[#f0f7f2] dark:hover:bg-gray-700/50 text-left transition">
                                <span class="material-symbols-outlined text-[20px] text-red-500 flex-shrink-0 mt-0.5">emergency</span>
                                <div>
                                    <p class="font-semibold text-[#1f7f4e] dark:text-[#8cefb5] text-sm">Emergency</p>
                                    <p class="text-[#376f50] dark:text-gray-400 text-xs">Get emergency contacts and help</p>
                                </div>
                            </button>
                            <button type="button" id="btnAskHealth" class="flex items-start gap-3 p-3 rounded-lg border border-primary bg-primary/5 dark:bg-gray-700/50 hover:bg-primary/10 dark:hover:bg-gray-700 text-left transition">
                                <span class="material-symbols-outlined text-[20px] text-primary flex-shrink-0 mt-0.5">health_and_safety</span>
                                <div>
                                    <p class="font-semibold text-primary text-sm">Ask health related</p>
                                    <p class="text-[#376f50] dark:text-gray-400 text-xs">Ask about your health concerns</p>
                                </div>
                            </button>
                        </div>
                    </div>

                    <form id="chatSupportForm" class="mt-4 flex items-end gap-2">
                        @csrf
                        <div class="flex-1">
                            <label class="sr-only" for="chatSupportInput">Your message</label>
                            <textarea id="chatSupportInput" rows="2" class="w-full rounded-xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-900/60 px-4 py-3 text-sm text-[#0e1b13] dark:text-gray-100 focus:border-primary focus:ring-2 focus:ring-primary/30 resize-none disabled:cursor-not-allowed disabled:opacity-60" placeholder="Ask health related questions..." disabled required></textarea>
                        </div>
                        <button type="submit" class="inline-flex h-[52px] items-center gap-2 rounded-xl bg-primary px-4 text-sm font-semibold text-[#0e1b13] hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <span class="material-symbols-outlined text-[18px]">send</span>
                            Send
                        </button>
                    </form>

                    <p id="chatSupportStatus" class="mt-3 text-xs text-[#4e976b] dark:text-gray-400"></p>
                </section>
            </div>
        </main>
    <script>
        (function () {
            const form = document.getElementById('chatSupportForm');
            const input = document.getElementById('chatSupportInput');
            const messages = document.getElementById('chatSupportMessages');
            const status = document.getElementById('chatSupportStatus');
            const submitButton = form?.querySelector('button[type="submit"]');
            const csrfToken = form?.querySelector('input[name="_token"]')?.value;
            const optionsContainer = document.getElementById('chatSupportOptions');
            const btnHowItWorks = document.getElementById('btnHowItWorks');
            const btnEmergency = document.getElementById('btnEmergency');
            const btnAskHealth = document.getElementById('btnAskHealth');

            if (!form || !input || !messages || !submitButton || !csrfToken) {
                return;
            }

            function enableChat() {
                if (optionsContainer) optionsContainer.style.display = 'none';
                form.classList.remove('hidden');
                input.disabled = false;
                submitButton.disabled = false;
                input.focus();
            }

            function moveOptionsToBottom() {
                if (!optionsContainer) {
                    return;
                }

                optionsContainer.style.display = 'flex';
                messages.appendChild(optionsContainer);
                messages.scrollTop = messages.scrollHeight;
            }

            function appendBubble(text, fromUser = false) {
                const container = document.createElement('div');
                container.className = fromUser ? 'flex justify-end' : 'flex items-start gap-2';
                
                if (!fromUser) {
                    const avatar = document.createElement('div');
                    avatar.className = 'w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center flex-shrink-0 mt-0.5';
                    avatar.innerHTML = '<span class="material-symbols-outlined text-base text-primary">smart_toy</span>';
                    container.appendChild(avatar);
                }
                
                const bubble = document.createElement('div');
                bubble.className = `max-w-[85%] rounded-2xl px-4 py-3 text-sm ${fromUser
                    ? 'rounded-tr-sm bg-primary text-[#0e1b13]'
                    : 'rounded-tl-sm border border-[#d0e7d9] bg-[#f7fbf8] text-[#376f50] dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200'}`;
                bubble.textContent = text;
                container.appendChild(bubble);
                messages.appendChild(container);
                messages.scrollTop = messages.scrollHeight;
            }

            btnHowItWorks?.addEventListener('click', (e) => {
                e.preventDefault();
                appendBubble('How it works?', true);
                appendBubble('Step 1: Open the chat and type your health question\nStep 2: Review the response and suggested steps\nStep 3: Follow up with your barangay health worker if needed', false);
                setTimeout(() => moveOptionsToBottom(), 300);
            });

            btnEmergency?.addEventListener('click', (e) => {
                e.preventDefault();
                appendBubble('Emergency', true);
                appendBubble('For medical emergencies, please call 911 or visit the nearest hospital immediately. Our assistant is for general health inquiries only.', false);
                setTimeout(() => moveOptionsToBottom(), 300);
            });

            btnAskHealth?.addEventListener('click', (e) => {
                e.preventDefault();
                enableChat();
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const message = input.value.trim();
                if (!message) {
                    return;
                }

                appendBubble(message, true);
                input.value = '';
                status.textContent = 'Thinking...';
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

                    if (!response.ok) {
                        const errorMessage = data?.message || data?.errors?.message?.[0] || 'Unable to send message right now.';
                        appendBubble(errorMessage, false);
                        status.textContent = '';
                        return;
                    }

                    appendBubble(data.message || 'No reply received.', false);
                    status.textContent = '';
                } catch (error) {
                    appendBubble('The chat service is unavailable right now.', false);
                    status.textContent = '';
                } finally {
                    submitButton.disabled = false;
                    input.disabled = false;
                    input.focus();
                }
            });
        })();
    </script>

        <nav class="lg:hidden fixed inset-x-0 bottom-0 z-40 border-t border-[#d0e7d9] bg-white/95 backdrop-blur dark:border-gray-700 dark:bg-gray-800/95" aria-label="Mobile navigation">
            <div class="grid grid-cols-5 gap-1 px-2 py-2">
                <a href="{{ route('users.main') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[11px] font-semibold {{ $isDashboard ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
                    <span class="material-symbols-outlined text-[20px]">home</span>
                    Home
                </a>
                <a href="{{ route('users.events') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[11px] font-semibold {{ $isEvents ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
                    <span class="material-symbols-outlined text-[20px]">event</span>
                    Events
                </a>
                <a href="{{ route('users.announcements') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[11px] font-semibold {{ $isAnnouncements ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
                    <span class="material-symbols-outlined text-[20px]">campaign</span>
                    News
                </a>
                <a href="{{ route('users.medical-records') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[11px] font-semibold {{ $isMedicalRecords ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
                    <span class="material-symbols-outlined text-[20px]">health_metrics</span>
                    Medical
                </a>
                <a href="{{ route('users.profile') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[11px] font-semibold {{ $isProfile ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
                    <span class="material-symbols-outlined text-[20px]">person</span>
                    Profile
                </a>
            </div>
        </nav>
    </div>

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

            const isCollapsed = localStorage.getItem(storageKey) === '1';
            applySidebarState(isCollapsed);

            toggleButton.addEventListener('click', () => {
                const nextCollapsed = !sidebar.classList.contains('is-collapsed');
                applySidebarState(nextCollapsed);
                localStorage.setItem(storageKey, nextCollapsed ? '1' : '0');
            });
        })();
    </script>
</body>
</html>
