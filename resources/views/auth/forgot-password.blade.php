<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    @include('partials.submit-guard')
    <title>Bantay Kalusugan - Forgot password</title>
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
                    class="hidden lg:flex w-1/2 flex-col items-center justify-center bg-secondary/20 p-8 relative overflow-hidden">
                    <div class="w-full h-full bg-center bg-no-repeat bg-cover rounded-xl"
                        style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuADWYfo162QTnYPm14ZX9dWydT4rHSuW--OMUOy6FJD3__9c8b48flGgJTP_N_Eh4wpI5_uOxbKThL41w0MXBce15vK5JYImPGjfaKWBGM4N1Du_U26q79arqavemumO74qZqH02m6bnXytUTguC2ukGM6VQVvA10fPZZ_dKeqd5O01td08lUjBbnfBIv38RBm7v3fE3hDG8UCzu05QA8rRQaFlT9WBhydiuPPQos7ueABG0GcvjB8qkd8S2xKO-J7Zjw88BWB_5hAs"); opacity: 0.9;'>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-primary/50 to-secondary/30"></div>
                    <div
                        class="absolute bottom-10 left-10 right-10 z-10 text-white p-6 rounded-lg bg-black/20 backdrop-blur-sm">
                        <div class="flex items-center gap-4 mb-4">
                            <img src="{{ asset('logo/logo.png') }}" alt="Bantay Kalusugan logo" class="w-12 h-12 rounded" />
                            <h1 class="text-4xl font-black font-heading tracking-tight">Bantay Kalusugan</h1>
                        </div>
                        <p class="text-lg font-light">We will email you a 6-digit code to reset your password.</p>
                    </div>
                </div>
                <div
                    class="auth-screen flex w-full lg:w-1/2 flex-col items-center justify-center bg-background-light dark:bg-background-dark sm:p-8">
                    <div class="auth-screen__inner space-y-4 sm:space-y-6">
                        <header class="auth-screen__brand lg:hidden">
                            <img src="{{ asset('logo/logo.png') }}" alt="Bantay Kalusugan logo" />
                            <h1 class="font-heading text-text-light dark:text-text-dark">Bantay Kalusugan</h1>
                        </header>

                        <div class="auth-panel bg-form-bg-light dark:bg-form-bg-dark p-6 sm:p-8 rounded-xl shadow-lg w-full">
                            <header class="auth-panel__header flex flex-col gap-0.5 sm:gap-1 mb-4 sm:mb-6">
                                <h2 class="auth-panel__title text-text-light dark:text-text-dark font-heading sm:text-3xl">Forgot password</h2>
                                <p class="auth-panel__subtitle sm:text-sm text-gray-500 dark:text-gray-400">Enter the email for your account. If it exists, we will send a verification code.</p>
                            </header>

                            <form id="forgotForm" class="auth-form sm:space-y-5" method="POST" action="{{ route('password.email') }}" novalidate>
                                @csrf
                                <div class="auth-field">
                                    <div @class([
                                        'auth-field-control border bg-background-light dark:bg-background-dark focus-within:ring-2 focus-within:ring-primary/50 focus-within:border-primary',
                                        'border-border-light dark:border-border-dark' => ! $errors->has('email'),
                                        'has-field-error border-red-500 dark:border-red-500 focus-within:border-red-500 focus-within:ring-red-500/40' => $errors->has('email'),
                                    ])>
                                        <input id="emailInput" name="email" type="email" autocomplete="email" maxlength="255"
                                            class="auth-field-input text-text-light dark:text-text-dark"
                                            value="{{ old('email') }}"
                                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" />
                                        <label for="emailInput" class="auth-field-label">Email</label>
                                    </div>
                                    <p id="forgotEmailClientError" class="auth-field-bubble {{ $errors->has('email') ? '' : 'hidden' }}" role="alert" aria-live="polite">{{ $errors->first('email') }}</p>
                                </div>

                                <button
                                    class="auth-btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/80 dark:focus:ring-offset-background-dark"
                                    type="submit">
                                    Send verification code
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
            const forgotForm = document.getElementById('forgotForm');
            const forgotEmailClientError = document.getElementById('forgotEmailClientError');
            const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            function showForgotError(message) {
                if (!forgotEmailClientError) {
                    return;
                }

                if (window.AuthFieldAlerts) {
                    window.AuthFieldAlerts.show(forgotEmailClientError, message);
                    window.AuthFieldAlerts.focusField(emailInput);
                    return;
                }

                forgotEmailClientError.textContent = message;
                forgotEmailClientError.classList.remove('hidden');
            }

            emailInput.addEventListener('input', function () {
                window.AuthFieldAlerts?.hide(forgotEmailClientError);
            });

            forgotForm.addEventListener('submit', function (e) {
                window.AuthFieldAlerts?.hide(forgotEmailClientError);

                const v = (emailInput.value || '').trim();
                if (!v) {
                    e.preventDefault();
                    showForgotError('Please enter your email address.');
                    return;
                }
                if (!pattern.test(v)) {
                    e.preventDefault();
                    showForgotError('Please enter a valid email address.');
                }
            });
        })();
    </script>
</body>

</html>
