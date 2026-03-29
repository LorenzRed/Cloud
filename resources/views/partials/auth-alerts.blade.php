@php
    $statusMessage = session('status');
    $errorMessage = session('error');
@endphp
@if ($statusMessage || $errorMessage)
    <div id="auth-alert-stack" class="fixed top-4 inset-x-4 z-[100] flex flex-col gap-2 sm:left-auto sm:right-4 sm:max-w-md sm:ml-auto pointer-events-none">
        @if ($statusMessage)
            <div class="pointer-events-auto flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-lg dark:border-emerald-800 dark:bg-emerald-950/90 dark:text-emerald-100" role="status">
                <span class="material-symbols-outlined shrink-0 text-lg text-emerald-600 dark:text-emerald-400">check_circle</span>
                <p class="flex-1 pt-0.5">{{ $statusMessage }}</p>
                <button type="button" data-auth-alert-dismiss class="shrink-0 rounded-lg p-1 text-emerald-700 hover:bg-emerald-100 dark:text-emerald-300 dark:hover:bg-emerald-900/50" aria-label="Dismiss notification">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>
        @endif
        @if ($errorMessage)
            <div class="pointer-events-auto flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 shadow-lg dark:border-red-800 dark:bg-red-950/90 dark:text-red-100" role="alert">
                <span class="material-symbols-outlined shrink-0 text-lg text-red-600 dark:text-red-400">error</span>
                <p class="flex-1 pt-0.5">{{ $errorMessage }}</p>
                <button type="button" data-auth-alert-dismiss class="shrink-0 rounded-lg p-1 text-red-700 hover:bg-red-100 dark:text-red-300 dark:hover:bg-red-900/50" aria-label="Dismiss">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>
        @endif
    </div>
    <script>
        (function () {
            var stack = document.getElementById('auth-alert-stack');
            if (!stack) return;
            stack.querySelectorAll('[data-auth-alert-dismiss]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var card = btn.closest('[role="status"], [role="alert"]');
                    if (card) card.remove();
                    if (stack && !stack.querySelector('[role="status"], [role="alert"]')) stack.remove();
                });
            });
            setTimeout(function () {
                if (stack && stack.parentNode) stack.remove();
            }, 10000);
        })();
    </script>
@endif
