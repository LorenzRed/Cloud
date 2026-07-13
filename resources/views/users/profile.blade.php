<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    @include('partials.users-viewport')
    <title>Bantay Kalusugan - Profile</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <style>
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
        $isProfile = request()->routeIs('users.profile');
        $isNotifications = request()->routeIs('users.notifications');
        $isMedicalRecords = request()->routeIs('users.medical-records');
    @endphp
    @include('sidebars.users')

    <div id="usersContent" class="flex min-h-screen flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] transition-all duration-200">
        <main class="flex-1 px-4 sm:px-5 md:px-6 lg:px-7 pt-4 md:pt-6 pb-24 md:pb-6 overflow-y-auto">
            <div class="w-full max-w-none space-y-6">
            <div class="flex justify-center">
                <div class="flex flex-col items-center text-center gap-2">
                    <div class="relative mt-14 mb-2 h-28 w-28 overflow-visible">
                        <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-full border border-[#d0e7d9] bg-primary/15 dark:border-gray-700">
                            @if ($user->profile_picture_path)
                                <img src="{{ route('profile.picture', ['user' => $user->id, 'v' => optional($user->updated_at)->timestamp]) }}" alt="Profile picture" class="h-full w-full rounded-full object-cover" />
                            @else
                                <span class="text-4xl font-bold text-primary">{{ strtoupper(substr($user->full_name ?: $user->name ?: 'U', 0, 1)) }}</span>
                            @endif
                        </div>

                        <button type="button" id="avatarEditBtn" class="absolute -bottom-1 -right-1 inline-flex h-10 w-10 items-center justify-center rounded-full bg-primary text-white border border-[#c4e8d1] dark:border-gray-700" aria-label="Edit profile">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                        </button>

                        <form id="avatarUploadForm" action="{{ route('users.profile.update') }}" method="POST" enctype="multipart/form-data" class="hidden">
                            @csrf
                            <input id="avatarFileInput" type="file" name="profile_picture" accept="image/*" class="hidden" />
                        </form>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-[#0e1b13] dark:text-white">{{ $user->full_name ?: $user->name }}</p>
                        <p class="text-base text-primary dark:text-primary/80 font-medium mt-1">{{ $user->contact_number ?: $user->email }}</p>
                        <p class="text-sm text-black dark:text-gray-200 mt-2 max-w-md mx-auto leading-relaxed">BantayKalusugan Health Monitoring System.</p>
                    </div>
                </div>
            </div>

            <section class="mt-8 rounded-2xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                <a href="{{ route('users.profile.details') }}" class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left border-b border-[#e7f3ec] dark:border-gray-700">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#4e976b] dark:text-gray-400">Profile</p>
                        <h2 class="mt-1 text-xl font-bold text-[#0e1b13] dark:text-white">Profile Details</h2>
                        <p class="mt-1 text-sm text-[#376f50] dark:text-gray-300">View your locked profile details and change your password.</p>
                    </div>
                    <span class="material-symbols-outlined text-[22px] text-[#4e976b] dark:text-gray-300">chevron_right</span>
                </a>

                <a href="{{ route('users.profile.chat-support') }}" class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left border-b border-[#e7f3ec] dark:border-gray-700">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#4e976b] dark:text-gray-400">Health Chat</p>
                        <h2 class="mt-1 text-xl font-bold text-[#0e1b13] dark:text-white">Chat Support</h2>
                        <p class="mt-1 text-sm text-[#376f50] dark:text-gray-300">Open chat help and see how it works.</p>
                    </div>
                    <span class="material-symbols-outlined text-[22px] text-[#4e976b] dark:text-gray-300">chevron_right</span>
                </a>

                <a href="{{ route('users.profile.bhw-chat') }}" class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left {{ ($unreadBhwChatCount ?? 0) > 0 ? 'bg-[#f2fbf6] dark:bg-gray-700/30' : '' }}">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#4e976b] dark:text-gray-400">Health Worker</p>
                        <h2 class="mt-1 text-xl font-bold text-[#0e1b13] dark:text-white">Barangay Health Worker</h2>
                        <p class="mt-1 text-sm text-[#376f50] dark:text-gray-300">Chat directly with your assigned health worker.</p>
                    </div>
                    <div class="flex flex-col items-end gap-1 shrink-0">
                        @if (($unreadBhwChatCount ?? 0) > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200">New</span>
                        @endif
                        <span class="material-symbols-outlined text-[22px] text-[#4e976b] dark:text-gray-300">chevron_right</span>
                    </div>
                </a>
            </section>

            <section class="space-y-3">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#4e976b] dark:text-gray-400 px-1">Account Settings</p>
                <div class="rounded-2xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left text-red-600 dark:text-red-300">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[22px]">logout</span>
                                <span class="font-semibold text-base">Logout</span>
                            </div>
                        </button>
                    </form>
                </div>
            </section>
            </div>
        </main>

        @include('partials.users-mobile-nav')
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
                    // Show hamburger icon, hide X
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
                    // Show X icon, hide hamburger
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
    <script>
        (function () {
            const avatarBtn = document.getElementById('avatarEditBtn');
            const fileInput = document.getElementById('avatarFileInput');
            const form = document.getElementById('avatarUploadForm');
            if (!avatarBtn || !fileInput || !form) return;

            avatarBtn.addEventListener('click', function () {
                fileInput.click();
            });

            fileInput.addEventListener('change', function () {
                if (!(fileInput.files && fileInput.files.length)) return;

                const fd = new FormData();
                fd.append('profile_picture', fileInput.files[0]);
                // include CSRF token
                const tokenInput = form.querySelector('input[name="_token"]');
                if (tokenInput) fd.append('_token', tokenInput.value);

                fetch("{{ route('users.profile.avatar') }}", {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(async (res) => {
                    if (!res.ok) {
                        const data = await res.json().catch(() => null);
                        const msg = data && data.errors ? data.errors.join(', ') : 'Upload failed';
                        alert(msg);
                        return;
                    }

                    const data = await res.json();
                    if (data && data.url) {
                        // Update avatar image on the page
                        const img = document.querySelector('#usersContent img[alt="Profile picture"]');
                        if (img) {
                            img.src = data.url + (data.url.includes('?') ? '&' : '?') + 'ts=' + Date.now();
                        }

                        // show a small toast
                        const toast = document.createElement('div');
                        toast.className = 'fixed top-4 right-4 z-50 max-w-xs rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 shadow-lg transition-opacity duration-300';
                        toast.textContent = 'Profile picture updated.';
                        document.body.appendChild(toast);
                        setTimeout(() => { toast.classList.add('opacity-0'); setTimeout(()=>toast.remove(),300); }, 1500);
                    }
                }).catch((err) => {
                    console.error(err);
                    alert('Upload failed.');
                });
            });
        })();
    </script>
</body>
</html>
