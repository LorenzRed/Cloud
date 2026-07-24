<script>
    const toggleFiltersBtn = document.getElementById('toggleFiltersBtn');
    const filtersPanel = document.getElementById('filtersPanel');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const activeFilterBadge = document.getElementById('activeFilterBadge');
    const noResultsRow = document.getElementById('noResultsRow');
    const recordSearch = document.getElementById('recordSearch');
    const purokFilter = document.getElementById('purokFilter');
    const dateFromFilter = document.getElementById('dateFromFilter');
    const dateToFilter = document.getElementById('dateToFilter');
    const statVisible = document.getElementById('statVisible');
    const downloadFilteredBtn = document.getElementById('downloadFilteredBtn');
    const recordRows = Array.from(document.querySelectorAll('.record-row'));

    const csvFilename = @json($csvFilename ?? 'records.csv');
    const csvTitle = @json($csvTitle ?? '');
    const csvHeaders = @json($csvHeaders ?? []);
    const csvKeys = @json($csvKeys ?? []);
    const hasDateFilter = @json($hasDateFilter ?? false);

    function setFiltersOpen(open) {
        if (!filtersPanel || !toggleFiltersBtn) return;
        filtersPanel.classList.toggle('open', open);
        filtersPanel.classList.toggle('collapsed', !open);
        toggleFiltersBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggleFiltersBtn.classList.toggle('active', open);
    }

    toggleFiltersBtn?.addEventListener('click', () => {
        const isOpen = filtersPanel?.classList.contains('open');
        setFiltersOpen(!isOpen);
    });

    if (window.innerWidth >= 768) {
        setFiltersOpen(true);
    }

    function countActiveFilters() {
        let count = 0;
        if ((recordSearch?.value || '').trim()) count += 1;
        if (purokFilter?.value) count += 1;
        if (hasDateFilter && dateFromFilter?.value) count += 1;
        if (hasDateFilter && dateToFilter?.value) count += 1;
        return count;
    }

    function updateFilterBadge() {
        const count = countActiveFilters();
        if (!activeFilterBadge) return;
        activeFilterBadge.textContent = String(count);
        activeFilterBadge.classList.toggle('hidden', count === 0);
        toggleFiltersBtn?.classList.toggle('active', count > 0 || filtersPanel?.classList.contains('open'));
    }

    function rowMatchesFilters(row) {
        const query = (recordSearch?.value || '').trim().toLowerCase();
        const purok = purokFilter?.value || '';
        const dateFrom = dateFromFilter?.value || '';
        const dateTo = dateToFilter?.value || '';

        const matchesSearch = !query || (row.dataset.search || '').includes(query);
        const matchesPurok = !purok || row.dataset.purok === purok;

        let matchesDate = true;
        if (hasDateFilter && (dateFrom || dateTo)) {
            const rowDates = (row.dataset.dates || row.dataset.date || '')
                .split(',')
                .map((value) => value.trim())
                .filter(Boolean);

            if (!rowDates.length) {
                matchesDate = false;
            } else {
                matchesDate = rowDates.some((rowDate) => {
                    if (dateFrom && rowDate < dateFrom) return false;
                    if (dateTo && rowDate > dateTo) return false;
                    return true;
                });
            }
        }

        return matchesSearch && matchesPurok && matchesDate;
    }

    function collapseRecordRows() {
        document.querySelectorAll('.record-row.is-expanded').forEach((row) => {
            row.classList.remove('is-expanded');
            row.querySelector('.record-mobile-toggle')?.setAttribute('aria-expanded', 'false');
        });
    }

    function showRecordToast(message, tone = 'info') {
        let toast = document.getElementById('recordListToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'recordListToast';
            toast.className = 'fixed bottom-24 md:bottom-6 left-1/2 -translate-x-1/2 z-[80] max-w-[min(92vw,24rem)] px-4 py-2.5 rounded-lg text-white text-sm font-semibold shadow-lg opacity-0 pointer-events-none transition-opacity duration-300 text-center';
            document.body.appendChild(toast);
        }

        toast.textContent = message;
        toast.style.backgroundColor = tone === 'error' ? '#D0021B' : (tone === 'success' ? '#10B981' : '#4A90E2');
        toast.classList.remove('opacity-0');
        window.clearTimeout(showRecordToast._timer);
        showRecordToast._timer = window.setTimeout(() => toast.classList.add('opacity-0'), 2800);
    }

    function hasCompleteDateRange() {
        return Boolean((dateFromFilter?.value || '').trim() && (dateToFilter?.value || '').trim());
    }

    function updateDownloadFilteredState() {
        if (!downloadFilteredBtn) return;

        if (hasDateFilter) {
            const enabled = hasCompleteDateRange();
            downloadFilteredBtn.disabled = !enabled;
            downloadFilteredBtn.setAttribute('aria-disabled', enabled ? 'false' : 'true');
            downloadFilteredBtn.title = enabled
                ? 'Download records for the selected receive date range'
                : 'Select From and To receive dates to enable';
            return;
        }

        const enabled = countActiveFilters() > 0;
        downloadFilteredBtn.disabled = !enabled;
        downloadFilteredBtn.setAttribute('aria-disabled', enabled ? 'false' : 'true');
        downloadFilteredBtn.title = enabled
            ? 'Download records for the current filters'
            : 'Apply at least one filter to enable';
    }

    function filterRecords() {
        collapseRecordRows();

        let visibleCount = 0;

        recordRows.forEach((row) => {
            const visible = rowMatchesFilters(row);
            row.classList.toggle('hidden', !visible);
            if (visible) visibleCount += 1;
        });

        if (noResultsRow) {
            noResultsRow.classList.toggle('hidden', visibleCount > 0 || recordRows.length === 0);
        }
        if (statVisible) {
            statVisible.textContent = String(visibleCount);
        }
        updateFilterBadge();
        updateDownloadFilteredState();
    }

    function clearFilters() {
        if (recordSearch) recordSearch.value = '';
        if (purokFilter) purokFilter.value = '';
        if (dateFromFilter) dateFromFilter.value = '';
        if (dateToFilter) dateToFilter.value = '';
        filterRecords();
    }

    function escapeCsv(value) {
        const text = String(value ?? '');
        if (/[",\n]/.test(text)) {
            return `"${text.replace(/"/g, '""')}"`;
        }
        return text;
    }

    function entryMatchesDateFilter(entry) {
        if (!hasDateFilter) return true;

        const dateFrom = dateFromFilter?.value || '';
        const dateTo = dateToFilter?.value || '';
        if (!dateFrom && !dateTo) return true;

        const entryDate = String(entry?.given_on || '').trim();
        if (!entryDate) return false;
        if (dateFrom && entryDate < dateFrom) return false;
        if (dateTo && entryDate > dateTo) return false;
        return true;
    }

    function downloadFilteredCsv() {
        if (hasDateFilter && !hasCompleteDateRange()) {
            updateDownloadFilteredState();
            return;
        }

        if (!hasDateFilter && countActiveFilters() === 0) {
            updateDownloadFilteredState();
            return;
        }

        const visibleRows = recordRows.filter((row) => !row.classList.contains('hidden'));
        if (!visibleRows.length) {
            showRecordToast('No records to download for the current filters.', 'error');
            return;
        }

        const lines = [];
        if (csvTitle) {
            lines.push(escapeCsv(csvTitle));
            lines.push('');
        }
        lines.push(csvHeaders.map(escapeCsv).join(','));

        let exportedCount = 0;
        visibleRows.forEach((row) => {
            let payload = null;
            try {
                payload = JSON.parse(row.dataset.csv || 'null');
            } catch (error) {
                return;
            }

            const entries = Array.isArray(payload) ? payload : (payload && typeof payload === 'object' ? [payload] : []);
            entries.forEach((data) => {
                if (!entryMatchesDateFilter(data)) return;
                lines.push(csvKeys.map((key) => escapeCsv(data[key] ?? '')).join(','));
                exportedCount += 1;
            });
        });

        if (exportedCount === 0) {
            showRecordToast('No records to download for the current filters.', 'error');
            return;
        }

        const blob = new Blob(['\uFEFF' + lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = csvFilename.replace('.csv', '-filtered.csv');
        link.click();
        URL.revokeObjectURL(url);
        showRecordToast('Filtered records downloaded.', 'success');
    }

    recordSearch?.addEventListener('input', filterRecords);
    purokFilter?.addEventListener('change', filterRecords);
    dateFromFilter?.addEventListener('change', filterRecords);
    dateToFilter?.addEventListener('change', filterRecords);
    clearFiltersBtn?.addEventListener('click', clearFilters);
    downloadFilteredBtn?.addEventListener('click', downloadFilteredCsv);
    filterRecords();

    const mobileRecordQuery = window.matchMedia('(max-width: 767px)');

    function toggleRecordRow(row) {
        if (!mobileRecordQuery.matches) {
            return;
        }

        const isExpanded = row.classList.contains('is-expanded');

        collapseRecordRows();

        if (!isExpanded) {
            row.classList.add('is-expanded');
            row.querySelector('.record-mobile-toggle')?.setAttribute('aria-expanded', 'true');
        }
    }

    document.querySelectorAll('.record-mobile-toggle').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const row = button.closest('.record-row');
            if (row) {
                toggleRecordRow(row);
            }
        });
    });

    mobileRecordQuery.addEventListener('change', (event) => {
        if (!event.matches) {
            collapseRecordRows();
        }
    });
</script>
