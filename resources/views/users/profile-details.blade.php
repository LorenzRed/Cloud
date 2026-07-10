<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    @include('partials.users-viewport')
    <title>Bantay Kalusugan - Profile Details</title>
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

        .profile-static-field {
            user-select: none;
            -webkit-user-select: none;
        }
    </style>
    <style>
        .profile-static-field {
            user-select: none;
            -webkit-user-select: none;
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
        $householdPurok = $user->householdPurokLabel();
        $isDashboard = request()->routeIs('users.main');
        $isEvents = request()->routeIs('users.events');
        $isAnnouncements = request()->routeIs('users.announcements');
        $isProfile = request()->routeIs('users.profile*');
        $isNotifications = request()->routeIs('users.notifications');
        $isMedicalRecords = request()->routeIs('users.medical-records');
        $selectedGender = ucfirst(strtolower(trim((string) old('gender', $user->gender))));
        $selectedCivilStatus = trim((string) old('civil_status', $user->civil_status));
        $civilStatusOptions = \App\Models\User::CIVIL_STATUSES;
    @endphp
    @include('sidebars.users')

    @if (session('status'))
        <div id="profileUpdateToast" class="fixed top-4 right-4 z-50 max-w-xs rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 shadow-lg transition-opacity duration-300 dark:bg-green-900/30 dark:border-green-700 dark:text-green-200">
            {{ session('status') }}
        </div>
    @endif

    <div id="usersContent" class="flex min-h-screen flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] transition-all duration-200">
        <main class="flex-1 px-4 sm:px-5 md:px-6 lg:px-7 pt-4 md:pt-6 pb-24 md:pb-6 overflow-y-auto">
            <div class="w-full max-w-none space-y-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h1 class="mt-1 text-2xl font-bold text-[#0e1b13] dark:text-white">Profile Details</h1>
                    </div>
                    <a href="{{ route('users.profile') }}" class="inline-flex items-center gap-1 rounded-lg border border-[#d0e7d9] dark:border-gray-700 px-3 py-2 text-sm font-semibold text-[#1f7f4e] dark:text-gray-200">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Back
                    </a>
                </div>

                <section class="rounded-2xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm p-5 md:p-6">
                    @if ($errors->any())
                        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-base text-red-800 dark:bg-red-900/30 dark:border-red-700 dark:text-red-200">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="profileForm" method="POST" action="{{ route('users.profile.update') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileFullName">Full Name</label>
                            <div id="profileFullName" class="profile-static-field w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-gray-100 dark:bg-gray-900/60 px-3 flex items-center text-gray-700 dark:text-gray-300">{{ $user->full_name ?: $user->name }}</div>
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileEmail">Email</label>
                            <div class="relative">
                                <div id="profileEmail" class="profile-static-field w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-gray-100 dark:bg-gray-900/60 px-3 flex items-center text-gray-700 dark:text-gray-300">{{ $user->email }}</div>
                                <button type="button" id="openChangePasswordModalBtn" class="absolute right-0 top-full mt-0.5 text-sm font-semibold text-[#1f7f4e] hover:text-[#176b3d] hover:underline dark:text-primary leading-none">
                                    Change password
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1">Gender</label>
                            <select name="gender" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3">
                                <option value="">Select gender</option>
                                @foreach (['Male', 'Female', 'Other'] as $gender)
                                    <option value="{{ $gender }}" @selected($selectedGender === $gender)>{{ $gender }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileDateOfBirth">Date of Birth</label>
                            <div id="profileDateOfBirth" class="profile-static-field w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-gray-100 dark:bg-gray-900/60 px-3 flex items-center text-gray-700 dark:text-gray-300">{{ optional($user->date_of_birth)->format('F j, Y') ?: '—' }}</div>
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1" for="civil_status">Civil Status</label>
                            <select id="civil_status" name="civil_status" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3">
                                <option value="">Select civil status</option>
                                @foreach ($civilStatusOptions as $status)
                                    <option value="{{ $status }}" @selected($selectedCivilStatus === $status)>{{ $status }}</option>
                                @endforeach
                                @if ($selectedCivilStatus !== '' && ! in_array($selectedCivilStatus, $civilStatusOptions, true))
                                    <option value="{{ $selectedCivilStatus }}" selected>{{ $selectedCivilStatus }}</option>
                                @endif
                            </select>
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1">Contact Number</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1">Household No./Purok</label>
                            @if ($user->purok_id)
                                <input type="hidden" name="household_no" value="{{ old('household_no', $householdPurok) }}">
                                <div class="profile-static-field w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-gray-100 dark:bg-gray-900/60 px-3 flex items-center text-gray-700 dark:text-gray-300">{{ old('household_no', $householdPurok) ?: '—' }}</div>
                                <p class="text-sm text-[#4e976b] dark:text-gray-400 mt-1">Set from your verification map location.</p>
                            @else
                                <input type="text" name="household_no" value="{{ old('household_no', $householdPurok) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3" placeholder="---">
                            @endif
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-base font-medium mb-1">Barangay</label>
                            <input type="hidden" name="barangay" value="{{ old('barangay', $user->barangay) }}">
                            <div class="profile-static-field w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-gray-100 dark:bg-gray-900/60 px-3 flex items-center text-gray-700 dark:text-gray-300">{{ old('barangay', $user->barangay) ?: '—' }}</div>
                            <p class="text-sm text-[#4e976b] dark:text-gray-400 mt-1">Cannot be changed.</p>
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileMunicipality">Municipality</label>
                            <div id="profileMunicipality" class="profile-static-field w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-gray-100 dark:bg-gray-900/60 px-3 flex items-center text-gray-700 dark:text-gray-300">{{ $user->municipality ?: 'Victoria' }}</div>
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileProvince">Province</label>
                            <div id="profileProvince" class="profile-static-field w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-gray-100 dark:bg-gray-900/60 px-3 flex items-center text-gray-700 dark:text-gray-300">{{ $user->province ?: 'Oriental Mindoro' }}</div>
                        </div>

                        <div class="md:col-span-2 pt-2">
                            <button type="submit" class="inline-flex h-11 items-center gap-2 px-5 rounded-lg bg-primary text-base font-bold text-white">
                                <span class="material-symbols-outlined text-[18px] text-white">save</span>
                                <span>Save</span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </main>

        @include('partials.users-mobile-nav')
    </div>

    <div id="changePasswordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-2xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-[#e7f3ec] dark:border-gray-700 flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-[#0e1b13] dark:text-white">Change Password</h2>
                <button type="button" id="closeChangePasswordModalBtn" class="inline-flex h-9 w-9 items-center justify-center rounded-md hover:bg-gray-100 dark:hover:bg-gray-700" aria-label="Close change password">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('users.profile.password') }}" class="p-5 space-y-4">
                @csrf

                @if ($errors->password->any())
                    <div class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-900/30 dark:border-red-700 dark:text-red-200">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->password->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium mb-1" for="password">New Password</label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="new-password" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 text-[#0e1b13] dark:text-white px-3 pr-10">
                        <button type="button" id="togglePasswordBtn" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" aria-label="Show password" aria-pressed="false">
                            <span id="passwordEyeOpen" class="material-symbols-outlined text-[20px]">visibility</span>
                            <span id="passwordEyeClosed" class="material-symbols-outlined text-[20px] hidden">visibility_off</span>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1" for="password_confirmation">Confirm Password</label>
                    <div class="relative">
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 text-[#0e1b13] dark:text-white px-3 pr-10">
                        <button type="button" id="togglePasswordConfirmationBtn" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" aria-label="Show password" aria-pressed="false">
                            <span id="passwordConfirmationEyeOpen" class="material-symbols-outlined text-[20px]">visibility</span>
                            <span id="passwordConfirmationEyeClosed" class="material-symbols-outlined text-[20px] hidden">visibility_off</span>
                        </button>
                    </div>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">Use at least 8 characters with letters and numbers.</p>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1">
                    <button type="button" id="cancelChangePasswordModalBtn" class="w-full sm:w-auto h-10 px-4 rounded-lg border border-[#d0e7d9] dark:border-gray-700 text-sm font-semibold text-[#376f50] dark:text-gray-200">
                        Cancel
                    </button>
                    <button type="submit" class="w-full sm:w-auto h-10 px-4 rounded-lg bg-primary text-sm font-bold text-white">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const changePasswordModal = document.getElementById('changePasswordModal');
            const openChangePasswordModalBtn = document.getElementById('openChangePasswordModalBtn');
            const closeChangePasswordModalBtn = document.getElementById('closeChangePasswordModalBtn');
            const cancelChangePasswordModalBtn = document.getElementById('cancelChangePasswordModalBtn');
            const passwordInput = document.getElementById('password');
            const passwordConfirmationInput = document.getElementById('password_confirmation');
            const shouldOpenPasswordModal = @json(session('open_password_modal') || $errors->password->any());

            function bindPasswordToggle(toggleBtn, input, eyeOpen, eyeClosed) {
                if (!toggleBtn || !input || !eyeOpen || !eyeClosed) {
                    return;
                }

                toggleBtn.addEventListener('click', () => {
                    const isHidden = input.getAttribute('type') === 'password';
                    input.setAttribute('type', isHidden ? 'text' : 'password');
                    const nowVisible = input.getAttribute('type') === 'text';
                    toggleBtn.setAttribute('aria-pressed', nowVisible ? 'true' : 'false');
                    toggleBtn.setAttribute('aria-label', nowVisible ? 'Hide password' : 'Show password');
                    eyeOpen.classList.toggle('hidden', nowVisible);
                    eyeClosed.classList.toggle('hidden', !nowVisible);
                });
            }

            function resetPasswordVisibility(input, toggleBtn, eyeOpen, eyeClosed) {
                if (!input) {
                    return;
                }

                input.setAttribute('type', 'password');
                input.value = '';

                if (toggleBtn) {
                    toggleBtn.setAttribute('aria-pressed', 'false');
                    toggleBtn.setAttribute('aria-label', 'Show password');
                }

                eyeOpen?.classList.remove('hidden');
                eyeClosed?.classList.add('hidden');
            }

            bindPasswordToggle(
                document.getElementById('togglePasswordBtn'),
                passwordInput,
                document.getElementById('passwordEyeOpen'),
                document.getElementById('passwordEyeClosed')
            );

            bindPasswordToggle(
                document.getElementById('togglePasswordConfirmationBtn'),
                passwordConfirmationInput,
                document.getElementById('passwordConfirmationEyeOpen'),
                document.getElementById('passwordConfirmationEyeClosed')
            );

            function openChangePasswordModal() {
                changePasswordModal?.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                window.setTimeout(() => passwordInput?.focus(), 120);
            }

            function closeChangePasswordModal() {
                changePasswordModal?.classList.add('hidden');
                document.body.style.overflow = '';
                resetPasswordVisibility(
                    passwordInput,
                    document.getElementById('togglePasswordBtn'),
                    document.getElementById('passwordEyeOpen'),
                    document.getElementById('passwordEyeClosed')
                );
                resetPasswordVisibility(
                    passwordConfirmationInput,
                    document.getElementById('togglePasswordConfirmationBtn'),
                    document.getElementById('passwordConfirmationEyeOpen'),
                    document.getElementById('passwordConfirmationEyeClosed')
                );
            }

            openChangePasswordModalBtn?.addEventListener('click', openChangePasswordModal);
            closeChangePasswordModalBtn?.addEventListener('click', closeChangePasswordModal);
            cancelChangePasswordModalBtn?.addEventListener('click', closeChangePasswordModal);

            changePasswordModal?.addEventListener('click', (event) => {
                if (event.target === changePasswordModal) {
                    closeChangePasswordModal();
                }
            });

            if (shouldOpenPasswordModal) {
                openChangePasswordModal();
            }
        })();

        const profileUpdateToast = document.getElementById('profileUpdateToast');
        if (profileUpdateToast) {
            window.setTimeout(() => {
                profileUpdateToast.classList.add('opacity-0');
                window.setTimeout(() => profileUpdateToast.remove(), 300);
            }, 1000);
        }

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
