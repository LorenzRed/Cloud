<script>
@php
    $pdfSealPath = public_path('logo/victoria-seal-pdf.png');
    if (! is_file($pdfSealPath)) {
        $pdfSealPath = public_path('logo/victoria-seal.png');
    }
    $pdfSealData = is_file($pdfSealPath)
        ? 'data:image/png;base64,'.base64_encode(file_get_contents($pdfSealPath))
        : null;
    $pdfRightSealPath = public_path('logo/oriental-mindoro-seal.png');
    $pdfRightSealData = is_file($pdfRightSealPath)
        ? 'data:image/png;base64,'.base64_encode(file_get_contents($pdfRightSealPath))
        : null;
@endphp
window.reportPdfSeal = @json($pdfSealData);
window.reportPdfRightSeal = @json($pdfRightSealData);
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
                    const drawSeal = (data, centerX) => {
                        if (!data) return;
                        const seal = doc.getImageProperties(data);
                        const scale = Math.min(31 / seal.width, 29 / seal.height);
                        const width = seal.width * scale;
                        const height = seal.height * scale;
                        doc.addImage(data, 'PNG', centerX - width / 2, 14 + (29 - height) / 2, width, height);
                    };
                    drawSeal(window.reportPdfSeal, 35);
                    drawSeal(window.reportPdfRightSeal, pageWidth - 35);
                    const headingX = pageWidth / 2;
                    doc.setTextColor(115, 115, 115);
                    doc.setFont('times', 'normal');
                    doc.setFontSize(10);
                    doc.text('Republika ng Pilipinas', headingX, 19, { align: 'center' });
                    doc.text('LALAWIGAN NG ORIENTAL MINDORO', headingX, 23.5, { align: 'center' });
                    doc.text('Bayan ng Victoria', headingX, 28, { align: 'center' });
                    doc.text('BARANGAY MACATOC', headingX, 32.5, { align: 'center' });
                    doc.setFont('times', 'bold');
                    doc.setFontSize(11);
                    doc.text('TANGGAPAN NG SANGGUNIANG BARANGAY', headingX, 38, { align: 'center' });
                    const dividerY = 44;
                    doc.setDrawColor(100, 100, 100);
                    doc.setLineWidth(0.2);
                    doc.line(14, dividerY, pageWidth - 14, dividerY);
                    doc.setTextColor(0, 0, 0);
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
