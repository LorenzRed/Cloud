<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<title>Bantay Kalusugan - Profile Details</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bhw-shell-styles')
<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }

    .profile-static-field {
        user-select: none;
        -webkit-user-select: none;
    }

    main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }
    .page-content {
        height: 100%;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#4A90E2",
                    "secondary": "#50E3C2",
                    "accent-orange": "#F5A623",
                    "accent-red": "#D0021B",
                    "background-light": "#F4F4F4",
                    "background-dark": "#1A1A1A",
                    "text-light": "#4A4A4A",
                    "text-dark": "#E0E0E0",
                    "card-light": "#FFFFFF",
                    "card-dark": "#2C2C2C",
                    "border-light": "#EAEAEA",
                    "border-dark": "#3D3D3D",
                },
                fontFamily: { "display": ["Manrope", "sans-serif"] },
            },
        },
    }
</script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@include('sidebars.bhw_head')

@if (session('status'))
    <div id="profileUpdateToast" class="fixed top-4 right-4 z-50 max-w-xs rounded-lg border border-secondary/40 bg-secondary/10 px-4 py-3 text-sm text-text-light dark:text-text-dark shadow-lg transition-opacity duration-300">
        {{ session('status') }}
    </div>
@endif

<div id="bhwContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    <main class="flex-1 flex flex-col min-h-0 px-4 sm:px-5 md:px-6 lg:px-8 pt-4 lg:pt-6 pb-24 lg:pb-6 overflow-hidden">
        <div class="page-content">
            <div class="w-full max-w-none space-y-6 pb-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-2xl font-bold leading-tight">Profile Details</h1>
                    </div>
                    <a href="{{ route('bhw-head.my-profile') }}" class="inline-flex items-center gap-1 rounded-lg border border-border-light dark:border-border-dark px-3 py-2 text-sm font-semibold text-primary shrink-0 hover:bg-primary/5 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Back
                    </a>
                </div>

                <section class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-5 sm:p-6">
                    @if ($errors->any() && ! $errors->password->any())
                        <div class="mb-4 rounded-lg border border-accent-red/40 bg-accent-red/10 px-4 py-3 text-sm">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="profileSettingsForm" method="POST" action="{{ route('bhw-head.settings.update') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium mb-1" for="full_name">Full Name</label>
                            <input id="full_name" type="text" name="full_name" value="{{ old('full_name', $user->full_name ?: $user->name) }}" required class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1" for="email">Email</label>
                            <div class="relative">
                                <input id="email" type="email" value="{{ $user->email }}" disabled class="w-full h-10 rounded-lg bg-background-light/70 dark:bg-background-dark/70 border border-border-light dark:border-border-dark px-3 text-sm text-text-light/80 dark:text-text-dark/80 cursor-not-allowed">
                                <button type="button" id="openChangePasswordModalBtn" class="absolute right-0 top-full mt-0.5 text-sm font-semibold text-primary hover:underline leading-none">
                                    Change password
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1" for="barangay">Barangay</label>
                            <input type="hidden" name="barangay" value="{{ $user->barangay }}">
                            <input id="barangay" type="text" value="{{ $user->barangay ?: '—' }}" disabled class="w-full h-10 rounded-lg bg-background-light/70 dark:bg-background-dark/70 border border-border-light dark:border-border-dark px-3 text-sm text-text-light/80 dark:text-text-dark/80 cursor-not-allowed">
                        </div>

                        <div class="pt-2 md:col-span-2">
                            <button type="submit" class="inline-flex h-10 items-center gap-2 px-4 rounded-lg bg-primary text-sm font-semibold text-white">
                                <span class="material-symbols-outlined text-[18px]">save</span>
                                <span>Save</span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>

    @include('partials.bhw-head-mobile-nav')
</div>

<div id="changePasswordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark shadow-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-border-light dark:border-border-dark flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-text-light dark:text-text-dark">Change Password</h2>
            <button type="button" id="closeChangePasswordModalBtn" class="inline-flex h-9 w-9 items-center justify-center rounded-md hover:bg-primary/10" aria-label="Close change password">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('bhw-head.settings.password') }}" class="p-5 space-y-4">
            @csrf

            @if ($errors->password->any())
                <div class="rounded-lg border border-accent-red/40 bg-accent-red/10 px-4 py-3 text-sm text-accent-red">
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
                    <input id="password" type="password" name="password" required autocomplete="new-password" class="w-full h-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark px-3 pr-10 text-sm">
                    <button type="button" id="togglePasswordBtn" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-text-light/50 hover:text-text-light dark:text-text-dark/50 dark:hover:text-text-dark" aria-label="Show password" aria-pressed="false">
                        <span id="passwordEyeOpen" class="material-symbols-outlined text-[20px]">visibility</span>
                        <span id="passwordEyeClosed" class="material-symbols-outlined text-[20px] hidden">visibility_off</span>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1" for="password_confirmation">Confirm Password</label>
                <div class="relative">
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full h-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark px-3 pr-10 text-sm">
                    <button type="button" id="togglePasswordConfirmationBtn" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-text-light/50 hover:text-text-light dark:text-text-dark/50 dark:hover:text-text-dark" aria-label="Show password" aria-pressed="false">
                        <span id="passwordConfirmationEyeOpen" class="material-symbols-outlined text-[20px]">visibility</span>
                        <span id="passwordConfirmationEyeClosed" class="material-symbols-outlined text-[20px] hidden">visibility_off</span>
                    </button>
                </div>
            </div>

            <p class="text-xs text-text-light/60 dark:text-text-dark/60">At least 10 characters, with uppercase, lowercase, a number, and a special character (e.g. !@#$%).</p>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1">
                <button type="button" id="cancelChangePasswordModalBtn" class="w-full sm:w-auto h-10 px-4 rounded-lg border border-border-light dark:border-border-dark text-sm font-semibold text-text-light dark:text-text-dark hover:bg-primary/5">
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
        }, 2200);
    }
</script>
</body>
</html>
