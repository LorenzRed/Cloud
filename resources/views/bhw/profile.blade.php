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

    .family-action-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.5rem;
            width: 100%;
        }

    .family-action-row--two {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    @media (min-width: 640px) {
        .family-action-row {
            width: auto;
            min-width: 22rem;
        }

        .family-action-row--two {
            min-width: 15rem;
        }
    }

    .family-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        min-height: 2.5rem;
        width: 100%;
        padding: 0.45rem 0.4rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.2;
        text-align: center;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .family-member-card {
        min-height: 5.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .family-member-card__name {
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.35;
    }

    .family-member-card__meta {
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
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
        min-width: 0;
        align-items: flex-start;
    }

    .dark .household-member-row {
        border-bottom-color: #3D3D3D;
    }

    .household-member-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .household-member-row > div {
        min-width: 0;
        flex: 1;
    }

    .household-member-row p {
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.35;
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

    @media (max-width: 1023px) {
        #medicalHistoryFormWrapper {
            align-items: flex-end;
            justify-content: flex-end;
            padding: 0;
            overflow: hidden;
        }

        #medicalHistoryFormWrapper .transaction-modal-panel {
            max-width: 100%;
            max-height: min(94dvh, 100%);
            width: 100%;
            border-radius: 1.25rem 1.25rem 0 0;
            border-left: 0;
            border-right: 0;
            border-bottom: 0;
            box-shadow: 0 -12px 40px rgba(15, 23, 42, 0.2);
        }

        #medicalHistoryFormWrapper .transaction-modal-header {
            padding: 0.875rem 1rem 0.75rem;
            padding-top: max(0.875rem, env(safe-area-inset-top, 0px));
        }

        #medicalHistoryFormWrapper .transaction-modal-header .tx-modal-eyebrow {
            display: none;
        }

        #medicalHistoryFormWrapper .transaction-modal-header h5 {
            font-size: 1.0625rem;
            margin-top: 0;
        }

        #medicalHistoryFormWrapper .transaction-modal-header .tx-modal-subtitle {
            font-size: 0.75rem;
            line-height: 1.35;
            margin-top: 0.25rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        #medicalHistoryFormWrapper .transaction-modal-body {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
            padding: 0.875rem 1rem 0;
            padding-bottom: 0;
        }

        #medicalHistoryFormWrapper .tx-form {
            gap: 0.875rem;
            padding-bottom: 0;
        }

        #medicalHistoryFormWrapper .tx-form > section:not(.tx-recipient-section) {
            gap: 0.625rem;
        }

        #medicalHistoryFormWrapper .tx-section-heading p {
            font-size: 0.6875rem;
        }

        #medicalHistoryFormWrapper .tx-details-row {
            gap: 0.625rem;
        }

        #medicalHistoryFormWrapper .tx-med-grid {
            gap: 0.625rem;
        }

        #medicalHistoryFormWrapper .tx-med-card {
            padding: 0.75rem 0.875rem;
        }

        #medicalHistoryFormWrapper .tx-form-actions {
            position: sticky;
            bottom: 0;
            z-index: 5;
            margin-top: 0.5rem;
            margin-left: -1rem;
            margin-right: -1rem;
            padding: 0.75rem 1rem;
            padding-bottom: max(0.75rem, env(safe-area-inset-bottom, 0px));
            border-top: 1px solid #EAEAEA;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(8px);
            gap: 0.5rem;
        }

        .dark #medicalHistoryFormWrapper .tx-form-actions {
            border-top-color: #3D3D3D;
            background: rgba(44, 44, 44, 0.97);
        }

        #medicalHistoryFormWrapper .tx-form-actions button {
            height: 2.75rem;
            width: 100%;
            border-radius: 0.875rem;
        }
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

    /* Maintenance case cards — active vs completed */
    .mnt-case-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        width: 100%;
        min-width: 0;
    }
    .mnt-section-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0.25rem 0 0;
        font-size: 0.6875rem;
        font-weight: 750;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(74, 74, 74, 0.55);
    }
    .dark .mnt-section-label {
        color: rgba(224, 224, 224, 0.55);
    }
    .mnt-section-label .material-symbols-outlined {
        font-size: 1rem;
    }
    .mnt-case-card {
        width: 100%;
        min-width: 0;
        border-radius: 1rem;
        overflow: hidden;
        border: 1px solid #EAEAEA;
        background: #fff;
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
    }
    .dark .mnt-case-card {
        border-color: #3D3D3D;
        background: #2C2C2C;
        box-shadow: none;
    }
    .mnt-case-card--active {
        border-color: rgba(74, 144, 226, 0.35);
        box-shadow: 0 0 0 1px rgba(74, 144, 226, 0.08), 0 8px 20px rgb(74 144 226 / 0.08);
    }
    .dark .mnt-case-card--active {
        border-color: rgba(74, 144, 226, 0.45);
        box-shadow: 0 0 0 1px rgba(74, 144, 226, 0.12);
    }
    .mnt-case-card--completed {
        border-color: rgba(34, 197, 94, 0.28);
        background: linear-gradient(180deg, rgba(34, 197, 94, 0.06) 0%, #fff 42%);
    }
    .dark .mnt-case-card--completed {
        border-color: rgba(34, 197, 94, 0.35);
        background: linear-gradient(180deg, rgba(34, 197, 94, 0.12) 0%, #2C2C2C 45%);
    }
    .mnt-case-banner {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 0.9rem;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .mnt-case-card--active .mnt-case-banner {
        background: rgba(74, 144, 226, 0.1);
        color: #2f6fb8;
    }
    .dark .mnt-case-card--active .mnt-case-banner {
        background: rgba(74, 144, 226, 0.18);
        color: #93c5fd;
    }
    .mnt-case-card--completed .mnt-case-banner {
        background: rgba(34, 197, 94, 0.14);
        color: #15803d;
    }
    .dark .mnt-case-card--completed .mnt-case-banner {
        background: rgba(34, 197, 94, 0.2);
        color: #86efac;
    }
    .mnt-case-banner .material-symbols-outlined {
        font-size: 1.05rem;
        font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 20;
    }
    .mnt-case-body {
        padding: 0.9rem 0.9rem 1rem;
    }
    @media (min-width: 640px) {
        .mnt-case-body {
            padding: 1rem 1.1rem 1.15rem;
        }
    }
    .mnt-case-head {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    @media (min-width: 640px) {
        .mnt-case-head {
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between;
        }
    }
    .mnt-case-title {
        margin: 0.35rem 0 0;
        font-size: 1.125rem;
        font-weight: 800;
        line-height: 1.25;
        color: #2f2f2f;
        word-break: break-word;
    }
    .dark .mnt-case-title {
        color: #f3f4f6;
    }
    .mnt-case-meta {
        margin: 0.35rem 0 0;
        font-size: 0.8125rem;
        line-height: 1.4;
        color: rgba(74, 74, 74, 0.72);
    }
    .dark .mnt-case-meta {
        color: rgba(224, 224, 224, 0.7);
    }
    .mnt-case-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        width: 100%;
    }
    @media (min-width: 640px) {
        .mnt-case-actions {
            display: flex;
            width: auto;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
    }
    .mnt-case-actions .mnt-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        min-height: 2.5rem;
        padding: 0.45rem 0.75rem;
        border-radius: 0.75rem;
        font-size: 0.8125rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .mnt-btn--visit {
        border: 1px solid rgba(74, 144, 226, 0.35);
        background: rgba(74, 144, 226, 0.08);
        color: #2f6fb8;
    }
    .dark .mnt-btn--visit {
        border-color: rgba(74, 144, 226, 0.45);
        background: rgba(74, 144, 226, 0.16);
        color: #93c5fd;
    }
    .mnt-btn--complete {
        border: 1px solid rgba(34, 197, 94, 0.4);
        background: rgba(34, 197, 94, 0.1);
        color: #15803d;
    }
    .dark .mnt-btn--complete {
        border-color: rgba(34, 197, 94, 0.45);
        background: rgba(34, 197, 94, 0.16);
        color: #86efac;
    }
    .mnt-visit-list {
        margin-top: 0.9rem;
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }
    .mnt-visit-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.15rem;
    }
    .mnt-visit-heading p {
        margin: 0;
        font-size: 0.6875rem;
        font-weight: 750;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(74, 74, 74, 0.55);
    }
    .dark .mnt-visit-heading p {
        color: rgba(224, 224, 224, 0.55);
    }
    .mnt-visit-card {
        border-radius: 0.85rem;
        border: 1px solid #EAEAEA;
        background: #f7f9fc;
        padding: 0.8rem 0.85rem;
        min-width: 0;
    }
    .dark .mnt-visit-card {
        border-color: #3D3D3D;
        background: #242424;
    }
    .mnt-case-card--completed .mnt-visit-card {
        background: #f3f4f6;
        border-style: dashed;
    }
    .dark .mnt-case-card--completed .mnt-visit-card {
        background: #1f1f1f;
    }
    .mnt-visit-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.65rem;
    }
    .mnt-visit-date {
        margin: 0;
        font-size: 0.9375rem;
        font-weight: 750;
        color: #2f2f2f;
    }
    .dark .mnt-visit-date {
        color: #f3f4f6;
    }
    .mnt-visit-ago {
        margin: 0.15rem 0 0;
        font-size: 0.6875rem;
        color: rgba(74, 74, 74, 0.55);
    }
    .dark .mnt-visit-ago {
        color: rgba(224, 224, 224, 0.55);
    }
    .mnt-visit-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        padding: 0.2rem 0.55rem;
        font-size: 0.625rem;
        font-weight: 750;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        background: rgba(74, 144, 226, 0.12);
        color: #2f6fb8;
        flex-shrink: 0;
    }
    .mnt-case-card--completed .mnt-visit-chip {
        background: rgba(107, 114, 128, 0.14);
        color: #4b5563;
    }
    .dark .mnt-case-card--completed .mnt-visit-chip {
        background: rgba(156, 163, 175, 0.18);
        color: #d1d5db;
    }
    .mnt-vital-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.45rem;
        margin-top: 0.7rem;
    }
    @media (min-width: 480px) {
        .mnt-vital-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    .mnt-vital-pill {
        min-width: 0;
        border-radius: 0.7rem;
        background: #fff;
        border: 1px solid #EAEAEA;
        padding: 0.45rem 0.55rem;
    }
    .dark .mnt-vital-pill {
        background: #2C2C2C;
        border-color: #3D3D3D;
    }
    .mnt-vital-pill span {
        display: block;
    }
    .mnt-vital-pill__label {
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgba(74, 74, 74, 0.55);
        margin-bottom: 0.1rem;
    }
    .dark .mnt-vital-pill__label {
        color: rgba(224, 224, 224, 0.55);
    }
    .mnt-vital-pill__value {
        font-size: 0.875rem;
        font-weight: 750;
        line-height: 1.25;
        color: #2f2f2f;
        word-break: break-word;
    }
    .dark .mnt-vital-pill__value {
        color: #f3f4f6;
    }
    .mnt-visit-notes {
        margin: 0.65rem 0 0;
        padding: 0.55rem 0.65rem;
        border-radius: 0.65rem;
        background: rgba(74, 144, 226, 0.06);
        font-size: 0.8125rem;
        line-height: 1.4;
        color: rgba(47, 47, 47, 0.85);
        word-break: break-word;
    }
    .dark .mnt-visit-notes {
        background: rgba(74, 144, 226, 0.12);
        color: rgba(243, 244, 246, 0.85);
    }
    .mnt-case-card--completed .mnt-visit-notes {
        background: rgba(107, 114, 128, 0.1);
    }
    .mnt-empty-visits {
        margin-top: 0.85rem;
        border-radius: 0.75rem;
        border: 1px dashed #EAEAEA;
        padding: 0.75rem 0.85rem;
        font-size: 0.8125rem;
        color: rgba(74, 74, 74, 0.65);
    }
    .dark .mnt-empty-visits {
        border-color: #3D3D3D;
        color: rgba(224, 224, 224, 0.65);
    }
    .mnt-period-box {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.4rem;
        margin-bottom: 0.55rem;
        border-radius: 0.75rem;
        background: rgba(236, 72, 153, 0.08);
        border: 1px solid rgba(236, 72, 153, 0.18);
        padding: 0.65rem 0.75rem;
        font-size: 0.8125rem;
        color: #9d174d;
    }
    .dark .mnt-period-box {
        background: rgba(236, 72, 153, 0.14);
        border-color: rgba(236, 72, 153, 0.28);
        color: #f9a8d4;
    }
    @media (min-width: 480px) {
        .mnt-period-box {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 1023px) {
        #monitoringCaseFormWrapper,
        #addVisitModalWrapper,
        #familyChildModal,
        #familySeniorModal,
        #familySpouseModal,
        #consultationFormWrapper {
            align-items: flex-end;
            justify-content: flex-end;
            padding: 0;
            overflow: hidden;
        }

        #monitoringCaseFormWrapper .transaction-modal-panel,
        #addVisitModalWrapper .transaction-modal-panel,
        #familyChildModal .transaction-modal-panel,
        #familySeniorModal .transaction-modal-panel,
        #familySpouseModal .transaction-modal-panel,
        #consultationFormWrapper .transaction-modal-panel {
            max-width: 100%;
            max-height: min(94dvh, 100%);
            width: 100%;
            margin-top: 0;
            border-radius: 1.25rem 1.25rem 0 0;
            border-left: 0;
            border-right: 0;
            border-bottom: 0;
            box-shadow: 0 -12px 40px rgba(15, 23, 42, 0.2);
        }

        #monitoringCaseFormWrapper .transaction-modal-header,
        #addVisitModalWrapper .transaction-modal-header,
        #familyChildModal .transaction-modal-header,
        #familySeniorModal .transaction-modal-header,
        #familySpouseModal .transaction-modal-header,
        #consultationFormWrapper .transaction-modal-header {
            padding: 0.875rem 1rem 0.75rem;
            padding-top: max(0.875rem, env(safe-area-inset-top, 0px));
        }

        #monitoringCaseFormWrapper .transaction-modal-header .tx-modal-eyebrow,
        #addVisitModalWrapper .transaction-modal-header .tx-modal-eyebrow,
        #consultationFormWrapper .transaction-modal-header .text-xs.font-semibold.uppercase {
            display: none;
        }

        #monitoringCaseFormWrapper .transaction-modal-header h5,
        #addVisitModalWrapper .transaction-modal-header h5,
        #familyChildModal .transaction-modal-header h5,
        #familySeniorModal .transaction-modal-header h5,
        #familySpouseModal .transaction-modal-header h5,
        #consultationFormWrapper .transaction-modal-header h5 {
            font-size: 1.0625rem;
            margin-top: 0;
        }

        #monitoringCaseFormWrapper .transaction-modal-header .tx-modal-subtitle,
        #addVisitModalWrapper .transaction-modal-header .tx-modal-subtitle,
        #familyChildModal .transaction-modal-header .tx-modal-subtitle,
        #familySeniorModal .transaction-modal-header .tx-modal-subtitle,
        #familySpouseModal .transaction-modal-header .tx-modal-subtitle,
        #consultationFormWrapper .transaction-modal-header p.text-sm {
            font-size: 0.75rem;
            line-height: 1.35;
            margin-top: 0.25rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        #monitoringCaseFormWrapper .transaction-modal-body,
        #addVisitModalWrapper .transaction-modal-body,
        #familyChildModal .transaction-modal-body,
        #familySeniorModal .transaction-modal-body,
        #familySpouseModal .transaction-modal-body,
        #consultationFormWrapper .transaction-modal-body {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
            padding: 0.875rem 1rem 0;
            padding-bottom: 0;
        }

        #monitoringCaseFormWrapper .tx-form,
        #addVisitModalWrapper .tx-form,
        #familyChildModal .tx-form,
        #familySeniorModal .tx-form,
        #familySpouseModal .tx-form,
        #consultationFormWrapper .tx-form {
            gap: 0.875rem;
            padding-bottom: 0;
        }

        #monitoringCaseFormWrapper .mnt-vitals-scroll {
            max-height: none;
            overflow: visible;
            padding-right: 0;
        }

        #monitoringCaseFormWrapper .mnt-metric-card,
        #addVisitModalWrapper .mnt-metric-card,
        #consultationFormWrapper .mnt-metric-card {
            padding: 0.75rem 0.875rem;
        }

        #monitoringCaseFormWrapper .tx-form-actions,
        #addVisitModalWrapper .tx-form-actions,
        #familyChildModal .tx-form-actions,
        #familySeniorModal .tx-form-actions,
        #familySpouseModal .tx-form-actions,
        #consultationFormWrapper .tx-form-actions {
            position: sticky;
            bottom: 0;
            z-index: 5;
            margin-top: 0.5rem;
            margin-left: -1rem;
            margin-right: -1rem;
            padding: 0.75rem 1rem;
            padding-bottom: max(0.75rem, env(safe-area-inset-bottom, 0px));
            border-top: 1px solid #EAEAEA;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(8px);
            gap: 0.5rem;
        }

        .dark #monitoringCaseFormWrapper .tx-form-actions,
        .dark #addVisitModalWrapper .tx-form-actions,
        .dark #familyChildModal .tx-form-actions,
        .dark #familySeniorModal .tx-form-actions,
        .dark #familySpouseModal .tx-form-actions,
        .dark #consultationFormWrapper .tx-form-actions {
            border-top-color: #3D3D3D;
            background: rgba(44, 44, 44, 0.97);
        }

        #consultationFormWrapper .tx-form-actions button {
            width: 100%;
            min-height: 2.75rem;
        }

        #monitoringCaseFormWrapper .tx-form-actions button,
        #addVisitModalWrapper .tx-form-actions button,
        #familyChildModal .tx-form-actions button,
        #familySeniorModal .tx-form-actions button,
        #familySpouseModal .tx-form-actions button {
            height: 2.75rem;
            width: 100%;
            border-radius: 0.875rem;
        }
    }

    @media (min-width: 1024px) {
        .mnt-vitals-scroll {
            max-height: 14rem;
        }

        #monitoringCaseFormWrapper .transaction-modal-panel,
        #addVisitModalWrapper .transaction-modal-panel {
            max-height: min(92vh, 820px);
        }

        #monitoringCaseFormWrapper .transaction-modal-body {
            overflow: hidden;
            min-height: 0;
        }

        #addVisitModalWrapper .transaction-modal-body {
            overflow-y: auto;
            min-height: 0;
        }

        #monitoringCaseFormWrapper .tx-form,
        #addVisitModalWrapper .tx-form {
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

    .consult-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .consult-card {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 0.875rem;
        align-items: start;
        border-radius: 1rem;
        border: 1px solid #EAEAEA;
        background: #fff;
        padding: 0.875rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        min-width: 0;
    }
    .dark .consult-card {
        border-color: #3D3D3D;
        background: #2C2C2C;
        box-shadow: none;
    }

    .consult-date-badge {
        width: 3.35rem;
        border-radius: 0.85rem;
        background: linear-gradient(180deg, rgba(74, 144, 226, 0.16), rgba(74, 144, 226, 0.06));
        border: 1px solid rgba(74, 144, 226, 0.22);
        text-align: center;
        padding: 0.45rem 0.25rem 0.5rem;
        flex-shrink: 0;
    }
    .dark .consult-date-badge {
        background: linear-gradient(180deg, rgba(74, 144, 226, 0.22), rgba(74, 144, 226, 0.08));
        border-color: rgba(74, 144, 226, 0.3);
    }
    .consult-date-badge__month {
        display: block;
        font-size: 0.625rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #4A90E2;
        line-height: 1;
    }
    .consult-date-badge__day {
        display: block;
        margin-top: 0.2rem;
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1;
        color: #2f2f2f;
    }
    .dark .consult-date-badge__day {
        color: #E0E0E0;
    }
    .consult-date-badge__year {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.625rem;
        font-weight: 650;
        color: rgba(74, 74, 74, 0.55);
        line-height: 1;
    }
    .dark .consult-date-badge__year {
        color: rgba(224, 224, 224, 0.5);
    }

    .consult-card__body {
        min-width: 0;
    }
    .consult-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
    }
    .consult-card__title {
        margin: 0;
        font-size: 0.9375rem;
        font-weight: 750;
        line-height: 1.3;
        color: #2f2f2f;
        word-break: break-word;
    }
    .dark .consult-card__title {
        color: #E0E0E0;
    }
    .consult-time-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        flex-shrink: 0;
        border-radius: 9999px;
        background: rgba(74, 144, 226, 0.1);
        color: #4A90E2;
        font-size: 0.6875rem;
        font-weight: 700;
        padding: 0.28rem 0.55rem;
        white-space: nowrap;
    }
    .dark .consult-time-chip {
        background: rgba(74, 144, 226, 0.18);
    }
    .consult-time-chip .material-symbols-outlined {
        font-size: 0.875rem;
    }
    .consult-card__note {
        margin: 0.55rem 0 0;
        font-size: 0.8125rem;
        line-height: 1.45;
        color: rgba(74, 74, 74, 0.72);
        word-break: break-word;
    }
    .dark .consult-card__note {
        color: rgba(224, 224, 224, 0.68);
    }
    .consult-card__meta {
        margin-top: 0.55rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    .consult-meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        border-radius: 0.5rem;
        background: #f4f6f9;
        border: 1px solid #EAEAEA;
        color: rgba(74, 74, 74, 0.75);
        font-size: 0.6875rem;
        font-weight: 650;
        padding: 0.22rem 0.45rem;
    }
    .dark .consult-meta-chip {
        background: #242424;
        border-color: #3D3D3D;
        color: rgba(224, 224, 224, 0.7);
    }
    .consult-meta-chip .material-symbols-outlined {
        font-size: 0.875rem;
    }

    .consult-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.5rem;
        border-radius: 1rem;
        border: 1px dashed #d5dbe3;
        background: #f8fafc;
        padding: 1.75rem 1.25rem;
    }
    .dark .consult-empty {
        border-color: #3D3D3D;
        background: #242424;
    }
    .consult-empty .material-symbols-outlined {
        font-size: 2.25rem;
        color: #9aa7b8;
    }
    .dark .consult-empty .material-symbols-outlined {
        color: #6b7280;
    }
    .consult-empty__title {
        margin: 0;
        font-size: 0.9375rem;
        font-weight: 750;
        color: #2f2f2f;
    }
    .dark .consult-empty__title {
        color: #E0E0E0;
    }
    .consult-empty__text {
        margin: 0;
        max-width: 16rem;
        font-size: 0.8125rem;
        line-height: 1.45;
        color: rgba(74, 74, 74, 0.65);
    }
    .dark .consult-empty__text {
        color: rgba(224, 224, 224, 0.6);
    }

    @media (max-width: 1023px) {
        #consultations .profile-tab-toolbar {
            padding-bottom: 0.75rem;
        }
        #consultations .profile-tab-toolbar h4 {
            font-size: 1rem;
        }
        #toggleConsultationForm {
            width: 100%;
            height: 2.75rem;
            border-radius: 0.85rem;
        }
        .consult-card {
            gap: 0.75rem;
            padding: 0.8rem;
            border-radius: 0.95rem;
        }
        .consult-date-badge {
            width: 3.1rem;
            border-radius: 0.75rem;
        }
        .consult-card__title {
            font-size: 0.875rem;
        }
    }

    @media (min-width: 768px) {
        .consult-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.875rem;
        }
        .consult-empty {
            grid-column: 1 / -1;
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
        'Father', 'Grandfather' => 'Wife',
        'Mother', 'Grandmother' => 'Husband',
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

    $normalizeMemberGenderCode = static function (?string $gender): string {
        $value = strtolower(trim((string) $gender));
        if ($value === '' || $value === 'n/a') {
            return '';
        }
        if (str_starts_with($value, 'm')) {
            return 'M';
        }
        if (str_starts_with($value, 'f')) {
            return 'F';
        }

        return '';
    };

    $ageFromBirthdate = static function (?string $birthdate): ?int {
        return \App\Support\PersonAge::ageInYears($birthdate);
    };

    $residentBirthdate = $residentUser?->date_of_birth
        ? $residentUser->date_of_birth->toDateString()
        : null;
    $residentAgeValue = $ageFromBirthdate($residentBirthdate) ?? ($residentAge !== null ? (int) $residentAge : null);
    $residentGenderCode = $normalizeMemberGenderCode((string) $residentGender);

    $householdMemberOptions = collect([
        [
            'name' => $residentName,
            'source' => 'resident',
            'index' => null,
            'label' => $residentName.' (Resident)',
            'role' => 'Resident',
            'age' => $residentAgeValue,
            'gender' => $residentGenderCode,
        ],
    ]);

    if ($spouseName !== '') {
        $spouseBirthdate = trim((string) ($spouseDetails['birthdate'] ?? ''));
        $spouseGenderCode = $normalizeMemberGenderCode((string) ($spouseDetails['gender'] ?? ''));
        if ($spouseGenderCode === '') {
            $spouseGenderCode = match (trim((string) ($spouseDetails['parent_role'] ?? ''))) {
                'Father', 'Grandfather' => 'M',
                'Mother', 'Grandmother' => 'F',
                default => '',
            };
        }

        $householdMemberOptions->push([
            'name' => $spouseName,
            'source' => 'spouse',
            'index' => null,
            'label' => $spouseName.' (Spouse)',
            'role' => 'Spouse',
            'age' => $ageFromBirthdate($spouseBirthdate !== '' ? $spouseBirthdate : null),
            'gender' => $spouseGenderCode,
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

        $childBirthdate = trim((string) ($childRow['birthdate'] ?? ''));

        $householdMemberOptions->push([
            'name' => $childOptionName,
            'source' => 'household_child',
            'index' => $childIndex,
            'label' => $childOptionName.' (Child)',
            'role' => 'Child',
            'age' => $ageFromBirthdate($childBirthdate !== '' ? $childBirthdate : null),
            'gender' => $normalizeMemberGenderCode((string) ($childRow['gender'] ?? '')),
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

        $seniorBirthdate = trim((string) ($seniorRow['birthdate'] ?? ''));
        $seniorAge = $ageFromBirthdate($seniorBirthdate !== '' ? $seniorBirthdate : null);
        if ($seniorAge === null && isset($seniorRow['age_in_years']) && is_numeric($seniorRow['age_in_years'])) {
            $seniorAge = (int) $seniorRow['age_in_years'];
        }

        $householdMemberOptions->push([
            'name' => $seniorOptionName,
            'source' => 'household_senior',
            'index' => $seniorIndex,
            'label' => $seniorOptionName.' (Senior)',
            'role' => 'Senior',
            'age' => $seniorAge,
            'gender' => $normalizeMemberGenderCode((string) ($seniorRow['gender'] ?? '')),
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
    $failedVisitCaseId = null;
    $failedHouseholdAction = null;
    if ($errors->any()) {
        if (old('consultation_date') !== null) {
            $failedProfileFormTab = 'consultations';
        } elseif (old('household_action') !== null) {
            $failedProfileFormTab = 'family';
            $failedHouseholdAction = old('household_action');
        } elseif (old('form_intent') === 'monitoring_visit') {
            $failedProfileFormTab = 'maintenance';
            $failedVisitCaseId = old('monitoring_case_id');
        } elseif (old('visit_date') !== null && old('transaction_date') === null) {
            $failedProfileFormTab = 'maintenance';
        } elseif (old('transaction_date') !== null || old('philhealth_number') !== null) {
            $failedProfileFormTab = 'medical-history';
        }
    }

    $editableChildren = collect($resident->household_children ?? [])
        ->values()
        ->filter(fn ($row) => is_array($row) && trim((string) ($row['full_name'] ?? $row['name'] ?? '')) !== '')
        ->values();
    $editableSeniors = collect($resident->otherFamilyMembers())
        ->values();
    $familyPeopleCount = 1
        + ($spouseName !== '' ? 1 : 0)
        + $editableChildren->count()
        + $editableSeniors->count();
    $educationOptions = \App\Models\ResidentRequest::EDUCATIONAL_ATTAINMENTS;
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
                                <a class="profile-tab-link tab-link" data-tab="family" href="#">Family</a>
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
                                                            <p class="summary-card-label">Other family in the house</p>
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

                                <div id="family" class="tab-content min-w-0">
                                    <div class="profile-tab-toolbar">
                                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                            <div class="min-w-0">
                                                <h4 class="text-base sm:text-lg font-bold text-text-light dark:text-text-dark">Family Members</h4>
                                                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">Add or update household members so family counting stays accurate (new members, removals, and corrections).</p>
                                            </div>
                                            <div class="family-action-row {{ $spouseName === '' ? '' : 'family-action-row--two' }} shrink-0">
                                                @if ($spouseName === '')
                                                    <button type="button" id="openAddSpouseBtn" class="family-action-btn border border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark">
                                                        <span class="material-symbols-outlined text-base shrink-0">favorite</span>
                                                        <span>Add Spouse</span>
                                                    </button>
                                                @endif
                                                <button type="button" id="openAddChildBtn" class="family-action-btn border border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark">
                                                    <span class="material-symbols-outlined text-base shrink-0">child_care</span>
                                                    <span>Add Child</span>
                                                </button>
                                                <button type="button" id="openAddSeniorBtn" class="family-action-btn bg-primary text-white font-bold hover:opacity-90">
                                                    <span class="material-symbols-outlined text-base shrink-0">elderly</span>
                                                    <span>Add other family</span>
                                        </button>
                                            </div>
                                    </div>

                                        @if (session('status') && (request('tab') === 'family' || old('household_action')))
                                            <div class="mt-3 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-200">
                                            {{ session('status') }}
                                        </div>
                                    @endif

                                        @if ($errors->any() && old('household_action'))
                                            <div class="mt-3 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-900/30 dark:border-red-700 dark:text-red-200">
                                            <ul class="list-disc pl-5 space-y-1">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    </div>

                                    <div class="profile-tab-scroll space-y-4">
                                        <div class="rounded-xl border border-primary/20 bg-primary/5 px-4 py-3 text-sm">
                                            <p class="font-semibold text-text-light dark:text-text-dark">Household count: <span class="text-primary">{{ $familyPeopleCount }}</span> {{ $familyPeopleCount === 1 ? 'person' : 'people' }}</p>
                                            <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1">Includes the resident, spouse, children, and elderly members listed below.</p>
                                                </div>

                                        <div class="family-member-card rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                                            <div class="flex items-start justify-between gap-3 w-full">
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60">Resident</p>
                                                    <p class="family-member-card__name font-semibold mt-1 text-text-light dark:text-text-dark">{{ $residentName }}</p>
                                                    <p class="family-member-card__meta text-xs text-text-light/60 dark:text-text-dark/60 mt-1">Primary account holder — edit personal details from verification/profile, not here.</p>
                                                </div>
                                                <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary shrink-0">Resident</span>
                                            </div>
                                        </div>

                                        @if ($spouseName !== '')
                                            <div class="family-member-card rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                                                <div class="flex items-start justify-between gap-3 w-full">
                                                    <div class="min-w-0 flex-1 pr-1">
                                                        <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60">{{ $spouseRoleLabel }}</p>
                                                        <p class="family-member-card__name font-semibold mt-1 text-text-light dark:text-text-dark">{{ $spouseName }}</p>
                                                        @if (! empty($spouseMetaParts))
                                                            <p class="family-member-card__meta text-xs text-text-light/60 dark:text-text-dark/60 mt-1">{{ implode(' · ', $spouseMetaParts) }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <button type="button" class="open-edit-spouse-btn inline-flex items-center justify-center rounded-lg h-9 w-9 border border-border-light dark:border-border-dark hover:bg-card-light dark:hover:bg-card-dark" title="Edit spouse" aria-label="Edit spouse"
                                                            @php
                                                                $spouseEditFirst = trim((string) ($spouseDetails['first_name'] ?? ''));
                                                                $spouseEditLast = trim((string) ($spouseDetails['last_name'] ?? ''));
                                                                if ($spouseEditFirst === '' && $spouseEditLast === '' && $spouseName !== '') {
                                                                    $nameParts = preg_split('/\s+/', $spouseName) ?: [];
                                                                    $spouseEditFirst = $nameParts[0] ?? '';
                                                                    $spouseEditLast = count($nameParts) > 1 ? (string) end($nameParts) : '';
                                                                }
                                                            @endphp
                                                            data-first-name="{{ $spouseEditFirst }}"
                                                            data-last-name="{{ $spouseEditLast }}"
                                                            data-middle-initial="{{ $spouseDetails['middle_initial'] ?? '' }}"
                                                            data-birthdate="{{ $spouseDetails['birthdate'] ?? '' }}"
                                                            data-parent-role="{{ $spouseDetails['parent_role'] ?? '' }}"
                                                            data-place-of-birth="{{ $spouseDetails['place_of_birth'] ?? '' }}"
                                                            data-educational-attainment="{{ $spouseDetails['educational_attainment'] ?? '' }}"
                                                        >
                                                            <span class="material-symbols-outlined text-base">edit</span>
                                                        </button>
                                                        <button type="button" class="open-remove-member-btn inline-flex items-center justify-center rounded-lg h-9 w-9 border border-red-300 bg-red-50 text-red-600 hover:bg-red-100 dark:border-red-800 dark:bg-red-950/50 dark:text-red-300" title="Remove spouse" aria-label="Remove spouse"
                                                            data-action="remove_spouse"
                                                            data-member-name="{{ $spouseName }}"
                                                            data-member-label="spouse"
                                                        >
                                                            <span class="material-symbols-outlined text-base">person_remove</span>
                                                </button>
                                            </div>
                                                </div>
                                            </div>
                                        @endif

                                        @forelse ($editableChildren as $childIndex => $childRow)
                                            @php
                                                $childName = trim((string) ($childRow['full_name'] ?? $childRow['name'] ?? ''));
                                                $childMeta = collect([
                                                    $childRow['relationship'] ?? null,
                                                    $childRow['gender'] ?? null,
                                                    ! empty($childRow['birthdate']) ? \App\Support\PersonAge::labelFromBirthdate((string) $childRow['birthdate']) : null,
                                                ])->filter()->implode(' · ');
                                            @endphp
                                            <div class="family-member-card rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                                                <div class="flex items-start justify-between gap-3 w-full">
                                                    <div class="min-w-0 flex-1 pr-1">
                                                        <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60">Child</p>
                                                        <p class="family-member-card__name font-semibold mt-1 text-text-light dark:text-text-dark">{{ $childName }}</p>
                                                        @if ($childMeta !== '')
                                                            <p class="family-member-card__meta text-xs text-text-light/60 dark:text-text-dark/60 mt-1">{{ $childMeta }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <button type="button" class="open-edit-child-btn inline-flex items-center justify-center rounded-lg h-9 w-9 border border-border-light dark:border-border-dark hover:bg-card-light dark:hover:bg-card-dark" title="Edit child" aria-label="Edit child"
                                                            data-index="{{ $childIndex }}"
                                                            data-full-name="{{ $childName }}"
                                                            data-birthdate="{{ $childRow['birthdate'] ?? '' }}"
                                                            data-gender="{{ $childRow['gender'] ?? '' }}"
                                                            data-relationship="{{ $childRow['relationship'] ?? '' }}"
                                                            data-currently-studying="{{ $childRow['currently_studying'] ?? 'no' }}"
                                                            data-school="{{ $childRow['school'] ?? '' }}"
                                                            data-educational-attainment="{{ $childRow['educational_attainment'] ?? '' }}"
                                                            data-occupation="{{ $childRow['occupation'] ?? '' }}"
                                                        >
                                                            <span class="material-symbols-outlined text-base">edit</span>
                                                        </button>
                                                        <button type="button" class="open-remove-member-btn inline-flex items-center justify-center rounded-lg h-9 w-9 border border-red-300 bg-red-50 text-red-600 hover:bg-red-100 dark:border-red-800 dark:bg-red-950/50 dark:text-red-300" title="Remove child" aria-label="Remove child"
                                                            data-action="remove_child"
                                                            data-index="{{ $childIndex }}"
                                                            data-member-name="{{ $childName }}"
                                                            data-member-label="child"
                                                        >
                                                            <span class="material-symbols-outlined text-base">person_remove</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="rounded-xl border border-dashed border-border-light dark:border-border-dark px-4 py-6 text-center text-sm text-text-light/60 dark:text-text-dark/60">
                                                No children listed yet. Use <span class="font-semibold">Add Child</span> when a new family member is born or joins the household.
                                            </div>
                                        @endforelse

                                        @forelse ($editableSeniors as $seniorIndex => $seniorRow)
                                            @php
                                                $seniorName = trim((string) ($seniorRow['full_name'] ?? $seniorRow['name'] ?? ''));
                                                $seniorMeta = collect([
                                                    $seniorRow['relationship'] ?? null,
                                                    $seniorRow['gender'] ?? null,
                                                    isset($seniorRow['age_in_years']) ? $seniorRow['age_in_years'].' yrs' : (! empty($seniorRow['birthdate']) ? \App\Support\PersonAge::labelFromBirthdate((string) $seniorRow['birthdate']) : null),
                                                ])->filter()->implode(' · ');
                                            @endphp
                                            <div class="family-member-card rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                                                <div class="flex items-start justify-between gap-3 w-full">
                                                    <div class="min-w-0 flex-1 pr-1">
                                                        <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60">Other family</p>
                                                        <p class="family-member-card__name font-semibold mt-1 text-text-light dark:text-text-dark">{{ $seniorName }}</p>
                                                        @if ($seniorMeta !== '')
                                                            <p class="family-member-card__meta text-xs text-text-light/60 dark:text-text-dark/60 mt-1">{{ $seniorMeta }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <button type="button" class="open-edit-senior-btn inline-flex items-center justify-center rounded-lg h-9 w-9 border border-border-light dark:border-border-dark hover:bg-card-light dark:hover:bg-card-dark" title="Edit other family member" aria-label="Edit other family member"
                                                            data-index="{{ $seniorIndex }}"
                                                            data-full-name="{{ $seniorName }}"
                                                            data-birthdate="{{ $seniorRow['birthdate'] ?? '' }}"
                                                            data-gender="{{ $seniorRow['gender'] ?? '' }}"
                                                            data-relationship="{{ $seniorRow['relationship'] ?? '' }}"
                                                            data-place-of-birth="{{ $seniorRow['place_of_birth'] ?? '' }}"
                                                            data-civil-status="{{ $seniorRow['civil_status'] ?? '' }}"
                                                            data-educational-attainment="{{ $seniorRow['educational_attainment'] ?? '' }}"
                                                            data-occupation="{{ $seniorRow['occupation'] ?? '' }}"
                                                        >
                                                            <span class="material-symbols-outlined text-base">edit</span>
                                                        </button>
                                                        <button type="button" class="open-remove-member-btn inline-flex items-center justify-center rounded-lg h-9 w-9 border border-red-300 bg-red-50 text-red-600 hover:bg-red-100 dark:border-red-800 dark:bg-red-950/50 dark:text-red-300" title="Remove other family member" aria-label="Remove other family member"
                                                            data-action="remove_senior"
                                                            data-index="{{ $seniorIndex }}"
                                                            data-member-name="{{ $seniorName }}"
                                                            data-member-label="other family member"
                                                        >
                                                            <span class="material-symbols-outlined text-base">person_remove</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="rounded-xl border border-dashed border-border-light dark:border-border-dark px-4 py-6 text-center text-sm text-text-light/60 dark:text-text-dark/60">
                                                No other family members listed. Use <span class="font-semibold">Add other family</span> when a relative joins the household, or remove them here if they leave.
                                            </div>
                                        @endforelse
                                    </div>

                                    {{-- Child modal --}}
                                    <div id="familyChildModal" class="hidden fixed inset-0 z-50 flex items-end lg:items-center justify-center bg-black/50 px-0 py-0 lg:px-5 lg:py-4">
                                        <div class="transaction-modal-panel max-w-lg w-full">
                                            <div class="transaction-modal-header flex items-start justify-between gap-3 border-b border-border-light dark:border-border-dark px-5 py-4 shrink-0">
                                                <div class="min-w-0">
                                                    <h5 id="familyChildModalTitle" class="text-lg font-bold">Add Child</h5>
                                                    <p class="tx-modal-subtitle text-sm text-text-light/70 dark:text-text-dark/70 mt-1">Updates the household list used for family counting.</p>
                                                </div>
                                                <button type="button" class="close-family-child-modal inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border-light dark:border-border-dark" aria-label="Close"><span class="material-symbols-outlined text-base">close</span></button>
                                            </div>
                                            <div class="transaction-modal-body">
                                                <form method="POST" action="{{ route('bhw.residents.household.update', $resident) }}" class="tx-form" id="familyChildForm">
                                            @csrf
                                                    <input type="hidden" name="household_action" id="family_child_action" value="add_child" />
                                                    <input type="hidden" name="member_index" id="family_child_index" value="" />
                                            <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="family_child_full_name">Full Name</label>
                                                        <input id="family_child_full_name" name="full_name" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm" />
                                                    </div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="family_child_birthdate">Birthdate</label>
                                                            <input id="family_child_birthdate" name="birthdate" type="date" required max="{{ now()->toDateString() }}" class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="family_child_gender">Gender</label>
                                                            <select id="family_child_gender" name="gender" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm">
                                                                <option value="">Select</option>
                                                                <option value="Male">Male</option>
                                                                <option value="Female">Female</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="family_child_relationship">Relationship</label>
                                                            <select id="family_child_relationship" name="relationship" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm">
                                                                <option value="">Select</option>
                                                                @foreach (\App\Models\ResidentRequest::CHILD_RELATIONSHIPS as $rel)
                                                                    <option value="{{ $rel }}">{{ $rel }}</option>
                                                    @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="family_child_studying">Status</label>
                                                            <select id="family_child_studying" name="currently_studying" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm">
                                                                <option value="infant">Infant</option>
                                                                <option value="yes">Studying</option>
                                                                <option value="no">Working / Not studying</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div id="familyChildSchoolFields" class="hidden">
                                                        <label class="block text-sm font-semibold mb-1.5" for="family_child_school">School</label>
                                                        <input id="family_child_school" name="school" class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm" />
                                                    </div>
                                                    <div id="familyChildWorkFields" class="hidden space-y-3">
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="family_child_edu">Educational Attainment</label>
                                                            <select id="family_child_edu" name="educational_attainment" class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm">
                                                                <option value="">Select</option>
                                                                @foreach ($educationOptions as $edu)
                                                                    <option value="{{ $edu }}">{{ $edu }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="family_child_occupation">Occupation</label>
                                                            <input id="family_child_occupation" name="occupation" class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm" />
                                                        </div>
                                                    </div>
                                                    <div class="tx-form-actions flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1 border-t border-border-light dark:border-border-dark">
                                                        <button type="button" class="close-family-child-modal h-11 px-4 rounded-xl border border-border-light dark:border-border-dark text-sm font-semibold">Cancel</button>
                                                        <button type="submit" class="h-11 px-5 rounded-xl bg-primary text-white text-sm font-bold">Save Child</button>
                                                    </div>
                                                </form>
                                            </div>
                                                </div>
                                            </div>

                                    {{-- Senior modal --}}
                                    <div id="familySeniorModal" class="hidden fixed inset-0 z-50 flex items-end lg:items-center justify-center bg-black/50 px-0 py-0 lg:px-5 lg:py-4">
                                        <div class="transaction-modal-panel max-w-lg w-full">
                                            <div class="transaction-modal-header flex items-start justify-between gap-3 border-b border-border-light dark:border-border-dark px-5 py-4 shrink-0">
                                                <div class="min-w-0">
                                                    <h5 id="familySeniorModalTitle" class="text-lg font-bold">Add other family</h5>
                                                    <p class="tx-modal-subtitle text-sm text-text-light/70 dark:text-text-dark/70 mt-1">Must be at least 60 years old. Remove here if the member passes away.</p>
                                                </div>
                                                <button type="button" class="close-family-senior-modal inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border-light dark:border-border-dark" aria-label="Close"><span class="material-symbols-outlined text-base">close</span></button>
                                            </div>
                                            <div class="transaction-modal-body">
                                                <form method="POST" action="{{ route('bhw.residents.household.update', $resident) }}" class="tx-form" id="familySeniorForm">
                                                    @csrf
                                                    <input type="hidden" name="household_action" id="family_senior_action" value="add_senior" />
                                                    <input type="hidden" name="member_index" id="family_senior_index" value="" />
                                            <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="family_senior_full_name">Full Name</label>
                                                        <input id="family_senior_full_name" name="full_name" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm" />
                                                    </div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="family_senior_birthdate">Birthdate</label>
                                                            <input id="family_senior_birthdate" name="birthdate" type="date" required max="{{ now()->toDateString() }}" class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="family_senior_gender">Gender</label>
                                                            <select id="family_senior_gender" name="gender" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm">
                                                                <option value="">Select</option>
                                                                <option value="Male">Male</option>
                                                                <option value="Female">Female</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="family_senior_relationship">Relationship</label>
                                                        <select id="family_senior_relationship" name="relationship" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm">
                                                            <option value="">Select</option>
                                                            @foreach (\App\Models\ResidentRequest::HOUSEHOLD_SENIOR_RELATIONSHIPS as $rel)
                                                                <option value="{{ $rel }}">{{ $rel }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="family_senior_pob">Place of Birth</label>
                                                        <input id="family_senior_pob" name="place_of_birth" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="family_senior_civil">Marital Status</label>
                                                        <select id="family_senior_civil" name="civil_status" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm">
                                                            <option value="">Select</option>
                                                            @foreach (\App\Models\User::CIVIL_STATUSES as $status)
                                                                <option value="{{ $status }}">{{ $status }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="family_senior_edu">Educational Attainment</label>
                                                        <select id="family_senior_edu" name="educational_attainment" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm">
                                                            <option value="">Select</option>
                                                            @foreach ($educationOptions as $edu)
                                                                <option value="{{ $edu }}">{{ $edu }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="family_senior_occupation">Occupation <span class="font-normal text-text-light/50">(optional)</span></label>
                                                        <input id="family_senior_occupation" name="occupation" class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm" />
                                                    </div>
                                                    <div class="tx-form-actions flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1 border-t border-border-light dark:border-border-dark">
                                                        <button type="button" class="close-family-senior-modal h-11 px-4 rounded-xl border border-border-light dark:border-border-dark text-sm font-semibold">Cancel</button>
                                                        <button type="submit" class="h-11 px-5 rounded-xl bg-primary text-white text-sm font-bold">Save family member</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                            </div>

                                    {{-- Spouse modal --}}
                                    <div id="familySpouseModal" class="hidden fixed inset-0 z-50 flex items-end lg:items-center justify-center bg-black/50 px-0 py-0 lg:px-5 lg:py-4">
                                        <div class="transaction-modal-panel max-w-lg w-full">
                                            <div class="transaction-modal-header flex items-start justify-between gap-3 border-b border-border-light dark:border-border-dark px-5 py-4 shrink-0">
                                                <div class="min-w-0">
                                                    <h5 id="familySpouseModalTitle" class="text-lg font-bold">Add Spouse</h5>
                                                    <p class="tx-modal-subtitle text-sm text-text-light/70 dark:text-text-dark/70 mt-1">Spouse is included in household counting and member pickers.</p>
                                                </div>
                                                <button type="button" class="close-family-spouse-modal inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border-light dark:border-border-dark" aria-label="Close"><span class="material-symbols-outlined text-base">close</span></button>
                                            </div>
                                            <div class="transaction-modal-body">
                                                <form method="POST" action="{{ route('bhw.residents.household.update', $resident) }}" class="tx-form" id="familySpouseForm">
                                                    @csrf
                                                    <input type="hidden" name="household_action" value="set_spouse" />
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="family_spouse_first">First Name</label>
                                                            <input id="family_spouse_first" name="spouse_first_name" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="family_spouse_last">Last Name</label>
                                                            <input id="family_spouse_last" name="spouse_last_name" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm" />
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="family_spouse_mi">M.I. <span class="font-normal text-text-light/50">(optional)</span></label>
                                                            <input id="family_spouse_mi" name="spouse_middle_initial" maxlength="5" class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="family_spouse_birthdate">Birthdate</label>
                                                            <input id="family_spouse_birthdate" name="spouse_birthdate" type="date" required max="{{ now()->toDateString() }}" class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm" />
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="family_spouse_role">Guardian Role</label>
                                                        <select id="family_spouse_role" name="spouse_parent_role" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm">
                                                            <option value="">Select</option>
                                                            @foreach (\App\Models\ResidentRequest::SPOUSE_PARENT_ROLES as $role)
                                                                <option value="{{ $role }}">{{ $role }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="family_spouse_pob">Place of Birth</label>
                                                        <input id="family_spouse_pob" name="spouse_place_of_birth" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-semibold mb-1.5" for="family_spouse_edu">Educational Attainment</label>
                                                        <select id="family_spouse_edu" name="spouse_educational_attainment" required class="w-full h-11 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-3 text-sm">
                                                            <option value="">Select</option>
                                                            @foreach ($educationOptions as $edu)
                                                                <option value="{{ $edu }}">{{ $edu }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="tx-form-actions flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1 border-t border-border-light dark:border-border-dark">
                                                        <button type="button" class="close-family-spouse-modal h-11 px-4 rounded-xl border border-border-light dark:border-border-dark text-sm font-semibold">Cancel</button>
                                                        <button type="submit" class="h-11 px-5 rounded-xl bg-primary text-white text-sm font-bold">Save Spouse</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                            </div>

                                    {{-- Remove confirm --}}
                                    <div id="familyRemoveModal" class="hidden fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/50 px-0 sm:px-4">
                                        <div class="w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark shadow-xl">
                                            <div class="flex items-start justify-between gap-3 border-b border-border-light dark:border-border-dark px-4 py-4">
                                                <div class="min-w-0">
                                                    <h5 class="text-lg font-bold text-red-600 dark:text-red-300">Remove Member</h5>
                                                    <p id="familyRemoveMessage" class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1"></p>
                                                </div>
                                                <button type="button" id="closeFamilyRemoveModal" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark" aria-label="Close"><span class="material-symbols-outlined">close</span></button>
                                            </div>
                                            <form method="POST" action="{{ route('bhw.residents.household.update', $resident) }}" class="px-4 py-4 space-y-4">
                                                @csrf
                                                <input type="hidden" name="household_action" id="family_remove_action" value="" />
                                                <input type="hidden" name="member_index" id="family_remove_index" value="" />
                                                <p class="text-xs text-text-light/60 dark:text-text-dark/60">This updates family counting. Active monitoring for this person will be marked completed.</p>
                                                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                                                    <button type="button" id="cancelFamilyRemoveModal" class="h-11 px-4 rounded-xl border border-border-light dark:border-border-dark text-sm font-semibold">Cancel</button>
                                                    <button type="submit" class="h-11 px-4 rounded-xl bg-red-600 text-white text-sm font-bold">Remove</button>
                                                </div>
                                            </form>
                                        </div>
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

                                    <div id="medicalHistoryFormWrapper" class="hidden fixed inset-0 z-50 flex items-end lg:items-center justify-center bg-black/50 px-0 py-0 lg:px-5 lg:py-4">
                                        <div class="transaction-modal-panel" role="dialog" aria-modal="true" aria-labelledby="transactionModalTitle">
                                            <div class="transaction-modal-header flex items-start justify-between gap-3 border-b border-border-light dark:border-border-dark px-5 py-4 shrink-0">
                                                <div class="min-w-0">
                                                    <p class="tx-modal-eyebrow text-xs font-semibold uppercase tracking-wide text-primary/80 dark:text-primary/90">Medicine dispensing</p>
                                                    <h5 id="transactionModalTitle" class="text-lg font-bold text-text-light dark:text-text-dark mt-1">Add Transaction</h5>
                                                    <p class="tx-modal-subtitle text-sm text-text-light/70 dark:text-text-dark/70 mt-1">Select who received medicine, then enter pcs for each item. Age updates from the selected member.</p>
                                                </div>
                                                <button type="button" id="closeMedicalHistoryForm" class="inline-flex items-center justify-center w-9 h-9 lg:w-8 lg:h-8 rounded-lg border border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark shrink-0" aria-label="Close transaction form">
                                                    <span class="material-symbols-outlined text-base">close</span>
                                                </button>
                                            </div>

                                            <div class="transaction-modal-body">
                                            @php
                                                $defaultTransactionGender = old('gender');
                                                if (! $defaultTransactionGender) {
                                                    $defaultTransactionGender = $householdMemberOptions
                                                        ->firstWhere('name', $defaultTransactionMemberSelectValue)['gender']
                                                        ?? '';
                                                }
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
                                                                data-age="{{ $option['age'] ?? '' }}"
                                                                data-gender="{{ $option['gender'] ?? '' }}"
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
                                                        @php
                                                            $defaultSelectedMemberAge = $householdMemberOptions
                                                                ->firstWhere('name', $defaultTransactionMemberSelectValue)['age']
                                                                ?? $residentAge;
                                                        @endphp
                                                        <input id="age" name="age" value="{{ old('age', $defaultSelectedMemberAge) }}" class="w-full h-11 rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" placeholder="e.g., 58" type="number" min="0" max="150" required />
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

                                    <div id="monitoringCaseFormWrapper" class="hidden fixed inset-0 z-50 flex items-end lg:items-center justify-center bg-black/50 px-0 py-0 lg:px-5 lg:py-4">
                                        <div class="transaction-modal-panel" role="dialog" aria-modal="true" aria-labelledby="monitoringModalTitle">
                                            <div class="transaction-modal-header flex items-start justify-between gap-3 border-b border-border-light dark:border-border-dark px-5 py-4 shrink-0">
                                                <div class="min-w-0">
                                                    <p class="tx-modal-eyebrow text-xs font-semibold uppercase tracking-wide text-primary/80 dark:text-primary/90">Household monitoring</p>
                                                    <h5 id="monitoringModalTitle" class="text-lg font-bold text-text-light dark:text-text-dark mt-1">Add Monitoring</h5>
                                                    <p class="tx-modal-subtitle text-sm text-text-light/70 dark:text-text-dark/70 mt-1">Select a household member, monitoring type, and initial vitals for follow-up.</p>
                                                </div>
                                                <button type="button" id="closeMonitoringCaseForm" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark shrink-0" aria-label="Close monitoring form">
                                                    <span class="material-symbols-outlined text-base">close</span>
                                                </button>
                                            </div>

                                            <div class="transaction-modal-body">
                                            <form method="POST" action="{{ route('bhw.residents.monitoring-cases.store', $resident) }}" class="tx-form" id="monitoringCaseForm">
                                                    @csrf
                                                <input type="hidden" name="form_intent" value="monitoring_case" />

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
                                                                    <label class="block text-sm font-semibold mb-1.5" for="monitoring_height_cm">Height (cm) <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></label>
                                                                    <input id="monitoring_height_cm" name="height_cm" value="{{ old('height_cm') }}" data-visit-height="new" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="number" min="30" max="300" step="0.1" placeholder="e.g., 165" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1.5" for="monitoring_weight_kg">Weight (kg) <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></label>
                                                                    <input id="monitoring_weight_kg" name="weight_kg" value="{{ old('weight_kg') }}" data-visit-weight="new" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="number" min="1" max="500" step="0.1" placeholder="e.g., 60" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1.5">BMI (auto) <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></label>
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
                                                                    <label class="block text-sm font-semibold mb-1.5" for="monitoring_muac_cm">MUAC (cm) <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></label>
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

                                    <div class="profile-tab-scroll">
                                        @php
                                            $monitoringCases = $resident->monitoringCases ?? collect();
                                            $activeMonitoringCases = $monitoringCases->where('is_active', true)->values();
                                            $completedMonitoringCases = $monitoringCases->where('is_active', false)->values();
                                        @endphp

                                        @if ($monitoringCases->isEmpty())
                                            <div class="rounded-xl border border-dashed border-border-light dark:border-border-dark p-8 text-center text-sm text-text-light/70 dark:text-text-dark/70">
                                                <span class="material-symbols-outlined text-4xl text-primary/70">health_and_safety</span>
                                                <p class="mt-3 font-semibold">No household monitoring yet.</p>
                                                <p class="mt-1">Add monitoring for a pregnant family member or a senior with health concerns.</p>
                                            </div>
                                        @else
                                            <div class="mnt-case-list">
                                                @if ($activeMonitoringCases->isNotEmpty())
                                                    <p class="mnt-section-label">
                                                        <span class="material-symbols-outlined">monitor_heart</span>
                                                        Active monitoring · {{ $activeMonitoringCases->count() }}
                                                    </p>
                                                    @foreach ($activeMonitoringCases as $monitoringCase)
                                                        @include('partials.bhw-monitoring-case-card', ['monitoringCase' => $monitoringCase])
                                                    @endforeach
                                                @endif

                                                @if ($completedMonitoringCases->isNotEmpty())
                                                    <p class="mnt-section-label {{ $activeMonitoringCases->isNotEmpty() ? 'mt-2' : '' }}">
                                                        <span class="material-symbols-outlined">task_alt</span>
                                                        Completed · {{ $completedMonitoringCases->count() }}
                                                    </p>
                                                    @foreach ($completedMonitoringCases as $monitoringCase)
                                                        @include('partials.bhw-monitoring-case-card', ['monitoringCase' => $monitoringCase])
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div id="addVisitModalWrapper" class="hidden fixed inset-0 z-50 flex items-end lg:items-center justify-center bg-black/50 px-0 py-0 lg:px-5 lg:py-4">
                                        <div class="transaction-modal-panel" role="dialog" aria-modal="true" aria-labelledby="addVisitModalTitle">
                                            <div class="transaction-modal-header flex items-start justify-between gap-3 border-b border-border-light dark:border-border-dark px-5 py-4 shrink-0">
                                                <div class="min-w-0">
                                                    <p class="tx-modal-eyebrow text-xs font-semibold uppercase tracking-wide text-primary/80 dark:text-primary/90">Follow-up visit</p>
                                                    <h5 id="addVisitModalTitle" class="text-lg font-bold text-text-light dark:text-text-dark mt-1">Add Visit</h5>
                                                    <p class="tx-modal-subtitle text-sm text-text-light/70 dark:text-text-dark/70 mt-1">Recording visit for <span id="addVisitMemberName" class="font-semibold text-text-light dark:text-text-dark"></span>. Leave weight, height, BMI, BP, or MUAC blank to keep the previous visit values.</p>
                                                </div>
                                                <button type="button" id="closeAddVisitModalBtn" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark shrink-0" aria-label="Close add visit form">
                                                    <span class="material-symbols-outlined text-base">close</span>
                                                </button>
                                            </div>

                                            <div class="transaction-modal-body">
                                                <form id="addVisitForm" method="POST" action="#" class="tx-form">
                                                    @csrf
                                                    <input type="hidden" name="form_intent" value="monitoring_visit" />
                                                    <input type="hidden" name="monitoring_case_id" id="addVisitCaseId" value="" />

                                                    <section class="tx-recipient-section">
                                                        <div class="tx-recipient-row">
                                                            <label class="tx-recipient-row__label text-text-light dark:text-text-dark" for="add_visit_date">Visit Date</label>
                                                    <input
                                                                id="add_visit_date"
                                                                name="visit_date"
                                                                value="{{ old('form_intent') === 'monitoring_visit' ? old('visit_date', now()->toDateString()) : now()->toDateString() }}"
                                                                class="tx-recipient-row__select w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3"
                                                                type="date"
                                                        required
                                                    />
                                                        </div>
                                                    </section>

                                                    <section class="mnt-vitals-section">
                                                        <div class="tx-section-heading">
                                                            <h6 class="text-sm font-bold text-text-light dark:text-text-dark">Vitals <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></h6>
                                                            <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Leave blank to keep values from the previous visit. Enter new numbers only when they change. BMI recalculates when height and weight are available.</p>
                                                        </div>
                                                        <div class="mnt-vitals-grid">
                                                            <div class="mnt-metric-card">
                                                                <div class="mnt-metric-card__head">
                                                                    <span class="mnt-metric-card__icon">
                                                                        <span class="material-symbols-outlined text-[20px]">monitor_weight</span>
                                                                    </span>
                                                                    <div class="min-w-0">
                                                                        <p class="mnt-metric-card__title text-text-light dark:text-text-dark">Height, Weight &amp; BMI</p>
                                                                        <p class="mnt-metric-card__subtitle">Optional · blank keeps previous visit</p>
                                                                    </div>
                                                                </div>
                                                                <div class="mnt-metric-fields">
                                                                    <div>
                                                                        <label class="block text-sm font-semibold mb-1.5" for="add_visit_height_cm">Height (cm) <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></label>
                                                                        <input id="add_visit_height_cm" name="height_cm" value="{{ old('form_intent') === 'monitoring_visit' ? old('height_cm') : '' }}" data-visit-height="visit" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="number" min="30" max="300" step="0.1" placeholder="e.g., 165" />
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-semibold mb-1.5" for="add_visit_weight_kg">Weight (kg) <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></label>
                                                                        <input id="add_visit_weight_kg" name="weight_kg" value="{{ old('form_intent') === 'monitoring_visit' ? old('weight_kg') : '' }}" data-visit-weight="visit" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="number" min="1" max="500" step="0.1" placeholder="e.g., 60" />
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-semibold mb-1.5">BMI (auto) <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></label>
                                                                        <input data-visit-bmi="visit" type="text" readonly class="w-full rounded-xl bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm px-3 font-semibold" placeholder="Auto" />
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div id="addVisitBpSection" class="hidden mnt-metric-card">
                                                                <div class="mnt-metric-card__head">
                                                                    <span class="mnt-metric-card__icon">
                                                                        <span class="material-symbols-outlined text-[20px]">cardiology</span>
                                                                    </span>
                                                                    <div class="min-w-0">
                                                                        <p class="mnt-metric-card__title text-text-light dark:text-text-dark">Blood Pressure</p>
                                                                        <p class="mnt-metric-card__subtitle">Optional · blank keeps previous visit</p>
                                                                    </div>
                                                                </div>
                                                                <div class="mnt-metric-fields">
                                                                    <div>
                                                                        <label class="block text-sm font-semibold mb-1.5" for="add_visit_bp_systolic">Systolic</label>
                                                                        <input id="add_visit_bp_systolic" name="bp_systolic" value="{{ old('form_intent') === 'monitoring_visit' ? old('bp_systolic') : '' }}" data-visit-bp-sys="visit" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="number" min="50" max="300" placeholder="120" />
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-semibold mb-1.5" for="add_visit_bp_diastolic">Diastolic</label>
                                                                        <input id="add_visit_bp_diastolic" name="bp_diastolic" value="{{ old('form_intent') === 'monitoring_visit' ? old('bp_diastolic') : '' }}" data-visit-bp-dia="visit" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="number" min="30" max="200" placeholder="80" />
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-semibold mb-1.5">BP Reading</label>
                                                                        <input data-visit-bp-preview="visit" type="text" readonly class="w-full rounded-xl bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm px-3 font-semibold" placeholder="Auto" />
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="mnt-metric-card">
                                                                <div class="mnt-metric-card__head">
                                                                    <span class="mnt-metric-card__icon">
                                                                        <span class="material-symbols-outlined text-[20px]">straighten</span>
                                                                    </span>
                                                                    <div class="min-w-0">
                                                                        <p class="mnt-metric-card__title text-text-light dark:text-text-dark">MUAC</p>
                                                                        <p class="mnt-metric-card__subtitle">Optional · blank keeps previous visit</p>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1.5" for="add_visit_muac_cm">MUAC (cm) <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></label>
                                                                    <input id="add_visit_muac_cm" name="muac_cm" value="{{ old('form_intent') === 'monitoring_visit' ? old('muac_cm') : '' }}" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="number" min="0" max="100" step="0.1" placeholder="e.g., 24.5" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </section>

                                                    <section id="addVisitPregnantSection" class="hidden space-y-3">
                                                        <div class="tx-section-heading">
                                                            <h6 class="text-sm font-bold text-text-light dark:text-text-dark">Pregnant follow-up</h6>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="add_visit_last_period_date">Last Period Date <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></label>
                                                            <input id="add_visit_last_period_date" name="last_period_date" value="{{ old('form_intent') === 'monitoring_visit' ? old('last_period_date') : '' }}" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3" type="date" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1.5" for="add_visit_anti_tetanus">Anti-Tetanus <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></label>
                                                            <select id="add_visit_anti_tetanus" name="anti_tetanus" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3">
                                                                <option value="" {{ old('form_intent') === 'monitoring_visit' && old('anti_tetanus') ? '' : 'selected' }}>Not given</option>
                                                                <option value="TT1" {{ old('form_intent') === 'monitoring_visit' && old('anti_tetanus') === 'TT1' ? 'selected' : '' }}>TT1</option>
                                                                <option value="TT2" {{ old('form_intent') === 'monitoring_visit' && old('anti_tetanus') === 'TT2' ? 'selected' : '' }}>TT2</option>
                                                                <option value="TT3" {{ old('form_intent') === 'monitoring_visit' && old('anti_tetanus') === 'TT3' ? 'selected' : '' }}>TT3</option>
                                                                <option value="TT4" {{ old('form_intent') === 'monitoring_visit' && old('anti_tetanus') === 'TT4' ? 'selected' : '' }}>TT4</option>
                                                                <option value="TT5" {{ old('form_intent') === 'monitoring_visit' && old('anti_tetanus') === 'TT5' ? 'selected' : '' }}>TT5</option>
                                                                <option value="Booster" {{ old('form_intent') === 'monitoring_visit' && old('anti_tetanus') === 'Booster' ? 'selected' : '' }}>Booster</option>
                                                            </select>
                                                        </div>
                                                    </section>

                                                    <section>
                                                        <label class="block text-sm font-semibold mb-1.5" for="add_visit_notes">Notes <span class="font-normal text-text-light/50 dark:text-text-dark/50">(optional)</span></label>
                                                        <textarea id="add_visit_notes" name="notes" rows="2" maxlength="500" class="w-full rounded-xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm px-3 py-2" placeholder="Additional visit notes">{{ old('form_intent') === 'monitoring_visit' ? old('notes') : '' }}</textarea>
                                                    </section>

                                                    <div class="tx-form-actions flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-1 border-t border-border-light dark:border-border-dark">
                                                        <button type="button" id="cancelAddVisitModalBtn" class="inline-flex items-center justify-center h-11 px-4 rounded-xl border border-border-light dark:border-border-dark text-sm font-semibold text-text-light dark:text-text-dark hover:bg-background-light dark:hover:bg-background-dark">
                                                            Cancel
                                                        </button>
                                                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl h-11 px-5 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap">
                                                            <span class="material-symbols-outlined text-base">save</span>
                                                            <span>Save Visit</span>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                            </div>
                                    </div>

                                    <div id="completeMonitoringModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4 py-6">
                                        <div class="w-full max-w-md rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark shadow-xl" role="dialog" aria-modal="true" aria-labelledby="completeMonitoringModalTitle">
                                            <div class="flex items-start justify-between gap-3 border-b border-border-light dark:border-border-dark px-5 py-4">
                                                <div class="min-w-0">
                                                    <h5 id="completeMonitoringModalTitle" class="text-lg font-bold text-text-light dark:text-text-dark">Mark monitoring completed?</h5>
                                                    <p id="completeMonitoringModalMessage" class="mt-1 text-sm text-text-light/70 dark:text-text-dark/70">
                                                        Visit records will stay available in monitoring history.
                                                    </p>
                                                </div>
                                                <button type="button" id="closeCompleteMonitoringModal" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark shrink-0" aria-label="Close confirmation">
                                                    <span class="material-symbols-outlined text-base">close</span>
                                                </button>
                                            </div>
                                            <div class="px-5 py-4">
                                                <p class="text-sm text-text-light/80 dark:text-text-dark/80">
                                                    This stops new visits for this case. You can still review all previous records, and you can start a new monitoring case later if needed.
                                                </p>
                                            </div>
                                            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-border-light dark:border-border-dark px-5 py-4">
                                                <button type="button" id="cancelCompleteMonitoringModal" class="inline-flex items-center justify-center rounded-xl h-11 px-5 border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">
                                                    Cancel
                                                </button>
                                                <form id="completeMonitoringForm" method="POST" action="#">
                                                    @csrf
                                                    <button type="submit" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl h-11 px-5 bg-primary text-white text-sm font-bold">
                                                        <span class="material-symbols-outlined text-base">check_circle</span>
                                                        Mark Completed
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="consultations" class="tab-content min-w-0">
                                    <div class="profile-tab-toolbar">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="min-w-0">
                                                <h4 class="text-base sm:text-lg font-bold text-text-light dark:text-text-dark">Consultations</h4>
                                                <p class="mt-0.5 text-xs sm:text-sm text-text-light/60 dark:text-text-dark/60">Appointments scheduled for this resident</p>
                                            </div>
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
                                    <div class="consult-list">
                                        @forelse ($resident->consultations as $consultation)
                                            @php
                                                $consultAt = $consultation->scheduled_at;
                                                $checkupLabel = $consultation->medical_checkup ?: 'General consultation';
                                                $isPast = $consultAt ? $consultAt->isPast() : false;
                                            @endphp
                                            <article class="consult-card">
                                                <div class="consult-date-badge" aria-hidden="true">
                                                    <span class="consult-date-badge__month">{{ $consultAt?->format('M') ?: '—' }}</span>
                                                    <span class="consult-date-badge__day">{{ $consultAt?->format('d') ?: '—' }}</span>
                                                    <span class="consult-date-badge__year">{{ $consultAt?->format('Y') ?: '' }}</span>
                                                </div>
                                                <div class="consult-card__body">
                                                    <div class="consult-card__top">
                                                        <h5 class="consult-card__title">{{ $checkupLabel }}</h5>
                                                        <span class="consult-time-chip">
                                                            <span class="material-symbols-outlined">schedule</span>
                                                            {{ $consultAt?->format('g:i A') ?: '—' }}
                                                        </span>
                                                    </div>
                                                @if ($consultation->note)
                                                        <p class="consult-card__note">{{ $consultation->note }}</p>
                                                @endif
                                                    <div class="consult-card__meta">
                                                        <span class="consult-meta-chip">
                                                            <span class="material-symbols-outlined">calendar_month</span>
                                                            {{ $consultAt?->format('D, M j') ?: 'No date' }}
                                                        </span>
                                                        <span class="consult-meta-chip">
                                                            <span class="material-symbols-outlined">{{ $isPast ? 'event_available' : 'upcoming' }}</span>
                                                            {{ $isPast ? 'Past' : 'Upcoming' }}
                                                        </span>
                                            </div>
                                                </div>
                                            </article>
                                        @empty
                                            <div class="consult-empty">
                                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">event_busy</span>
                                                <p class="consult-empty__title">No consultations yet</p>
                                                <p class="consult-empty__text">Schedule an appointment to notify this resident about their checkup.</p>
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
    const genderSelect = document.getElementById('gender');
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
    const preserveTransactionDemographics = @json(old('age') !== null || old('gender') !== null);

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

    function applyTransactionMemberDemographics(selectedOption) {
        if (! selectedOption) {
            return;
        }

        const memberAge = selectedOption.dataset.age;
        if (ageInput && memberAge !== undefined && memberAge !== '') {
            ageInput.value = memberAge;
        }

        const memberGender = selectedOption.dataset.gender || '';
        if (genderSelect && (memberGender === 'M' || memberGender === 'F')) {
            genderSelect.value = memberGender;
        }
    }

    function applyTransactionMemberSelection({ fillDemographics = true } = {}) {
        const selectedOption = transactionMemberSelect?.selectedOptions?.[0];

        if (transactionMemberNameInput) {
            transactionMemberNameInput.value = transactionMemberSelect?.value || '';
        }
        if (transactionMemberSourceInput) {
            transactionMemberSourceInput.value = selectedOption?.dataset.source || '';
        }
        if (transactionMemberIndexInput) {
            transactionMemberIndexInput.value = selectedOption?.dataset.index || '';
        }

        if (fillDemographics) {
            applyTransactionMemberDemographics(selectedOption);
        }

        syncTransactionRecipientHints();
        syncTransactionMedicationCards();
    }

    transactionMemberSelect?.addEventListener('change', () => {
        applyTransactionMemberSelection({ fillDemographics: true });
    });

    function resetTransactionModalViewport() {
        if (! medicalHistoryFormWrapper) {
            return;
        }

        medicalHistoryFormWrapper.style.top = '';
        medicalHistoryFormWrapper.style.height = '';
        medicalHistoryFormWrapper.style.bottom = '';

        const panel = medicalHistoryFormWrapper.querySelector('.transaction-modal-panel');
        if (panel) {
            panel.style.maxHeight = '';
        }
    }

    function syncTransactionModalToViewport() {
        if (! medicalHistoryFormWrapper || medicalHistoryFormWrapper.classList.contains('hidden')) {
            return;
        }

        if (window.matchMedia('(min-width: 1024px)').matches || ! window.visualViewport) {
            resetTransactionModalViewport();
            return;
        }

        const vv = window.visualViewport;
        const availableHeight = Math.max(280, Math.floor(vv.height));

        medicalHistoryFormWrapper.style.top = `${Math.max(0, Math.floor(vv.offsetTop))}px`;
        medicalHistoryFormWrapper.style.height = `${Math.floor(vv.height)}px`;
        medicalHistoryFormWrapper.style.bottom = 'auto';

        const panel = medicalHistoryFormWrapper.querySelector('.transaction-modal-panel');
        if (panel) {
            panel.style.maxHeight = `${availableHeight}px`;
        }
    }

    function scrollTransactionFieldIntoView(target) {
        if (! target || ! medicalHistoryFormWrapper || medicalHistoryFormWrapper.classList.contains('hidden')) {
            return;
        }

        if (! medicalHistoryFormWrapper.contains(target)) {
            return;
        }

        const body = medicalHistoryFormWrapper.querySelector('.transaction-modal-body');
        if (! body) {
            return;
        }

        const scrollTarget = () => {
            const bodyRect = body.getBoundingClientRect();
            const targetRect = target.getBoundingClientRect();
            const padding = 24;
            const visibleBottom = bodyRect.bottom - padding;
            const visibleTop = bodyRect.top + padding;

            if (targetRect.bottom > visibleBottom) {
                body.scrollTop += targetRect.bottom - visibleBottom;
            } else if (targetRect.top < visibleTop) {
                body.scrollTop -= visibleTop - targetRect.top;
            }
        };

        requestAnimationFrame(() => {
            scrollTarget();
            window.setTimeout(scrollTarget, 280);
        });
    }

    function closeMedicalHistoryModal() {
        medicalHistoryFormWrapper?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        resetTransactionModalViewport();
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

    applyTransactionMemberSelection({ fillDemographics: ! preserveTransactionDemographics });

    toggleMedicalHistoryFormBtn?.addEventListener('click', () => {
        medicalHistoryFormWrapper?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        applyTransactionMemberSelection({ fillDemographics: true });
        syncTransactionRecipientHints();
        syncTransactionMedicationCards();
        syncTransactionModalToViewport();
        transactionMemberSelect?.focus();
    });

    closeMedicalHistoryFormBtn?.addEventListener('click', closeMedicalHistoryModal);
    cancelMedicalHistoryFormBtn?.addEventListener('click', closeMedicalHistoryModal);

    medicalHistoryFormWrapper?.addEventListener('click', (event) => {
        if (event.target === medicalHistoryFormWrapper) {
            closeMedicalHistoryModal();
        }
    });

    medicalHistoryFormWrapper?.addEventListener('focusin', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.matches('input, select, textarea')) {
            syncTransactionModalToViewport();
            scrollTransactionFieldIntoView(target);
        }
    });

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', syncTransactionModalToViewport);
        window.visualViewport.addEventListener('scroll', syncTransactionModalToViewport);
    }

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
        document.body.classList.remove('overflow-hidden');
        resetMonitoringModalViewport();
    }

    function resetMonitoringModalViewport() {
        if (! monitoringCaseFormWrapper) {
            return;
        }

        monitoringCaseFormWrapper.style.top = '';
        monitoringCaseFormWrapper.style.height = '';
        monitoringCaseFormWrapper.style.bottom = '';

        const panel = monitoringCaseFormWrapper.querySelector('.transaction-modal-panel');
        if (panel) {
            panel.style.maxHeight = '';
        }
    }

    function syncMonitoringModalToViewport() {
        if (! monitoringCaseFormWrapper || monitoringCaseFormWrapper.classList.contains('hidden')) {
            return;
        }

        if (window.matchMedia('(min-width: 1024px)').matches || ! window.visualViewport) {
            resetMonitoringModalViewport();
            return;
        }

        const vv = window.visualViewport;
        const availableHeight = Math.max(240, Math.floor(vv.height) - 8);

        monitoringCaseFormWrapper.style.top = `${Math.max(0, Math.floor(vv.offsetTop))}px`;
        monitoringCaseFormWrapper.style.height = `${Math.floor(vv.height)}px`;
        monitoringCaseFormWrapper.style.bottom = 'auto';

        const panel = monitoringCaseFormWrapper.querySelector('.transaction-modal-panel');
        if (panel) {
            panel.style.maxHeight = `${availableHeight}px`;
        }
    }

    function scrollMonitoringFieldIntoView(target) {
        if (! target || ! monitoringCaseFormWrapper || monitoringCaseFormWrapper.classList.contains('hidden')) {
            return;
        }

        if (! monitoringCaseFormWrapper.contains(target)) {
            return;
        }

        const body = monitoringCaseFormWrapper.querySelector('.transaction-modal-body');
        if (! body) {
            return;
        }

        const scrollTarget = () => {
            const bodyRect = body.getBoundingClientRect();
            const targetRect = target.getBoundingClientRect();
            const padding = 24;
            const visibleBottom = bodyRect.bottom - padding;
            const visibleTop = bodyRect.top + padding;

            if (targetRect.bottom > visibleBottom) {
                body.scrollTop += targetRect.bottom - visibleBottom;
            } else if (targetRect.top < visibleTop) {
                body.scrollTop -= visibleTop - targetRect.top;
            }
        };

        requestAnimationFrame(() => {
            scrollTarget();
            window.setTimeout(scrollTarget, 280);
        });
    }

    applyMonitoringMemberSelection();

    toggleMonitoringCaseFormBtn?.addEventListener('click', () => {
        monitoringCaseFormWrapper?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        applyMonitoringMemberSelection();
        syncPregnantPeriodFields();
        calculateVisitBmi('new');
        updateVisitBpPreview('new');
        syncMonitoringModalToViewport();
        monitoringMemberSelect?.focus();
    });

    closeMonitoringCaseFormBtn?.addEventListener('click', closeMonitoringCaseModal);
    cancelMonitoringCaseFormBtn?.addEventListener('click', closeMonitoringCaseModal);

    monitoringCaseFormWrapper?.addEventListener('click', (event) => {
        if (event.target === monitoringCaseFormWrapper) {
            closeMonitoringCaseModal();
        }
    });

    monitoringCaseFormWrapper?.addEventListener('focusin', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.matches('input, select, textarea')) {
            syncMonitoringModalToViewport();
            scrollMonitoringFieldIntoView(target);
        }
    });

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', syncMonitoringModalToViewport);
        window.visualViewport.addEventListener('scroll', syncMonitoringModalToViewport);
    }

    window.addEventListener('orientationchange', () => {
        window.setTimeout(syncMonitoringModalToViewport, 150);
    });

    const addVisitModalWrapper = document.getElementById('addVisitModalWrapper');
    const addVisitForm = document.getElementById('addVisitForm');
    const addVisitCaseIdInput = document.getElementById('addVisitCaseId');
    const addVisitMemberName = document.getElementById('addVisitMemberName');
    const addVisitBpSection = document.getElementById('addVisitBpSection');
    const addVisitPregnantSection = document.getElementById('addVisitPregnantSection');
    const addVisitLastPeriodDate = document.getElementById('add_visit_last_period_date');
    const closeAddVisitModalBtn = document.getElementById('closeAddVisitModalBtn');
    const cancelAddVisitModalBtn = document.getElementById('cancelAddVisitModalBtn');
    const failedVisitCaseId = @json($failedVisitCaseId);

    function resetAddVisitModalViewport() {
        if (! addVisitModalWrapper) {
                return;
            }

        addVisitModalWrapper.style.top = '';
        addVisitModalWrapper.style.height = '';
        addVisitModalWrapper.style.bottom = '';

        const panel = addVisitModalWrapper.querySelector('.transaction-modal-panel');
        if (panel) {
            panel.style.maxHeight = '';
        }
    }

    function syncAddVisitModalToViewport() {
        if (! addVisitModalWrapper || addVisitModalWrapper.classList.contains('hidden')) {
            return;
        }

        if (window.matchMedia('(min-width: 1024px)').matches || ! window.visualViewport) {
            resetAddVisitModalViewport();
            return;
        }

        const vv = window.visualViewport;
        const availableHeight = Math.max(280, Math.floor(vv.height));

        addVisitModalWrapper.style.top = `${Math.max(0, Math.floor(vv.offsetTop))}px`;
        addVisitModalWrapper.style.height = `${Math.floor(vv.height)}px`;
        addVisitModalWrapper.style.bottom = 'auto';

        const panel = addVisitModalWrapper.querySelector('.transaction-modal-panel');
        if (panel) {
            panel.style.maxHeight = `${availableHeight}px`;
        }
    }

    function scrollAddVisitFieldIntoView(target) {
        if (! target || ! addVisitModalWrapper || addVisitModalWrapper.classList.contains('hidden')) {
            return;
        }

        if (! addVisitModalWrapper.contains(target)) {
            return;
        }

        const body = addVisitModalWrapper.querySelector('.transaction-modal-body');
        if (! body) {
            return;
        }

        const scrollTarget = () => {
            const bodyRect = body.getBoundingClientRect();
            const targetRect = target.getBoundingClientRect();
            const padding = 24;
            const visibleBottom = bodyRect.bottom - padding;
            const visibleTop = bodyRect.top + padding;

            if (targetRect.bottom > visibleBottom) {
                body.scrollTop += targetRect.bottom - visibleBottom;
            } else if (targetRect.top < visibleTop) {
                body.scrollTop -= visibleTop - targetRect.top;
            }
        };

        requestAnimationFrame(() => {
            scrollTarget();
            window.setTimeout(scrollTarget, 280);
        });
    }

    function configureAddVisitModal(button) {
        if (! addVisitForm || ! button) {
            return;
        }

        const caseId = button.getAttribute('data-case-id') || '';
        const action = button.getAttribute('data-action') || '#';
        const memberName = button.getAttribute('data-member-name') || 'this member';
        const isPregnant = button.getAttribute('data-is-pregnant') === '1';
        const isSenior = button.getAttribute('data-is-senior') === '1';
        const firstPeriodDate = button.getAttribute('data-first-period-date') || '';
        const lastPeriodDate = button.getAttribute('data-last-period-date') || '';

        addVisitForm.setAttribute('action', action);
        if (addVisitCaseIdInput) {
            addVisitCaseIdInput.value = caseId;
        }
        if (addVisitMemberName) {
            addVisitMemberName.textContent = memberName;
        }

        addVisitBpSection?.classList.toggle('hidden', ! isSenior);
        addVisitPregnantSection?.classList.toggle('hidden', ! isPregnant);

        if (addVisitLastPeriodDate) {
            if (firstPeriodDate) {
                addVisitLastPeriodDate.min = firstPeriodDate;
            } else {
                addVisitLastPeriodDate.removeAttribute('min');
            }
            if (! addVisitLastPeriodDate.value && lastPeriodDate) {
                addVisitLastPeriodDate.value = lastPeriodDate;
            }
        }

        addVisitForm.querySelectorAll('#addVisitBpSection input').forEach((el) => {
            el.disabled = ! isSenior;
        });
        addVisitForm.querySelectorAll('#addVisitPregnantSection input, #addVisitPregnantSection select').forEach((el) => {
            el.disabled = ! isPregnant;
        });
    }

    function openAddVisitModal(button, { preserveValues = false } = {}) {
        if (! preserveValues && addVisitForm) {
            addVisitForm.reset();
            const dateInput = document.getElementById('add_visit_date');
            if (dateInput) {
                dateInput.value = new Date().toISOString().slice(0, 10);
            }
        }

        configureAddVisitModal(button);
        addVisitModalWrapper?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        calculateVisitBmi('visit');
        updateVisitBpPreview('visit');
        syncAddVisitModalToViewport();
        document.getElementById('add_visit_date')?.focus();
    }

    function closeAddVisitModal() {
        addVisitModalWrapper?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        resetAddVisitModalViewport();
    }

    document.querySelectorAll('.open-add-visit-modal').forEach((button) => {
        button.addEventListener('click', () => openAddVisitModal(button));
    });

    closeAddVisitModalBtn?.addEventListener('click', closeAddVisitModal);
    cancelAddVisitModalBtn?.addEventListener('click', closeAddVisitModal);

    addVisitModalWrapper?.addEventListener('click', (event) => {
        if (event.target === addVisitModalWrapper) {
            closeAddVisitModal();
        }
    });

    addVisitModalWrapper?.addEventListener('focusin', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.matches('input, select, textarea')) {
            syncAddVisitModalToViewport();
            scrollAddVisitFieldIntoView(target);
        }
    });

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', syncAddVisitModalToViewport);
        window.visualViewport.addEventListener('scroll', syncAddVisitModalToViewport);
    }

    const calculateVisitBmi = (caseId) => {
        const heightInput = document.querySelector(`[data-visit-height="${caseId}"]`);
        const weightInput = document.querySelector(`[data-visit-weight="${caseId}"]`);
        const bmiInput = document.querySelector(`[data-visit-bmi="${caseId}"]`);

        if (!heightInput || !weightInput || !bmiInput) {
                return;
            }

        const heightCm = parseFloat(heightInput.value);
        const weightKg = parseFloat(weightInput.value);

        if (!Number.isFinite(heightCm) || !Number.isFinite(weightKg) || heightCm < 30 || weightKg < 1) {
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
    const failedHouseholdAction = @json($failedHouseholdAction);
    const activeTab = pageTab || failedProfileFormTab;

    // ---------- Family tab member editors ----------
    const familyChildModal = document.getElementById('familyChildModal');
    const familySeniorModal = document.getElementById('familySeniorModal');
    const familySpouseModal = document.getElementById('familySpouseModal');
    const familyRemoveModal = document.getElementById('familyRemoveModal');
    const familyChildStudying = document.getElementById('family_child_studying');

    function openFamilyModal(modal) {
        modal?.classList.remove('hidden');
        modal?.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeFamilyModal(modal) {
        modal?.classList.add('hidden');
        modal?.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    function syncFamilyChildStatusFields() {
        const status = familyChildStudying?.value || 'infant';
        const schoolFields = document.getElementById('familyChildSchoolFields');
        const workFields = document.getElementById('familyChildWorkFields');
        const schoolInput = document.getElementById('family_child_school');
        const eduInput = document.getElementById('family_child_edu');
        const occupationInput = document.getElementById('family_child_occupation');

        schoolFields?.classList.toggle('hidden', status !== 'yes');
        workFields?.classList.toggle('hidden', status !== 'no');
        if (schoolInput) schoolInput.required = status === 'yes';
        if (eduInput) eduInput.required = status === 'no';
        if (occupationInput) occupationInput.required = status === 'no';
    }

    familyChildStudying?.addEventListener('change', syncFamilyChildStatusFields);

    function resetFamilyChildForm() {
        document.getElementById('familyChildForm')?.reset();
        document.getElementById('family_child_action').value = 'add_child';
        document.getElementById('family_child_index').value = '';
        document.getElementById('familyChildModalTitle').textContent = 'Add Child';
        if (familyChildStudying) familyChildStudying.value = 'infant';
        syncFamilyChildStatusFields();
    }

    function resetFamilySeniorForm() {
        document.getElementById('familySeniorForm')?.reset();
        document.getElementById('family_senior_action').value = 'add_senior';
        document.getElementById('family_senior_index').value = '';
        document.getElementById('familySeniorModalTitle').textContent = 'Add other family';
    }

    function resetFamilySpouseForm() {
        document.getElementById('familySpouseForm')?.reset();
        document.getElementById('familySpouseModalTitle').textContent = 'Add Spouse';
    }

    document.getElementById('openAddChildBtn')?.addEventListener('click', () => {
        resetFamilyChildForm();
        openFamilyModal(familyChildModal);
    });
    document.getElementById('openAddSeniorBtn')?.addEventListener('click', () => {
        resetFamilySeniorForm();
        openFamilyModal(familySeniorModal);
    });
    document.getElementById('openAddSpouseBtn')?.addEventListener('click', () => {
        resetFamilySpouseForm();
        openFamilyModal(familySpouseModal);
    });

    document.querySelectorAll('.close-family-child-modal').forEach((btn) => {
        btn.addEventListener('click', () => closeFamilyModal(familyChildModal));
    });
    document.querySelectorAll('.close-family-senior-modal').forEach((btn) => {
        btn.addEventListener('click', () => closeFamilyModal(familySeniorModal));
    });
    document.querySelectorAll('.close-family-spouse-modal').forEach((btn) => {
        btn.addEventListener('click', () => closeFamilyModal(familySpouseModal));
    });

    familyChildModal?.addEventListener('click', (e) => { if (e.target === familyChildModal) closeFamilyModal(familyChildModal); });
    familySeniorModal?.addEventListener('click', (e) => { if (e.target === familySeniorModal) closeFamilyModal(familySeniorModal); });
    familySpouseModal?.addEventListener('click', (e) => { if (e.target === familySpouseModal) closeFamilyModal(familySpouseModal); });

    document.querySelectorAll('.open-edit-child-btn').forEach((button) => {
        button.addEventListener('click', () => {
            resetFamilyChildForm();
            document.getElementById('family_child_action').value = 'update_child';
            document.getElementById('family_child_index').value = button.dataset.index || '';
            document.getElementById('familyChildModalTitle').textContent = 'Edit Child';
            document.getElementById('family_child_full_name').value = button.dataset.fullName || '';
            document.getElementById('family_child_birthdate').value = button.dataset.birthdate || '';
            document.getElementById('family_child_gender').value = button.dataset.gender || '';
            document.getElementById('family_child_relationship').value = button.dataset.relationship || '';
            document.getElementById('family_child_studying').value = button.dataset.currentlyStudying || 'infant';
            document.getElementById('family_child_school').value = button.dataset.school || '';
            document.getElementById('family_child_edu').value = button.dataset.educationalAttainment || '';
            document.getElementById('family_child_occupation').value = button.dataset.occupation || '';
            syncFamilyChildStatusFields();
            openFamilyModal(familyChildModal);
        });
    });

    document.querySelectorAll('.open-edit-senior-btn').forEach((button) => {
        button.addEventListener('click', () => {
            resetFamilySeniorForm();
            document.getElementById('family_senior_action').value = 'update_senior';
            document.getElementById('family_senior_index').value = button.dataset.index || '';
            document.getElementById('familySeniorModalTitle').textContent = 'Edit other family';
            document.getElementById('family_senior_full_name').value = button.dataset.fullName || '';
            document.getElementById('family_senior_birthdate').value = button.dataset.birthdate || '';
            document.getElementById('family_senior_gender').value = button.dataset.gender || '';
            document.getElementById('family_senior_relationship').value = button.dataset.relationship || '';
            document.getElementById('family_senior_pob').value = button.dataset.placeOfBirth || '';
            document.getElementById('family_senior_civil').value = button.dataset.civilStatus || '';
            document.getElementById('family_senior_edu').value = button.dataset.educationalAttainment || '';
            document.getElementById('family_senior_occupation').value = button.dataset.occupation || '';
            openFamilyModal(familySeniorModal);
        });
    });

    document.querySelectorAll('.open-edit-spouse-btn').forEach((button) => {
        button.addEventListener('click', () => {
            resetFamilySpouseForm();
            document.getElementById('familySpouseModalTitle').textContent = 'Edit Spouse';
            document.getElementById('family_spouse_first').value = button.dataset.firstName || '';
            document.getElementById('family_spouse_last').value = button.dataset.lastName || '';
            document.getElementById('family_spouse_mi').value = button.dataset.middleInitial || '';
            document.getElementById('family_spouse_birthdate').value = button.dataset.birthdate || '';
            document.getElementById('family_spouse_role').value = button.dataset.parentRole || '';
            document.getElementById('family_spouse_pob').value = button.dataset.placeOfBirth || '';
            document.getElementById('family_spouse_edu').value = button.dataset.educationalAttainment || '';
            openFamilyModal(familySpouseModal);
        });
    });

    document.querySelectorAll('.open-remove-member-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const name = button.dataset.memberName || 'this member';
            const label = button.dataset.memberLabel || 'member';
            document.getElementById('family_remove_action').value = button.dataset.action || '';
            document.getElementById('family_remove_index').value = button.dataset.index || '';
            document.getElementById('familyRemoveMessage').textContent =
                `Remove ${name} (${label}) from this household? Family counting will update.`;
            openFamilyModal(familyRemoveModal);
        });
    });

    document.getElementById('closeFamilyRemoveModal')?.addEventListener('click', () => closeFamilyModal(familyRemoveModal));
    document.getElementById('cancelFamilyRemoveModal')?.addEventListener('click', () => closeFamilyModal(familyRemoveModal));
    familyRemoveModal?.addEventListener('click', (e) => { if (e.target === familyRemoveModal) closeFamilyModal(familyRemoveModal); });

    syncFamilyChildStatusFields();

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

    if (hasValidationErrors && activeTab === 'family' && failedHouseholdAction) {
        if (['add_child', 'update_child'].includes(failedHouseholdAction)) {
            openFamilyModal(familyChildModal);
        } else if (['add_senior', 'update_senior'].includes(failedHouseholdAction)) {
            openFamilyModal(familySeniorModal);
        } else if (failedHouseholdAction === 'set_spouse') {
            openFamilyModal(familySpouseModal);
        } else if (String(failedHouseholdAction).startsWith('remove_')) {
            openFamilyModal(familyRemoveModal);
        }
    }

    if (hasValidationErrors && activeTab === 'maintenance') {
        if (failedVisitCaseId) {
            const visitBtn = document.querySelector(`.open-add-visit-modal[data-case-id="${failedVisitCaseId}"]`);
            if (visitBtn) {
                openAddVisitModal(visitBtn, { preserveValues: true });
            }
        } else {
            monitoringCaseFormWrapper?.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            applyMonitoringMemberSelection();
            syncPregnantPeriodFields();
            calculateVisitBmi('new');
            updateVisitBpPreview('new');
            syncMonitoringModalToViewport();
        }
    }

    if (hasValidationErrors && activeTab === 'medical-history') {
        medicalHistoryFormWrapper?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        applyTransactionMemberSelection({ fillDemographics: ! preserveTransactionDemographics });
        syncTransactionRecipientHints();
        syncTransactionMedicationCards();
        syncTransactionModalToViewport();
    }

    if (hasValidationErrors && activeTab === 'consultations') {
        consultationFormWrapper?.classList.remove('hidden');
    }

    syncPregnantPeriodFields();

    const completeMonitoringModal = document.getElementById('completeMonitoringModal');
    const completeMonitoringForm = document.getElementById('completeMonitoringForm');
    const completeMonitoringModalMessage = document.getElementById('completeMonitoringModalMessage');
    const closeCompleteMonitoringModalBtn = document.getElementById('closeCompleteMonitoringModal');
    const cancelCompleteMonitoringModalBtn = document.getElementById('cancelCompleteMonitoringModal');

    function openCompleteMonitoringModal(button) {
        if (! completeMonitoringModal || ! completeMonitoringForm) {
            return;
        }

        const memberName = button.dataset.memberName || 'this household member';
        const typeLabel = button.dataset.typeLabel || 'Monitoring';

        completeMonitoringForm.action = button.dataset.completeUrl || '#';
        if (completeMonitoringModalMessage) {
            completeMonitoringModalMessage.textContent = `Mark ${typeLabel.toLowerCase()} monitoring for ${memberName} as completed? Visit records will stay available in history.`;
        }

        completeMonitoringModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCompleteMonitoringModal() {
        completeMonitoringModal?.classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.open-complete-monitoring-modal').forEach((button) => {
        button.addEventListener('click', () => openCompleteMonitoringModal(button));
    });

    closeCompleteMonitoringModalBtn?.addEventListener('click', closeCompleteMonitoringModal);
    cancelCompleteMonitoringModalBtn?.addEventListener('click', closeCompleteMonitoringModal);

    completeMonitoringModal?.addEventListener('click', (event) => {
        if (event.target === completeMonitoringModal) {
            closeCompleteMonitoringModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && completeMonitoringModal && ! completeMonitoringModal.classList.contains('hidden')) {
            closeCompleteMonitoringModal();
        }
    });
</script>
</body>
</html>
