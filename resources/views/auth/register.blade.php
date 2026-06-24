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
    @include('partials.auth-alerts')
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
                                <form id="registerForm" class="space-y-5" method="POST" action="{{ route('register.perform') }}" novalidate>
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
                                    <p id="regEmailClientError" class="-mt-1 hidden text-sm text-red-600 dark:text-red-400" role="alert"></p>
                                    <div class="auth-field">
                                        <div @class([
                                            'auth-field-control auth-field-control--affix border bg-background-light dark:bg-background-dark focus-within:ring-2',
                                            'border-border-light dark:border-border-dark focus-within:ring-primary/50 focus-within:border-primary' => ! $errors->has('password'),
                                            'border-red-500 dark:border-red-500 focus-within:border-red-500 focus-within:ring-red-500/40' => $errors->has('password'),
                                        ])>
                                            <input id="passwordInput" name="password" type="password" autocomplete="new-password" minlength="8"
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
                                        <p class="text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                                    @enderror
                                    <p id="regPasswordClientError" class="hidden text-sm text-red-600 dark:text-red-400" role="alert"></p>
                                    <div class="auth-field">
                                        <div @class([
                                            'auth-field-control auth-field-control--affix border bg-background-light dark:bg-background-dark focus-within:ring-2',
                                            'border-border-light dark:border-border-dark focus-within:ring-primary/50 focus-within:border-primary' => ! $errors->has('password'),
                                            'border-red-500 dark:border-red-500 focus-within:border-red-500 focus-within:ring-red-500/40' => $errors->has('password'),
                                        ])>
                                            <input id="confirmPasswordInput" name="password_confirmation" type="password" autocomplete="new-password" minlength="8"
                                                class="auth-field-input text-text-light dark:text-text-dark"
                                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" />
                                            <label for="confirmPasswordInput" class="auth-field-label">Confirm password</label>
                                            <button id="toggleConfirmPassword" class="auth-field-affix" type="button" aria-label="Show password" aria-pressed="false">
                                                <svg id="confirmPasswordEyeOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                                <svg id="confirmPasswordEyeClosed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 hidden" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587A2.998 2.998 0 0 0 12 15a2.998 2.998 0 0 0 2.413-1.416" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A10.94 10.94 0 0 1 12 4.5c6 0 9.75 7.5 9.75 7.5a19.23 19.23 0 0 1-3.21 4.24" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.61 6.61A19.3 19.3 0 0 0 2.25 12s3.75 7.5 9.75 7.5a10.9 10.9 0 0 0 5.39-1.42" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <p id="regConfirmClientError" class="-mt-1 hidden text-sm text-red-600 dark:text-red-400" role="alert"></p>

                                    <!-- Terms and Conditions Checkbox -->
                                    <div class="flex items-start gap-3 mt-6">
                                        <input id="termsCheckbox" type="checkbox" name="terms_accepted" class="w-5 h-5 rounded border-border-light dark:border-border-dark text-primary cursor-pointer mt-0.5" />
                                        <label for="termsCheckbox" class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed cursor-pointer">
                                            I agree to the <button type="button" id="termsLink" class="text-primary font-semibold hover:underline">Terms and Conditions</button> and consent to my data being collected and shared as described.
                                        </label>
                                    </div>
                                    <p id="termsClientError" class="hidden text-sm text-red-600 dark:text-red-400 -mt-2" role="alert">You must agree to the terms and conditions.</p>

                                    <button class="w-full bg-primary text-white font-bold h-12 rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/80 dark:focus:ring-offset-background-dark transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed" type="submit" id="submitBtn" disabled>
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

    <!-- Terms and Conditions Modal -->
    <div id="termsModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-form-bg-light dark:bg-form-bg-dark rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-border-light dark:border-border-dark">
                <h3 class="text-2xl font-bold font-heading text-text-light dark:text-text-dark">Terms and Conditions</h3>
                <button type="button" id="closeTermsBtn" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 text-2xl leading-none">
                    ×
                </button>
            </div>

            <!-- Modal Content -->
            <div class="overflow-y-auto flex-1 p-6 space-y-6 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                <section>
                    <h4 class="font-bold text-lg text-text-light dark:text-text-dark mb-3">1. Data Collection and Location Services</h4>
                    <p>
                        This application collects location data for the purpose of providing maternal and child health services specific to your barangay. By registering, you consent to the collection and processing of your geographic location information to:
                    </p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>Identify your barangay of residence</li>
                        <li>Provide localized health information and services</li>
                        <li>Connect you with appropriate healthcare providers in your area</li>
                        <li>Facilitate emergency response coordination if needed</li>
                    </ul>
                </section>

                <section>
                    <h4 class="font-bold text-lg text-text-light dark:text-text-dark mb-3">2. Data Sharing and Consent</h4>
                    <p>
                        You acknowledge and agree that your personal information, including but not limited to:
                    </p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>Email address and contact information</li>
                        <li>Location and barangay information</li>
                        <li>Medical and health records (as applicable)</li>
                        <li>Demographics and family information</li>
                    </ul>
                    <p class="mt-3">
                        ...will be collected and may be shared with authorized healthcare professionals, barangay health workers (BHW), and local government health departments to ensure you receive appropriate and timely maternal and child health services. This data is shared in accordance with Republic Act 10173 (Data Privacy Act of 2012) and other applicable laws.
                    </p>
                </section>

                <section>
                    <h4 class="font-bold text-lg text-text-light dark:text-text-dark mb-3">3. Legal Basis</h4>
                    <p>
                        The collection and processing of your personal data is authorized under:
                    </p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li><strong>Republic Act 10173 (Data Privacy Act of 2012):</strong> Protects personal information while allowing processing for legitimate healthcare purposes</li>
                        <li><strong>Republic Act 9733 (Anti-Torture Act):</strong> Ensures protection of data subjects</li>
                        <li><strong>Local Health Code:</strong> Authorizes barangay health centers to maintain health records</li>
                        <li><strong>Your express consent:</strong> By checking this box, you provide explicit authorization</li>
                    </ul>
                </section>

                <section>
                    <h4 class="font-bold text-lg text-text-light dark:text-text-dark mb-3">4. Your Rights</h4>
                    <p>Under the Data Privacy Act, you have the right to:</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>Access your personal data</li>
                        <li>Rectify inaccurate information</li>
                        <li>Withdraw your consent (though this may affect service availability)</li>
                        <li>Lodge complaints with the National Privacy Commission</li>
                    </ul>
                </section>

                <section>
                    <h4 class="font-bold text-lg text-text-light dark:text-text-dark mb-3">5. Data Security</h4>
                    <p>
                        We implement appropriate technical and organizational measures to protect your personal data from unauthorized access, alteration, disclosure, or destruction. Access to your data is restricted to authorized personnel only.
                    </p>
                </section>

                <section>
                    <h4 class="font-bold text-lg text-text-light dark:text-text-dark mb-3">6. Consent Acknowledgment</h4>
                    <p>
                        By checking the "I agree to the Terms and Conditions" box, you explicitly acknowledge that:
                    </p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>You have read and understood these terms</li>
                        <li>You freely and voluntarily consent to data collection and processing</li>
                        <li>You authorize sharing of your data with authorized health entities</li>
                        <li>You understand your rights under the Data Privacy Act</li>
                    </ul>
                </section>

                <section>
                    <p class="text-xs text-gray-500 dark:text-gray-400 pt-4 border-t border-border-light dark:border-border-dark">
                        Last updated: May 13, 2026 | For questions about privacy, contact your local barangay health center.
                    </p>
                </section>
            </div>

            <!-- Modal Footer -->
            <div class="flex gap-3 p-6 border-t border-border-light dark:border-border-dark bg-gray-50 dark:bg-gray-900">
                <button type="button" id="acceptTermsBtn" class="flex-1 bg-primary text-white font-bold h-10 rounded-lg hover:bg-primary/90 transition-colors">
                    I Understand and Agree
                </button>
                <button type="button" id="declineTermsBtn" class="flex-1 bg-gray-300 dark:bg-gray-600 text-text-light dark:text-text-dark font-bold h-10 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
    <script>
        // Floating label functionality
        const emailInput = document.getElementById('emailInput');
        const passwordInput = document.getElementById('passwordInput');
        const confirmPasswordInput = document.getElementById('confirmPasswordInput');
        const registerForm = document.getElementById('registerForm');
        const regEmailClientError = document.getElementById('regEmailClientError');
        const regPasswordClientError = document.getElementById('regPasswordClientError');
        const regConfirmClientError = document.getElementById('regConfirmClientError');

        function clearRegClientErrors() {
            [regEmailClientError, regPasswordClientError, regConfirmClientError].forEach(function (el) {
                if (el) {
                    el.textContent = '';
                    el.classList.add('hidden');
                }
            });
        }

        function showRegError(el, message) {
            if (!el) return;
            el.textContent = message;
            el.classList.remove('hidden');
        }

        const regEmailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        registerForm.addEventListener('submit', function (e) {
            clearRegClientErrors();
            let ok = true;
            const emailVal = (emailInput.value || '').trim();
            if (!emailVal) {
                showRegError(regEmailClientError, 'Please enter your email address.');
                ok = false;
            } else if (!regEmailPattern.test(emailVal)) {
                showRegError(regEmailClientError, 'Please enter a valid email address.');
                ok = false;
            }
            const pw = passwordInput.value || '';
            if (pw.length < 8) {
                showRegError(regPasswordClientError, 'Password must be at least 8 characters.');
                ok = false;
            } else if (!/[a-zA-Z]/.test(pw)) {
                showRegError(regPasswordClientError, 'Password must include at least one letter.');
                ok = false;
            } else if (!/[0-9]/.test(pw)) {
                showRegError(regPasswordClientError, 'Password must include at least one number.');
                ok = false;
            }
            if (pw !== confirmPasswordInput.value) {
                showRegError(regConfirmClientError, 'Passwords do not match.');
                ok = false;
            }
            if (!ok) {
                e.preventDefault();
            }
        });

        function onRegInput() {
            clearRegClientErrors();
        }
        emailInput.addEventListener('input', onRegInput);
        passwordInput.addEventListener('input', onRegInput);
        confirmPasswordInput.addEventListener('input', onRegInput);

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

        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordEyeOpen = document.getElementById('confirmPasswordEyeOpen');
        const confirmPasswordEyeClosed = document.getElementById('confirmPasswordEyeClosed');

        toggleConfirmPassword.addEventListener('click', () => {
            const isHidden = confirmPasswordInput.getAttribute('type') === 'password';
            confirmPasswordInput.setAttribute('type', isHidden ? 'text' : 'password');
            const nowVisible = confirmPasswordInput.getAttribute('type') === 'text';
            toggleConfirmPassword.setAttribute('aria-pressed', nowVisible ? 'true' : 'false');
            toggleConfirmPassword.setAttribute('aria-label', nowVisible ? 'Hide password' : 'Show password');
            confirmPasswordEyeOpen.classList.toggle('hidden', nowVisible);
            confirmPasswordEyeClosed.classList.toggle('hidden', !nowVisible);
        });

        // Terms and Conditions Modal
        const termsModal = document.getElementById('termsModal');
        const termsLink = document.getElementById('termsLink');
        const closeTermsBtn = document.getElementById('closeTermsBtn');
        const acceptTermsBtn = document.getElementById('acceptTermsBtn');
        const declineTermsBtn = document.getElementById('declineTermsBtn');
        const termsCheckbox = document.getElementById('termsCheckbox');
        const termsClientError = document.getElementById('termsClientError');
        const submitBtn = document.getElementById('submitBtn');

        // Open modal when terms link is clicked
        termsLink.addEventListener('click', (e) => {
            e.preventDefault();
            termsModal.classList.remove('hidden');
        });

        // Close modal functions
        function closeModal() {
            termsModal.classList.add('hidden');
        }

        closeTermsBtn.addEventListener('click', closeModal);
        declineTermsBtn.addEventListener('click', closeModal);

        // Accept terms - check checkbox and close modal
        acceptTermsBtn.addEventListener('click', () => {
            termsCheckbox.checked = true;
            updateSubmitButton();
            termsClientError.classList.add('hidden');
            closeModal();
        });

        // Close modal when clicking outside of it
        termsModal.addEventListener('click', (e) => {
            if (e.target === termsModal) {
                closeModal();
            }
        });

        // Update submit button state based on checkbox
        function updateSubmitButton() {
            submitBtn.disabled = !termsCheckbox.checked;
        }

        termsCheckbox.addEventListener('change', updateSubmitButton);

        // Validate terms before form submission
        registerForm.addEventListener('submit', function (e) {
            if (!termsCheckbox.checked) {
                e.preventDefault();
                termsClientError.textContent = 'You must agree to the terms and conditions to register.';
                termsClientError.classList.remove('hidden');
                clearRegClientErrors();
                return;
            }
            termsClientError.classList.add('hidden');
        });
    </script>
</body>

</html>