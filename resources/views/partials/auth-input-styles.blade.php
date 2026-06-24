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
        width: 100%;
    }

    .auth-field-control {
        position: relative;
        display: flex;
        align-items: stretch;
        min-height: 3rem;
        border-radius: 0.75rem !important;
        overflow: hidden;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .auth-field-control:focus-within {
        outline: none;
    }

    /* Rest: label centered in field (grey, full size). */
    .auth-field-label {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #A9A9A9;
        font-size: 1rem;
        line-height: 1.25;
        transition: top 0.2s ease, transform 0.2s ease, font-size 0.2s ease, color 0.2s ease, padding 0.2s ease, font-weight 0.2s ease;
        pointer-events: none;
        z-index: 4;
        max-width: calc(100% - 5rem);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transform-origin: left center;
        padding: 0;
        margin: 0;
        background: none;
        font-weight: 400;
    }

    .dark .auth-field-label {
        color: #9CA3AF;
    }

    .auth-field-control--affix .auth-field-label {
        max-width: calc(100% - 3.5rem);
    }

    /* Floated: only when .label-floating (JS: focus, value, or autofill). */
    .auth-field-control.label-floating .auth-field-label {
        top: 6px;
        left: 14px;
        transform: translateY(0);
        font-size: 0.6875rem;
        line-height: 1.2;
        font-weight: 600;
        letter-spacing: 0.01em;
        color: #4A90E2;
        max-width: calc(100% - 5rem);
        padding: 0 5px 0 3px;
        margin-left: -2px;
        border-radius: 3px;
        background-color: inherit;
    }

    .dark .auth-field-control.label-floating .auth-field-label {
        color: #60A5FA;
    }

    /* Rest: even vertical padding so label can sit in true center. */
    .auth-field-input {
        position: relative;
        z-index: 0;
        flex: 1 1 auto;
        min-width: 0;
        width: 100%;
        min-height: 3rem;
        height: 3rem;
        border: 0 !important;
        background: transparent !important;
        padding: 0.875rem 16px;
        font-size: 1rem;
        line-height: 1.25;
        color: inherit;
        border-radius: 0 !important;
        -webkit-appearance: none;
        appearance: none;
        box-shadow: none !important;
        transition: padding 0.2s ease;
    }

    .auth-field-control.label-floating .auth-field-input {
        padding: 1rem 16px 0.75rem;
    }

    .auth-field-input:focus {
        outline: none;
        box-shadow: none;
    }

    .auth-field-control--affix:not(.label-floating) .auth-field-input {
        padding: 0.875rem 0.35rem 0.875rem 16px;
    }

    .auth-field-control--affix.label-floating .auth-field-input {
        padding: 1rem 0.35rem 0.75rem 16px;
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
