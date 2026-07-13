@php
    $exportAllRoute = $exportAllRoute ?? '#';
    $searchPlaceholder = $searchPlaceholder ?? 'Search records...';
    $pageTitle = $pageTitle ?? 'Records';
@endphp

<div class="record-toolbar flex flex-col gap-2.5">
    <h1 class="record-page-title text-text-light dark:text-text-dark">{{ $pageTitle }}</h1>

    <div class="record-toolbar-row">
        <div class="record-search-wrap">
            <div class="relative flex-1 min-w-0">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50 text-lg pointer-events-none">search</span>
                <input
                    id="recordSearch"
                    type="search"
                    maxlength="100"
                    autocomplete="off"
                    placeholder="{{ $searchPlaceholder }}"
                    class="w-full h-10 pl-10 pr-3 rounded-xl bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm"
                />
            </div>
            <button
                type="button"
                id="toggleFiltersBtn"
                class="md:hidden h-10 px-3.5 rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-xs font-semibold inline-flex items-center justify-center gap-1.5 shrink-0"
                aria-expanded="false"
                aria-controls="filtersPanel"
            >
                <span class="material-symbols-outlined text-base">tune</span>
                <span class="sr-only">Toggle </span>Filters
                <span id="activeFilterBadge" class="hidden min-w-[1.125rem] h-4 px-1 rounded-full bg-primary text-white text-[10px] font-bold inline-flex items-center justify-center">0</span>
            </button>
        </div>

        <div id="filtersPanel" class="collapsed w-full md:w-auto">
            <div class="filter-field filter-field--purok">
                <label for="purokFilter">Purok</label>
                <select id="purokFilter" class="filter-control">
                    <option value="">All puroks</option>
                    @foreach ($purokOptions as $purokName)
                        <option value="{{ $purokName }}">{{ $purokName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field filter-field--date">
                <label for="dateFromFilter">From</label>
                <input type="date" id="dateFromFilter" class="filter-control" />
            </div>
            <div class="filter-field filter-field--date">
                <label for="dateToFilter">To</label>
                <input type="date" id="dateToFilter" class="filter-control" />
            </div>
            <button type="button" id="clearFiltersBtn" class="h-10 px-3 rounded-xl border border-border-light dark:border-border-dark text-xs font-semibold text-text-light/70 dark:text-text-dark/70 hover:bg-background-light dark:hover:bg-background-dark shrink-0">
                Clear filters
            </button>
        </div>
    </div>

    <div class="record-export-actions">
        <a href="{{ $exportAllRoute }}" class="record-export-btn border border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark text-text-light dark:text-text-dark">
            <span class="material-symbols-outlined text-base">download</span>
            Download all
        </a>
        <button type="button" id="downloadFilteredBtn" class="record-export-btn bg-primary text-white">
            <span class="material-symbols-outlined text-base">file_download</span>
            Download filtered
        </button>
    </div>
</div>
