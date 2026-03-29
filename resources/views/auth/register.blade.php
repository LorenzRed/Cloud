<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Bantay Kalusugan - Create Account</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&amp;family=Nunito+Sans:opsz,wght@6..12,400;6..12,700;6..12,900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <style>
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24
        }

        /* Hide native browser password toggle icons */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }

        input[type="password"]::-webkit-inner-spin-button,
        input[type="password"]::-webkit-outer-spin-button,
        input[type="password"]::-webkit-search-cancel-button,
        input[type="password"]::-webkit-search-decoration,
        input[type="password"]::-webkit-clear-button {
            display: none;
        }

        input[type="password"]::-webkit-password-toggle-button {
            display: none !important;
        }

        /* Prevent blue autofill highlight */
        :is(input, textarea, select):is(:-webkit-autofill, :-webkit-autofill:hover, :-webkit-autofill:focus) {
            -webkit-text-fill-color: #333333;
            caret-color: #333333;
            box-shadow: 0 0 0 1000px #F4F7F6 inset;
            transition: background-color 5000s ease-in-out 0s;
        }

        .dark :is(input, textarea, select):is(:-webkit-autofill, :-webkit-autofill:hover, :-webkit-autofill:focus) {
            -webkit-text-fill-color: #E5E7EB;
            caret-color: #E5E7EB;
            box-shadow: 0 0 0 1000px #1F2937 inset;
        }

        /* Floating Label Styles */
        .floating-label-group {
            position: relative;
            width: 100%;
        }

        .floating-label {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #A9A9A9;
            font-size: 16px;
            transition: all 0.3s ease;
            pointer-events: none;
            background: transparent;
            padding: 0 4px;
        }

        .dark .floating-label {
            color: #9CA3AF;
        }

        .floating-input {
            padding-left: 16px !important;
        }

        .floating-input::placeholder {
            color: transparent;
        }

        /* When input has focus or value */
        .floating-label-group:focus-within .floating-label,
        .floating-label-group.has-value .floating-label {
            top: 6px;
            font-size: 12px;
            color: #4A90E2;
            font-weight: 500;
        }
    </style>
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

<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
    <div class="relative flex min-h-screen w-full flex-col group/design-root overflow-x-hidden" style="width: 100%;">
        <div class="layout-container flex h-full grow flex-col">
            <main class="flex min-h-screen w-full">
                <div class="flex flex-1 items-stretch">
                    <div class="w-1/2 flex-col items-center justify-center bg-secondary/20 p-8 relative overflow-hidden hidden lg:flex">
                        <div class="w-full h-full bg-center bg-no-repeat bg-cover rounded-xl" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuADWYfo162QTnYPm14ZX9dWydT4rHSuW--OMUOy6FJD3__9c8b48flGgJTP_N_Eh4wpI5_uOxbKThL41w0MXBce15vK5JYImPGjfaKWBGM4N1Du_U26q79arqavemumO74qZqH02m6bnXytUTguC2ukGM6VQVvA10fPZZ_dKeqd5O01td08lUjBbnfBIv38RBm7v3fE3hDG8UCzu05QA8rRQaFlT9WBhydiuPPQos7ueABG0GcvjB8qkd8S2xKO-J7Zjw88BWB_5hAs"); opacity: 0.9;'></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-primary/50 to-secondary/30"></div>
                        <div class="absolute bottom-10 left-10 right-10 z-10 text-white p-6 rounded-lg bg-black/20 backdrop-blur-sm">
                            <div class="flex items-center gap-4 mb-4">
                                <img src="{{ asset('logo/logo.png') }}" alt="Bantay Kalusugan logo" class="w-12 h-12 rounded" />
                                <h1 class="text-4xl font-black font-heading tracking-tight">Bantay Kalusugan</h1>
                            </div>
                            <p class="text-lg font-light">Your community's partner in maternal and child health. Protecting futures, one check-up at a time.</p>
                        </div>
                    </div>
                    <div class="flex w-full lg:w-1/2 flex-col items-center justify-center bg-background-light dark:bg-background-dark p-4 sm:p-8">
                        <div class="w-full max-w-md space-y-6">
                            <div class="flex lg:hidden items-center justify-center gap-2 mb-6">
                                <img src="{{ asset('logo/logo.png') }}" alt="Bantay Kalusugan logo" class="w-10 h-10 sm:w-12 sm:h-12 rounded" />
                                <h1 class="text-2xl sm:text-3xl font-black font-heading tracking-tight text-text-light dark:text-text-dark">Bantay Kalusugan</h1>
                            </div>
                            <div class="bg-form-bg-light dark:bg-form-bg-dark p-6 sm:p-8 rounded-xl shadow-lg w-full">
                                <div class="flex justify-between items-center mb-6">
                                    <div class="flex flex-col">
                                        <h2 class="text-3xl font-black font-heading text-text-light dark:text-text-dark">Create Account</h2>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Join us to manage health records.</p>
                                    </div>
                                </div>
                                <div class="hidden sm:flex mb-6">
                                    <div class="flex h-12 flex-1 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 p-1 border border-gray-200 dark:border-gray-700">
                                        <a class="flex cursor-pointer h-full grow items-center justify-center overflow-hidden rounded-md px-2 text-text-light dark:text-text-dark text-sm font-bold leading-normal transition-all duration-300" href="{{ route('login') }}">
                                            <span class="truncate">Login</span>
                                        </a>
                                        <a class="flex cursor-pointer h-full grow items-center justify-center overflow-hidden rounded-md px-2 bg-primary text-white shadow-md text-sm font-bold leading-normal transition-all duration-300" href="{{ route('register') }}">
                                            <span class="truncate">Register</span>
                                        </a>
                                    </div>
                                </div>
                                <form class="space-y-6" method="POST" action="{{ route('register.perform') }}">
                                    @csrf
                                    <div class="floating-label-group">
                                        <input id="emailInput" name="email" class="floating-input form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 focus:border-primary border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-12 text-base font-normal leading-normal" type="email" value="{{ old('email') }}" required />
                                        <label for="emailInput" class="floating-label">Email</label>
                                    </div>
                                    <div class="floating-label-group">
                                        <div class="flex w-full flex-1 items-stretch rounded-lg relative border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark overflow-hidden focus-within:ring-2 focus-within:ring-primary/50 focus-within:border-primary">
                                            <input id="passwordInput" name="password" class="floating-input form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden text-text-light dark:text-text-dark focus:outline-0 focus:ring-0 border-0 bg-transparent h-12 pr-2 text-base font-normal leading-normal" type="password" required />
                                            <button id="togglePassword" class="text-border-light dark:text-border-dark flex items-center justify-center px-3 bg-transparent hover:text-primary dark:hover:text-primary" type="button">
                                                <svg id="passwordEyeOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                                <svg id="passwordEyeClosed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 hidden">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587A2.998 2.998 0 0 0 12 15a2.998 2.998 0 0 0 2.413-1.416" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A10.94 10.94 0 0 1 12 4.5c6 0 9.75 7.5 9.75 7.5a19.23 19.23 0 0 1-3.21 4.24" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.61 6.61A19.3 19.3 0 0 0 2.25 12s3.75 7.5 9.75 7.5a10.9 10.9 0 0 0 5.39-1.42" />
                                                </svg>
                                            </button>
                                        </div>
                                        <label for="passwordInput" class="floating-label">Password</label>
                                    </div>
                                    <div class="floating-label-group">
                                        <div class="flex w-full flex-1 items-stretch rounded-lg relative border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark overflow-hidden focus-within:ring-2 focus-within:ring-primary/50 focus-within:border-primary">
                                            <input id="confirmPasswordInput" name="password_confirmation" class="floating-input form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden text-text-light dark:text-text-dark focus:outline-0 focus:ring-0 border-0 bg-transparent h-12 pr-2 text-base font-normal leading-normal" type="password" required />
                                            <button id="toggleConfirmPassword" class="text-border-light dark:text-border-dark flex items-center justify-center px-3 bg-transparent hover:text-primary dark:hover:text-primary" type="button">
                                                <svg id="confirmPasswordEyeOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                                <svg id="confirmPasswordEyeClosed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 hidden">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587A2.998 2.998 0 0 0 12 15a2.998 2.998 0 0 0 2.413-1.416" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A10.94 10.94 0 0 1 12 4.5c6 0 9.75 7.5 9.75 7.5a19.23 19.23 0 0 1-3.21 4.24" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.61 6.61A19.3 19.3 0 0 0 2.25 12s3.75 7.5 9.75 7.5a10.9 10.9 0 0 0 5.39-1.42" />
                                                </svg>
                                            </button>
                                        </div>
                                        <label for="confirmPasswordInput" class="floating-label">Confirm Password</label>
                                    </div>
                                    <button class="w-full bg-primary text-white font-bold h-12 rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/80 dark:focus:ring-offset-background-dark transition-colors duration-300" type="submit">
                                        Register
                                    </button>
                                </form>
                                <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
                                    Already have an account? <a class="font-bold text-primary hover:underline" href="{{ route('login') }}">Login</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        // Floating label functionality
        const emailInput = document.getElementById('emailInput');
        const passwordInput = document.getElementById('passwordInput');
        const confirmPasswordInput = document.getElementById('confirmPasswordInput');

        function updateFloatingLabel(input) {
            const group = input.closest('.floating-label-group');
            if (!group) {
                return;
            }

            if (input.value) {
                group.classList.add('has-value');
            } else {
                group.classList.remove('has-value');
            }
        }

        emailInput.addEventListener('input', () => updateFloatingLabel(emailInput));
        passwordInput.addEventListener('input', () => updateFloatingLabel(passwordInput));
        confirmPasswordInput.addEventListener('input', () => updateFloatingLabel(confirmPasswordInput));
        emailInput.addEventListener('blur', () => updateFloatingLabel(emailInput));
        passwordInput.addEventListener('blur', () => updateFloatingLabel(passwordInput));
        confirmPasswordInput.addEventListener('blur', () => updateFloatingLabel(confirmPasswordInput));

        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordEyeOpen = document.getElementById('passwordEyeOpen');
        const passwordEyeClosed = document.getElementById('passwordEyeClosed');

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            passwordEyeOpen.classList.toggle('hidden', type === 'text');
            passwordEyeClosed.classList.toggle('hidden', type !== 'text');
        });

        // Toggle confirm password visibility
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordEyeOpen = document.getElementById('confirmPasswordEyeOpen');
        const confirmPasswordEyeClosed = document.getElementById('confirmPasswordEyeClosed');

        toggleConfirmPassword.addEventListener('click', () => {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            confirmPasswordEyeOpen.classList.toggle('hidden', type === 'text');
            confirmPasswordEyeClosed.classList.toggle('hidden', type !== 'text');
        });
    </script>
</body>

</html>