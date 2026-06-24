<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
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
        $selectedGender = ucfirst(strtolower(trim((string) old('gender', $user->gender))));
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
                            <label class="block text-base font-medium mb-1">Full Name (Firstname, Middle Initial, Lastname)</label>
                            <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3">
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
                            <label class="block text-base font-medium mb-1">Date of Birth</label>
                            <input id="dateOfBirth" type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1">Age</label>
                            <input id="computedAge" type="number" value="{{ old('age', $user->age) }}" readonly class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-gray-100 dark:bg-gray-900/60 px-3">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1">Civil Status</label>
                            <input type="text" name="civil_status" value="{{ old('civil_status', $user->civil_status) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3" placeholder="---">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1">Contact Number</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1">Household No./Purok</label>
                            <input type="text" name="household_no" value="{{ old('household_no', $user->household_no) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3" placeholder="---">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-base font-medium mb-1">Barangay</label>
                            <input type="text" name="barangay" value="{{ old('barangay', $user->barangay) }}" readonly class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-gray-100 dark:bg-gray-900/60 px-3 cursor-not-allowed">
                            <p class="text-sm text-[#4e976b] dark:text-gray-400 mt-1">Cannot be changed.</p>
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1">Municipality</label>
                            <input type="text" id="municipalityAddressInput" name="municipality" value="{{ old('municipality', $user->municipality ?: 'Victoria') }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 px-3" placeholder="---">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1">Province</label>
                            <input type="text" id="provinceAddressInput" name="province" value="{{ old('province', $user->province ?: 'Oriental Mindoro') }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 px-3" placeholder="---">
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
        const dateOfBirth = document.getElementById('dateOfBirth');
        const computedAge = document.getElementById('computedAge');
        function updateAgePreview() {
            if (!dateOfBirth || !computedAge) {
                return;
            }

            if (!dateOfBirth.value) {
                computedAge.value = '';
                return;
            }

            const birthDate = new Date(dateOfBirth.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            computedAge.value = age >= 0 ? age : '';
        }

        if (dateOfBirth) {
            dateOfBirth.addEventListener('input', updateAgePreview);
        }

        updateAgePreview();

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
