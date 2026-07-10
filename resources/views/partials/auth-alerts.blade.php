@php
    $statusMessage = session('status');
    $errorMessage = session('error');
@endphp
@if ($statusMessage || $errorMessage)
    <div id="auth-alert-stack" class="fixed top-4 inset-x-5 z-[100] flex flex-col gap-2 sm:inset-x-4 sm:left-auto sm:right-4 sm:max-w-md sm:ml-auto pointer-events-none">
        @if ($statusMessage)
            <div data-auth-alert data-auto-dismiss="2000" class="pointer-events-auto flex items-start gap-2 sm:gap-3 rounded-lg sm:rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-emerald-900 shadow-lg transition-all duration-300 dark:border-emerald-800 dark:bg-emerald-950/90 dark:text-emerald-100" role="status">
                <span class="material-symbols-outlined shrink-0 text-base sm:text-lg text-emerald-600 dark:text-emerald-400">check_circle</span>
                <p class="flex-1 pt-0.5">{{ $statusMessage }}</p>
                <button type="button" data-auth-alert-dismiss class="shrink-0 rounded-lg p-1 text-emerald-700 hover:bg-emerald-100 dark:text-emerald-300 dark:hover:bg-emerald-900/50" aria-label="Dismiss notification">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>
        @endif
        @if ($errorMessage)
            <div data-auth-alert data-auto-dismiss="2000" class="pointer-events-auto flex items-start gap-2 sm:gap-3 rounded-lg sm:rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-red-900 shadow-lg transition-all duration-300 dark:border-red-800 dark:bg-red-950/90 dark:text-red-100" role="alert">
                <span class="material-symbols-outlined shrink-0 text-base sm:text-lg text-red-600 dark:text-red-400">error</span>
                <p class="flex-1 pt-0.5">{{ $errorMessage }}</p>
                <button type="button" data-auth-alert-dismiss class="shrink-0 rounded-lg p-1 text-red-700 hover:bg-red-100 dark:text-red-300 dark:hover:bg-red-900/50" aria-label="Dismiss">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>
        @endif
    </div>
@endif
<script>
    (function () {
        var FADE_MS = 300;
        var DEFAULT_DELAY = 2000;
        var timers = new WeakMap();

        function fadeOutAndRemove(el) {
            if (!el || !el.parentNode) {
                return;
            }

            el.style.opacity = '0';
            el.style.transform = 'translateY(-6px)';

            window.setTimeout(function () {
                el.remove();

                var stack = document.getElementById('auth-alert-stack');
                if (stack && !stack.children.length) {
                    stack.remove();
                }
            }, FADE_MS);
        }

        function findFieldControl(el) {
            var field = el && el.closest ? el.closest('.auth-field') : null;

            return field ? field.querySelector('.auth-field-control') : null;
        }

        function setFieldErrorState(el, active) {
            var control = findFieldControl(el);

            if (control) {
                control.classList.toggle('has-field-error', active);
            }

            var field = el && el.closest ? el.closest('.auth-field') : null;
            var input = field ? field.querySelector('.auth-field-input') : null;

            if (input) {
                input.setAttribute('aria-invalid', active ? 'true' : 'false');
            }
        }

        function hideFieldError(el) {
            if (!el) {
                return;
            }

            var timer = timers.get(el);
            if (timer) {
                window.clearTimeout(timer);
                timers.delete(el);
            }

            el.textContent = '';
            el.classList.add('hidden');
            el.style.opacity = '';
            el.style.transform = '';
            el.style.transition = '';
            setFieldErrorState(el, false);
        }

        function fadeOutFieldError(el) {
            if (!el || el.classList.contains('hidden')) {
                return;
            }

            var timer = timers.get(el);
            if (timer) {
                window.clearTimeout(timer);
                timers.delete(el);
            }

            el.style.transition = 'opacity ' + FADE_MS + 'ms ease, transform ' + FADE_MS + 'ms ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-4px) scale(0.98)';

            window.setTimeout(function () {
                hideFieldError(el);
            }, FADE_MS);
        }

        function hideAllInForm(form) {
            if (!form) {
                return;
            }

            form.querySelectorAll('.auth-field-bubble[role="alert"]').forEach(hideFieldError);
        }

        function focusFieldInput(el) {
            var field = el && el.closest ? el.closest('.auth-field') : null;
            var input = field ? field.querySelector('.auth-field-input, input[type="checkbox"]') : null;

            if (input) {
                input.focus();
            }
        }

        function scheduleFieldError(el, delay) {
            if (!el) {
                return;
            }

            var timer = timers.get(el);
            if (timer) {
                window.clearTimeout(timer);
            }

            timers.set(el, window.setTimeout(function () {
                fadeOutFieldError(el);
            }, delay));
        }

        function scheduleToast(el, delay) {
            if (!el || el.dataset.authDismissScheduled === '1') {
                return;
            }

            el.dataset.authDismissScheduled = '1';
            el.style.transition = 'opacity ' + FADE_MS + 'ms ease, transform ' + FADE_MS + 'ms ease';

            window.setTimeout(function () {
                fadeOutAndRemove(el);
            }, delay);
        }

        window.AuthFieldAlerts = {
            show: function (el, message, delay) {
                if (!el) {
                    return;
                }

                delay = delay === undefined ? DEFAULT_DELAY : delay;
                el.textContent = message;
                el.classList.remove('hidden');
                el.style.opacity = '0';
                el.style.transform = 'translateY(-4px) scale(0.98)';
                el.style.transition = 'opacity ' + FADE_MS + 'ms ease, transform ' + FADE_MS + 'ms ease';
                setFieldErrorState(el, true);

                window.requestAnimationFrame(function () {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0) scale(1)';
                });

                scheduleFieldError(el, delay);
            },
            hide: hideFieldError,
            hideAll: hideAllInForm,
            focusField: focusFieldInput,
            schedule: scheduleFieldError,
        };

        function initAuthAlerts() {
            document.querySelectorAll('[data-auth-alert]').forEach(function (el) {
                var delay = Number(el.getAttribute('data-auto-dismiss')) || DEFAULT_DELAY;
                scheduleToast(el, delay);
            });

            document.querySelectorAll('[data-auth-alert-dismiss]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var card = btn.closest('[data-auth-alert]');
                    if (card) {
                        fadeOutAndRemove(card);
                    }
                });
            });

            document.querySelectorAll('.auth-field-bubble[role="alert"]').forEach(function (el) {
                if (el.classList.contains('hidden')) {
                    return;
                }

                setFieldErrorState(el, true);
                scheduleFieldError(el, DEFAULT_DELAY);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAuthAlerts);
        } else {
            initAuthAlerts();
        }
    })();
</script>
