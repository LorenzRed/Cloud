<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Bantay Kalusugan - Resident Portal</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <style>
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24;
        }

        .dropdown-container {
            position: relative;
            display: inline-block;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            width: 190px;
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            z-index: 20;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
            overflow: hidden;
        }

        .dark .dropdown-menu {
            background-color: #1f2937;
        }

        .dropdown-container:focus-within .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            width: 100%;
            text-decoration: none;
            color: #374151;
            transition: background-color 0.2s;
        }

        .dark .dropdown-item {
            color: #d1d5db;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: #f3f4f6;
            outline: none;
        }

        .dark .dropdown-item:hover,
        .dark .dropdown-item:focus {
            background-color: #374151;
        }

        .dropdown-divider {
            height: 1px;
            background-color: #e5e7eb;
        }

        .dark .dropdown-divider {
            background-color: #4b5563;
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
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-[#0e1b13] dark:text-gray-200">
    @include('partials.app-flash')
    @php
        $user = Auth::user();
        $displayName = $user->full_name ?: $user->name;
        $emailLocal = strstr($user->email, '@', true) ?: $user->email;
        $initial = strtoupper(substr($displayName ?: $emailLocal, 0, 1));
        $residentId = 'RES-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT);
        $profilePicturePath = $user->profile_picture_path;
        $status = $verificationStatus ?? 'unverified';
        $statusLabel = match ($status) {
            'accepted' => 'Verified Account',
            'pending' => 'Pending Verification',
            'rejected' => 'Not Verified',
            default => 'Unverified',
        };
        $statusIcon = match ($status) {
            'accepted' => 'verified',
            'pending' => 'schedule',
            'rejected' => 'gpp_bad',
            default => 'help',
        };
        $statusClass = match ($status) {
            'accepted' => 'bg-[#e7f3ec] dark:bg-green-900/50 text-[#28a745] dark:text-green-300',
            'pending' => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300',
            'rejected' => 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300',
            default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300',
        };

        $computedAge = $user->date_of_birth ? \Illuminate\Support\Carbon::parse($user->date_of_birth)->age : null;
        $displayAge = $computedAge ?? 'N/A';

        $municipality = filled($user->municipality) ? trim((string) $user->municipality) : null;
        $province = filled($user->province) ? trim((string) $user->province) : null;

        $hasDetailedAddress = filled($user->household_no) || filled($municipality) || filled($province);

        if ($hasDetailedAddress) {
            $addressSegments = array_values(array_filter([
                filled($user->household_no) ? $user->household_no : null,
                $user->barangay ?: null,
                $municipality,
                $province,
            ]));
            $displayAddress = !empty($addressSegments) ? implode(', ', $addressSegments) : 'N/A';
        } else {
            $displayAddress = $user->barangay ?: 'N/A';
        }
    @endphp

    <div class="relative flex min-h-screen w-full flex-col overflow-hidden">
        <header class="sticky top-0 z-10 flex items-center justify-between border-b border-[#e7f3ec] dark:border-gray-700 bg-white/90 dark:bg-gray-800/90 backdrop-blur px-6 md:px-8 py-4">
            <div class="flex items-center gap-3 text-[#0e1b13] dark:text-white">
                <img src="{{ asset('logo/logo.png') }}" alt="Bantay Kalusugan logo" class="h-7 w-auto object-contain" />
                <div>
                    <h1 class="text-lg font-bold leading-tight">Bantay Kalusugan</h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('users.notifications') }}" class="relative flex items-center justify-center rounded-lg size-10 bg-[#e7f3ec] dark:bg-gray-700 text-[#0e1b13] dark:text-white" aria-label="Open notifications">
                    <span class="material-symbols-outlined text-xl">notifications</span>
                    @if (($unreadInboxCount ?? 0) > 0)
                        <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">{{ ($unreadInboxCount ?? 0) > 99 ? '99+' : $unreadInboxCount }}</span>
                    @endif
                </a>

                <div class="dropdown-container">
                    <button class="flex items-center justify-center rounded-lg size-10 bg-[#e7f3ec] dark:bg-gray-700 text-[#0e1b13] dark:text-white" type="button" tabindex="0" aria-haspopup="true" aria-expanded="false" aria-label="Open menu">
                        <span class="material-symbols-outlined text-xl">settings</span>
                    </button>
                    <div class="dropdown-menu" role="menu" aria-label="User menu">
                        <a href="{{ route('users.profile') }}" class="dropdown-item" role="menuitem" tabindex="-1">
                            <span class="material-symbols-outlined text-lg">person</span>
                            <span>Profile</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-left" role="menuitem" tabindex="-1">
                                <span class="material-symbols-outlined text-lg">logout</span>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-6 md:px-8 py-8 overflow-y-auto">
            <div class="mx-auto w-full max-w-7xl grid grid-cols-1 lg:grid-cols-12 gap-8">
                <section class="lg:col-span-4 space-y-5">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-[#e7f3ec] dark:border-gray-700">
                        <div class="flex flex-col items-center text-center gap-4">
                            @if ($profilePicturePath)
                                <img
                                    src="{{ route('profile.picture', ['user' => $user->id, 'v' => optional($user->updated_at)->timestamp]) }}"
                                    alt="Profile picture"
                                    class="size-24 rounded-full object-cover border border-[#d0e7d9] dark:border-gray-700"
                                />
                            @else
                                <div class="size-24 rounded-full bg-primary/15 text-primary flex items-center justify-center text-3xl font-bold">{{ $initial }}</div>
                            @endif
                            <div>
                                <p class="text-xl font-bold text-[#0e1b13] dark:text-white">{{ $displayName }}</p>
                                <p class="text-sm text-[#4e976b] dark:text-gray-400">{{ $residentId }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">
                                <span class="material-symbols-outlined text-base">{{ $statusIcon }}</span>
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="mt-6 space-y-2.5 text-sm">
                            <div class="flex items-center justify-between border-t border-[#d0e7d9] dark:border-gray-700 pt-3">
                                <span class="text-[#4e976b] dark:text-gray-400">Email</span>
                                <span class="font-medium text-right">{{ $user->email }}</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-[#d0e7d9] dark:border-gray-700 pt-3">
                                <span class="text-[#4e976b] dark:text-gray-400">Age</span>
                                <span class="font-medium">{{ $displayAge }}</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-[#d0e7d9] dark:border-gray-700 pt-3">
                                <span class="text-[#4e976b] dark:text-gray-400">Gender</span>
                                <span class="font-medium">{{ $user->gender ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-[#d0e7d9] dark:border-gray-700 pt-3">
                                <span class="text-[#4e976b] dark:text-gray-400">Contact</span>
                                <span class="font-medium">{{ $user->contact_number ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-[#d0e7d9] dark:border-gray-700 pt-3">
                                <span class="text-[#4e976b] dark:text-gray-400">Address</span>
                                <span class="font-medium text-right">{{ $displayAddress }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-[#e7f3ec] to-white dark:from-gray-800 dark:to-gray-800 rounded-xl p-5 border border-[#d0e7d9] dark:border-gray-700">
                        <h3 class="font-bold mb-2">Health Tip</h3>
                        <p class="text-sm text-[#376f50] dark:text-gray-300">Stay hydrated, complete your vaccinations, and keep your check-up schedule updated.</p>
                    </div>
                </section>

                <section class="lg:col-span-8 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-[#e7f3ec] dark:border-gray-700 shadow-sm">
                            <p class="text-sm text-[#4e976b] dark:text-gray-400">Profile Status</p>
                            <p class="text-2xl font-bold mt-1">{{ $statusLabel }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-[#e7f3ec] dark:border-gray-700 shadow-sm">
                            <p class="text-sm text-[#4e976b] dark:text-gray-400">Next Check-up</p>
                            <p class="text-2xl font-bold mt-1">{{ $nextCheckupLabel ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-[#e7f3ec] dark:border-gray-700 shadow-sm">
                            <p class="text-sm text-[#4e976b] dark:text-gray-400">Vaccination</p>
                            <p class="text-2xl font-bold mt-1">Updated</p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-[#e7f3ec] dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold">Upcoming Schedule</h3>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-primary/20 text-[#0e1b13] dark:text-white">This Month</span>
                        </div>
                        <div class="space-y-3">
                            @forelse (($upcomingConsultations ?? collect()) as $consultation)
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-[#f2fbf6] dark:bg-gray-700/50">
                                    <span class="material-symbols-outlined text-primary">event</span>
                                    <div class="flex-1">
                                        <p class="font-semibold">{{ $consultation->medical_checkup ?: 'General Consultation' }}</p>
                                        <p class="text-sm text-[#4e976b] dark:text-gray-400">{{ $consultation->scheduled_at?->format('M d, Y · h:i A') }}</p>
                                        @if ($consultation->note)
                                            <p class="text-xs text-[#4e976b] dark:text-gray-400 mt-1">{{ $consultation->note }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-lg border border-[#d0e7d9] dark:border-gray-700 p-4 text-sm text-[#4e976b] dark:text-gray-400">
                                    No upcoming appointments yet.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-[#e7f3ec] dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold">Medical Summary</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="p-3 rounded-lg border border-[#d0e7d9] dark:border-gray-700">
                                <p class="text-[#4e976b] dark:text-gray-400">Last Check-up</p>
                                <p class="font-semibold mt-1">{{ $lastCheckupLabel ?? 'N/A' }}</p>
                            </div>
                            @forelse (($medicalSummaryRecords ?? collect()) as $record)
                                <div class="p-3 rounded-lg border border-[#d0e7d9] dark:border-gray-700">
                                    <p class="text-[#4e976b] dark:text-gray-400">{{ $record->record_type }}</p>
                                    <p class="font-semibold mt-1">{{ $record->record_value }}</p>
                                </div>
                            @empty
                                <div class="sm:col-span-2 p-3 rounded-lg border border-[#d0e7d9] dark:border-gray-700 text-[#4e976b] dark:text-gray-400">
                                    No medical records yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div class="fixed right-4 bottom-6 z-30">
            <button id="chatbotToggle" type="button" class="inline-flex items-center gap-2 h-11 px-4 rounded-full bg-primary text-[#0e1b13] text-sm font-bold shadow-sm hover:opacity-90">
                <span class="material-symbols-outlined text-lg">smart_toy</span>
                Health Chat
            </button>

            <div id="chatbotPanel" class="hidden mt-3 w-[320px] max-w-[90vw] rounded-xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-[#e7f3ec] dark:border-gray-700">
                    <div>
                        <p class="font-semibold">Bantay Assistant</p>
                        <p class="text-xs text-[#4e976b] dark:text-gray-400">Ask about appointments and records</p>
                    </div>
                    <button id="chatbotClose" type="button" class="inline-flex items-center justify-center size-7 rounded-md hover:bg-[#f2fbf6] dark:hover:bg-gray-700" aria-label="Close chatbot">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>

                <div id="chatbotMessages" class="h-72 overflow-y-auto px-3 py-3 space-y-2 bg-[#f6fbf8] dark:bg-gray-900/40">
                    <div class="max-w-[85%] rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-[#d0e7d9] dark:border-gray-700">
                        Hi! I can help with your appointments, verification status, and medical records.
                    </div>
                </div>

                <form id="chatbotForm" class="p-3 border-t border-[#e7f3ec] dark:border-gray-700 flex items-center gap-2">
                    <input id="chatbotInput" type="text" class="flex-1 h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Type a message..." maxlength="300" required />
                    <button type="submit" class="inline-flex items-center justify-center h-10 px-3 rounded-lg bg-primary text-[#0e1b13] text-sm font-bold">
                        <span class="material-symbols-outlined text-base">send</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function () {
            window.history.pushState(null, "", window.location.href);
        };

        const chatbotToggle = document.getElementById('chatbotToggle');
        const chatbotPanel = document.getElementById('chatbotPanel');
        const chatbotClose = document.getElementById('chatbotClose');
        const chatbotForm = document.getElementById('chatbotForm');
        const chatbotInput = document.getElementById('chatbotInput');
        const chatbotMessages = document.getElementById('chatbotMessages');

        chatbotToggle?.addEventListener('click', () => {
            chatbotPanel?.classList.toggle('hidden');
            const isOpen = !chatbotPanel?.classList.contains('hidden');

            if (isOpen) {
                chatbotToggle.classList.add('hidden');
                chatbotInput?.focus();
            }
        });

        chatbotClose?.addEventListener('click', () => {
            chatbotPanel?.classList.add('hidden');
            chatbotToggle?.classList.remove('hidden');
        });

        function appendMessage(text, fromUser = false) {
            if (!chatbotMessages) {
                return;
            }

            const item = document.createElement('div');
            item.className = `max-w-[85%] rounded-lg px-3 py-2 text-sm ${fromUser
                ? 'ml-auto bg-primary text-[#0e1b13]'
                : 'bg-white dark:bg-gray-800 border border-[#d0e7d9] dark:border-gray-700'}`;
            item.textContent = text;
            chatbotMessages.appendChild(item);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }

        function getBotReply(message) {
            const text = message.toLowerCase();

            if (text.includes('appointment') || text.includes('schedule') || text.includes('checkup')) {
                return 'Your upcoming appointments are shown in the Upcoming Schedule card. You can also check Notifications for new BHW updates.';
            }

            if (text.includes('medical') || text.includes('record') || text.includes('height') || text.includes('weight') || text.includes('bmi')) {
                return 'Your Medical Summary is based on records added by BHW. New records appear here automatically after they are added.';
            }

            if (text.includes('verify') || text.includes('verification') || text.includes('status')) {
                return 'Your verification status is shown on your profile card. If changes are made by BHW, it updates in your account.';
            }

            if (text.includes('notification') || text.includes('inbox') || text.includes('message')) {
                return 'Tap the bell icon on the top-right to open Notifications and read all messages from BHW.';
            }

            return 'I can help with appointments, medical records, verification status, and notifications.';
        }

        chatbotForm?.addEventListener('submit', (event) => {
            event.preventDefault();

            const message = chatbotInput?.value.trim();
            if (!message) {
                return;
            }

            appendMessage(message, true);
            chatbotInput.value = '';

            setTimeout(() => {
                appendMessage(getBotReply(message), false);
            }, 250);
        });
    </script>
</body>
</html>
