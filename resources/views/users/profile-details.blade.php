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
        $isProfile = request()->routeIs('users.profile*');
        $isNotifications = request()->routeIs('users.notifications');
        $residentRequest = $residentRequest ?? null;

        $lockedInputClass = 'w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-gray-100 dark:bg-gray-900/60 px-3 text-gray-700 dark:text-gray-300 cursor-not-allowed';

        $representativeName = trim((string) (
            $residentRequest?->full_name
            ?: $user->full_name
            ?: $user->name
            ?: ''
        ));
        $representativeRole = trim((string) ($residentRequest?->parent_role ?: $user->parent_role ?: ''));
        $representativeContact = trim((string) ($residentRequest?->contact ?: $user->contact_number ?: ''));

        $spouseDetails = $residentRequest?->spouse_details ?? $user->spouse_details ?? [];
        if (! is_array($spouseDetails)) {
            $spouseDetails = [];
        }
        $spouseName = trim((string) (
            $residentRequest?->spouse_full_name
            ?: $user->spouse_full_name
            ?: ($spouseDetails['full_name'] ?? '')
        ));
        $spouseMeta = collect([
            $spouseDetails['parent_role'] ?? null,
            filled($spouseDetails['gender'] ?? null) ? ucfirst(strtolower(trim((string) $spouseDetails['gender']))) : null,
            filled($spouseDetails['birthdate'] ?? null) ? \Illuminate\Support\Carbon::parse((string) $spouseDetails['birthdate'])->format('M d, Y') : null,
            $spouseDetails['place_of_birth'] ?? null,
            $spouseDetails['educational_attainment'] ?? null,
        ])->filter(fn ($part) => filled($part))->values()->all();

        $householdChildren = collect($residentRequest?->household_children ?? [])
            ->filter(fn ($child) => is_array($child) && trim((string) ($child['full_name'] ?? $child['name'] ?? '')) !== '')
            ->map(function (array $child) {
                $name = trim((string) ($child['full_name'] ?? $child['name'] ?? ''));
                $meta = collect([
                    $child['relationship'] ?? null,
                    filled($child['gender'] ?? null) ? ucfirst(strtolower(trim((string) $child['gender']))) : null,
                    filled($child['birthdate'] ?? null) ? \Illuminate\Support\Carbon::parse((string) $child['birthdate'])->format('M d, Y') : null,
                    $child['educational_attainment'] ?? null,
                    $child['occupation'] ?? null,
                ])->filter(fn ($part) => filled($part))->implode(' · ');

                return ['full_name' => $name, 'meta' => $meta];
            })
            ->values();

        $householdSeniors = collect($residentRequest?->household_seniors ?? [])
            ->filter(fn ($senior) => is_array($senior) && trim((string) ($senior['full_name'] ?? $senior['name'] ?? '')) !== '')
            ->map(function (array $senior) {
                $name = trim((string) ($senior['full_name'] ?? $senior['name'] ?? ''));
                $age = $senior['age_in_years'] ?? null;
                if ($age === null && filled($senior['birthdate'] ?? null)) {
                    $age = \App\Support\PersonAge::ageInYears((string) $senior['birthdate']);
                }
                $meta = collect([
                    $senior['relationship'] ?? null,
                    filled($senior['gender'] ?? null) ? ucfirst(strtolower(trim((string) $senior['gender']))) : null,
                    $age !== null ? $age.' yrs' : null,
                    filled($senior['birthdate'] ?? null) ? \Illuminate\Support\Carbon::parse((string) $senior['birthdate'])->format('M d, Y') : null,
                    $senior['place_of_birth'] ?? null,
                    $senior['educational_attainment'] ?? null,
                    $senior['occupation'] ?? null,
                ])->filter(fn ($part) => filled($part))->implode(' · ');

                return ['full_name' => $name, 'meta' => $meta];
            })
            ->values();

        $hasHouseholdMembers = $spouseName !== '' || $householdChildren->isNotEmpty() || $householdSeniors->isNotEmpty();
        $familyNumber = trim((string) ($residentRequest?->family_number ?: $user->family_number ?: ''));
        $placeOfBirth = trim((string) ($residentRequest?->place_of_birth ?: $user->place_of_birth ?: ''));
        $educationalAttainment = trim((string) ($residentRequest?->educational_attainment ?: $user->educational_attainment ?: ''));
        $civilStatus = trim((string) ($residentRequest?->civil_status ?: $user->civil_status ?: ''));
        $gender = trim((string) ($user->gender ?: ''));
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
                        <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">Personal and household details are locked. You can still change your password.</p>
                    </div>
                    <a href="{{ route('users.profile') }}" class="inline-flex items-center gap-1 rounded-lg border border-[#d0e7d9] dark:border-gray-700 px-3 py-2 text-sm font-semibold text-[#1f7f4e] dark:text-gray-200">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Back
                    </a>
                </div>

                <section class="rounded-2xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm p-5 md:p-6">
                    @if ($errors->any() && ! $errors->password->any())
                        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-base text-red-800 dark:bg-red-900/30 dark:border-red-700 dark:text-red-200">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-base font-medium mb-1" for="profileFullName">Full Name</label>
                            <input id="profileFullName" type="text" value="{{ $representativeName ?: '—' }}" disabled class="{{ $lockedInputClass }}">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileEmail">Email</label>
                            <div class="relative">
                                <input id="profileEmail" type="email" value="{{ $user->email }}" disabled class="{{ $lockedInputClass }}">
                                <button type="button" id="openChangePasswordModalBtn" class="absolute right-0 top-full mt-0.5 text-sm font-semibold text-[#1f7f4e] hover:text-[#176b3d] hover:underline dark:text-primary leading-none">
                                    Change password
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileGender">Gender</label>
                            <input id="profileGender" type="text" value="{{ $gender !== '' ? ucfirst(strtolower($gender)) : '—' }}" disabled class="{{ $lockedInputClass }}">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileDateOfBirth">Date of Birth</label>
                            <input id="profileDateOfBirth" type="text" value="{{ optional($user->date_of_birth)->format('F j, Y') ?: '—' }}" disabled class="{{ $lockedInputClass }}">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileCivilStatus">Civil Status</label>
                            <input id="profileCivilStatus" type="text" value="{{ $civilStatus !== '' ? $civilStatus : '—' }}" disabled class="{{ $lockedInputClass }}">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileContact">Contact Number</label>
                            <input id="profileContact" type="text" value="{{ $representativeContact !== '' ? $representativeContact : '—' }}" disabled class="{{ $lockedInputClass }}">
                        </div>

                        @if ($representativeRole !== '')
                            <div>
                                <label class="block text-base font-medium mb-1" for="profileParentRole">Parent Role</label>
                                <input id="profileParentRole" type="text" value="{{ $representativeRole }}" disabled class="{{ $lockedInputClass }}">
                            </div>
                        @endif

                        @if ($placeOfBirth !== '')
                            <div>
                                <label class="block text-base font-medium mb-1" for="profilePlaceOfBirth">Place of Birth</label>
                                <input id="profilePlaceOfBirth" type="text" value="{{ $placeOfBirth }}" disabled class="{{ $lockedInputClass }}">
                            </div>
                        @endif

                        @if ($educationalAttainment !== '')
                            <div>
                                <label class="block text-base font-medium mb-1" for="profileEducation">Educational Attainment</label>
                                <input id="profileEducation" type="text" value="{{ $educationalAttainment }}" disabled class="{{ $lockedInputClass }}">
                            </div>
                        @endif

                        @if ($familyNumber !== '')
                            <div>
                                <label class="block text-base font-medium mb-1" for="profileFamilyNumber">Family Number</label>
                                <input id="profileFamilyNumber" type="text" value="{{ $familyNumber }}" disabled class="{{ $lockedInputClass }}">
                            </div>
                        @endif

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileHousehold">Household No./Purok</label>
                            <input id="profileHousehold" type="text" value="{{ $householdPurok ?: '—' }}" disabled class="{{ $lockedInputClass }}">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileBarangay">Barangay</label>
                            <input id="profileBarangay" type="text" value="{{ $user->barangay ?: '—' }}" disabled class="{{ $lockedInputClass }}">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileMunicipality">Municipality</label>
                            <input id="profileMunicipality" type="text" value="{{ $user->municipality ?: 'Victoria' }}" disabled class="{{ $lockedInputClass }}">
                        </div>

                        <div>
                            <label class="block text-base font-medium mb-1" for="profileProvince">Province</label>
                            <input id="profileProvince" type="text" value="{{ $user->province ?: 'Oriental Mindoro' }}" disabled class="{{ $lockedInputClass }}">
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm p-5 md:p-6 space-y-4">
                    <div>
                        <h2 class="text-lg font-bold text-[#0e1b13] dark:text-white">Household Members</h2>
                        <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">Representative, spouse, children, and older members from your registration.</p>
                    </div>

                    <div class="space-y-3">
                        <div class="rounded-xl border border-[#d0e7d9] dark:border-gray-700 p-4">
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-[#1f7f4e] text-[22px] shrink-0">person</span>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-[#4e976b] dark:text-gray-400">Representative</p>
                                    <p class="mt-1 text-sm font-semibold text-[#0e1b13] dark:text-white break-words">{{ $representativeName !== '' ? $representativeName : '—' }}</p>
                                    <p class="mt-1 text-xs text-[#4e976b] dark:text-gray-400">
                                        {{ collect([$representativeRole ?: null, $gender !== '' ? ucfirst(strtolower($gender)) : null, $representativeContact !== '' ? $representativeContact : null])->filter()->implode(' · ') ?: 'Account holder' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if ($spouseName !== '')
                            <div class="rounded-xl border border-[#d0e7d9] dark:border-gray-700 p-4">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-[#1f7f4e] text-[22px] shrink-0">favorite</span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-[#4e976b] dark:text-gray-400">Spouse</p>
                                        <p class="mt-1 text-sm font-semibold text-[#0e1b13] dark:text-white break-words">{{ $spouseName }}</p>
                                        @if (! empty($spouseMeta))
                                            <p class="mt-1 text-xs text-[#4e976b] dark:text-gray-400 break-words">{{ implode(' · ', $spouseMeta) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @foreach ($householdChildren as $child)
                            <div class="rounded-xl border border-[#d0e7d9] dark:border-gray-700 p-4">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-[#1f7f4e] text-[22px] shrink-0">child_care</span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-[#4e976b] dark:text-gray-400">Child</p>
                                        <p class="mt-1 text-sm font-semibold text-[#0e1b13] dark:text-white break-words">{{ $child['full_name'] }}</p>
                                        @if ($child['meta'] !== '')
                                            <p class="mt-1 text-xs text-[#4e976b] dark:text-gray-400 break-words">{{ $child['meta'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @foreach ($householdSeniors as $senior)
                            <div class="rounded-xl border border-[#d0e7d9] dark:border-gray-700 p-4">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-[#1f7f4e] text-[22px] shrink-0">elderly</span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-[#4e976b] dark:text-gray-400">Older</p>
                                        <p class="mt-1 text-sm font-semibold text-[#0e1b13] dark:text-white break-words">{{ $senior['full_name'] }}</p>
                                        @if ($senior['meta'] !== '')
                                            <p class="mt-1 text-xs text-[#4e976b] dark:text-gray-400 break-words">{{ $senior['meta'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if (! $hasHouseholdMembers)
                            <div class="rounded-xl border border-dashed border-[#d0e7d9] dark:border-gray-700 p-4 text-sm text-[#4e976b] dark:text-gray-400">
                                No spouse, child, or older household members on file. Only the representative is listed.
                            </div>
                        @endif
                    </div>
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
