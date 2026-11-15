<script>
@php
    $pdfSealPath = public_path('logo/victoria-seal-pdf.png');
    if (! is_file($pdfSealPath)) {
        $pdfSealPath = public_path('logo/victoria-seal.png');
    }
    $pdfSealData = is_file($pdfSealPath)
        ? 'data:image/png;base64,'.base64_encode(file_get_contents($pdfSealPath))
        : null;
@endphp
window.reportPdfSeal = @json($pdfSealData);
@verbatim
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
            rows.push([path.slice(0, -1).join(' / ') || 'Report', path[path.length - 1], value ?? '']);
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
                    const pageWidth = doc.internal.pageSize.getWidth();
                    if (window.reportPdfSeal) {
                        const seal = doc.getImageProperties(window.reportPdfSeal);
                        const scale = Math.min(30 / seal.width, 30 / seal.height);
                        doc.addImage(window.reportPdfSeal, 'PNG', 14, 10,
                            seal.width * scale, seal.height * scale);
                    }
                    const headingX = 50;
                    const headingWidth = pageWidth - headingX - 14;
                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(10);
                    doc.text('Republic of the Philippines', headingX, 15);
                    doc.text('Province of Oriental Mindoro', headingX, 21);
                    doc.setFont('helvetica', 'bold');
                    doc.setFontSize(13);
                    doc.text('MUNICIPALITY OF VICTORIA', headingX, 28);
                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(11);
                    const barangayLines = doc.splitTextToSize(String(barangay), headingWidth);
                    doc.text(barangayLines, headingX, 35);
                    const dividerY = Math.max(44, 39 + barangayLines.length * 5);
                    doc.setDrawColor(35, 112, 65);
                    doc.line(14, dividerY, pageWidth - 14, dividerY);
                    doc.setFont('helvetica', 'bold');
                    doc.setFontSize(14);
                    const titleLines = doc.splitTextToSize(title, pageWidth - 28);
                    doc.text(titleLines, 14, dividerY + 9);
                    doc.setFont('helvetica', 'normal');
                    doc.autoTable({ startY: dividerY + 13 + (titleLines.length - 1) * 6, body: metadata, theme: 'plain', styles: { fontSize: 10 } });
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

@endverbatim
</script>
