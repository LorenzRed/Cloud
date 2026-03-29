<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
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
                    class="flex w-full lg:w-1/2 flex-col items-center justify-center bg-background-light dark:bg-background-dark p-4 sm:p-8">
                    <div class="w-full max-w-md space-y-6">
                        <div class="flex lg:hidden items-center justify-center gap-2 mb-6">
                            <img src="{{ asset('logo/logo.png') }}" alt="Bantay Kalusugan logo" class="w-10 h-10 sm:w-12 sm:h-12 rounded" />
                            <h1 class="text-2xl sm:text-3xl font-black font-heading tracking-tight">Bantay Kalusugan</h1>
                        </div>
                        <div class="bg-form-bg-light dark:bg-form-bg-dark p-6 sm:p-8 rounded-xl shadow-lg w-full">
                            <h2 class="text-3xl font-black font-heading text-text-light dark:text-text-dark mb-2">Forgot password</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Enter the email for your account. If it exists, we will send a verification code.</p>
                            <form id="forgotForm" class="space-y-5" method="POST" action="{{ route('password.email') }}" novalidate>
                                @csrf
                                <div class="auth-field">
                                    <div @class([
                                        'auth-field-control border bg-background-light dark:bg-background-dark focus-within:ring-2 focus-within:ring-primary/50 focus-within:border-primary',
                                        'border-border-light dark:border-border-dark' => ! $errors->has('email'),
                                        'border-red-500 dark:border-red-500 focus-within:border-red-500 focus-within:ring-red-500/40' => $errors->has('email'),
                                    ])>
                                        <input id="emailInput" name="email" type="email" autocomplete="email" maxlength="255"
                                            class="auth-field-input text-text-light dark:text-text-dark"
                                            value="{{ old('email') }}"
                                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" />
                                        <label for="emailInput" class="auth-field-label">Email</label>
                                    </div>
                                </div>
                                @error('email')
                                    <p class="-mt-1 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                                @enderror
                                <p id="forgotEmailClientError" class="-mt-1 hidden text-sm text-red-600 dark:text-red-400" role="alert"></p>
                                <button
                                    class="w-full bg-primary text-white font-bold h-12 rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/80 dark:focus:ring-offset-background-dark transition-colors duration-300"
                                    type="submit">
                                    Send verification code
                                </button>
                            </form>
                            <p class="text-center text-sm text-border-light dark:text-border-dark mt-6">
                                <a class="font-bold text-primary hover:underline" href="{{ route('login') }}">Back to login</a>
                            </p>
                        </div>
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

            emailInput.addEventListener('input', function () {
                if (forgotEmailClientError) {
                    forgotEmailClientError.textContent = '';
                    forgotEmailClientError.classList.add('hidden');
                }
            });

            forgotForm.addEventListener('submit', function (e) {
                const v = (emailInput.value || '').trim();
                if (!v) {
                    e.preventDefault();
                    forgotEmailClientError.textContent = 'Please enter your email address.';
                    forgotEmailClientError.classList.remove('hidden');
                    return;
                }
                if (!pattern.test(v)) {
                    e.preventDefault();
                    forgotEmailClientError.textContent = 'Please enter a valid email address.';
                    forgotEmailClientError.classList.remove('hidden');
                }
            });
        })();
    </script>
</body>

</html>
