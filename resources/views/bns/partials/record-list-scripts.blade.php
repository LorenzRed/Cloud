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
            const rowDate = row.dataset.date || '';
            if (!rowDate) {
                matchesDate = false;
            } else {
                if (dateFrom && rowDate < dateFrom) matchesDate = false;
                if (dateTo && rowDate > dateTo) matchesDate = false;
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

    function downloadFilteredCsv() {
        const visibleRows = recordRows.filter((row) => !row.classList.contains('hidden'));
        if (!visibleRows.length) {
            alert('No records to download for the current filters.');
            return;
        }

        const lines = [csvHeaders.map(escapeCsv).join(',')];
        visibleRows.forEach((row) => {
            let data = {};
            try {
                data = JSON.parse(row.dataset.csv || '{}');
            } catch (error) {
                return;
            }
            lines.push(csvKeys.map((key) => escapeCsv(data[key] ?? '')).join(','));
        });

        const blob = new Blob(['\uFEFF' + lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = csvFilename.replace('.csv', '-filtered.csv');
        link.click();
        URL.revokeObjectURL(url);
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
