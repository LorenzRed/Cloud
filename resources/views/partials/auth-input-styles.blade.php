<style>
    .material-symbols-outlined {
        font-variation-settings:
            'FILL' 0,
            'wght' 400,
            'GRAD' 0,
            'opsz' 24;
    }

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

    :is(.auth-field-input):is(:-webkit-autofill, :-webkit-autofill:hover, :-webkit-autofill:focus) {
        -webkit-text-fill-color: #333333;
        caret-color: #333333;
        box-shadow: 0 0 0 1000px #F4F7F6 inset;
        transition: background-color 5000s ease-in-out 0s;
    }

    .dark :is(.auth-field-input):is(:-webkit-autofill, :-webkit-autofill:hover, :-webkit-autofill:focus) {
        -webkit-text-fill-color: #E5E7EB;
        caret-color: #E5E7EB;
        box-shadow: 0 0 0 1000px #1F2937 inset;
    }

    .auth-field {
        position: relative;
        width: 100%;
    }

    .auth-field-bubble {
        position: absolute;
        left: 0.5rem;
        right: 0.5rem;
        bottom: auto;
        top: calc(100% + 0.4rem);
        z-index: 40;
        margin: 0;
        padding: 0.45rem 0.7rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        line-height: 1.35;
        font-weight: 600;
        color: #ffffff;
        background-color: #dc2626;
        box-shadow: 0 12px 24px rgba(220, 38, 38, 0.22);
        pointer-events: none;
    }

    .auth-field-bubble::before {
        content: '';
        position: absolute;
        left: 1.25rem;
        bottom: 100%;
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-bottom: 6px solid #dc2626;
    }

    .dark .auth-field-bubble {
        background-color: #ef4444;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.35);
    }

    .dark .auth-field-bubble::before {
        border-bottom-color: #ef4444;
    }

    .auth-field-bubble.hidden {
        display: none !important;
    }

    .auth-field-bubble--block {
        left: 0;
        right: 0;
    }

    .auth-field-control.has-field-error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.14);
    }

    #loginForm,
    #registerForm {
        overflow: visible;
    }

    .auth-field-control {
        position: relative;
        display: flex;
        align-items: stretch;
        --auth-field-height: 3.5rem;
        --auth-label-rest-size: 1.0625rem;
        --auth-label-float-size: 0.8125rem;
        --auth-input-size: 1.0625rem;
        min-height: var(--auth-field-height);
        height: var(--auth-field-height);
        border-radius: 0.75rem !important;
        overflow: hidden;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .auth-field-control:focus-within {
        outline: none;
    }

    /* Rest: label centered in field. */
    .auth-field-label {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #A9A9A9;
        font-size: var(--auth-label-rest-size);
        line-height: 1.2;
        transition: top 0.2s ease, transform 0.2s ease, font-size 0.2s ease, color 0.2s ease, font-weight 0.2s ease;
        pointer-events: none;
        z-index: 4;
        max-width: calc(100% - 5rem);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transform-origin: left center;
        padding: 0;
        margin: 0;
        background: transparent !important;
        font-weight: 400;
    }

    .dark .auth-field-label {
        color: #9CA3AF;
    }

    .auth-field-control--affix .auth-field-label {
        max-width: calc(100% - 3.5rem);
    }

    /* Floated: label sits on top edge inside the field — no background, fixed field height. */
    .auth-field-control.label-floating .auth-field-label {
        top: 0.4375rem;
        left: 16px;
        transform: translateY(0);
        font-size: var(--auth-label-float-size);
        line-height: 1.1;
        font-weight: 600;
        letter-spacing: 0.01em;
        color: #4A90E2;
        max-width: calc(100% - 5rem);
        padding: 0;
        margin: 0;
        border-radius: 0;
        background: transparent !important;
    }

    .dark .auth-field-control.label-floating .auth-field-label {
        color: #60A5FA;
    }

    .auth-field-input {
        position: relative;
        z-index: 0;
        flex: 1 1 auto;
        min-width: 0;
        width: 100%;
        min-height: var(--auth-field-height);
        height: var(--auth-field-height);
        border: 0 !important;
        background: transparent !important;
        padding: 0 16px;
        font-size: var(--auth-input-size);
        line-height: 1.25;
        color: inherit;
        border-radius: 0 !important;
        -webkit-appearance: none;
        appearance: none;
        box-shadow: none !important;
        transition: padding 0.2s ease;
    }

    .auth-field-control:not(.label-floating) .auth-field-input {
        padding-top: 0;
        padding-bottom: 0;
    }

    .auth-field-control.label-floating .auth-field-input {
        padding: 1.3125rem 16px 0.4375rem;
        font-size: var(--auth-input-size);
    }

    .auth-field-input:focus {
        outline: none;
        box-shadow: none;
    }

    .auth-field-control--affix:not(.label-floating) .auth-field-input {
        padding: 0 0.35rem 0 16px;
    }

    .auth-field-control--affix.label-floating .auth-field-input {
        padding: 1.3125rem 0.35rem 0.4375rem 16px;
        font-size: var(--auth-input-size);
    }

    button.auth-field-affix,
    .auth-field-affix {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        align-self: stretch;
        padding: 0 14px 0 10px;
        margin: 0 !important;
        border: none !important;
        border-left: none !important;
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
        outline: none;
        color: #A9A9A9;
        z-index: 2;
        cursor: pointer;
        -webkit-appearance: none;
        appearance: none;
    }

    .dark .auth-field-affix {
        color: #6B7280;
    }

    .auth-field-affix:hover {
        color: #4A90E2;
    }

    .dark .auth-field-affix:hover {
        color: #60A5FA;
    }

    .auth-field-affix:focus-visible {
        outline: 2px solid #4A90E2;
        outline-offset: 1px;
    }

    .auth-field-input::placeholder {
        color: transparent;
    }

    .auth-field-control--readonly {
        background-color: #f3f4f6;
    }

    .dark .auth-field-control--readonly {
        background-color: rgba(31, 41, 55, 0.5);
    }

    .auth-field-control--readonly .auth-field-input:read-only {
        cursor: not-allowed;
        opacity: 0.95;
    }

    /* Lets JS detect autofill via animation event. */
    @keyframes auth-autofill-detect {
        from {
            opacity: 1;
        }

        to {
            opacity: 1;
        }
    }

    .auth-field-input:-webkit-autofill {
        animation-name: auth-autofill-detect;
        animation-duration: 0.01s;
    }

    .auth-screen {
        width: 100%;
    }

    .auth-screen__inner {
        width: 100%;
        max-width: 28rem;
        margin-left: auto;
        margin-right: auto;
    }

    .auth-panel {
        width: 100%;
    }

    .auth-tabs {
        display: flex;
        padding: 0.25rem;
        border-radius: 9999px;
        background: rgba(148, 163, 184, 0.14);
        border: 1px solid rgba(148, 163, 184, 0.2);
    }

    .dark .auth-tabs {
        background: rgba(15, 23, 42, 0.45);
        border-color: rgba(71, 85, 105, 0.55);
    }

    .auth-tabs__item {
        flex: 1 1 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2.5rem;
        padding: 0 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 700;
        line-height: 1.2;
        color: #64748b;
        transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    }

    .dark .auth-tabs__item {
        color: #94a3b8;
    }

    .auth-tabs__item.is-active {
        color: #ffffff;
        background: #4A90E2;
        box-shadow: 0 8px 20px rgba(74, 144, 226, 0.28);
    }

    .auth-btn-primary {
        width: 100%;
        min-height: 2.75rem;
        border-radius: 0.875rem;
        background: #4A90E2;
        color: #ffffff;
        font-size: 0.9375rem;
        font-weight: 700;
        line-height: 1.2;
        box-shadow: 0 10px 24px rgba(74, 144, 226, 0.28);
        transition: background-color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
    }

    .auth-btn-primary:hover {
        background: rgba(74, 144, 226, 0.92);
    }

    .auth-btn-primary:active {
        transform: translateY(1px);
        box-shadow: 0 6px 16px rgba(74, 144, 226, 0.22);
    }

    .auth-btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        box-shadow: none;
    }

    @media (min-width: 640px) {
        .auth-field-control {
            --auth-field-height: 3.625rem;
            --auth-label-rest-size: 1.125rem;
            --auth-label-float-size: 0.875rem;
            --auth-input-size: 1.125rem;
        }

        .auth-btn-primary {
            min-height: 3rem;
            border-radius: 0.75rem;
            font-size: 1rem;
            box-shadow: none;
        }

        .auth-btn-primary:active {
            transform: none;
        }

        .auth-screen__inner {
            flex: none;
            display: block;
        }

        .auth-footer {
            margin-top: 1.5rem;
            padding-top: 0;
            padding-bottom: 0;
            text-align: center;
        }
    }

    @media (max-width: 639px) {
        .auth-screen {
            padding: 1.25rem 1.25rem 1.5rem;
            align-items: stretch !important;
            justify-content: flex-start !important;
            min-height: 100dvh;
        }

        .auth-screen__inner {
            max-width: 22rem;
            padding-top: 0.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }

        .auth-screen__brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            width: 100%;
            margin-bottom: 1.25rem;
            text-align: center;
        }

        .auth-screen__brand img {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.625rem;
        }

        .auth-screen__brand h1 {
            font-size: 1.125rem;
            font-weight: 900;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .auth-panel {
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            border-radius: 0 !important;
        }

        .auth-panel__header {
            margin-top: 2.5rem;
            margin-bottom: 2rem;
        }

        .auth-panel__title {
            font-size: 1.5rem;
            font-weight: 900;
            line-height: 1.15;
            letter-spacing: -0.03em;
        }

        .auth-panel__subtitle {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            line-height: 1.45;
            color: #64748b;
        }

        .dark .auth-panel__subtitle {
            color: #94a3b8;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .auth-field {
            position: relative;
            padding-top: 0;
        }

        .auth-field-control {
            position: relative;
            display: flex;
            align-items: stretch;
            overflow: visible;
            --auth-field-height: 3.5rem;
            --auth-label-rest-size: 1.0625rem;
            --auth-label-float-size: 0.8125rem;
            --auth-input-size: 1.0625rem;
            min-height: var(--auth-field-height) !important;
            height: var(--auth-field-height) !important;
            padding: 0;
            margin-top: 0;
            border-radius: 0.75rem !important;
            background: #f1f5f9 !important;
            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.18);
        }

        .auth-field-control.label-floating {
            min-height: var(--auth-field-height) !important;
            height: var(--auth-field-height) !important;
            margin-top: 0;
        }

        .dark .auth-field-control {
            background: rgba(15, 23, 42, 0.55) !important;
            box-shadow: inset 0 0 0 1px rgba(71, 85, 105, 0.45);
        }

        .auth-field-control:focus-within {
            background: #ffffff !important;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.14), inset 0 0 0 1px #4A90E2;
        }

        .dark .auth-field-control:focus-within {
            background: rgba(15, 23, 42, 0.85) !important;
        }

        .auth-field-label {
            left: 12px;
            font-size: var(--auth-label-rest-size);
            line-height: 1.2;
            max-width: calc(100% - 3.5rem);
        }

        .auth-field-control--affix .auth-field-label {
            max-width: calc(100% - 3rem);
        }

        .auth-field-control.label-floating .auth-field-label {
            top: 0.4375rem;
            left: 12px;
            transform: translateY(0);
            font-size: var(--auth-label-float-size);
            font-weight: 600;
            line-height: 1.1;
            color: #4A90E2;
            max-width: calc(100% - 3.5rem);
            padding: 0;
            margin: 0;
            background: transparent !important;
        }

        .dark .auth-field-control.label-floating .auth-field-label {
            color: #60a5fa;
            background: transparent !important;
        }

        .auth-field-input {
            min-height: var(--auth-field-height) !important;
            height: var(--auth-field-height) !important;
            padding: 0 12px !important;
            font-size: var(--auth-input-size) !important;
            line-height: 1.25;
        }

        .auth-field-control.label-floating .auth-field-input {
            min-height: var(--auth-field-height) !important;
            height: var(--auth-field-height) !important;
            padding: 1.3125rem 12px 0.4375rem !important;
            font-size: var(--auth-input-size) !important;
        }

        :is(.auth-field-input):is(:-webkit-autofill, :-webkit-autofill:hover, :-webkit-autofill:focus) {
            -webkit-text-fill-color: #334155;
            caret-color: #334155;
            box-shadow: 0 0 0 1000px #f1f5f9 inset;
        }

        .dark :is(.auth-field-input):is(:-webkit-autofill, :-webkit-autofill:hover, :-webkit-autofill:focus) {
            -webkit-text-fill-color: #e2e8f0;
            caret-color: #e2e8f0;
            box-shadow: 0 0 0 1000px rgba(15, 23, 42, 0.55) inset;
        }

        .auth-field-control--affix:not(.label-floating) .auth-field-input {
            padding: 0 0.25rem 0 12px !important;
        }

        .auth-field-control--affix.label-floating .auth-field-input {
            min-height: var(--auth-field-height) !important;
            height: var(--auth-field-height) !important;
            padding: 1.3125rem 0.25rem 0.4375rem 12px !important;
            font-size: var(--auth-input-size) !important;
        }

        .auth-field-control--affix .auth-field-input {
            flex: 1 1 auto;
            min-width: 0;
        }

        button.auth-field-affix,
        .auth-field-affix {
            align-self: stretch;
            width: auto;
            min-height: 0;
            padding: 0 10px 0 6px;
            margin: 0 !important;
            border-radius: 0;
            color: #94a3b8;
        }

        .auth-field-affix svg {
            width: 1rem;
            height: 1rem;
        }

        .auth-btn-primary {
            min-height: 2.375rem;
            font-size: 0.875rem;
            border-radius: 0.75rem;
        }

        .auth-tabs__item {
            min-height: 2.25rem;
            font-size: 0.8125rem;
        }

        .auth-forgot-password {
            margin-top: 0.375rem;
            margin-bottom: 0.375rem;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }

        .auth-field-bubble {
            position: absolute;
            left: 0.5rem;
            right: 0.5rem;
            top: calc(100% + 0.35rem);
            margin-top: 0;
            font-size: 0.6875rem;
            padding: 0.4rem 0.6rem;
        }

        .auth-field-bubble::before {
            display: block;
            left: 1rem;
        }

        .auth-footer {
            margin-top: auto;
            padding-top: 1.5rem;
            padding-bottom: 0.25rem;
            text-align: center;
            font-size: 0.8125rem;
            color: #64748b;
            width: 100%;
        }

        .dark .auth-footer {
            color: #94a3b8;
        }

        .auth-terms {
            margin-top: 0.5rem;
        }

        .auth-terms label {
            font-size: 0.8125rem;
            line-height: 1.45;
        }

        .auth-terms input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            margin-top: 0.125rem;
        }

        .auth-hint {
            margin-top: 0.35rem;
            padding-left: 0.125rem;
            font-size: 0.75rem;
            line-height: 1.4;
        }
    }
</style>
<script>
    (function () {
        function isAutofilled(el) {
            try {
                return el.matches && el.matches(':-webkit-autofill');
            } catch (e) {
                return false;
            }
        }

        function updateLabelFloat(input) {
            var ctl = input && input.closest('.auth-field-control');
            if (!ctl) {
                return;
            }
            var v = input.value != null ? String(input.value).trim() : '';
            var hasVal = v.length > 0;
            var focused = document.activeElement === input;
            var float = hasVal || focused || isAutofilled(input);
            ctl.classList.toggle('label-floating', float);
        }

        function bindAuthFloat(input) {
            if (!input || input.dataset.authLabelBound === '1') {
                return;
            }
            input.dataset.authLabelBound = '1';

            input.addEventListener('focus', function () {
                updateLabelFloat(input);
            });
            input.addEventListener('blur', function () {
                requestAnimationFrame(function () {
                    updateLabelFloat(input);
                });
            });
            input.addEventListener('input', function () {
                updateLabelFloat(input);
            });
            input.addEventListener('change', function () {
                updateLabelFloat(input);
            });
            input.addEventListener('animationstart', function (e) {
                if (e.animationName === 'auth-autofill-detect') {
                    updateLabelFloat(input);
                }
            }, true);

            updateLabelFloat(input);
        }

        function initAuthLabels() {
            document.querySelectorAll('.auth-field-input').forEach(bindAuthFloat);
            setTimeout(function () {
                document.querySelectorAll('.auth-field-input').forEach(updateLabelFloat);
            }, 50);
            setTimeout(function () {
                document.querySelectorAll('.auth-field-input').forEach(updateLabelFloat);
            }, 200);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAuthLabels);
        } else {
            initAuthLabels();
        }
    })();
</script>
