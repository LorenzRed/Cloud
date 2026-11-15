@php
    // Use the complete authorized relationships, before the card display limits.
    $transactionDownload = [
        'resident' => $resident->full_name,
        'transactions' => $resident->transactions->map(fn ($transaction) => array_merge(
            $transaction->only(['transaction_date', 'member_name', 'age', 'gender', 'philhealth_number', 'notes', 'created_at']),
            ['medicines' => $transaction->medicationEntries(), 'health_concerns' => $transaction->inferredConditions()]
        ))->values()->all(),
    ];
    $maintenanceDownload = [
        'resident' => $resident->full_name,
        'monitoring_cases' => $resident->monitoringCases->map(fn ($case) => array_merge(
            $case->only(['member_name', 'member_type', 'issue_notes', 'first_period_date', 'last_period_date', 'is_active', 'completed_at']),
            ['visits' => $case->visits->map(fn ($visit) => $visit->only([
                'visit_date', 'weight_kg', 'height_cm', 'bmi_value', 'muac_cm', 'anti_tetanus',
                'blood_pressure', 'maintenance_medicines', 'notes',
            ]))->values()->all()]
        ))->values()->all(),
    ];
@endphp
<script src="https://cdn.jsdelivr.net/npm/jspdf@4.2.0/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@5.0.2/dist/jspdf.plugin.autotable.min.js"></script>
<script src="{{ asset('js/captain-report-downloads.js') }}"></script>
<script>
    const residentDownloads = [
        { id: 'transactionDownloads', title: 'Resident transactions', report: @json($transactionDownload) },
        { id: 'maintenanceDownloads', title: 'Resident maintenance', report: @json($maintenanceDownload) },
    ];
    residentDownloads.forEach(({ id, title, report }) => initCaptainReportDownloads({
        report, title,
        tab: id === 'transactionDownloads' ? 'transactions' : 'maintenance',
        period: 'all',
        barangay: @json($resident->barangay ?? ''),
        filenamePrefix: @json('resident-'.$resident->id),
        scope: document.getElementById(id),
        statusId: `${id}Status`,
    }));
</script>
