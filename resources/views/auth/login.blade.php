<!DOCTYPE html>

<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Bantay Kalusugan - Login &amp; Registration</title>
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
    <div class="relative flex min-h-screen w-full flex-col group/design-root overflow-x-hidden">
        <div class="layout-container flex h-full grow flex-col">
            <main class="flex min-h-screen w-full">
                <div class="flex flex-1 items-stretch">
                    <!-- Left Panel -->
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
                            <p class="text-lg font-light">Your community's partner in maternal and child health.
                                Protecting futures, one check-up at a time.</p>
                        </div>
                    </div>
                    <!-- Right Panel -->
                    <div
                        class="flex w-full lg:w-1/2 flex-col items-center justify-center bg-background-light dark:bg-background-dark p-4 sm:p-8">
                        <div class="w-full max-w-md space-y-6">
                            <!-- Logo for mobile -->
                            <div class="flex lg:hidden items-center justify-center gap-2 mb-6">
                                <img src="{{ asset('logo/logo.png') }}" alt="Bantay Kalusugan logo" class="w-10 h-10 sm:w-12 sm:h-12 rounded" />
                                <h1
                                    class="text-2xl sm:text-3xl font-black font-heading tracking-tight text-text-light dark:text-text-dark">
                                    Bantay Kalusugan</h1>
                            </div>
                            <!-- Form Container -->
                            <div class="bg-form-bg-light dark:bg-form-bg-dark p-6 sm:p-8 rounded-xl shadow-lg w-full">
                                <div class="flex flex-wrap justify-between gap-3 mb-6">
                                    <div class="flex flex-col gap-1">
                                        <p
                                            class="text-text-light dark:text-text-dark text-3xl font-black leading-tight tracking-tight font-heading">
                                            Welcome Back</p>
                                        <p class="text-primary text-base font-normal leading-normal">Log in to track
                                            health records.</p>
                                    </div>
                                </div>
                                <div class="hidden sm:flex mb-6">
                                    <div
                                        class="flex h-12 flex-1 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 p-1 border border-gray-200 dark:border-gray-700">
                                        <a class="flex cursor-pointer h-full grow items-center justify-center overflow-hidden rounded-md px-2 bg-primary text-white shadow-md text-sm font-bold leading-normal transition-all duration-300"
                                            href="{{ route('login') }}">
                                            <span class="truncate">Login</span>
                                        </a>
                                        <a class="flex cursor-pointer h-full grow items-center justify-center overflow-hidden rounded-md px-2 text-text-light dark:text-text-dark text-sm font-bold leading-normal transition-all duration-300"
                                            href="{{ route('register') }}">
                                            <span class="truncate">Register</span>
                                        </a>
                                    </div>
                                </div>
                                <!-- Login Form -->
                                <form id="loginForm" class="space-y-5" method="POST" action="{{ route('login.perform') }}" novalidate>
                                    @csrf
                                    <div class="auth-field">
                                        <div @class([
                                            'auth-field-control border bg-background-light dark:bg-background-dark focus-within:ring-2 focus-within:ring-primary/50 focus-within:border-primary',
                                            'border-border-light dark:border-border-dark' => ! $errors->has('email'),
                                            'border-red-500 dark:border-red-500 focus-within:border-red-500 focus-within:ring-red-500/40' => $errors->has('email'),
                                        ])>
                                            <input id="emailInput" name="email" type="email" autocomplete="username" maxlength="255"
                                                class="auth-field-input text-text-light dark:text-text-dark"
                                                value="{{ old('email') }}"
                                                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" />
                                            <label for="emailInput" class="auth-field-label">Email</label>
                                        </div>
                                    </div>
                                    @error('email')
                                        <p id="emailError" class="-mt-1 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                                    @enderror
                                    <p id="emailClientError" class="-mt-1 hidden text-sm text-red-600 dark:text-red-400" role="alert"></p>
                                    <div class="auth-field">
                                        <div @class([
                                            'auth-field-control auth-field-control--affix border bg-background-light dark:bg-background-dark focus-within:ring-2',
                                            'border-border-light dark:border-border-dark focus-within:ring-primary/50 focus-within:border-primary' => ! $errors->has('password'),
                                            'border-red-500 dark:border-red-500 focus-within:border-red-500 focus-within:ring-red-500/40' => $errors->has('password'),
                                        ])>
                                            <input id="passwordInput" name="password" type="password" autocomplete="current-password"
                                                class="auth-field-input text-text-light dark:text-text-dark"
                                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" />
                                            <label for="passwordInput" class="auth-field-label">Password</label>
                                            <button id="togglePassword" class="auth-field-affix" type="button" aria-label="Show password" aria-pressed="false">
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
                                    </div>
                                    @error('password')
                                        <p class="-mt-1 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                                    @enderror
                                    <p id="passwordClientError" class="-mt-1 hidden text-sm text-red-600 dark:text-red-400" role="alert"></p>
                                    <div class="flex justify-end">
                                        <a class="text-sm font-medium text-primary hover:underline" href="{{ route('password.request') }}">Forgot password?</a>
                                    </div>
                                    <button
                                        class="w-full bg-primary text-white font-bold h-12 rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/80 dark:focus:ring-offset-background-dark transition-colors duration-300"
                                        type="submit">
                                        Login
                                    </button>
                                </form>
                                <script>
                                    const emailInput = document.getElementById('emailInput');
                                    const passwordInput = document.getElementById('passwordInput');
                                    const loginForm = document.getElementById('loginForm');
                                    const emailClientError = document.getElementById('emailClientError');
                                    const passwordClientError = document.getElementById('passwordClientError');

                                    function clearClientErrors() {
                                        if (emailClientError) {
                                            emailClientError.textContent = '';
                                            emailClientError.classList.add('hidden');
                                        }
                                        if (passwordClientError) {
                                            passwordClientError.textContent = '';
                                            passwordClientError.classList.add('hidden');
                                        }
                                    }

                                    function showClientError(el, message) {
                                        if (!el) return;
                                        el.textContent = message;
                                        el.classList.remove('hidden');
                                    }

                                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                                    loginForm.addEventListener('submit', function (e) {
                                        clearClientErrors();
                                        let ok = true;
                                        const emailVal = (emailInput.value || '').trim();
                                        if (!emailVal) {
                                            showClientError(emailClientError, 'Please enter your email address.');
                                            ok = false;
                                        } else if (!emailPattern.test(emailVal)) {
                                            showClientError(emailClientError, 'Please enter a valid email address.');
                                            ok = false;
                                        }
                                        if (!passwordInput.value) {
                                            showClientError(passwordClientError, 'Please enter your password.');
                                            ok = false;
                                        }
                                        if (!ok) {
                                            e.preventDefault();
                                        }
                                    });

                                    function onAuthInput() {
                                        clearClientErrors();
                                    }
                                    emailInput.addEventListener('input', onAuthInput);
                                    passwordInput.addEventListener('input', onAuthInput);

                                    const togglePassword = document.getElementById('togglePassword');
                                    const passwordEyeOpen = document.getElementById('passwordEyeOpen');
                                    const passwordEyeClosed = document.getElementById('passwordEyeClosed');

                                    togglePassword.addEventListener('click', () => {
                                        const isHidden = passwordInput.getAttribute('type') === 'password';
                                        passwordInput.setAttribute('type', isHidden ? 'text' : 'password');
                                        const nowVisible = passwordInput.getAttribute('type') === 'text';
                                        togglePassword.setAttribute('aria-pressed', nowVisible ? 'true' : 'false');
                                        togglePassword.setAttribute('aria-label', nowVisible ? 'Hide password' : 'Show password');
                                        passwordEyeOpen.classList.toggle('hidden', nowVisible);
                                        passwordEyeClosed.classList.toggle('hidden', !nowVisible);
                                    });
                                </script>
                                <p class="text-center text-sm text-border-light dark:text-border-dark mt-6">
                                    Don't have an account? <a class="font-bold text-primary hover:underline"
                                        href="{{ route('register') }}">Register</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>