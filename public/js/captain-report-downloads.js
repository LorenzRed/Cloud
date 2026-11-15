/* Export the same authorized, filtered payload used to render the report. */
function initCaptainReportDownloads({ report, tab, period, barangay, filenamePrefix = 'barangay-captain', title = tab === 'bns' ? 'Child monitoring' : 'Community health', scope = document, statusId = 'reportDownloadStatus' }) {
    const label = (value) => String(value).replace(/_/g, ' ').replace(/([a-z])([A-Z])/g, '$1 $2')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
    const rows = [];
    function collect(value, path) {
        if (value !== null && typeof value === 'object') {
            // Presentation colors and duplicate chart summaries are not report metrics.
            for (const [key, child] of Object.entries(value)) {
                if (key === 'colors' || key === 'chart') continue;
                const name = /^\d+$/.test(key) ? `Record ${Number(key) + 1}` : label(key);
                collect(child, [...path, name]);
            }
        } else {
            rows.push([path.slice(0, -1).join(' / ') || 'Report', path.at(-1), value ?? '']);
        }
    }
    collect(report, []);
    const metadata = [
        ['Barangay', barangay],
        ['Report', title],
        ['Period', label(period)],
        ['From', report.period?.from_label || 'All time'],
        ['To', report.period?.to_label || 'Present'],
        ['Generated', report.generated_at || ''],
    ];
    const filename = `${filenamePrefix}-${tab}-${period}-${new Date().toISOString().slice(0, 10)}`;
    function csvCell(value) {
        let text = String(value);
        // Prevent spreadsheet applications from interpreting user-entered text as formulas.
        if (/^[\s\uFEFF]*[=+@-]/.test(text) || /^[\t\r\n]/.test(text)) text = `'${text}`;
        return `"${text.replace(/"/g, '""')}"`;
    }
    scope.querySelectorAll('[data-report-download]').forEach((button) => {
        button.addEventListener('click', async () => {
            const status = document.getElementById(statusId);
            button.disabled = true;
            status.textContent = '';
            try {
                if (button.dataset.reportDownload === 'csv') {
                    const data = [...metadata, [], ['Section', 'Field', 'Value'], ...rows];
                    const blob = new Blob(['\uFEFF', data.map((row) => row.map(csvCell).join(',')).join('\r\n')], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `${filename}.csv`;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    setTimeout(() => URL.revokeObjectURL(url), 1000);
                } else {
                    if (!window.jspdf?.jsPDF || !window.jspdf.jsPDF.API.autoTable) {
                        throw new Error('PDF tools could not load. Check your connection and reload the page.');
                    }
                    const doc = new window.jspdf.jsPDF();
                    doc.setFontSize(16);
                    doc.text(`Barangay Health Reports: ${title}`, 14, 18);
                    doc.autoTable({ startY: 25, body: metadata, theme: 'plain', styles: { fontSize: 10 } });
                    doc.autoTable({
                        startY: doc.lastAutoTable.finalY + 6,
                        head: [['Section', 'Field', 'Value']], body: rows,
                        styles: { fontSize: 8, overflow: 'linebreak' },
                        columnStyles: { 0: { cellWidth: 65 }, 1: { cellWidth: 55 } },
                        headStyles: { fillColor: [74, 144, 226] },
                        margin: { bottom: 18 },
                    });
                    const pages = doc.getNumberOfPages();
                    for (let page = 1; page <= pages; page++) {
                        doc.setPage(page);
                        doc.setFontSize(9);
                        doc.text(`Page ${page} of ${pages}`, 14, doc.internal.pageSize.getHeight() - 10);
                    }
                    await doc.save(`${filename}.pdf`, { returnPromise: true });
                }
                status.textContent = 'Download ready.';
            } catch (error) {
                status.textContent = error.message || 'Download failed. Please try again.';
            } finally {
                button.disabled = false;
            }
        });
    });
}
