<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<title>Bantay Kalusugan - Resident Profile</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bhw-shell-styles')
<style>
    .material-symbols-outlined {
        font-variation-settings:
            'FILL' 0,
            'wght' 400,
            'GRAD' 0,
            'opsz' 24;
        vertical-align: middle;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    .profile-page-layout {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        height: 100%;
    }

    .profile-page-header {
        flex-shrink: 0;
    }

    .profile-layout {
        flex: 1;
        min-height: 0;
        align-items: stretch;
    }

    @media (min-width: 1024px) {
        .profile-layout {
            display: grid;
            grid-template-columns: minmax(260px, 300px) minmax(0, 1fr);
            grid-template-rows: minmax(0, 1fr);
        }
    }

    @media (max-width: 1023px) {
        .profile-layout {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            overflow: hidden;
        }

        .profile-layout > .min-w-0.flex.flex-col {
            flex: 1;
            min-height: 0;
        }
    }

    .profile-sidebar-card,
    .profile-main-panel {
        min-width: 0;
    }

    .profile-main-panel {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    .profile-tab-shell {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .profile-tab-body {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .profile-tab-nav {
        display: flex;
        gap: 0.375rem;
        padding: 0.375rem;
        margin: 0.75rem 1rem 0;
        border-radius: 0.75rem;
        background: rgba(244, 244, 244, 0.9);
        overflow-x: auto;
        scrollbar-width: none;
        flex-shrink: 0;
    }

    .dark .profile-tab-nav {
        background: rgba(26, 26, 26, 0.9);
    }

    .profile-tab-nav::-webkit-scrollbar {
            display: none;
    }

    .profile-tab-link {
        flex: 1;
        min-width: max-content;
        text-align: center;
        padding: 0.5rem 0.875rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: rgba(74, 74, 74, 0.7);
        border: 1px solid transparent;
        transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
        white-space: nowrap;
    }

    .dark .profile-tab-link {
        color: rgba(224, 224, 224, 0.7);
    }

    .profile-tab-link.active {
        color: #4A90E2;
        background: #FFFFFF;
        border-color: #EAEAEA;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    }

    .dark .profile-tab-link.active {
        background: #2C2C2C;
        border-color: #3D3D3D;
    }

    .profile-tab-toolbar {
        flex-shrink: 0;
        padding-bottom: 0.75rem;
    }

    .profile-tab-scroll {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
        padding-bottom: max(1.5rem, env(safe-area-inset-bottom, 0px));
        scroll-padding-bottom: max(1.5rem, env(safe-area-inset-bottom, 0px));
    }

    .profile-tab-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .profile-tab-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .profile-tab-scroll::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    .summary-card {
        border-radius: 0.875rem;
        border: 1px solid #EAEAEA;
        background: #F9FAFB;
        padding: 0.875rem 1rem;
    }

    .dark .summary-card {
        border-color: #3D3D3D;
        background: rgba(26, 26, 26, 0.5);
    }

    .summary-card-label {
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(74, 74, 74, 0.55);
    }

    .dark .summary-card-label {
        color: rgba(224, 224, 224, 0.55);
    }

    .summary-meta-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 600;
        background: rgba(74, 144, 226, 0.1);
        color: #4A90E2;
    }

    .household-member-row {
        display: flex;
        gap: 0.75rem;
        padding: 0.625rem 0;
        border-bottom: 1px solid #EAEAEA;
    }

    .dark .household-member-row {
        border-bottom-color: #3D3D3D;
    }

    .household-member-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .sidebar-info-grid {
        display: grid;
        gap: 0.625rem;
    }

    @media (min-width: 640px) and (max-width: 1023px) {
        .sidebar-info-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    .sidebar-info-item {
        border-radius: 0.75rem;
        border: 1px solid #EAEAEA;
        background: #F9FAFB;
        padding: 0.75rem;
    }

    .dark .sidebar-info-item {
        border-color: #3D3D3D;
        background: rgba(26, 26, 26, 0.45);
    }

    .profile-detail-row {
        width: 100%;
        text-align: left;
    }

    .page-content {
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .profile-chat-glow {
        box-shadow: 0 0 0 1px rgba(74, 144, 226, 0.35), 0 4px 14px rgba(74, 144, 226, 0.24);
        animation: profile-chat-pulse 2.4s ease-in-out infinite;
    }

    @keyframes profile-chat-pulse {
        0%, 100% {
            box-shadow: 0 0 0 1px rgba(74, 144, 226, 0.35), 0 4px 14px rgba(74, 144, 226, 0.24);
        }

        50% {
            box-shadow: 0 0 0 1px rgba(74, 144, 226, 0.5), 0 4px 18px rgba(74, 144, 226, 0.38);
        }
    }

    .profile-chat-compact .material-symbols-outlined {
        font-size: 1rem;
        line-height: 1;
    }

    #medicalHistoryFormWrapper.active {
        display: flex;
    }

    .transaction-modal-panel {
        width: 100%;
        max-width: 42rem;
        max-height: min(92vh, 880px);
        display: flex;
        flex-direction: column;
        border-radius: 1rem;
        border: 1px solid #EAEAEA;
        background: #FFFFFF;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
        overflow: hidden;
    }

    .dark .transaction-modal-panel {
        border-color: #3D3D3D;
        background: #2C2C2C;
    }

    @media (min-width: 1024px) {
        .transaction-modal-panel {
            max-width: min(72rem, 88vw);
            max-height: none;
        }
    }

    .transaction-modal-header {
        flex-shrink: 0;
    }

    @media (min-width: 1024px) {
        .transaction-modal-header {
            padding: 0.875rem 1.25rem;
        }

        .transaction-modal-header h5 {
            font-size: 1.125rem;
            line-height: 1.35;
        }

        .transaction-modal-header p:last-of-type {
            font-size: 0.75rem;
            line-height: 1.4;
            margin-top: 0.25rem;
        }
    }

    .transaction-modal-body {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 1.25rem 1.5rem 1.5rem;
    }

    @media (min-width: 1024px) {
        .transaction-modal-body {
            overflow: visible;
            padding: 0.625rem 1.25rem 1rem;
        }

        .transaction-modal-body .tx-form {
            gap: 0.4375rem;
        }

        .transaction-modal-body .tx-form > section:not(.tx-recipient-section) {
            gap: 0.4375rem;
        }

        .transaction-modal-body .tx-section-heading p {
            margin-top: 0.125rem;
        }

        .transaction-modal-panel label:not(.sr-only):not(.tx-recipient-row__label) {
            font-size: 0.8125rem;
            margin-bottom: 0.375rem;
        }

        .tx-recipient-row__label {
            margin-bottom: 0;
        }

        .transaction-modal-panel input:not([type="hidden"]),
        .transaction-modal-panel select {
            height: 2.375rem;
            font-size: 0.8125rem;
            padding-left: 0.625rem;
            padding-right: 0.625rem;
        }

        .transaction-modal-panel .tx-med-input {
            height: 1.875rem;
            padding-left: 0.375rem;
            padding-right: 0.375rem;
        }

        .transaction-modal-panel .tx-form-actions {
            padding-top: 0.25rem;
        }

        .transaction-modal-panel .tx-form-actions button {
            height: 2.375rem;
            font-size: 0.8125rem;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .transaction-modal-panel .tx-condition-preview {
            padding: 0.625rem 0.875rem;
        }

        .transaction-modal-panel .tx-condition-preview .material-symbols-outlined {
            font-size: 1.25rem;
        }

        .transaction-modal-panel .tx-condition-preview > div > span:first-child {
            height: 2rem;
            width: 2rem;
        }
    }

    .transaction-modal-body .tx-form {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .transaction-modal-body .tx-form > section {
        display: flex;
        flex-direction: column;
    }

    .transaction-modal-body .tx-form > section:not(.tx-recipient-section) {
        gap: 0.5rem;
    }

    .tx-recipient-section {
        margin: 0;
        gap: 0;
    }

    .tx-recipient-row {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        margin: 0;
    }

    .tx-recipient-row__label {
        font-size: 0.875rem;
        font-weight: 700;
        color: inherit;
        margin: 0;
        padding: 0;
        line-height: 1.25;
        flex-shrink: 0;
    }

    .tx-recipient-row__select {
        width: 100%;
        min-width: 0;
        height: 2.5rem;
    }

    .transaction-modal-panel .tx-recipient-row__select {
        height: 2.375rem;
    }

    @media (min-width: 1024px) {
        .tx-recipient-row {
            flex-direction: row;
            align-items: center;
            gap: 0.625rem;
        }

        .tx-recipient-row__label {
            font-size: 0.8125rem;
            white-space: nowrap;
        }

        .tx-recipient-row__select {
            flex: 1;
        }
    }

    .tx-recipient-custom {
        margin-top: 0.25rem;
    }

    @media (min-width: 1024px) {
        .tx-recipient-custom {
            margin-top: 0.125rem;
            padding-left: calc(11.5rem + 0.625rem);
        }
    }

    .tx-details-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    @media (min-width: 640px) {
        .tx-details-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1024px) {
        .tx-details-row {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.5rem;
        }
    }

    .tx-med-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    @media (min-width: 640px) {
        .tx-med-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1024px) {
        .tx-med-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.5rem;
        }
    }

    .tx-med-card {
        border-radius: 0.875rem;
        border: 1px solid #EAEAEA;
        background: #FAFAFA;
        padding: 0.875rem 1rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
    }

    @media (min-width: 1024px) {
        .tx-med-card {
            padding: 0.5rem 0.625rem;
        }

        .tx-med-card .tx-med-icon {
            height: 1.75rem;
            width: 1.75rem;
        }

        .tx-med-card .tx-med-icon .material-symbols-outlined {
            font-size: 1rem;
        }

        .tx-med-card__hint {
            margin-top: 0.375rem;
            padding: 0.375rem 0.5rem;
            font-size: 0.6875rem;
            line-height: 1.35;
        }
    }

    .dark .tx-med-card {
        border-color: #3D3D3D;
        background: #1A1A1A;
    }

    .tx-med-card.is-active {
        border-color: rgba(74, 144, 226, 0.45);
        background: rgba(74, 144, 226, 0.06);
        box-shadow: 0 0 0 1px rgba(74, 144, 226, 0.12);
    }

    .dark .tx-med-card.is-active {
        background: rgba(74, 144, 226, 0.12);
    }

    .tx-med-card__hint {
        display: none;
        margin-top: 0.625rem;
        padding: 0.5rem 0.625rem;
        border-radius: 0.625rem;
        font-size: 0.75rem;
        line-height: 1.45;
    }

    .tx-med-card.is-active .tx-med-card__hint {
        display: block;
    }

    .tx-med-card__hint--rose {
        background: rgba(244, 63, 94, 0.1);
        color: #9f1239;
    }

    .dark .tx-med-card__hint--rose {
        background: rgba(244, 63, 94, 0.16);
        color: #fecdd3;
    }

    .tx-med-card__hint--amber {
        background: rgba(245, 158, 11, 0.12);
        color: #92400e;
    }

    .dark .tx-med-card__hint--amber {
        background: rgba(245, 158, 11, 0.16);
        color: #fde68a;
    }

    .tx-med-card__hint--sky {
        background: rgba(14, 165, 233, 0.12);
        color: #075985;
    }

    .dark .tx-med-card__hint--sky {
        background: rgba(14, 165, 233, 0.16);
        color: #bae6fd;
    }

    .tx-condition-preview {
        border-radius: 0.875rem;
        border: 1px dashed rgba(74, 144, 226, 0.35);
        background: linear-gradient(135deg, rgba(74, 144, 226, 0.08) 0%, rgba(80, 227, 194, 0.06) 100%);
        padding: 1rem 1.125rem;
    }

    .dark .tx-condition-preview {
        border-color: rgba(74, 144, 226, 0.4);
        background: linear-gradient(135deg, rgba(74, 144, 226, 0.14) 0%, rgba(80, 227, 194, 0.08) 100%);
    }

    .mnt-vitals-scroll {
        max-height: min(20rem, 45vh);
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 0.125rem;
        -webkit-overflow-scrolling: touch;
    }

    .mnt-vitals-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .mnt-vitals-scroll::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(74, 144, 226, 0.35);
    }

    .dark .mnt-vitals-scroll::-webkit-scrollbar-thumb {
        background: rgba(74, 144, 226, 0.5);
    }

    .mnt-vitals-scroll .mnt-type-row {
        margin-bottom: 0.5rem;
    }

    @media (min-width: 1024px) {
        .mnt-vitals-scroll {
            max-height: 14rem;
        }

        #monitoringCaseFormWrapper .transaction-modal-panel {
            max-height: min(92vh, 820px);
        }

        #monitoringCaseFormWrapper .transaction-modal-body {
        overflow: hidden;
            min-height: 0;
        }

        #monitoringCaseFormWrapper .tx-form {
            min-height: 0;
        }

        .mnt-vitals-section {
            flex: 1;
            min-height: 0;
        display: flex;
            flex-direction: column;
        }

        .mnt-vitals-section .mnt-vitals-scroll {
            flex: 1;
            min-height: 0;
            max-height: 14rem;
        }
    }

    .mnt-vitals-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    .mnt-metric-card {
        border-radius: 0.875rem;
        border: 1px solid #EAEAEA;
        background: #FAFAFA;
        padding: 0.75rem 0.875rem;
    }

    .dark .mnt-metric-card {
        border-color: #3D3D3D;
        background: #1A1A1A;
    }

    @media (min-width: 1024px) {
        .mnt-metric-card {
            padding: 0.625rem 0.75rem;
        }
    }

    .mnt-metric-card__head {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin-bottom: 0.625rem;
    }

    @media (min-width: 1024px) {
        .mnt-metric-card__head {
            margin-bottom: 0.5rem;
        }
    }

    .mnt-metric-card__icon {
        display: inline-flex;
        height: 2rem;
        width: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 0.75rem;
        background: rgba(74, 144, 226, 0.1);
        color: #4A90E2;
        flex-shrink: 0;
    }

    @media (min-width: 1024px) {
        .mnt-metric-card__icon {
            height: 1.75rem;
            width: 1.75rem;
        }

        .mnt-metric-card__icon .material-symbols-outlined {
            font-size: 1rem;
        }
    }

    .mnt-metric-card__title {
        font-size: 0.875rem;
        font-weight: 700;
        line-height: 1.3;
    }

    @media (min-width: 1024px) {
        .mnt-metric-card__title {
            font-size: 0.8125rem;
        }
    }

    .mnt-metric-card__subtitle {
        font-size: 0.6875rem;
        color: rgba(26, 26, 26, 0.55);
        margin-top: 0.125rem;
    }

    .dark .mnt-metric-card__subtitle {
        color: rgba(245, 245, 245, 0.55);
    }

    .mnt-metric-fields {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    @media (min-width: 640px) {
        .mnt-metric-fields {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .mnt-metric-fields--duo {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .mnt-setup-section {
        gap: 0.5rem;
    }

    @media (min-width: 1024px) {
        .mnt-setup-section {
            gap: 0.4375rem;
        }
    }

    .mnt-member-type-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    @media (min-width: 1024px) {
        .mnt-member-type-row {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.625rem;
        }
    }

    .mnt-type-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    @media (min-width: 640px) {
        .mnt-type-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1024px) {
        .transaction-modal-panel textarea {
            font-size: 0.8125rem;
            padding: 0.5rem 0.625rem;
            min-height: 4.5rem;
        }
    }

    @media (min-width: 1024px) {
        .consultation-modal-panel.transaction-modal-panel {
            max-width: min(48rem, 88vw);
        }
    }

    .consult-schedule-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    @media (min-width: 640px) {
        .consult-schedule-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.625rem;
        }
    }

    main {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        min-height: 0;
    }
</style>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#4A90E2",
                    "secondary": "#50E3C2",
                    "accent-orange": "#F5A623",
                    "accent-red": "#D0021B",
                    "background-light": "#F4F4F4",
                    "background-dark": "#1A1A1A",
                    "text-light": "#4A4A4A",
                    "text-dark": "#E0E0E0",
                    "card-light": "#FFFFFF",
                    "card-dark": "#2C2C2C",
                    "border-light": "#EAEAEA",
                    "border-dark": "#3D3D3D",
                },
                fontFamily: {
                    "display": ["Manrope", "sans-serif"]
                },
            },
        },
    }
</script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@php
    $residentUser = $resident->user;
    $residentName = $resident->full_name ?: ($resident->user->name ?? 'Resident');
    $residentGender = $resident->user->gender ?? 'N/A';
    $residentAge = $resident->age ?? ($resident->user->age ?? null);

    $residentMunicipality = trim((string) ($residentUser?->municipality ?? '')) ?: 'Victoria';
    $residentProvince = trim((string) ($residentUser?->province ?? '')) ?: 'Oriental Mindoro';
    $residentPurok = trim((string) ($resident->purok?->name ?? $residentUser?->purok?->name ?? ''));
    if (strcasecmp($residentPurok, 'Non') === 0) {
        $residentPurok = '';
    }
    $residentBarangay = trim((string) ($resident->barangay ?? $residentUser?->barangay ?? ''));
    if (strcasecmp($residentBarangay, 'N/A') === 0) {
        $residentBarangay = '';
    }
    $residentMapAddress = trim((string) ($resident->address ?? ''));

    $residentAddressParts = [];
    if ($residentMapAddress !== '') {
        $residentAddressParts[] = $residentMapAddress;
    }
    if ($residentPurok !== '') {
        $residentAddressParts[] = $residentPurok;
    }
    if ($residentBarangay !== '') {
        $residentAddressParts[] = $residentBarangay;
    }
    $residentAddressParts[] = $residentMunicipality;
    $residentAddressParts[] = $residentProvince;

    $residentAddress = implode(', ', $residentAddressParts);

    $residentContact = $resident->contact ?: ($resident->user->contact_number ?? null);
    $familyNumber = trim((string) ($resident->family_number ?? $residentUser?->family_number ?? ''));
    $familyNumberLabel = $familyNumber !== '' ? $familyNumber : 'Not assigned';
    $residentProfilePicturePath = $residentUser?->profile_picture_path;

    $parentRoleLabel = trim((string) ($resident->parent_role ?? ''));
    $civilStatusLabel = trim((string) ($resident->civil_status ?? $residentUser?->civil_status ?? ''));
    $placeOfBirthLabel = trim((string) ($resident->place_of_birth ?? $residentUser?->place_of_birth ?? ''));
    $educationalAttainmentLabel = trim((string) ($resident->educational_attainment ?? $residentUser?->educational_attainment ?? ''));
    $residentMetaParts = array_values(array_filter([
        $residentAge !== null ? $residentAge.' yrs old' : null,
        $residentGender !== 'N/A' ? $residentGender : null,
        $parentRoleLabel !== '' ? $parentRoleLabel : null,
        $civilStatusLabel !== '' ? $civilStatusLabel : null,
        $placeOfBirthLabel !== '' ? $placeOfBirthLabel : null,
        $educationalAttainmentLabel !== '' ? $educationalAttainmentLabel : null,
    ]));

    $spouseDetails = $resident->spouse_details ?? $residentUser?->spouse_details ?? [];
    if (! is_array($spouseDetails)) {
        $spouseDetails = [];
    }

    $spouseName = trim((string) (
        $resident->spouse_full_name
        ?: ($residentUser?->spouse_full_name ?? '')
        ?: ($spouseDetails['full_name'] ?? '')
    ));

    $spouseRoleLabel = match ($parentRoleLabel) {
        'Father' => 'Wife',
        'Mother' => 'Husband',
        default => 'Spouse',
    };

    $spouseParentRoleLabel = trim((string) ($spouseDetails['parent_role'] ?? ''));
    $spouseGenderLabel = filled($spouseDetails['gender'] ?? null)
        ? ucfirst(strtolower(trim((string) $spouseDetails['gender'])))
        : '';
    $spouseBirthdateLabel = filled($spouseDetails['birthdate'] ?? null)
        ? \Illuminate\Support\Carbon::parse((string) $spouseDetails['birthdate'])->format('M d, Y')
        : '';
    $spousePlaceOfBirthLabel = trim((string) ($spouseDetails['place_of_birth'] ?? ''));
    $spouseEducationalAttainmentLabel = trim((string) ($spouseDetails['educational_attainment'] ?? ''));
    $spouseMetaParts = array_values(array_filter([
        $spouseParentRoleLabel !== '' ? $spouseParentRoleLabel : null,
        $spouseGenderLabel !== '' ? $spouseGenderLabel : null,
        $spouseBirthdateLabel !== '' ? $spouseBirthdateLabel : null,
        $spousePlaceOfBirthLabel !== '' ? $spousePlaceOfBirthLabel : null,
        $spouseEducationalAttainmentLabel !== '' ? $spouseEducationalAttainmentLabel : null,
    ]));

    $householdChildren = collect($resident->household_children ?? [])
        ->map(function ($child) {
            if (! is_array($child)) {
                return null;
            }

            $fullName = trim((string) ($child['full_name'] ?? $child['name'] ?? ''));
            if ($fullName === '') {
                return null;
            }

            $relationship = trim((string) ($child['relationship'] ?? ''));
            $gender = trim((string) ($child['gender'] ?? ''));
            $birthdate = trim((string) ($child['birthdate'] ?? ''));
            $birthdateLabel = $birthdate !== ''
                ? \Illuminate\Support\Carbon::parse($birthdate)->format('M d, Y')
                : 'N/A';
            $ageLabel = $birthdate !== '' ? \App\Support\PersonAge::labelFromBirthdate($birthdate) : null;
            $studying = match ($child['currently_studying'] ?? '') {
                'yes' => 'Studying',
                'infant' => 'Infant',
                default => 'Working',
            };
            $school = trim((string) ($child['school'] ?? ''));
            $attainment = trim((string) ($child['educational_attainment'] ?? ''));
            $occupation = trim((string) ($child['occupation'] ?? ''));

            $metaParts = array_values(array_filter([
                $relationship !== '' ? $relationship : null,
                $gender !== '' ? $gender : null,
                $birthdateLabel,
                $ageLabel,
                $studying,
                ($child['currently_studying'] ?? '') === 'yes' ? ($school !== '' ? $school : null) : null,
                ($child['currently_studying'] ?? '') === 'no' && $attainment !== '' ? $attainment : null,
                ($child['currently_studying'] ?? '') === 'no' && $occupation !== '' ? $occupation : null,
            ]));

            return [
                'full_name' => $fullName,
                'meta' => implode(' · ', $metaParts),
            ];
        })
        ->filter()
        ->values();

    $householdSeniors = collect($resident->household_seniors ?? [])
        ->map(function ($senior) {
            if (! is_array($senior)) {
                return null;
            }

            $fullName = trim((string) ($senior['full_name'] ?? $senior['name'] ?? ''));
            if ($fullName === '') {
                return null;
            }

            $relationship = trim((string) ($senior['relationship'] ?? ''));
            $gender = trim((string) ($senior['gender'] ?? ''));
            $birthdate = trim((string) ($senior['birthdate'] ?? ''));
            $birthdateLabel = $birthdate !== ''
                ? \Illuminate\Support\Carbon::parse($birthdate)->format('M d, Y')
                : 'N/A';
            $ageLabel = $birthdate !== '' ? \App\Support\PersonAge::labelFromBirthdate($birthdate) : null;
            $placeOfBirth = trim((string) ($senior['place_of_birth'] ?? ''));
            $educationalAttainment = trim((string) ($senior['educational_attainment'] ?? ''));
            $occupation = trim((string) ($senior['occupation'] ?? ''));

            $metaParts = array_values(array_filter([
                $relationship !== '' ? $relationship : null,
                $gender !== '' ? $gender : null,
                $birthdateLabel,
                $ageLabel,
                'Senior citizen',
                $placeOfBirth !== '' ? $placeOfBirth : null,
                $educationalAttainment !== '' ? $educationalAttainment : null,
                $occupation !== '' ? $occupation : null,
            ]));

            return [
                'full_name' => $fullName,
                'meta' => implode(' · ', $metaParts),
            ];
        })
        ->filter()
        ->values();

    $monitoringMemberOptions = collect([
        [
            'name' => $residentName,
            'source' => 'resident',
            'index' => null,
            'label' => $residentName.' (Resident)',
        ],
    ]);

    if ($spouseName !== '') {
        $monitoringMemberOptions->push([
            'name' => $spouseName,
            'source' => 'spouse',
            'index' => null,
            'label' => $spouseName.' (Spouse)',
        ]);
    }

    foreach (($resident->household_seniors ?? []) as $seniorIndex => $seniorRow) {
        if (! is_array($seniorRow)) {
            continue;
        }

        $seniorOptionName = trim((string) ($seniorRow['full_name'] ?? $seniorRow['name'] ?? ''));
        if ($seniorOptionName === '') {
            continue;
        }

        $monitoringMemberOptions->push([
            'name' => $seniorOptionName,
            'source' => 'household_senior',
            'index' => $seniorIndex,
            'label' => $seniorOptionName.' (Senior in household)',
        ]);
    }

    $monitoringMemberOptions = $monitoringMemberOptions->values();

    $householdMemberOptions = collect([
        [
            'name' => $residentName,
            'source' => 'resident',
            'index' => null,
            'label' => $residentName.' (Resident)',
            'role' => 'Resident',
        ],
    ]);

    if ($spouseName !== '') {
        $householdMemberOptions->push([
            'name' => $spouseName,
            'source' => 'spouse',
            'index' => null,
            'label' => $spouseName.' (Spouse)',
            'role' => 'Spouse',
        ]);
    }

    foreach (($resident->household_children ?? []) as $childIndex => $childRow) {
        if (! is_array($childRow)) {
            continue;
        }

        $childOptionName = trim((string) ($childRow['full_name'] ?? $childRow['name'] ?? ''));
        if ($childOptionName === '') {
            continue;
        }

        $householdMemberOptions->push([
            'name' => $childOptionName,
            'source' => 'household_child',
            'index' => $childIndex,
            'label' => $childOptionName.' (Child)',
            'role' => 'Child',
        ]);
    }

    foreach (($resident->household_seniors ?? []) as $seniorIndex => $seniorRow) {
        if (! is_array($seniorRow)) {
            continue;
        }

        $seniorOptionName = trim((string) ($seniorRow['full_name'] ?? $seniorRow['name'] ?? ''));
        if ($seniorOptionName === '') {
            continue;
        }

        $householdMemberOptions->push([
            'name' => $seniorOptionName,
            'source' => 'household_senior',
            'index' => $seniorIndex,
            'label' => $seniorOptionName.' (Senior)',
            'role' => 'Senior',
        ]);
    }

    $householdMemberOptions = $householdMemberOptions->values();

    $defaultTransactionMemberName = old('member_name', $residentName);
    $defaultTransactionMemberSource = old('member_source', 'resident');
    $defaultTransactionMemberIndex = old('member_index');
    $defaultTransactionMemberSelectValue = $householdMemberOptions->first()['name'] ?? $residentName;

    foreach ($householdMemberOptions as $option) {
        if (
            $defaultTransactionMemberName === $option['name']
            && (string) $defaultTransactionMemberSource === (string) $option['source']
            && (string) ($defaultTransactionMemberIndex ?? '') === (string) ($option['index'] ?? '')
        ) {
            $defaultTransactionMemberSelectValue = $option['name'];
            break;
        }
    }

    $defaultMonitoringMemberName = old('member_name', $residentName);
    $defaultMonitoringMemberSource = old('member_source', 'resident');
    $defaultMonitoringMemberIndex = old('member_index');
    $defaultMonitoringMemberSelectValue = $householdMemberOptions->first()['name'] ?? $residentName;

    foreach ($householdMemberOptions as $option) {
        if (
            $defaultMonitoringMemberName === $option['name']
            && (string) $defaultMonitoringMemberSource === (string) $option['source']
            && (string) ($defaultMonitoringMemberIndex ?? '') === (string) ($option['index'] ?? '')
        ) {
            $defaultMonitoringMemberSelectValue = $option['name'];
            break;
        }
    }

    $failedProfileFormTab = null;
    if ($errors->any()) {
        if (old('consultation_date') !== null) {
            $failedProfileFormTab = 'consultations';
        } elseif (old('visit_date') !== null && old('transaction_date') === null) {
            $failedProfileFormTab = 'maintenance';
        } elseif (old('transaction_date') !== null || old('philhealth_number') !== null) {
            $failedProfileFormTab = 'medical-history';
        }
    }
@endphp

    @include('sidebars.bhw')

<div id="bhwContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.bhw-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 overflow-hidden p-4 sm:p-6 lg:p-8 pb-24 lg:pb-6 min-w-0">
        <div class="page-content">
            <div class="profile-page-layout max-w-7xl mx-auto w-full min-w-0">
                <div class="profile-page-header flex flex-col md:flex-row justify-between items-start md:items-center mb-3 lg:mb-5 gap-3 shrink-0">
                    <div class="min-w-0">
                        <nav class="text-sm text-text-light/70 dark:text-text-dark/70 mb-2">
                            <a class="hover:underline" href="{{ route('bhw.residents') }}">Residents</a>
                            <span class="mx-2">/</span>
                            <span class="break-words">{{ $residentName }}</span>
                        </nav>
                        <h1 class="text-2xl lg:text-3xl font-bold text-text-light dark:text-text-dark">Resident Profile</h1>
                    </div>
                </div>

                <div class="profile-layout flex-1 min-h-0">
                    <div class="min-w-0 shrink-0 lg:min-h-0 lg:flex lg:flex-col">
                        <div class="profile-sidebar-card bg-card-light dark:bg-card-dark rounded-xl p-4 sm:p-5 shadow-sm border border-border-light dark:border-border-dark lg:h-full flex flex-col">
                            <div class="flex flex-col sm:flex-row lg:flex-col items-center gap-2 sm:gap-2.5 lg:text-center">
                                @if ($residentProfilePicturePath && $residentUser)
                                    <img
                                        src="{{ route('profile.picture', ['user' => $residentUser->id, 'v' => optional($residentUser->updated_at)->timestamp]) }}"
                                        alt="Resident profile picture"
                                        class="w-16 h-16 sm:w-[4.5rem] sm:h-[4.5rem] lg:w-20 lg:h-20 rounded-full object-cover border-2 border-primary/20 shrink-0"
                                    />
                                @else
                                    <div class="w-16 h-16 sm:w-[4.5rem] sm:h-[4.5rem] lg:w-20 lg:h-20 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xl lg:text-2xl font-bold shrink-0">
                                        {{ strtoupper(substr($residentName, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1 text-center sm:text-left lg:text-center">
                                    <p class="text-base lg:text-lg font-bold leading-tight break-words text-text-light dark:text-text-dark">{{ $residentName }}</p>
                                    @if (! empty($residentMetaParts))
                                        <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5 line-clamp-2">{{ implode(' · ', array_slice($residentMetaParts, 0, 3)) }}</p>
                                    @endif
                                    <a href="{{ route('bhw.residents.chat', $resident) }}" class="profile-chat-compact profile-chat-glow inline-flex items-center justify-center gap-1.5 mt-2 px-3.5 py-1.5 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-95 transition">
                                        <span class="material-symbols-outlined text-base">forum</span>
                                        <span>Chat</span>
                                        @if (($residentChatUnreadCount ?? 0) > 0)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-white text-red-600 leading-none">{{ $residentChatUnreadCount }}</span>
                                        @endif
                                    </a>
                                </div>
                            </div>
                            <div class="sidebar-info-grid mt-3 lg:mt-4 w-full">
                                <div class="sidebar-info-item">
                                    <p class="summary-card-label">Family Number</p>
                                    <p class="text-sm font-semibold mt-1 break-words text-text-light dark:text-text-dark">{{ $familyNumberLabel }}</p>
                                </div>
                                <div class="sidebar-info-item">
                                    <p class="summary-card-label">Contact</p>
                                    <p class="text-sm font-semibold mt-1 break-words text-text-light dark:text-text-dark">{{ $residentContact ?: 'N/A' }}</p>
                                </div>
                                <div class="sidebar-info-item sm:col-span-3 lg:col-span-1">
                                    <p class="summary-card-label">Address</p>
                                    <p class="text-sm mt-1 break-words leading-relaxed text-text-light dark:text-text-dark">{{ $residentAddress ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0 flex flex-col min-h-0 flex-1 lg:min-h-0">
                        <div class="profile-main-panel bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark profile-tab-shell">
                            <nav aria-label="Tabs" class="profile-tab-nav">
                                <a aria-current="page" class="profile-tab-link tab-link active" data-tab="overview" href="#">Overview</a>
                                <a class="profile-tab-link tab-link" data-tab="medical-history" href="#">Transaction</a>
                                <a class="profile-tab-link tab-link" data-tab="maintenance" href="#">Maintenance</a>
                                <a class="profile-tab-link tab-link" data-tab="consultations" href="#">Consultations</a>
                                </nav>

                            <div class="p-4 sm:p-5 flex-1 min-h-0 profile-tab-body">
                                <div id="overview" class="tab-content active min-h-0 flex-1">
                                    <div class="profile-tab-scroll space-y-4">
                                        <div class="summary-card">
                                            <div class="flex items-start gap-3">
                                                <span class="material-symbols-outlined text-primary text-xl shrink-0 mt-0.5">person</span>
                                                <div class="min-w-0 flex-1">
                                                    <p class="summary-card-label">Resident</p>
                                                    <p class="text-sm font-semibold mt-1 break-words text-text-light dark:text-text-dark">{{ $residentName }}</p>
                                                    @if (! empty($residentMetaParts))
                                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                                            @foreach ($residentMetaParts as $metaPart)
                                                                <span class="summary-meta-chip">{{ $metaPart }}</span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                            </div>

                                    @if ($spouseName !== '' || $householdChildren->isNotEmpty() || $householdSeniors->isNotEmpty())
                                        <div class="summary-card">
                                            <div class="flex items-center gap-2 mb-3">
                                                <span class="material-symbols-outlined text-primary text-lg">groups</span>
                                                <h5 class="text-sm font-bold text-text-light dark:text-text-dark">Household Members</h5>
                                        </div>
                                            <div class="space-y-0">

                                            @if ($spouseName !== '')
                                                <div class="household-member-row">
                                                    <span class="material-symbols-outlined text-text-light/50 dark:text-text-dark/50 text-lg shrink-0">favorite</span>
                                                    <div class="min-w-0">
                                                        <p class="summary-card-label">{{ $spouseRoleLabel }}</p>
                                                        <p class="text-sm font-semibold mt-0.5 break-words text-text-light dark:text-text-dark">{{ $spouseName }}</p>
                                                        @if (! empty($spouseMetaParts))
                                                            <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1 break-words leading-relaxed">{{ implode(' · ', $spouseMetaParts) }}</p>
                                                        @endif
                                        </div>
                                        </div>
                                            @endif

                                            @if ($householdChildren->isNotEmpty())
                                                @foreach ($householdChildren as $child)
                                                    <div class="household-member-row">
                                                        <span class="material-symbols-outlined text-text-light/50 dark:text-text-dark/50 text-lg shrink-0">child_care</span>
                                                        <div class="min-w-0">
                                                            <p class="summary-card-label">Child</p>
                                                            <p class="text-sm font-semibold mt-0.5 break-words text-text-light dark:text-text-dark">{{ $child['full_name'] }}</p>
                                                            @if ($child['meta'] !== '')
                                                                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1 break-words leading-relaxed">{{ $child['meta'] }}</p>
                                                            @endif
                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif

                                            @if ($householdSeniors->isNotEmpty())
                                                @foreach ($householdSeniors as $senior)
                                                    <div class="household-member-row">
                                                        <span class="material-symbols-outlined text-text-light/50 dark:text-text-dark/50 text-lg shrink-0">elderly</span>
                                                        <div class="min-w-0">
                                                            <p class="summary-card-label">Elderly in household</p>
                                                            <p class="text-sm font-semibold mt-0.5 break-words text-text-light dark:text-text-dark">{{ $senior['full_name'] }}</p>
                                                            @if ($senior['meta'] !== '')
                                                                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1 break-words leading-relaxed">{{ $senior['meta'] }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                            </div>
                                        </div>
                                    @endif
                                    </div>
                                </div>

                                <div id="medical-history" class="tab-content min-w-0">
                                    <div class="profile-tab-toolbar">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                            <h4 class="text-base sm:text-lg font-bold text-text-light dark:text-text-dark">Transaction Record</h4>
                                            <button type="button" id="toggleMedicalHistoryForm" class="inline-flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap shrink-0">
                                            <span class="material-symbols-outlined text-base">add</span>
                                                <span>Add Transaction</span>
                                        </button>
                                    </div>

                                    @if (session('status'))
                                            <div class="mt-3 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-200">
                                            {{ session('status') }}
                                        </div>
                                    @endif

                                    @if ($errors->any())
                                            <div class="mt-3 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-900/30 dark:border-red-700 dark:text-red-200">
                                            <ul class="list-disc pl-5 space-y-1">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    </div>

                                    <div id="medicalHistoryFormWrapper" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6 lg:px-5 lg:py-4">
                                        <div class="transaction-modal-panel" role="dialog" aria-modal="true" aria-labelledby="transactionModalTitle">
                                            <div class="transaction-modal-header flex items-start justify-between gap-3 border-b border-border-light dark:border-border-dark px-5 py-4 shrink-0">
                                                <div class="min-w-0">
                                                    <p class="text-xs font-semibold uppercase tracking-wide text-primary/80 dark:text-primary/90">Medicine dispensing</p>
                                                    <h5 id="transactionModalTitle" class="text-lg font-bold text-text-light dark:text-text-dark mt-1">Add Transaction</h5>
                                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">Select who in the household received medicine, then record pcs for each item. Health concerns update based on your selection.</p>
                                                </div>
                                                <button type="button" id="closeMedicalHistoryForm" class="inline-flex items-center justify-center w-8 h-8 lg:w-8 lg:h-8 rounded-lg border border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark shrink-0" aria-label="Close transaction form">
                                                    <span class="material-symbols-outlined text-base">close</span>
                                                </button>
                                            </div>

                                            <div class="transaction-modal-body">
                                            @php
                                                $defaultTransactionGender = old('gender');
                                                if (! $defaultTransactionGender && filled($residentGender)) {
                                                    $genderLower = strtolower((string) $residentGender);
                                                    $defaultTransactionGender = str_starts_with($genderLower, 'm') ? 'M' : (str_starts_with($genderLower, 'f') ? 'F' : '');
                                                }
                                            @endphp
                                            <form id="transactionForm" method="POST" action="{{ route('bhw.residents.medical-history.store', $resident) }}" class="tx-form">
                                            @csrf

                                            <input type="hidden" name="member_source" id="transaction_member_source" value="{{ $defaultTransactionMemberSource }}" />
                                            <input type="hidden" name="member_index" id="transaction_member_index" value="{{ $defaultTransactionMemberIndex }}" />
                                            <input type="hidden" name="member_name" id="transaction_member_name" value="{{ $defaultTransactionMemberName }}" required />

                                            <section class="tx-recipient-section">
                                                <div class="tx-recipient-row">
                                                    <label class="tx-recipient-row__label text-text-light dark:text-text-dark" for="transaction_member_select">Who received the medicine?</label>
                                                    <select
                                                        id="transaction_member_select"
                                                        class="tx-recipient-row__select w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3"
                                                    >
                                                        @foreach ($householdMemberOptions as $option)
                                                            <option
                                                                value="{{ $option['name'] }}"
                                                                data-source="{{ $option['source'] }}"
                                                                data-index="{{ $option['index'] ?? '' }}"
                                                                {{ $defaultTransactionMemberSelectValue === $option['name'] ? 'selected' : '' }}
                                                            >{{ $option['label'] }}</option>
                                                    @endforeach
                                                    </select>
                                                </div>
                                            </section>

                                            <section class="space-y-3">
                                                <div class="tx-section-heading">
                                                    <h6 class="text-sm font-bold text-text-light dark:text-text-dark">Transaction details</h6>
                                                    <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Record details for this dispensing entry.</p>
                                            </div>
                                                <div class="tx-details-row">
                                            <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="age">Age</label>
                                                        <input id="age" name="age" value="{{ old('age', $residentAge) }}" class="w-full h-11 rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" placeholder="e.g., 58" type="number" min="0" max="150" required />
                                            </div>
                                                <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="transaction_date">Date</label>
                                                        <input id="transaction_date" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" class="w-full h-11 rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="date" required />
                                                </div>
                                                <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="gender">Gender</label>
                                                        <select id="gender" name="gender" class="w-full h-11 rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" required>
                                                            <option value="" disabled {{ $defaultTransactionGender ? '' : 'selected' }}>Select gender</option>
                                                            <option value="M" {{ $defaultTransactionGender === 'M' ? 'selected' : '' }}>Male</option>
                                                            <option value="F" {{ $defaultTransactionGender === 'F' ? 'selected' : '' }}>Female</option>
                                                        </select>
                                                </div>
                                                <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="philhealth_number">PhilHealth Number</label>
                                                        <input id="philhealth_number" name="philhealth_number" value="{{ old('philhealth_number') }}" class="w-full h-11 rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" placeholder="PhilHealth number" type="text" required />
                                                </div>
                                            </div>
                                            </section>

                                            <section class="space-y-3">
                                                <div class="tx-section-heading">
                                                    <h6 class="text-sm font-bold text-text-light dark:text-text-dark">Medicines dispensed</h6>
                                                    <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Enter pieces (pcs) for each medicine given to the selected family member.</p>
                                                </div>
                                                <div class="tx-med-grid">
                                                    @foreach (\App\Models\ResidentTransaction::medicationCatalog() as $field => $meta)
                                                        <div class="tx-med-card" data-med-card data-med-field="{{ $field }}" data-med-condition="{{ $meta['condition'] }}" data-med-label="{{ $meta['label'] }}" data-med-hint="{{ $meta['hint'] }}" data-med-tone="{{ $meta['tone'] }}">
                                                            <div class="flex items-start gap-2.5">
                                                                <span class="tx-med-icon inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                                                    <span class="material-symbols-outlined text-[20px]">{{ $meta['icon'] }}</span>
                                                                </span>
                                                                <div class="min-w-0 flex-1">
                                                                    <p class="text-sm font-bold text-text-light dark:text-text-dark leading-tight">{{ $meta['label'] }}</p>
                                                                    <p class="text-[11px] lg:text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5 leading-snug">{{ $meta['condition'] }} · {{ $meta['hint'] }}</p>
                                                                </div>
                                                                <div class="shrink-0 w-[4rem] lg:w-[3.75rem]">
                                                                    <label class="sr-only" for="{{ $field }}">{{ $meta['label'] }} pieces</label>
                                                                    <input
                                                                        id="{{ $field }}"
                                                                        name="{{ $field }}"
                                                                        value="{{ old($field) }}"
                                                                        class="tx-med-input w-full h-9 lg:h-8 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm text-center font-semibold px-1.5"
                                                                        placeholder="0"
                                                                        type="number"
                                                                        min="0"
                                                                        max="999"
                                                                        inputmode="numeric"
                                                                    />
                                                                    <p class="text-[10px] text-center text-text-light/50 dark:text-text-dark/50 mt-0.5">pcs</p>
                                                                </div>
                                                            </div>
                                                            <div class="tx-med-card__hint tx-med-card__hint--{{ $meta['tone'] }}">
                                                                <span class="tx-hint-recipient font-semibold">{{ $defaultTransactionMemberName }}</span> may have <span class="font-semibold">{{ $meta['condition'] }}</span> based on this medicine.
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </section>

                                            <div id="transactionConditionPreview" class="tx-condition-preview hidden">
                                                <div class="flex items-start gap-3">
                                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/15 text-primary">
                                                        <span class="material-symbols-outlined text-[22px]">health_and_safety</span>
                                                    </span>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60">Suggested health concerns</p>
                                                        <p id="transactionConditionSummary" class="text-sm font-semibold text-text-light dark:text-text-dark mt-1"></p>
                                                        <ul id="transactionConditionList" class="mt-2 space-y-1.5 text-sm text-text-light/80 dark:text-text-dark/80"></ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="tx-form-actions flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-1 border-t border-border-light dark:border-border-dark">
                                                <button type="button" id="cancelMedicalHistoryForm" class="inline-flex items-center justify-center h-11 px-4 rounded-xl border border-border-light dark:border-border-dark text-sm font-semibold text-text-light dark:text-text-dark hover:bg-background-light dark:hover:bg-background-dark">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl h-11 px-5 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap">
                                                    <span class="material-symbols-outlined text-base">save</span>
                                                    <span>Save and Notify User</span>
                                                </button>
                                            </div>
                                            </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="profile-tab-scroll">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @forelse ($resident->transactions as $transaction)
                                            <div class="rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60">Transaction</p>
                                                        <p class="font-semibold mt-1 text-text-light dark:text-text-dark">{{ $transaction->transaction_date?->format('M d, Y') }}</p>
                                                        @if (filled($transaction->member_name))
                                                            <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1">For <span class="font-semibold text-text-light dark:text-text-dark">{{ $transaction->member_name }}</span></p>
                                                        @endif
                                                    </div>
                                                    <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary shrink-0">
                                                        {{ $transaction->gender }}
                                                    </span>
                                                </div>

                                                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                                                    <div>
                                                        <p class="text-text-light/60 dark:text-text-dark/60">Age</p>
                                                        <p class="font-semibold">{{ $transaction->age }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-text-light/60 dark:text-text-dark/60">PhilHealth</p>
                                                        <p class="font-semibold break-words">{{ $transaction->philhealth_number }}</p>
                                                    </div>
                                                </div>

                                                @if ($transaction->medicationEntries() !== [])
                                                    <div class="mt-3 pt-3 border-t border-border-light dark:border-border-dark">
                                                        <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60 mb-2">Medicines</p>
                                                        <div class="space-y-1.5 text-sm">
                                                            @foreach ($transaction->medicationEntries() as $medication)
                                                <div class="flex items-start justify-between gap-2">
                                                                    <div class="min-w-0">
                                                                        <span class="text-text-light dark:text-text-dark font-medium">{{ $medication['label'] }}</span>
                                                                        @if (! empty($medication['condition']))
                                                                            <p class="text-xs text-text-light/55 dark:text-text-dark/55 mt-0.5">{{ $medication['condition'] }}</p>
                                                                        @endif
                                                                    </div>
                                                                    <span class="font-semibold shrink-0">{{ $medication['pcs'] }} pc{{ $medication['pcs'] === 1 ? '' : 's' }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        @if ($transaction->inferredConditions() !== [])
                                                            <div class="mt-3 rounded-lg bg-primary/8 border border-primary/15 px-3 py-2">
                                                                <p class="text-[10px] font-semibold uppercase tracking-wide text-primary/80">Health concerns</p>
                                                                <p class="text-xs font-semibold text-text-light dark:text-text-dark mt-1">{{ implode(' · ', $transaction->inferredConditions()) }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif

                                                <p class="mt-3 text-xs text-text-light/50 dark:text-text-dark/50">
                                                    Recorded {{ $transaction->created_at?->diffForHumans() }}
                                                </p>
                                            </div>
                                        @empty
                                            <div class="rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4 text-sm text-text-light/70 dark:text-text-dark/70 md:col-span-2">
                                                No transactions posted yet.
                                            </div>
                                        @endforelse
                                    </div>
                                    </div>
                                </div>

                                <div id="maintenance" class="tab-content min-w-0">
                                    <div class="profile-tab-toolbar">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                                    <div>
                                                <h4 class="text-base sm:text-lg font-bold text-text-light dark:text-text-dark">Household Maintenance</h4>
                                                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">Monitor pregnant family members and seniors with health issues.</p>
                                                    </div>
                                            <button type="button" id="toggleMonitoringCaseForm" class="inline-flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap shrink-0">
                                                <span class="material-symbols-outlined text-base">add</span>
                                                <span>Add Monitoring</span>
                                                    </button>
                                                </div>

                                        @if (session('status') && (request('tab') === 'maintenance' || str_contains(session('status'), 'monitoring')))
                                            <div class="mt-3 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-200">
                                                {{ session('status') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div id="monitoringCaseFormWrapper" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6 lg:px-5 lg:py-4">
                                        <div class="transaction-modal-panel" role="dialog" aria-modal="true" aria-labelledby="monitoringModalTitle">
                                            <div class="transaction-modal-header flex items-start justify-between gap-3 border-b border-border-light dark:border-border-dark px-5 py-4 shrink-0">
                                                <div class="min-w-0">
                                                    <p class="text-xs font-semibold uppercase tracking-wide text-primary/80 dark:text-primary/90">Household monitoring</p>
                                                    <h5 id="monitoringModalTitle" class="text-lg font-bold text-text-light dark:text-text-dark mt-1">Add Monitoring</h5>
                                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">Select a household member, monitoring type, and initial vitals for follow-up.</p>
                                                </div>
                                                <button type="button" id="closeMonitoringCaseForm" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark shrink-0" aria-label="Close monitoring form">
                                                    <span class="material-symbols-outlined text-base">close</span>
                                                </button>
                                            </div>

                                            <div class="transaction-modal-body">
                                            <form method="POST" action="{{ route('bhw.residents.monitoring-cases.store', $resident) }}" class="tx-form" id="monitoringCaseForm">
                                                    @csrf

                                                <input type="hidden" name="member_source" id="member_source" value="{{ $defaultMonitoringMemberSource }}" />
                                                <input type="hidden" name="member_index" id="member_index" value="{{ $defaultMonitoringMemberIndex }}" />
                                                <input type="hidden" name="member_name" id="member_name" value="{{ $defaultMonitoringMemberName }}" />

                                                <section class="tx-recipient-section mnt-setup-section">
                                                    <div class="mnt-member-type-row">
                                                        <div class="tx-recipient-row">
                                                            <label class="tx-recipient-row__label text-text-light dark:text-text-dark" for="monitoring_member_select">Who to monitor?</label>
                                                            <select
                                                                id="monitoring_member_select"
                                                                class="tx-recipient-row__select w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3"
                                                            >
                                                                @foreach ($householdMemberOptions as $option)
                                                                    <option
                                                                        value="{{ $option['name'] }}"
                                                                        data-source="{{ $option['source'] }}"
                                                                        data-index="{{ $option['index'] ?? '' }}"
                                                                        {{ $defaultMonitoringMemberSelectValue === $option['name'] ? 'selected' : '' }}
                                                                    >{{ $option['label'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="tx-recipient-row">
                                                            <label class="tx-recipient-row__label text-text-light dark:text-text-dark" for="monitoring_visit_date">Date</label>
                                                    <input
                                                                id="monitoring_visit_date"
                                                                name="visit_date"
                                                                value="{{ old('visit_date', now()->toDateString()) }}"
                                                                class="tx-recipient-row__select w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3"
                                                                type="date"
                                                        required
                                                    />
                                                        </div>
                                                        <div class="tx-recipient-row">
                                                            <label class="tx-recipient-row__label text-text-light dark:text-text-dark" for="member_type">Monitoring Type <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></label>
                                                            <select id="member_type" name="member_type" class="tx-recipient-row__select w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3">
                                                                <option value="" {{ old('member_type') ? '' : 'selected' }}>Not specified</option>
                                                                <option value="pregnant" {{ old('member_type') === 'pregnant' ? 'selected' : '' }}>Pregnant</option>
                                                                <option value="senior" {{ old('member_type') === 'senior' ? 'selected' : '' }}>Senior with issue</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </section>

                                                <section class="mnt-vitals-section">
                                                    <div class="tx-section-heading">
                                                        <h6 class="text-sm font-bold text-text-light dark:text-text-dark">Initial vitals</h6>
                                                        <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Record baseline vitals. Scroll inside if more fields appear.</p>
                                                    </div>
                                                    <div class="mnt-vitals-scroll">
                                                        <div id="pregnantPeriodFields" class="hidden mnt-type-row">
                                                            <div>
                                                                <label class="block text-sm font-semibold mb-1.5" for="first_period_date">First Period Date</label>
                                                                <input id="first_period_date" name="first_period_date" value="{{ old('first_period_date') }}" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="date" />
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-semibold mb-1.5" for="last_period_date">Last Period Date</label>
                                                                <input id="last_period_date" name="last_period_date" value="{{ old('last_period_date') }}" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="date" />
                                                            </div>
                                                        </div>
                                                        <div class="mnt-vitals-grid">
                                                        <div class="mnt-metric-card">
                                                            <div class="mnt-metric-card__head">
                                                                <span class="mnt-metric-card__icon">
                                                                    <span class="material-symbols-outlined text-[20px]">monitor_weight</span>
                                                                </span>
                                                                <div class="min-w-0">
                                                                    <p class="mnt-metric-card__title text-text-light dark:text-text-dark">Height, Weight &amp; BMI</p>
                                                                    <p class="mnt-metric-card__subtitle">BMI is calculated automatically</p>
                                                                </div>
                                                            </div>
                                                            <div class="mnt-metric-fields">
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1.5" for="monitoring_height_cm">Height (cm)</label>
                                                                    <input id="monitoring_height_cm" name="height_cm" value="{{ old('height_cm') }}" data-visit-height="new" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="number" min="0" step="0.1" placeholder="e.g., 165" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1.5" for="monitoring_weight_kg">Weight (kg)</label>
                                                                    <input id="monitoring_weight_kg" name="weight_kg" value="{{ old('weight_kg') }}" data-visit-weight="new" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="number" min="0" step="0.1" placeholder="e.g., 60" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1.5">BMI (auto)</label>
                                                                    <input data-visit-bmi="new" type="text" readonly class="w-full rounded-xl bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm px-3 font-semibold" placeholder="Auto" />
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mnt-metric-card">
                                                            <div class="mnt-metric-card__head">
                                                                <span class="mnt-metric-card__icon">
                                                                    <span class="material-symbols-outlined text-[20px]">cardiology</span>
                                                                </span>
                                                                <div class="min-w-0">
                                                                    <p class="mnt-metric-card__title text-text-light dark:text-text-dark">Blood Pressure</p>
                                                                    <p class="mnt-metric-card__subtitle">Reading preview updates as you type</p>
                                                                </div>
                                                            </div>
                                                            <div class="mnt-metric-fields">
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1.5" for="monitoring_bp_systolic">Systolic</label>
                                                                    <input id="monitoring_bp_systolic" name="bp_systolic" value="{{ old('bp_systolic') }}" data-visit-bp-sys="new" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="number" min="50" max="300" placeholder="120" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1.5" for="monitoring_bp_diastolic">Diastolic</label>
                                                                    <input id="monitoring_bp_diastolic" name="bp_diastolic" value="{{ old('bp_diastolic') }}" data-visit-bp-dia="new" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="number" min="30" max="200" placeholder="80" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1.5">BP Reading</label>
                                                                    <input data-visit-bp-preview="new" type="text" readonly class="w-full rounded-xl bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm px-3 font-semibold" placeholder="Auto" />
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div id="pregnantMuacFields" class="hidden mnt-metric-card">
                                                            <div class="mnt-metric-card__head">
                                                                <span class="mnt-metric-card__icon">
                                                                    <span class="material-symbols-outlined text-[20px]">pregnant_woman</span>
                                                                </span>
                                                                <div class="min-w-0">
                                                                    <p class="mnt-metric-card__title text-text-light dark:text-text-dark">MUAC &amp; Anti-Tetanus</p>
                                                                    <p class="mnt-metric-card__subtitle">For pregnant monitoring</p>
                                                                </div>
                                                            </div>
                                                            <div class="mnt-metric-fields mnt-metric-fields--duo">
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1.5" for="monitoring_muac_cm">MUAC (cm)</label>
                                                                    <input id="monitoring_muac_cm" name="muac_cm" value="{{ old('muac_cm') }}" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="number" min="0" step="0.1" placeholder="e.g., 24.5" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1.5" for="monitoring_anti_tetanus">Anti-Tetanus</label>
                                                                    <select id="monitoring_anti_tetanus" name="anti_tetanus" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3">
                                                                        <option value="" {{ old('anti_tetanus') ? '' : 'selected' }}>Not given</option>
                                                                        <option value="TT1" {{ old('anti_tetanus') === 'TT1' ? 'selected' : '' }}>TT1</option>
                                                                        <option value="TT2" {{ old('anti_tetanus') === 'TT2' ? 'selected' : '' }}>TT2</option>
                                                                        <option value="TT3" {{ old('anti_tetanus') === 'TT3' ? 'selected' : '' }}>TT3</option>
                                                                        <option value="TT4" {{ old('anti_tetanus') === 'TT4' ? 'selected' : '' }}>TT4</option>
                                                                        <option value="TT5" {{ old('anti_tetanus') === 'TT5' ? 'selected' : '' }}>TT5</option>
                                                                        <option value="Booster" {{ old('anti_tetanus') === 'Booster' ? 'selected' : '' }}>Booster</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    </div>
                                                </section>

                                                <div class="tx-form-actions flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-1 border-t border-border-light dark:border-border-dark">
                                                    <button type="button" id="cancelMonitoringCaseForm" class="inline-flex items-center justify-center h-11 px-4 rounded-xl border border-border-light dark:border-border-dark text-sm font-semibold text-text-light dark:text-text-dark hover:bg-background-light dark:hover:bg-background-dark">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl h-11 px-5 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap">
                                                        <span class="material-symbols-outlined text-base">save</span>
                                                        <span>Save Monitoring</span>
                                                    </button>
                                                </div>
                                            </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="profile-tab-scroll space-y-4">
                                        @forelse ($resident->monitoringCases as $monitoringCase)
                                            <article class="rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                                                <div class="flex flex-wrap items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $monitoringCase->isPregnant() ? 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200' }}">
                                                                {{ $monitoringCase->typeLabel() }}
                                                            </span>
                                                            @if ($monitoringCase->is_active)
                                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800 dark:bg-green-900/30 dark:text-green-200">Active</span>
                                                            @endif
                                                        </div>
                                                        <h5 class="text-lg font-bold mt-2 text-text-light dark:text-text-dark">{{ $monitoringCase->member_name }}</h5>
                                                        @if ($monitoringCase->isPregnant() && ($monitoringCase->first_period_date || $monitoringCase->last_period_date))
                                                            <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">
                                                                Period:
                                                                {{ $monitoringCase->first_period_date?->format('M d, Y') ?? 'N/A' }}
                                                                to
                                                                {{ $monitoringCase->last_period_date?->format('M d, Y') ?? 'Present' }}
                                                            </p>
                                                        @endif
                                                        @if ($monitoringCase->issue_notes)
                                                            <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-2">{{ $monitoringCase->issue_notes }}</p>
                                                        @endif
                                                    </div>
                                                    <button type="button" class="toggle-visit-form inline-flex items-center gap-1 rounded-lg h-9 px-3 border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-card-light dark:hover:bg-card-dark" data-case-id="{{ $monitoringCase->id }}">
                                                        <span class="material-symbols-outlined text-base">add</span>
                                                        Add Visit
                                                    </button>
                                                </div>

                                                <div id="visitForm-{{ $monitoringCase->id }}" class="hidden mt-4 rounded-lg border border-border-light dark:border-border-dark p-3 bg-card-light dark:bg-card-dark">
                                                    <form method="POST" action="{{ route('bhw.monitoring-cases.visits.store', $monitoringCase) }}" class="space-y-4 monitoring-visit-form" data-case-id="{{ $monitoringCase->id }}">
                                                        @csrf
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1">Visit Date</label>
                                                            <input name="visit_date" value="{{ now()->toDateString() }}" class="w-full sm:max-w-xs h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" type="date" required />
                                                        </div>

                                                        <div class="rounded-lg border border-border-light dark:border-border-dark p-3 bg-background-light dark:bg-background-dark">
                                                            <p class="text-sm font-bold text-text-light dark:text-text-dark mb-3">Height, Weight &amp; BMI</p>
                                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1">Height (cm)</label>
                                                                    <input name="height_cm" data-visit-height="{{ $monitoringCase->id }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" type="number" min="0" step="0.1" placeholder="e.g., 165" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1">Weight (kg)</label>
                                                                    <input name="weight_kg" data-visit-weight="{{ $monitoringCase->id }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" type="number" min="0" step="0.1" placeholder="e.g., 60" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1">BMI (auto)</label>
                                                                    <input data-visit-bmi="{{ $monitoringCase->id }}" type="text" readonly class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" placeholder="Auto-calculated" />
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if ($monitoringCase->isSenior())
                                                            <div class="rounded-lg border border-border-light dark:border-border-dark p-3 bg-background-light dark:bg-background-dark">
                                                                <p class="text-sm font-bold text-text-light dark:text-text-dark mb-3">Blood Pressure (BP)</p>
                                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                                                    <div>
                                                                        <label class="block text-sm font-semibold mb-1">Systolic</label>
                                                                        <input name="bp_systolic" data-visit-bp-sys="{{ $monitoringCase->id }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" type="number" min="50" max="300" placeholder="e.g., 120" />
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-semibold mb-1">Diastolic</label>
                                                                        <input name="bp_diastolic" data-visit-bp-dia="{{ $monitoringCase->id }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" type="number" min="30" max="200" placeholder="e.g., 80" />
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-semibold mb-1">BP Reading</label>
                                                                        <input data-visit-bp-preview="{{ $monitoringCase->id }}" type="text" readonly class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm font-semibold" placeholder="Auto: 120/80" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if ($monitoringCase->isPregnant())
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1">MUAC (cm)</label>
                                                                    <input name="muac_cm" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" type="number" min="0" step="0.1" placeholder="Mid-upper arm circumference" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1">Anti-Tetanus</label>
                                                                    <select name="anti_tetanus" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm">
                                                                        <option value="">Not given</option>
                                                                        <option value="TT1">TT1</option>
                                                                        <option value="TT2">TT2</option>
                                                                        <option value="TT3">TT3</option>
                                                                        <option value="TT4">TT4</option>
                                                                        <option value="TT5">TT5</option>
                                                                        <option value="Booster">Booster</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if ($monitoringCase->isSenior())
                                                            <div>
                                                                <label class="block text-sm font-semibold mb-1">Maintenance Medicines (if needed)</label>
                                                                <textarea name="maintenance_medicines" rows="2" maxlength="1000" class="w-full rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" placeholder="List maintenance medicines and dosage"></textarea>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1">Notes (optional)</label>
                                                            <textarea name="notes" rows="2" maxlength="500" class="w-full rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" placeholder="Additional visit notes"></textarea>
                                                        </div>
                                                    <div class="flex justify-end">
                                                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg h-9 px-3 bg-primary text-white text-sm font-semibold">
                                                            <span class="material-symbols-outlined text-base">save</span>
                                                                Save Visit
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                                @if ($monitoringCase->visits->isNotEmpty())
                                                    <div class="mt-4 space-y-2">
                                                        <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60">Visit History</p>
                                                        @foreach ($monitoringCase->visits as $visit)
                                                            <div class="rounded-lg border border-border-light dark:border-border-dark p-3 text-sm">
                                                                <div class="flex items-center justify-between gap-2">
                                                                    <p class="font-semibold">{{ $visit->visit_date?->format('M d, Y') }}</p>
                                                                    <p class="text-xs text-text-light/50 dark:text-text-dark/50">{{ $visit->created_at?->diffForHumans() }}</p>
                                                                </div>
                                                                <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2 text-text-light/80 dark:text-text-dark/80">
                                                                    @if ($visit->height_cm !== null)
                                                                        <span>Height: {{ $visit->height_cm }} cm</span>
                                                                    @endif
                                                                    @if ($visit->weight_kg !== null)
                                                                        <span>Weight: {{ $visit->weight_kg }} kg</span>
                                                                    @endif
                                                                    @if ($visit->bmi_value !== null)
                                                                        <span>BMI: {{ $visit->bmi_value }}</span>
                                                                    @endif
                                                                    @if ($visit->muac_cm !== null)
                                                                        <span>MUAC: {{ $visit->muac_cm }} cm</span>
                                                                    @endif
                                                                    @if ($visit->anti_tetanus)
                                                                        <span>Anti-tetanus: {{ $visit->anti_tetanus }}</span>
                                                                    @endif
                                                                    @if ($visit->blood_pressure)
                                                                        <span>BP: {{ $visit->blood_pressure }}</span>
                                                                    @endif
                                                                </div>
                                                                @if ($visit->maintenance_medicines)
                                                                    <p class="mt-2 text-text-light/70 dark:text-text-dark/70">Maintenance: {{ $visit->maintenance_medicines }}</p>
                                                                @endif
                                                                @if ($visit->notes)
                                                                    <p class="mt-2 text-text-light/70 dark:text-text-dark/70">{{ $visit->notes }}</p>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="mt-4 text-sm text-text-light/60 dark:text-text-dark/60">No visits recorded yet.</p>
                                                @endif
                                            </article>
                                        @empty
                                            <div class="rounded-lg border border-dashed border-border-light dark:border-border-dark p-8 text-center text-sm text-text-light/70 dark:text-text-dark/70">
                                                <span class="material-symbols-outlined text-4xl text-primary/70">health_and_safety</span>
                                                <p class="mt-3 font-semibold">No household monitoring yet.</p>
                                                <p class="mt-1">Add monitoring for a pregnant family member or a senior with health concerns.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <div id="consultations" class="tab-content min-w-0">
                                    <div class="profile-tab-toolbar">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                            <h4 class="text-base sm:text-lg font-bold text-text-light dark:text-text-dark">Consultations</h4>
                                            <button type="button" id="toggleConsultationForm" class="inline-flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap shrink-0">
                                            <span class="material-symbols-outlined text-base">event</span>
                                            <span>Add Appointment</span>
                                        </button>
                                    </div>

                                        @if (session('status') && request('tab') === 'consultations')
                                            <div class="mt-3 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-200">
                                                {{ session('status') }}
                                            </div>
                                        @endif

                                        @if ($errors->any() && (request('tab') === 'consultations' || old('consultation_date')))
                                            <div class="mt-3 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-900/30 dark:border-red-700 dark:text-red-200">
                                                <ul class="list-disc pl-5 space-y-1">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>

                                    <div id="consultationFormWrapper" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6 lg:px-5 lg:py-4">
                                        <div class="transaction-modal-panel consultation-modal-panel" role="dialog" aria-modal="true" aria-labelledby="consultationModalTitle">
                                            <div class="transaction-modal-header flex items-start justify-between gap-3 border-b border-border-light dark:border-border-dark px-5 py-4 shrink-0">
                                                <div class="min-w-0">
                                                    <p class="text-xs font-semibold uppercase tracking-wide text-primary/80 dark:text-primary/90">Resident consultation</p>
                                                    <h5 id="consultationModalTitle" class="text-lg font-bold text-text-light dark:text-text-dark mt-1">Add Appointment</h5>
                                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">Schedule a consultation and notify the resident.</p>
                                                </div>
                                                <button type="button" id="closeConsultationForm" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark shrink-0" aria-label="Close consultation form">
                                                    <span class="material-symbols-outlined text-base">close</span>
                                                </button>
                                            </div>

                                            <div class="transaction-modal-body">
                                            <form method="POST" action="{{ route('bhw.residents.consultations.store', $resident) }}" class="tx-form" id="consultationForm">
                                            @csrf

                                                <section class="tx-recipient-section">
                                                    <div class="consult-schedule-row">
                                                        <div class="tx-recipient-row">
                                                            <label class="tx-recipient-row__label text-text-light dark:text-text-dark" for="consultation_date">Date</label>
                                                    <input
                                                        id="consultation_date"
                                                        name="consultation_date"
                                                                value="{{ old('consultation_date', now()->toDateString()) }}"
                                                                class="tx-recipient-row__select w-full h-11 rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3"
                                                        type="date"
                                                        required
                                                    />
                                                </div>
                                                        <div class="tx-recipient-row">
                                                            <label class="tx-recipient-row__label text-text-light dark:text-text-dark" for="consultation_time">Time</label>
                                                    <input
                                                        id="consultation_time"
                                                        name="consultation_time"
                                                        value="{{ old('consultation_time') }}"
                                                                class="tx-recipient-row__select w-full h-11 rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3"
                                                        type="time"
                                                        required
                                                    />
                                                        </div>
                                                    </div>
                                                </section>

                                                <section>
                                                    <div class="mnt-metric-card">
                                                        <div class="mnt-metric-card__head">
                                                            <span class="mnt-metric-card__icon">
                                                                <span class="material-symbols-outlined text-[20px]">stethoscope</span>
                                                            </span>
                                                            <div class="min-w-0">
                                                                <p class="mnt-metric-card__title text-text-light dark:text-text-dark">Medical Checkup</p>
                                                                <p class="mnt-metric-card__subtitle">What the consultation is for</p>
                                                </div>
                                            </div>
                                            <div>
                                                            <label class="sr-only" for="consultation_checkup">Medical Checkup</label>
                                                <input
                                                    id="consultation_checkup"
                                                    name="consultation_checkup"
                                                    value="{{ old('consultation_checkup') }}"
                                                                class="w-full h-11 rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3"
                                                    type="text"
                                                    placeholder="e.g., Prenatal checkup, BP monitoring, follow-up"
                                                    required
                                                />
                                            </div>
                                            </div>
                                                </section>

                                                <section>
                                                    <label class="block text-sm font-semibold mb-1.5" for="consultation_note">Note <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></label>
                                                    <textarea id="consultation_note" name="consultation_note" rows="2" maxlength="500" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3 py-2" placeholder="Optional instructions for the resident">{{ old('consultation_note') }}</textarea>
                                                </section>

                                                <div class="tx-form-actions flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-1 border-t border-border-light dark:border-border-dark">
                                                    <button type="button" id="cancelConsultationForm" class="inline-flex items-center justify-center h-11 px-4 rounded-xl border border-border-light dark:border-border-dark text-sm font-semibold text-text-light dark:text-text-dark hover:bg-background-light dark:hover:bg-background-dark">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl h-11 px-5 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap">
                                                    <span class="material-symbols-outlined text-base">send</span>
                                                    <span>Save and Notify User</span>
                                                </button>
                                            </div>
                                        </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="profile-tab-scroll">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @forelse ($resident->consultations as $consultation)
                                            <div class="rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                                                <p class="text-sm text-text-light/70 dark:text-text-dark/70">Date</p>
                                                <p class="font-semibold mt-1">{{ $consultation->scheduled_at?->format('M d, Y') }}</p>
                                                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-2">Time</p>
                                                <p class="font-semibold mt-1">{{ $consultation->scheduled_at?->format('h:i A') }}</p>
                                                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-2">Medical Checkup</p>
                                                <p class="font-semibold mt-1">{{ $consultation->medical_checkup ?: 'General consultation' }}</p>
                                                @if ($consultation->note)
                                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-2">{{ $consultation->note }}</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4 text-sm text-text-light/70 dark:text-text-dark/70 md:col-span-2">
                                                No consultations set yet.
                                            </div>
                                        @endforelse
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('partials.bhw-mobile-nav')
</div>

<script>
    document.querySelectorAll('.tab-link').forEach((tab) => {
        tab.addEventListener('click', (event) => {
            event.preventDefault();

            const targetTab = tab.getAttribute('data-tab');
            const tabs = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach((item) => {
                item.classList.remove('active');
                item.setAttribute('aria-current', 'false');
            });

            tab.classList.add('active');
            tab.setAttribute('aria-current', 'page');

            tabContents.forEach((content) => content.classList.remove('active'));
            document.getElementById(targetTab).classList.add('active');
        });
    });

    document.getElementById('printRecordBtn')?.addEventListener('click', () => {
        window.print();
    });

    const toggleMedicalHistoryFormBtn = document.getElementById('toggleMedicalHistoryForm');
    const closeMedicalHistoryFormBtn = document.getElementById('closeMedicalHistoryForm');
    const cancelMedicalHistoryFormBtn = document.getElementById('cancelMedicalHistoryForm');
    const medicalHistoryFormWrapper = document.getElementById('medicalHistoryFormWrapper');
    const transactionConditionPreview = document.getElementById('transactionConditionPreview');
    const transactionConditionSummary = document.getElementById('transactionConditionSummary');
    const transactionConditionList = document.getElementById('transactionConditionList');
    const transactionMemberSelect = document.getElementById('transaction_member_select');
    const transactionMemberNameInput = document.getElementById('transaction_member_name');
    const transactionMemberSourceInput = document.getElementById('transaction_member_source');
    const transactionMemberIndexInput = document.getElementById('transaction_member_index');
    const ageInput = document.getElementById('age');
    const toggleConsultationFormBtn = document.getElementById('toggleConsultationForm');
    const closeConsultationFormBtn = document.getElementById('closeConsultationForm');
    const cancelConsultationFormBtn = document.getElementById('cancelConsultationForm');
    const consultationFormWrapper = document.getElementById('consultationFormWrapper');
    const consultationDateInput = document.getElementById('consultation_date');
    const toggleMonitoringCaseFormBtn = document.getElementById('toggleMonitoringCaseForm');
    const closeMonitoringCaseFormBtn = document.getElementById('closeMonitoringCaseForm');
    const cancelMonitoringCaseFormBtn = document.getElementById('cancelMonitoringCaseForm');
    const monitoringCaseFormWrapper = document.getElementById('monitoringCaseFormWrapper');
    const memberTypeSelect = document.getElementById('member_type');
    const pregnantPeriodFields = document.getElementById('pregnantPeriodFields');
    const pregnantMuacFields = document.getElementById('pregnantMuacFields');
    const firstPeriodDateInput = document.getElementById('first_period_date');
    const monitoringMemberSelect = document.getElementById('monitoring_member_select');
    const memberNameInput = document.getElementById('member_name');
    const memberSourceInput = document.getElementById('member_source');
    const memberIndexInput = document.getElementById('member_index');

    function getTransactionRecipientName() {
        const selectedName = transactionMemberNameInput?.value?.trim() || '';

        return selectedName !== '' ? selectedName : 'this family member';
    }

    function syncTransactionRecipientHints() {
        const recipientName = getTransactionRecipientName();

        document.querySelectorAll('.tx-hint-recipient').forEach((node) => {
            node.textContent = recipientName;
        });
    }

    function applyTransactionMemberSelection() {
        const selectedOption = transactionMemberSelect?.selectedOptions?.[0];

        transactionMemberNameInput.value = transactionMemberSelect?.value || '';
        transactionMemberSourceInput.value = selectedOption?.dataset.source || '';
        transactionMemberIndexInput.value = selectedOption?.dataset.index || '';

        syncTransactionRecipientHints();
        syncTransactionMedicationCards();
    }

    transactionMemberSelect?.addEventListener('change', applyTransactionMemberSelection);

    function closeMedicalHistoryModal() {
        medicalHistoryFormWrapper?.classList.add('hidden');
    }

    function syncTransactionMedicationCards() {
        const activeMeds = [];
        const conditions = new Map();

        document.querySelectorAll('[data-med-card]').forEach((card) => {
            const input = card.querySelector('.tx-med-input');
            const pcs = Number.parseInt(input?.value || '0', 10) || 0;
            const isActive = pcs > 0;

            card.classList.toggle('is-active', isActive);

            if (! isActive) {
                return;
            }

            const label = card.dataset.medLabel || 'Medicine';
            const condition = card.dataset.medCondition || '';
            const hint = card.dataset.medHint || '';

            activeMeds.push({ label, pcs, condition, hint });

            if (condition !== '') {
                if (! conditions.has(condition)) {
                    conditions.set(condition, []);
                }
                conditions.get(condition).push(`${label} (${pcs} pc${pcs === 1 ? '' : 's'})`);
            }
        });

        if (! transactionConditionPreview || ! transactionConditionSummary || ! transactionConditionList) {
            return;
        }

        const recipientName = getTransactionRecipientName();
        if (activeMeds.length === 0) {
            transactionConditionPreview.classList.add('hidden');
            transactionConditionSummary.textContent = '';
            transactionConditionList.innerHTML = '';
            return;
        }

        transactionConditionPreview.classList.remove('hidden');

        const conditionNames = Array.from(conditions.keys());
        if (conditionNames.length === 0) {
            transactionConditionSummary.textContent = `${recipientName} — medicines recorded.`;
        } else if (conditionNames.length === 1) {
            transactionConditionSummary.textContent = `${recipientName} may have ${conditionNames[0]}.`;
        } else {
            transactionConditionSummary.textContent = `${recipientName} may have multiple health concerns based on these medicines.`;
        }

        transactionConditionList.innerHTML = conditionNames.map((condition) => {
            const meds = conditions.get(condition) || [];
            return `<li><span class="font-semibold text-text-light dark:text-text-dark">${condition}</span> — ${meds.join(', ')}</li>`;
        }).join('');
    }

    document.querySelectorAll('.tx-med-input').forEach((input) => {
        input.addEventListener('input', syncTransactionMedicationCards);
        input.addEventListener('change', syncTransactionMedicationCards);
    });

    applyTransactionMemberSelection();

    toggleMedicalHistoryFormBtn?.addEventListener('click', () => {
        medicalHistoryFormWrapper?.classList.remove('hidden');
        applyTransactionMemberSelection();
        syncTransactionRecipientHints();
        syncTransactionMedicationCards();
        transactionMemberSelect?.focus();
    });

    closeMedicalHistoryFormBtn?.addEventListener('click', closeMedicalHistoryModal);
    cancelMedicalHistoryFormBtn?.addEventListener('click', closeMedicalHistoryModal);

    function closeConsultationModal() {
        consultationFormWrapper?.classList.add('hidden');
    }

    toggleConsultationFormBtn?.addEventListener('click', () => {
        consultationFormWrapper?.classList.remove('hidden');
        consultationDateInput?.focus();
    });

    closeConsultationFormBtn?.addEventListener('click', closeConsultationModal);
    cancelConsultationFormBtn?.addEventListener('click', closeConsultationModal);

    const syncPregnantPeriodFields = () => {
        const isPregnant = memberTypeSelect?.value === 'pregnant';
        pregnantPeriodFields?.classList.toggle('hidden', !isPregnant);
        pregnantMuacFields?.classList.toggle('hidden', !isPregnant);
        if (firstPeriodDateInput) {
            firstPeriodDateInput.required = Boolean(isPregnant);
        }
    };

    memberTypeSelect?.addEventListener('change', syncPregnantPeriodFields);

    function applyMonitoringMemberSelection() {
        const selectedOption = monitoringMemberSelect?.selectedOptions?.[0];

        memberNameInput.value = monitoringMemberSelect?.value || '';
        memberSourceInput.value = selectedOption?.dataset.source || '';
        memberIndexInput.value = selectedOption?.dataset.index || '';
    }

    monitoringMemberSelect?.addEventListener('change', applyMonitoringMemberSelection);

    function closeMonitoringCaseModal() {
        monitoringCaseFormWrapper?.classList.add('hidden');
    }

    applyMonitoringMemberSelection();

    toggleMonitoringCaseFormBtn?.addEventListener('click', () => {
        monitoringCaseFormWrapper?.classList.remove('hidden');
        applyMonitoringMemberSelection();
        syncPregnantPeriodFields();
        calculateVisitBmi('new');
        updateVisitBpPreview('new');
        monitoringMemberSelect?.focus();
    });

    closeMonitoringCaseFormBtn?.addEventListener('click', closeMonitoringCaseModal);
    cancelMonitoringCaseFormBtn?.addEventListener('click', closeMonitoringCaseModal);

    document.querySelectorAll('.toggle-visit-form').forEach((button) => {
        button.addEventListener('click', () => {
            const caseId = button.getAttribute('data-case-id');
            const form = document.getElementById(`visitForm-${caseId}`);
            form?.classList.toggle('hidden');
        });
    });

    const calculateVisitBmi = (caseId) => {
        const heightInput = document.querySelector(`[data-visit-height="${caseId}"]`);
        const weightInput = document.querySelector(`[data-visit-weight="${caseId}"]`);
        const bmiInput = document.querySelector(`[data-visit-bmi="${caseId}"]`);

        if (!heightInput || !weightInput || !bmiInput) {
            return;
        }

        const heightCm = parseFloat(heightInput.value);
        const weightKg = parseFloat(weightInput.value);

        if (!Number.isFinite(heightCm) || !Number.isFinite(weightKg) || heightCm <= 0 || weightKg <= 0) {
            bmiInput.value = '';
            return;
        }

        bmiInput.value = (weightKg / Math.pow(heightCm / 100, 2)).toFixed(1);
    };

    const updateVisitBpPreview = (caseId) => {
        const systolicInput = document.querySelector(`[data-visit-bp-sys="${caseId}"]`);
        const diastolicInput = document.querySelector(`[data-visit-bp-dia="${caseId}"]`);
        const previewInput = document.querySelector(`[data-visit-bp-preview="${caseId}"]`);

        if (!previewInput) {
            return;
        }

        const systolic = systolicInput?.value?.trim();
        const diastolic = diastolicInput?.value?.trim();

        previewInput.value = systolic && diastolic ? `${systolic}/${diastolic}` : '';
    };

    document.querySelectorAll('[data-visit-height], [data-visit-weight]').forEach((input) => {
        input.addEventListener('input', () => {
            const caseId = input.getAttribute('data-visit-height') || input.getAttribute('data-visit-weight');
            if (caseId) {
                calculateVisitBmi(caseId);
            }
        });
    });

    document.querySelectorAll('[data-visit-bp-sys], [data-visit-bp-dia]').forEach((input) => {
        input.addEventListener('input', () => {
            const caseId = input.getAttribute('data-visit-bp-sys') || input.getAttribute('data-visit-bp-dia');
            if (caseId) {
                updateVisitBpPreview(caseId);
            }
        });
    });

    const pageTab = new URLSearchParams(window.location.search).get('tab');
    const failedProfileFormTab = @json($failedProfileFormTab);
    const activeTab = pageTab || failedProfileFormTab;

    if (activeTab) {
        const tabLink = document.querySelector(`.tab-link[data-tab="${activeTab}"]`);
        tabLink?.click();
    }

    const hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
    const hasStatusMessage = {{ session('status') ? 'true' : 'false' }};

    if ((hasValidationErrors || hasStatusMessage) && !activeTab) {
        const medicalHistoryTabLink = document.querySelector('.tab-link[data-tab="medical-history"]');
        medicalHistoryTabLink?.click();
    }

    if (hasValidationErrors && activeTab === 'maintenance') {
        monitoringCaseFormWrapper?.classList.remove('hidden');
        applyMonitoringMemberSelection();
        syncPregnantPeriodFields();
        calculateVisitBmi('new');
        updateVisitBpPreview('new');
    }

    if (hasValidationErrors && activeTab === 'medical-history') {
        medicalHistoryFormWrapper?.classList.remove('hidden');
        applyTransactionMemberSelection();
        syncTransactionRecipientHints();
        syncTransactionMedicationCards();
    }

    if (hasValidationErrors && activeTab === 'consultations') {
        consultationFormWrapper?.classList.remove('hidden');
    }

    syncPregnantPeriodFields();
</script>
</body>
</html>
