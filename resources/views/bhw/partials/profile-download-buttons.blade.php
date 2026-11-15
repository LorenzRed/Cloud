<div id="{{ $downloadId }}" class="w-full min-w-0 xl:w-auto xl:shrink-0">
    <div class="grid w-full grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:justify-start xl:justify-end">
        <button type="button" id="{{ $addButtonId }}" class="col-span-2 inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-primary px-3 sm:px-4 py-2.5 text-sm font-semibold text-white sm:order-last">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">add</span> {{ $addButtonLabel }}
        </button>
        <button type="button" data-report-download="pdf" class="inline-flex min-h-11 min-w-0 items-center justify-center gap-1.5 rounded-lg bg-primary px-3 sm:px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-50">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">picture_as_pdf</span> Download PDF
        </button>
        <button type="button" data-report-download="csv" class="inline-flex min-h-11 min-w-0 items-center justify-center gap-1.5 rounded-lg border border-border-light dark:border-border-dark px-3 sm:px-4 py-2.5 text-sm font-semibold disabled:opacity-50">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">download</span> Download CSV
        </button>
    </div>
    <p class="mt-2 xl:text-right text-xs text-text-light/70 dark:text-text-dark/70">Includes all {{ $downloadLabel }}, including older records.</p>
    <p id="{{ $downloadId }}Status" role="status" class="mt-1 text-sm"></p>
</div>
