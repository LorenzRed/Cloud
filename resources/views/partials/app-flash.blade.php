@if (session('status'))
    <div id="app-flash-toast" class="fixed top-4 left-1/2 z-[100] w-[calc(100%-2rem)] max-w-md -translate-x-1/2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-lg dark:border-emerald-800 dark:bg-emerald-950/90 dark:text-emerald-100 flex items-start gap-3" role="status">
        <span class="material-symbols-outlined shrink-0 text-lg text-emerald-600 dark:text-emerald-400">check_circle</span>
        <p class="flex-1 pt-0.5">{{ session('status') }}</p>
        <button type="button" id="app-flash-dismiss" class="shrink-0 rounded-lg p-1 text-emerald-700 hover:bg-emerald-100 dark:text-emerald-300 dark:hover:bg-emerald-900/50" aria-label="Dismiss notification">
            <span class="material-symbols-outlined text-lg">close</span>
        </button>
    </div>
    <script>
        (function () {
            var el = document.getElementById('app-flash-toast');
            var dismiss = document.getElementById('app-flash-dismiss');
            function remove() { if (el && el.parentNode) el.remove(); }
            if (dismiss) dismiss.addEventListener('click', remove);
            setTimeout(remove, 8000);
        })();
    </script>
@endif
