<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    @include('partials.submit-guard')
    <title>Bantay Kalusugan - Set new password</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&amp;family=Nunito+Sans:opsz,wght@6..12,400;6..12,700;6..12,900&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />
    @include('partials.auth-input-styles')
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#4A90E2",
                        "secondary": "#50E3C2",
                        "background-light": "#F4F7F6",
                        "background-dark": "#1F2937",
                        "text-light": "#333333",
                        "text-dark": "#E5E7EB",
                        "border-light": "#A9A9A9",
                        "border-dark": "#4B5563",
                        "form-bg-light": "#FFFFFF",
                        "form-bg-dark": "#374151"
                    },
                    fontFamily: {
                        "display": ["Nunito Sans", "sans-serif"],
                        "heading": ["Lato", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
    @include('partials.auth-alerts')
    <div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
        <main class="flex min-h-screen w-full">
            <div class="flex flex-1 items-stretch">
                <div
                    class="auth-hero-panel hidden lg:flex w-1/2 flex-col items-center justify-center bg-secondary/20 p-8 relative overflow-hidden">
                    <div class="w-full h-full bg-center bg-no-repeat bg-cover rounded-xl"
                        style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuADWYfo162QTnYPm14ZX9dWydT4rHSuW--OMUOy6FJD3__9c8b48flGgJTP_N_Eh4wpI5_uOxbKThL41w0MXBce15vK5JYImPGjfaKWBGM4N1Du_U26q79arqavemumO74qZqH02m6bnXytUTguC2ukGM6VQVvA10fPZZ_dKeqd5O01td08lUjBbnfBIv38RBm7v3fE3hDG8UCzu05QA8rRQaFlT9WBhydiuPPQos7ueABG0GcvjB8qkd8S2xKO-J7Zjw88BWB_5hAs"); opacity: 0.9;'>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-primary/50 to-secondary/30"></div>
                    <div
                        class="auth-hero-copy absolute bottom-10 left-10 right-10 z-10 text-white p-6 rounded-lg bg-black/20 backdrop-blur-sm">
                        <div class="flex items-center gap-4 mb-4">
                            <img src="{{ asset('logo/logo.png') }}" alt="Bantay Kalusugan logo" class="w-12 h-12 rounded" />
                            <h1 class="text-4xl font-black font-heading tracking-tight">Bantay Kalusugan</h1>
                        </div>
                        <p class="text-lg font-light">Enter the code from your email and choose a new password.</p>
                    </div>
                </div>
                <div
                    class="auth-screen flex w-full lg:w-1/2 flex-col items-center justify-center bg-background-light dark:bg-background-dark lg:p-8">
                    <div class="auth-screen__inner space-y-4 lg:space-y-6">
                        <header class="auth-screen__brand lg:hidden">
                            <img src="{{ asset('logo/logo.png') }}" alt="Bantay Kalusugan logo" />
                            <h1 class="font-heading text-text-light dark:text-text-dark">Bantay Kalusugan</h1>
                        </header>

                        <div class="auth-panel bg-form-bg-light dark:bg-form-bg-dark p-6 sm:p-8 rounded-xl shadow-lg w-full">
                            <header class="auth-panel__header flex flex-col gap-0.5 sm:gap-1 mb-4 sm:mb-6">
                                <h2 class="auth-panel__title text-text-light dark:text-text-dark font-heading sm:text-3xl">New password</h2>
                                <p class="auth-panel__subtitle sm:text-sm text-gray-500 dark:text-gray-400">Enter the 6-digit code from your email, then choose a new password.</p>
                            </header>

                            <form id="resetForm" class="auth-form sm:space-y-5" method="POST" action="{{ route('password.update') }}" novalidate>
                                @csrf
                                <div class="auth-field">
                                    <div @class([
                                        'auth-field-control auth-field-control--readonly border',
                                        'border-border-light dark:border-border-dark' => ! $errors->has('email'),
                                        'has-field-error border-red-500 dark:border-red-500' => $errors->has('email'),
                                    ])>
                                        <input id="emailInput" name="email" type="email" autocomplete="username" maxlength="255" readonly
                                            class="auth-field-input text-text-light dark:text-text-dark"
                                            value="{{ old('email', $email ?? '') }}"
                                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" />
                                        <label for="emailInput" class="auth-field-label">Email</label>
                                    </div>
                                    @error('email')
                                        <p class="auth-field-bubble" role="alert">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="auth-field">
                                    <div @class([
                                        'auth-field-control border bg-background-light dark:bg-background-dark focus-within:ring-2',
                                        'border-border-light dark:border-border-dark focus-within:ring-primary/50 focus-within:border-primary' => ! $errors->has('otp'),
                                        'has-field-error border-red-500 dark:border-red-500 focus-within:border-red-500 focus-within:ring-red-500/40' => $errors->has('otp'),
                                    ])>
                                        <input id="otpInput" name="otp" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code"
                                            class="auth-field-input text-text-light dark:text-text-dark tracking-widest text-center font-mono"
                                            value="{{ old('otp') }}"
                                            aria-invalid="{{ $errors->has('otp') ? 'true' : 'false' }}" />
                                        <label for="otpInput" class="auth-field-label">Verification code</label>
                                    </div>
                                    <p class="auth-hint text-xs text-gray-500 dark:text-gray-400">6-digit code from your email (expires in 15 minutes).</p>
                                    <p id="resetOtpClientError" class="auth-field-bubble {{ $errors->has('otp') ? '' : 'hidden' }}" role="alert" aria-live="polite">{{ $errors->first('otp') }}</p>
                                </div>

                                <div class="auth-field">
                                    <div @class([
                                        'auth-field-control auth-field-control--affix border bg-background-light dark:bg-background-dark focus-within:ring-2',
                                        'border-border-light dark:border-border-dark focus-within:ring-primary/50 focus-within:border-primary' => ! $errors->has('password'),
                                        'has-field-error border-red-500 dark:border-red-500 focus-within:border-red-500 focus-within:ring-red-500/40' => $errors->has('password'),
                                    ])>
                                        <input id="passwordInput" name="password" type="password" autocomplete="new-password" minlength="8"
                                            class="auth-field-input text-text-light dark:text-text-dark"
                                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" />
                                        <label for="passwordInput" class="auth-field-label">New password</label>
                                        <button id="togglePassword" type="button" class="auth-field-affix" aria-label="Show password" aria-pressed="false">
                                            <svg id="passwordEyeOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            <svg id="passwordEyeClosed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 hidden" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587A2.998 2.998 0 0 0 12 15a2.998 2.998 0 0 0 2.413-1.416" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A10.94 10.94 0 0 1 12 4.5c6 0 9.75 7.5 9.75 7.5a19.23 19.23 0 0 1-3.21 4.24" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.61 6.61A19.3 19.3 0 0 0 2.25 12s3.75 7.5 9.75 7.5a10.9 10.9 0 0 0 5.39-1.42" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="auth-hint text-xs text-gray-500 dark:text-gray-400">At least 8 characters with one letter and one number.</p>
                                    <p id="resetPwClientError" class="auth-field-bubble {{ $errors->has('password') ? '' : 'hidden' }}" role="alert" aria-live="polite">{{ $errors->first('password') }}</p>
                                </div>

                                <div class="auth-field">
                                    <div @class([
                                        'auth-field-control auth-field-control--affix border bg-background-light dark:bg-background-dark focus-within:ring-2',
                                        'border-border-light dark:border-border-dark focus-within:ring-primary/50 focus-within:border-primary' => ! $errors->has('password'),
                                        'has-field-error border-red-500 dark:border-red-500 focus-within:border-red-500 focus-within:ring-red-500/40' => $errors->has('password'),
                                    ])>
                                        <input id="confirmPasswordInput" name="password_confirmation" type="password" autocomplete="new-password" minlength="8"
                                            class="auth-field-input text-text-light dark:text-text-dark" />
                                        <label for="confirmPasswordInput" class="auth-field-label">Confirm password</label>
                                        <button id="toggleConfirm" type="button" class="auth-field-affix" aria-label="Show password" aria-pressed="false">
                                            <svg id="confirmEyeOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            <svg id="confirmEyeClosed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 hidden" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587A2.998 2.998 0 0 0 12 15a2.998 2.998 0 0 0 2.413-1.416" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A10.94 10.94 0 0 1 12 4.5c6 0 9.75 7.5 9.75 7.5a19.23 19.23 0 0 1-3.21 4.24" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.61 6.61A19.3 19.3 0 0 0 2.25 12s3.75 7.5 9.75 7.5a10.9 10.9 0 0 0 5.39-1.42" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p id="resetConfirmClientError" class="auth-field-bubble hidden" role="alert" aria-live="polite"></p>
                                </div>

                                <button
                                    class="auth-btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/80 dark:focus:ring-offset-background-dark"
                                    type="submit">
                                    Update password
                                </button>
                            </form>
                        </div>

                        <p class="auth-footer sm:text-sm text-border-light dark:text-border-dark">
                            <a class="font-bold text-primary hover:underline" href="{{ route('login') }}">Back to login</a>
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        (function () {
            const emailInput = document.getElementById('emailInput');
            const otpInput = document.getElementById('otpInput');
            const passwordInput = document.getElementById('passwordInput');
            const confirmPasswordInput = document.getElementById('confirmPasswordInput');
            const resetForm = document.getElementById('resetForm');
            const resetPwClientError = document.getElementById('resetPwClientError');
            const resetConfirmClientError = document.getElementById('resetConfirmClientError');
            const resetOtpClientError = document.getElementById('resetOtpClientError');

            function showResetError(el, message) {
                if (!el) {
                    return;
                }

                if (window.AuthFieldAlerts) {
                    window.AuthFieldAlerts.show(el, message);
                    return;
                }

                el.textContent = message;
                el.classList.remove('hidden');
            }

            function hideResetErrors() {
                [resetPwClientError, resetConfirmClientError, resetOtpClientError].forEach(function (el) {
                    window.AuthFieldAlerts?.hide(el);
                });
            }

            if (otpInput) {
                otpInput.addEventListener('input', function () {
                    otpInput.value = (otpInput.value || '').replace(/\D/g, '').slice(0, 6);
                    window.AuthFieldAlerts?.hide(resetOtpClientError);
                });
            }

            [passwordInput, confirmPasswordInput].forEach(function (input) {
                if (!input) {
                    return;
                }

                input.addEventListener('input', function () {
                    window.AuthFieldAlerts?.hide(resetPwClientError);
                    window.AuthFieldAlerts?.hide(resetConfirmClientError);
                });
            });

            var togglePw = document.getElementById('togglePassword');
            togglePw.addEventListener('click', function () {
                var isHidden = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isHidden ? 'text' : 'password');
                var nowVisible = passwordInput.getAttribute('type') === 'text';
                togglePw.setAttribute('aria-pressed', nowVisible ? 'true' : 'false');
                togglePw.setAttribute('aria-label', nowVisible ? 'Hide password' : 'Show password');
                document.getElementById('passwordEyeOpen').classList.toggle('hidden', nowVisible);
                document.getElementById('passwordEyeClosed').classList.toggle('hidden', !nowVisible);
            });

            var toggleCf = document.getElementById('toggleConfirm');
            toggleCf.addEventListener('click', function () {
                var isHidden = confirmPasswordInput.getAttribute('type') === 'password';
                confirmPasswordInput.setAttribute('type', isHidden ? 'text' : 'password');
                var nowVisible = confirmPasswordInput.getAttribute('type') === 'text';
                toggleCf.setAttribute('aria-pressed', nowVisible ? 'true' : 'false');
                toggleCf.setAttribute('aria-label', nowVisible ? 'Hide password' : 'Show password');
                document.getElementById('confirmEyeOpen').classList.toggle('hidden', nowVisible);
                document.getElementById('confirmEyeClosed').classList.toggle('hidden', !nowVisible);
            });

            resetForm.addEventListener('submit', function (e) {
                hideResetErrors();

                let ok = true;
                const otp = (otpInput && otpInput.value) ? otpInput.value.trim() : '';
                if (!/^\d{6}$/.test(otp)) {
                    showResetError(resetOtpClientError, 'Enter the 6-digit code from your email.');
                    ok = false;
                }

                const pw = passwordInput.value || '';
                if (pw.length < 8) {
                    showResetError(resetPwClientError, 'Password must be at least 8 characters.');
                    ok = false;
                } else if (!/[a-zA-Z]/.test(pw)) {
                    showResetError(resetPwClientError, 'Password must include at least one letter.');
                    ok = false;
                } else if (!/[0-9]/.test(pw)) {
                    showResetError(resetPwClientError, 'Password must include at least one number.');
                    ok = false;
                }

                if (pw !== confirmPasswordInput.value) {
                    showResetError(resetConfirmClientError, 'Passwords do not match.');
                    ok = false;
                }

                if (!ok) {
                    e.preventDefault();
                }
            });
        })();
    </script>
</body>

</html>
