<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    @include('partials.submit-guard')
    <title>Bantay Kalusugan - Finish registration</title>
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
    <style>
        .otp-boxes {
            display: flex;
            gap: 0.5rem;
            justify-content: space-between;
        }
        .otp-boxes input {
            width: 2.75rem;
            height: 3.25rem;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 700;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            border-radius: 0.5rem;
            border: 1px solid #A9A9A9;
            background: #F4F7F6;
            color: #333333;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .otp-boxes input:focus {
            border-color: #4A90E2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.35);
        }
        .otp-boxes.has-error input {
            border-color: #ef4444;
        }
        @media (min-width: 640px) {
            .otp-boxes {
                gap: 0.625rem;
            }
            .otp-boxes input {
                width: 3.25rem;
                height: 3.5rem;
                font-size: 1.375rem;
            }
        }
        .dark .otp-boxes input {
            border-color: #4B5563;
            background: #1F2937;
            color: #E5E7EB;
        }
    </style>
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
                        <p class="text-lg font-light">
                            @if ($emailVerified)
                                Email verified. Choose a password to finish registering.
                            @else
                                Enter the code from your email to verify your account.
                            @endif
                        </p>
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
                                <h2 class="auth-panel__title text-text-light dark:text-text-dark font-heading sm:text-3xl">
                                    {{ $emailVerified ? 'Create password' : 'Verify email' }}
                                </h2>
                                <p class="auth-panel__subtitle sm:text-sm text-gray-500 dark:text-gray-400">
                                    @if ($emailVerified)
                                        Set a password for <span class="font-semibold text-text-light dark:text-text-dark">{{ $email }}</span>.
                                    @else
                                        Enter the 6-digit code sent to <span class="font-semibold text-text-light dark:text-text-dark">{{ $email }}</span>.
                                    @endif
                                </p>
                            </header>

                            @unless ($emailVerified)
                                <form id="verifyOtpForm" class="auth-form sm:space-y-5" method="POST" action="{{ route('register.verify') }}" novalidate>
                                    @csrf
                                    <input type="hidden" name="email" value="{{ $email }}" />
                                    <input type="hidden" id="otpInput" name="otp" value="{{ old('otp') }}" />

                                    <div class="auth-field">
                                        <label class="mb-2 block text-sm font-semibold text-gray-600 dark:text-gray-300">Verification code</label>
                                        <div id="otpBoxes" class="otp-boxes {{ $errors->has('otp') ? 'has-error' : '' }}" role="group" aria-label="6-digit verification code">
                                            @for ($i = 0; $i < 6; $i++)
                                                <input
                                                    type="text"
                                                    inputmode="numeric"
                                                    pattern="[0-9]*"
                                                    maxlength="1"
                                                    autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                                    class="otp-digit"
                                                    data-otp-index="{{ $i }}"
                                                    aria-label="Digit {{ $i + 1 }}"
                                                />
                                            @endfor
                                        </div>
                                        <p class="auth-hint mt-2 text-xs text-gray-500 dark:text-gray-400">Code expires in 5 minutes.</p>
                                        <p id="regOtpClientError" class="auth-field-bubble auth-field-bubble--block {{ $errors->has('otp') ? '' : 'hidden' }}" role="alert" aria-live="polite">{{ $errors->first('otp') }}</p>
                                    </div>

                                    <button
                                        class="auth-btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/80 dark:focus:ring-offset-background-dark"
                                        type="submit" id="verifyBtn">
                                        Verify code
                                    </button>
                                </form>

                                <div class="mt-4 text-center space-y-2">
                                    @if (($resendDailyLockedSeconds ?? 0) > 0)
                                        <p id="resendStatus" class="text-sm text-gray-500 dark:text-gray-400" data-daily-locked="{{ (int) $resendDailyLockedSeconds }}">
                                            Resend limit reached (2 codes). Try again in <span id="resendCountdown">{{ \App\Support\RegisterEmailOtp::formatWait((int) $resendDailyLockedSeconds) }}</span>.
                                        </p>
                                    @else
                                        <form id="resendOtpForm" method="POST" action="{{ route('register.resend') }}" class="inline">
                                            @csrf
                                            <button
                                                type="submit"
                                                id="resendBtn"
                                                class="text-sm font-bold text-primary hover:underline disabled:text-gray-400 disabled:no-underline disabled:cursor-not-allowed"
                                                data-cooldown="{{ (int) ($resendCooldownSeconds ?? 0) }}"
                                                data-sends-left="{{ (int) ($resendSendsLeft ?? 0) }}"
                                                @disabled(($resendCooldownSeconds ?? 0) > 0 || ($resendSendsLeft ?? 0) <= 0)>
                                                <span id="resendBtnLabel">Resend code</span>
                                            </button>
                                        </form>
                                        <p id="resendHint" class="text-xs text-gray-500 dark:text-gray-400">
                                            @if (($resendSendsLeft ?? 0) <= 0)
                                                No resends left. Try again after 24 hours.
                                            @elseif (($resendCooldownSeconds ?? 0) > 0)
                                                You can resend in <span id="resendCountdown">{{ (int) $resendCooldownSeconds }}</span>s · {{ (int) $resendSendsLeft }} left today
                                            @else
                                                Didn’t get it? You can resend once · {{ (int) $resendSendsLeft }} left today
                                            @endif
                                        </p>
                                    @endif
                                    @error('email')
                                        <p class="text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                                    @enderror
                                </div>
                            @else
                                <form id="registerCompleteForm" class="auth-form sm:space-y-5" method="POST" action="{{ route('register.perform') }}" novalidate>
                                    @csrf
                                    <input type="hidden" name="email" value="{{ $email }}" />

                                    <div class="auth-field">
                                        <div @class([
                                            'auth-field-control auth-field-control--affix border bg-background-light dark:bg-background-dark focus-within:ring-2',
                                            'border-border-light dark:border-border-dark focus-within:ring-primary/50 focus-within:border-primary' => ! $errors->has('password'),
                                            'has-field-error border-red-500 dark:border-red-500 focus-within:border-red-500 focus-within:ring-red-500/40' => $errors->has('password'),
                                        ])>
                                            <input id="passwordInput" name="password" type="password" autocomplete="new-password" minlength="10"
                                                class="auth-field-input text-text-light dark:text-text-dark"
                                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" />
                                            <label for="passwordInput" class="auth-field-label">Password</label>
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
                                        <p class="auth-hint text-xs text-gray-500 dark:text-gray-400">At least 10 characters, with uppercase, lowercase, a number, and a special character (e.g. !@#$%).</p>
                                        <p id="regPwClientError" class="auth-field-bubble {{ $errors->has('password') ? '' : 'hidden' }}" role="alert" aria-live="polite">{{ $errors->first('password') }}</p>
                                    </div>

                                    <div class="auth-field">
                                        <div @class([
                                            'auth-field-control auth-field-control--affix border bg-background-light dark:bg-background-dark focus-within:ring-2',
                                            'border-border-light dark:border-border-dark focus-within:ring-primary/50 focus-within:border-primary' => ! $errors->has('password'),
                                            'has-field-error border-red-500 dark:border-red-500 focus-within:border-red-500 focus-within:ring-red-500/40' => $errors->has('password'),
                                        ])>
                                            <input id="confirmPasswordInput" name="password_confirmation" type="password" autocomplete="new-password" minlength="10"
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
                                        <p id="regConfirmClientError" class="auth-field-bubble hidden" role="alert" aria-live="polite"></p>
                                    </div>

                                    <div class="auth-field auth-terms sm:mt-2">
                                        <div class="flex items-start gap-2.5 sm:gap-3">
                                            <input id="termsCheckbox" type="checkbox" name="terms_accepted" value="1" class="w-4 h-4 sm:w-5 sm:h-5 rounded border-border-light dark:border-border-dark text-primary cursor-pointer mt-0.5 shrink-0" @checked(old('terms_accepted')) />
                                            <label for="termsCheckbox" class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed cursor-pointer">
                                                I agree to the <button type="button" id="termsLink" class="text-primary font-semibold hover:underline">Terms and Conditions</button> and consent to my data being collected and shared as described.
                                            </label>
                                        </div>
                                        <p id="termsClientError" class="auth-field-bubble auth-field-bubble--block {{ $errors->has('terms_accepted') ? '' : 'hidden' }}" role="alert" aria-live="polite">{{ $errors->first('terms_accepted') }}</p>
                                    </div>

                                    <button
                                        class="auth-btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/80 dark:focus:ring-offset-background-dark disabled:cursor-not-allowed"
                                        type="submit" id="submitBtn" disabled>
                                        Create account
                                    </button>
                                </form>
                            @endunless
                        </div>

                        <p class="auth-footer sm:text-sm text-border-light dark:text-border-dark">
                            <a class="font-bold text-primary hover:underline" href="{{ route('register') }}">Use a different email</a>
                            ·
                            <a class="font-bold text-primary hover:underline" href="{{ route('login') }}">Back to login</a>
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @if ($emailVerified)
    <div id="termsModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="termsModalTitle">
        <div class="fixed inset-0 bg-black/50" aria-hidden="true"></div>
        <div class="fixed inset-0 flex items-center justify-center p-3 sm:p-4 pointer-events-none">
            <div data-terms-modal-panel class="pointer-events-auto bg-form-bg-light dark:bg-form-bg-dark rounded-lg sm:rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex justify-between items-center p-4 sm:p-6 border-b border-border-light dark:border-border-dark">
                <h3 id="termsModalTitle" class="text-lg sm:text-2xl font-bold font-heading text-text-light dark:text-text-dark">Terms and Conditions</h3>
                <button type="button" id="closeTermsBtn" class="inline-flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200" aria-label="Close terms and conditions">
                    <span class="material-symbols-outlined text-[20px] sm:text-[22px]">close</span>
                </button>
            </div>

            <div class="overflow-y-auto flex-1 p-4 sm:p-6 space-y-4 sm:space-y-6 text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                <section>
                    <h4 class="font-bold text-base sm:text-lg text-text-light dark:text-text-dark mb-2 sm:mb-3">1. Data Collection and Location Services</h4>
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
                    <h4 class="font-bold text-base sm:text-lg text-text-light dark:text-text-dark mb-2 sm:mb-3">2. Data Sharing and Consent</h4>
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
                    <h4 class="font-bold text-base sm:text-lg text-text-light dark:text-text-dark mb-2 sm:mb-3">3. Legal Basis</h4>
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
                    <h4 class="font-bold text-base sm:text-lg text-text-light dark:text-text-dark mb-2 sm:mb-3">4. Your Rights</h4>
                    <p>Under the Data Privacy Act, you have the right to:</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>Access your personal data</li>
                        <li>Rectify inaccurate information</li>
                        <li>Withdraw your consent (though this may affect service availability)</li>
                        <li>Lodge complaints with the National Privacy Commission</li>
                    </ul>
                </section>

                <section>
                    <h4 class="font-bold text-base sm:text-lg text-text-light dark:text-text-dark mb-2 sm:mb-3">5. Data Security</h4>
                    <p>
                        We implement appropriate technical and organizational measures to protect your personal data from unauthorized access, alteration, disclosure, or destruction. Access to your data is restricted to authorized personnel only.
                    </p>
                </section>

                <section>
                    <h4 class="font-bold text-base sm:text-lg text-text-light dark:text-text-dark mb-2 sm:mb-3">6. Consent Acknowledgment</h4>
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

            <div class="flex gap-2 sm:gap-3 p-4 sm:p-6 border-t border-border-light dark:border-border-dark bg-gray-50 dark:bg-gray-900">
                <button type="button" id="acceptTermsBtn" class="flex-1 bg-primary text-white text-sm sm:text-base font-bold h-9 sm:h-10 rounded-lg hover:bg-primary/90 transition-colors">
                    I Understand and Agree
                </button>
                <button type="button" id="declineTermsBtn" class="flex-1 bg-gray-300 dark:bg-gray-600 text-text-light dark:text-text-dark text-sm sm:text-base font-bold h-9 sm:h-10 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                    Close
                </button>
            </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        (function () {
            @unless ($emailVerified)
            const verifyForm = document.getElementById('verifyOtpForm');
            const otpHidden = document.getElementById('otpInput');
            const otpBoxes = Array.from(document.querySelectorAll('.otp-digit'));
            const otpBoxWrap = document.getElementById('otpBoxes');
            const regOtpClientError = document.getElementById('regOtpClientError');

            function syncOtpHidden() {
                otpHidden.value = otpBoxes.map(function (box) {
                    return box.value || '';
                }).join('');
            }

            function showOtpError(message) {
                otpBoxWrap?.classList.add('has-error');
                if (window.AuthFieldAlerts) {
                    window.AuthFieldAlerts.show(regOtpClientError, message);
                    return;
                }
                if (regOtpClientError) {
                    regOtpClientError.textContent = message;
                    regOtpClientError.classList.remove('hidden');
                }
            }

            function clearOtpError() {
                otpBoxWrap?.classList.remove('has-error');
                window.AuthFieldAlerts?.hide(regOtpClientError);
            }

            function fillFromString(value, startIndex) {
                const digits = String(value || '').replace(/\D/g, '');
                if (!digits) {
                    return;
                }

                let i = startIndex;
                for (const digit of digits) {
                    if (i >= otpBoxes.length) {
                        break;
                    }
                    otpBoxes[i].value = digit;
                    i += 1;
                }
                syncOtpHidden();
                clearOtpError();

                const focusIndex = Math.min(i, otpBoxes.length - 1);
                otpBoxes[focusIndex].focus();
                otpBoxes[focusIndex].select();
            }

            const oldOtp = (otpHidden.value || '').replace(/\D/g, '').slice(0, 6);
            if (oldOtp) {
                fillFromString(oldOtp, 0);
            }

            otpBoxes.forEach(function (box, index) {
                box.addEventListener('input', function (e) {
                    const raw = (e.target.value || '').replace(/\D/g, '');
                    if (raw.length > 1) {
                        fillFromString(raw, index);
                        return;
                    }
                    e.target.value = raw.slice(0, 1);
                    syncOtpHidden();
                    clearOtpError();
                    if (e.target.value && index < otpBoxes.length - 1) {
                        otpBoxes[index + 1].focus();
                    }
                });

                box.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && !box.value && index > 0) {
                        otpBoxes[index - 1].focus();
                        otpBoxes[index - 1].value = '';
                        syncOtpHidden();
                        clearOtpError();
                        e.preventDefault();
                    }
                    if (e.key === 'ArrowLeft' && index > 0) {
                        otpBoxes[index - 1].focus();
                        e.preventDefault();
                    }
                    if (e.key === 'ArrowRight' && index < otpBoxes.length - 1) {
                        otpBoxes[index + 1].focus();
                        e.preventDefault();
                    }
                });

                box.addEventListener('paste', function (e) {
                    e.preventDefault();
                    const pasted = (e.clipboardData || window.clipboardData).getData('text');
                    fillFromString(pasted, index);
                });

                box.addEventListener('focus', function () {
                    box.select();
                });
            });

            verifyForm?.addEventListener('submit', function (e) {
                syncOtpHidden();
                const otp = (otpHidden.value || '').trim();
                if (!/^\d{6}$/.test(otp)) {
                    e.preventDefault();
                    showOtpError('Enter the 6-digit code from your email.');
                    otpBoxes[0]?.focus();
                }
            });

            otpBoxes[0]?.focus();

            (function setupResendCountdown() {
                const resendBtn = document.getElementById('resendBtn');
                const resendBtnLabel = document.getElementById('resendBtnLabel');
                const resendHint = document.getElementById('resendHint');
                const resendStatus = document.getElementById('resendStatus');
                const countdownEl = document.getElementById('resendCountdown');

                if (resendStatus && countdownEl) {
                    let remaining = parseInt(resendStatus.getAttribute('data-daily-locked') || '0', 10);
                    if (remaining <= 0) {
                        return;
                    }

                    const tickDaily = function () {
                        if (remaining <= 0) {
                            window.location.reload();
                            return;
                        }
                        const hours = Math.floor(remaining / 3600);
                        const minutes = Math.ceil((remaining % 3600) / 60);
                        if (hours > 0) {
                            countdownEl.textContent = hours + ' hour' + (hours === 1 ? '' : 's')
                                + (minutes > 0 ? ' ' + minutes + ' min' : '');
                        } else if (remaining >= 60) {
                            const mins = Math.ceil(remaining / 60);
                            countdownEl.textContent = mins + ' minute' + (mins === 1 ? '' : 's');
                        } else {
                            countdownEl.textContent = remaining + ' second' + (remaining === 1 ? '' : 's');
                        }
                        remaining -= 1;
                    };
                    tickDaily();
                    setInterval(tickDaily, 1000);
                    return;
                }

                if (!resendBtn || !resendBtnLabel) {
                    return;
                }

                let cooldown = parseInt(resendBtn.getAttribute('data-cooldown') || '0', 10);
                const sendsLeft = parseInt(resendBtn.getAttribute('data-sends-left') || '0', 10);

                const updateUi = function () {
                    if (sendsLeft <= 0) {
                        resendBtn.disabled = true;
                        resendBtnLabel.textContent = 'Resend code';
                        if (resendHint) {
                            resendHint.textContent = 'No resends left. Try again after 24 hours.';
                        }
                        return;
                    }

                    if (cooldown > 0) {
                        resendBtn.disabled = true;
                        resendBtnLabel.textContent = 'Resend in ' + cooldown + 's';
                        if (resendHint && countdownEl) {
                            countdownEl.textContent = String(cooldown);
                        } else if (resendHint) {
                            resendHint.innerHTML = 'You can resend in <span id="resendCountdown">' + cooldown + '</span>s · ' + sendsLeft + ' left today';
                        }
                        return;
                    }

                    resendBtn.disabled = false;
                    resendBtnLabel.textContent = 'Resend code';
                    if (resendHint) {
                        resendHint.textContent = (sendsLeft === 1
                            ? 'Didn’t get it? You can resend once · 1 left today'
                            : 'Didn’t get it? Resend available · ' + sendsLeft + ' left today');
                    }
                };

                updateUi();

                if (cooldown > 0) {
                    const timer = setInterval(function () {
                        cooldown -= 1;
                        if (cooldown <= 0) {
                            clearInterval(timer);
                            cooldown = 0;
                        }
                        const liveCountdown = document.getElementById('resendCountdown');
                        if (liveCountdown && cooldown > 0) {
                            liveCountdown.textContent = String(cooldown);
                        }
                        if (resendBtnLabel && cooldown > 0) {
                            resendBtnLabel.textContent = 'Resend in ' + cooldown + 's';
                        }
                        if (cooldown <= 0) {
                            updateUi();
                        }
                    }, 1000);
                }
            })();
            @else
            const passwordInput = document.getElementById('passwordInput');
            const confirmPasswordInput = document.getElementById('confirmPasswordInput');
            const registerForm = document.getElementById('registerCompleteForm');
            const regPwClientError = document.getElementById('regPwClientError');
            const regConfirmClientError = document.getElementById('regConfirmClientError');
            const termsCheckbox = document.getElementById('termsCheckbox');
            const termsClientError = document.getElementById('termsClientError');
            const submitBtn = document.getElementById('submitBtn');

            function showRegError(el, message) {
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

            function hideRegErrors() {
                [regPwClientError, regConfirmClientError, termsClientError].forEach(function (el) {
                    window.AuthFieldAlerts?.hide(el);
                });
            }

            function updateSubmitButton() {
                if (submitBtn && termsCheckbox) {
                    submitBtn.disabled = !termsCheckbox.checked;
                }
            }

            updateSubmitButton();

            [passwordInput, confirmPasswordInput].forEach(function (input) {
                input?.addEventListener('input', function () {
                    window.AuthFieldAlerts?.hide(regPwClientError);
                    window.AuthFieldAlerts?.hide(regConfirmClientError);
                });
            });

            document.getElementById('togglePassword')?.addEventListener('click', function () {
                var isHidden = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isHidden ? 'text' : 'password');
                var nowVisible = passwordInput.getAttribute('type') === 'text';
                this.setAttribute('aria-pressed', nowVisible ? 'true' : 'false');
                this.setAttribute('aria-label', nowVisible ? 'Hide password' : 'Show password');
                document.getElementById('passwordEyeOpen').classList.toggle('hidden', nowVisible);
                document.getElementById('passwordEyeClosed').classList.toggle('hidden', !nowVisible);
            });

            document.getElementById('toggleConfirm')?.addEventListener('click', function () {
                var isHidden = confirmPasswordInput.getAttribute('type') === 'password';
                confirmPasswordInput.setAttribute('type', isHidden ? 'text' : 'password');
                var nowVisible = confirmPasswordInput.getAttribute('type') === 'text';
                this.setAttribute('aria-pressed', nowVisible ? 'true' : 'false');
                this.setAttribute('aria-label', nowVisible ? 'Hide password' : 'Show password');
                document.getElementById('confirmEyeOpen').classList.toggle('hidden', nowVisible);
                document.getElementById('confirmEyeClosed').classList.toggle('hidden', !nowVisible);
            });

            const termsModal = document.getElementById('termsModal');
            const termsModalPanel = termsModal?.querySelector('[data-terms-modal-panel]');
            const termsLink = document.getElementById('termsLink');
            const closeTermsBtn = document.getElementById('closeTermsBtn');
            const acceptTermsBtn = document.getElementById('acceptTermsBtn');
            const declineTermsBtn = document.getElementById('declineTermsBtn');

            function openTermsModal() {
                termsModal?.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeTermsModal() {
                termsModal?.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            termsLink?.addEventListener('click', function (e) {
                e.preventDefault();
                openTermsModal();
            });
            closeTermsBtn?.addEventListener('click', closeTermsModal);
            declineTermsBtn?.addEventListener('click', closeTermsModal);
            acceptTermsBtn?.addEventListener('click', function () {
                if (termsCheckbox) {
                    termsCheckbox.checked = true;
                }
                updateSubmitButton();
                termsClientError?.classList.add('hidden');
                closeTermsModal();
            });
            termsModalPanel?.addEventListener('click', function (e) {
                e.stopPropagation();
            });
            termsCheckbox?.addEventListener('change', function () {
                updateSubmitButton();
                if (termsCheckbox.checked) {
                    window.AuthFieldAlerts?.hide(termsClientError);
                }
            });

            registerForm?.addEventListener('submit', function (e) {
                hideRegErrors();
                let ok = true;
                const pw = passwordInput.value || '';
                if (pw.length < 10) {
                    showRegError(regPwClientError, 'Password must be at least 10 characters.');
                    ok = false;
                } else if (!/[a-z]/.test(pw) || !/[A-Z]/.test(pw)) {
                    showRegError(regPwClientError, 'Password must include uppercase and lowercase letters.');
                    ok = false;
                } else if (!/[0-9]/.test(pw)) {
                    showRegError(regPwClientError, 'Password must include at least one number.');
                    ok = false;
                } else if (!/[^A-Za-z0-9]/.test(pw)) {
                    showRegError(regPwClientError, 'Password must include at least one special character (e.g. !@#$%).');
                    ok = false;
                }
                if (pw !== confirmPasswordInput.value) {
                    showRegError(regConfirmClientError, 'Passwords do not match.');
                    ok = false;
                }
                if (!termsCheckbox?.checked) {
                    showRegError(termsClientError, 'You must agree to the terms and conditions to register.');
                    ok = false;
                }
                if (!ok) {
                    e.preventDefault();
                }
            });
            @endunless
        })();
    </script>
</body>

</html>
