<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
<meta charset="utf-8"/>
@include('partials.bns-viewport')
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Bantay Kalusugan - Child Monitoring</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bns-shell-styles')
<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }

    main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }

    .page-content {
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .monitoring-page-layout {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        width: 100%;
        max-width: 80rem;
        margin: 0 auto;
    }

    .monitoring-panel {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    .monitoring-toolbar { flex-shrink: 0; margin-bottom: 0.75rem; }

    .monitoring-toolbar-row {
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
        width: 100%;
    }

    .monitoring-search-wrap {
        display: flex;
        align-items: stretch;
        gap: 0.5rem;
        width: 100%;
        min-width: 0;
    }

    #filtersPanel {
        display: none;
    }
    #filtersPanel.open { display: flex; }
    @media (min-width: 768px) {
        .monitoring-toolbar-row {
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: flex-end;
            gap: 0.75rem;
            width: 100%;
        }

        .monitoring-search-wrap {
            flex: 1 1 auto;
            min-width: 10rem;
            max-width: none;
            width: auto;
        }

        #filtersPanel {
            display: flex !important;
            flex: 0 0 auto;
            flex-wrap: nowrap;
            align-items: flex-end;
            gap: 0.5rem;
            width: auto;
            min-width: 0;
            padding-top: 0;
            border-top: none;
        }

        #toggleFiltersBtn { display: none; }
    }
    @media (max-width: 767px) {
        #filtersPanel {
            padding-top: 0.625rem;
            border-top: 1px solid #EAEAEA;
        }
        .dark #filtersPanel { border-top-color: #3D3D3D; }
        #filtersPanel.collapsed:not(.open) { display: none; }
        #filtersPanel.open {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.625rem;
        }
    }

    .monitoring-filter-control {
        height: 2.25rem;
        min-width: 0;
        padding: 0 0.5rem;
        border-radius: 0.5rem;
        border: 1px solid #EAEAEA;
        background: #FFFFFF;
        font-size: 0.8125rem;
    }
    .dark .monitoring-filter-control {
        border-color: #3D3D3D;
        background: #1A1A1A;
        color: #E0E0E0;
    }
    @media (min-width: 768px) {
        .monitoring-filter-control { width: auto; min-width: 7.5rem; }
    }
    @media (max-width: 767px) {
        .monitoring-filter-control { width: 100%; }
    }

    #toggleFiltersBtn.active {
        border-color: #4A90E2;
        background: rgba(74, 144, 226, 0.1);
        color: #4A90E2;
    }

    .monitoring-table-wrap {
        flex: 1;
        min-height: 0;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 0.5rem;
        border: 1px solid #EAEAEA;
    }
    .dark .monitoring-table-wrap { border-color: #3D3D3D; }

    #healthModal { display: none; }
    #healthModal.active { display: flex; align-items: center; justify-content: center; }
    .health-tab.active { border-color: #4A90E2; color: #4A90E2; background: rgba(74, 144, 226, 0.08); }

    #healthModalPanel {
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: 72rem;
        height: min(90vh, 880px);
        min-height: 0;
    }

    #healthModalMain {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    #healthModalContent {
        flex: 1;
        min-width: 0;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    @media (min-width: 1024px) {
        #healthModalMain {
            flex-direction: row;
        }

        #healthModalProfile {
            width: 18rem;
            flex-shrink: 0;
            border-bottom: none !important;
            border-right: 1px solid #EAEAEA;
            overflow-y: auto;
        }

        .dark #healthModalProfile {
            border-right-color: #3D3D3D;
        }

        #healthModalProfile .profile-detail-row {
            grid-template-columns: 1fr;
            gap: 0.125rem;
        }

        #healthModalProfile .profile-detail-row dt {
            font-size: 0.75rem;
        }

        #healthModalContent {
            flex: 1;
            min-width: 0;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }
    }

    #healthModalBody {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .profile-detail-row {
        display: grid;
        grid-template-columns: 6.5rem 1fr;
        gap: 0.5rem 0.75rem;
        align-items: start;
        font-size: 0.875rem;
    }

    .profile-detail-row dt {
        color: rgba(74, 74, 74, 0.65);
        font-weight: 500;
    }

    .dark .profile-detail-row dt {
        color: rgba(224, 224, 224, 0.65);
    }

    .profile-detail-row dd {
        font-weight: 600;
        word-break: break-word;
    }

    .profile-info-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        width: 100%;
        margin-top: 0.75rem;
        padding: 0.625rem 0.875rem;
        border-radius: 0.75rem;
        border: 1px solid #EAEAEA;
        background: #FFFFFF;
        font-size: 0.8125rem;
        font-weight: 600;
        color: inherit;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }
    .dark .profile-info-toggle {
        border-color: #3D3D3D;
        background: #1A1A1A;
    }
    .profile-info-toggle:active {
        background: rgba(74, 144, 226, 0.06);
    }
    .profile-info-toggle .profile-info-chevron {
        font-size: 1.25rem;
        color: rgba(74, 74, 74, 0.45);
        transition: transform 0.2s ease;
    }
    .dark .profile-info-toggle .profile-info-chevron {
        color: rgba(224, 224, 224, 0.45);
    }
    .profile-info-toggle[aria-expanded="true"] .profile-info-chevron {
        transform: rotate(180deg);
    }

    @media (max-width: 1023px) {
        #healthModalProfile {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        #healthModalProfile .profile-info-toggle {
            margin-top: 0;
        }

        #healthModalProfile .profile-info-details {
            display: none;
            margin-top: 0.625rem;
            padding-top: 0.625rem;
            border-top: 1px solid #EAEAEA;
        }
        .dark #healthModalProfile .profile-info-details {
            border-top-color: #3D3D3D;
        }
        #healthModalProfile .profile-info-details.is-open {
            display: block;
        }
        #healthModalProfile .profile-detail-row {
            grid-template-columns: 5.5rem 1fr;
            gap: 0.375rem 0.625rem;
            font-size: 0.8125rem;
        }
    }

    @media (min-width: 1024px) {
        .profile-info-toggle { display: none !important; }
        .profile-info-details {
            display: block !important;
            margin-top: 0;
            padding-top: 0;
            border-top: none !important;
        }
    }

    .health-tabs-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .health-tabs-scroll::-webkit-scrollbar { display: none; }

    .health-form-field {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 0;
    }

    .health-form-label {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: rgba(74, 74, 74, 0.55);
    }
    .dark .health-form-label {
        color: rgba(224, 224, 224, 0.55);
    }

    .health-form-submit {
        width: 100%;
    }
    @media (min-width: 640px) {
        .health-form-submit {
            width: auto;
        }
    }

    .health-section-title {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgba(74, 74, 74, 0.55);
        margin-bottom: 0.625rem;
    }
    .dark .health-section-title {
        color: rgba(224, 224, 224, 0.55);
    }

    .health-records-mobile {
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
    }

    .health-records-desktop {
        display: none;
    }

    .health-record-card {
        border: 1px solid #EAEAEA;
        border-radius: 0.75rem;
        background: #FFFFFF;
        padding: 0.875rem 1rem;
    }
    .dark .health-record-card {
        border-color: #3D3D3D;
        background: #2C2C2C;
    }

    .health-record-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.625rem;
        padding-bottom: 0.625rem;
        margin-bottom: 0.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }
    .dark .health-record-card-head {
        border-bottom-color: rgba(255, 255, 255, 0.08);
    }

    .health-record-card-title {
        font-size: 0.875rem;
        font-weight: 700;
        line-height: 1.3;
        color: inherit;
        min-width: 0;
    }

    .health-record-card-badges {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 0.375rem;
        flex-shrink: 0;
    }

    .weight-edit-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 0.5rem;
        border: 1px solid #EAEAEA;
        background: #F4F4F4;
        color: #4A90E2;
        flex-shrink: 0;
        padding: 0;
        line-height: 1;
    }
    .dark .weight-edit-icon-btn {
        border-color: #3D3D3D;
        background: #1A1A1A;
        color: #7EB6F0;
    }
    .weight-edit-icon-btn:hover {
        background: rgba(74, 144, 226, 0.12);
        border-color: rgba(74, 144, 226, 0.35);
    }
    .weight-edit-icon-btn .material-symbols-outlined {
        font-size: 1rem;
        line-height: 1;
    }

    .weight-edit-icon-btn.is-danger {
        color: #D0021B;
    }
    .dark .weight-edit-icon-btn.is-danger {
        color: #F87171;
    }
    .weight-edit-icon-btn.is-danger:hover {
        background: rgba(208, 2, 27, 0.1);
        border-color: rgba(208, 2, 27, 0.35);
    }

    #dewormingDeleteModal,
    #immunizationNotifyModal { display: none; }
    #dewormingDeleteModal.active,
    #immunizationNotifyModal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .health-record-detail {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.4375rem 0;
        font-size: 0.8125rem;
    }
    .health-record-detail + .health-record-detail {
        border-top: 1px solid rgba(0, 0, 0, 0.04);
    }
    .dark .health-record-detail + .health-record-detail {
        border-top-color: rgba(255, 255, 255, 0.06);
    }

    .health-record-label {
        flex-shrink: 0;
        width: 38%;
        max-width: 6.5rem;
        font-size: 0.625rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgba(74, 74, 74, 0.55);
        padding-top: 0.125rem;
    }
    .dark .health-record-label {
        color: rgba(224, 224, 224, 0.55);
    }

    .health-record-value {
        flex: 1;
        min-width: 0;
        text-align: right;
        font-weight: 600;
        word-break: break-word;
    }

    .health-records-empty {
        border: 1px dashed #EAEAEA;
        border-radius: 0.75rem;
        padding: 1.25rem 1rem;
        text-align: center;
        font-size: 0.8125rem;
        color: rgba(74, 74, 74, 0.6);
    }
    .dark .health-records-empty {
        border-color: #3D3D3D;
        color: rgba(224, 224, 224, 0.6);
    }

    .vita-tip {
        display: flex;
        gap: 0.625rem;
        align-items: flex-start;
        padding: 0.75rem 0.875rem;
        border-radius: 0.875rem;
        background: rgba(74, 144, 226, 0.08);
        border: 1px solid rgba(74, 144, 226, 0.18);
    }
    .dark .vita-tip {
        background: rgba(74, 144, 226, 0.12);
        border-color: rgba(74, 144, 226, 0.28);
    }
    .vita-tip-icon {
        color: #4A90E2;
        font-size: 1.25rem;
        line-height: 1;
        margin-top: 0.1rem;
    }
    .vita-date-field input[type="date"] {
        height: 2.75rem;
        font-size: 1rem;
        font-weight: 600;
        letter-spacing: 0.01em;
    }
    .vita-schedule-card,
    .vita-log-card {
        border: 1px solid #EAEAEA;
        border-radius: 0.875rem;
        background: #fff;
        overflow: hidden;
    }
    .dark .vita-schedule-card,
    .dark .vita-log-card {
        border-color: #3D3D3D;
        background: #2C2C2C;
    }
    .vita-schedule-card + .vita-schedule-card,
    .vita-log-card + .vita-log-card {
        margin-top: 0.625rem;
    }
    .vita-schedule-card.is-missed {
        border-color: rgba(208, 2, 27, 0.28);
        background: rgba(208, 2, 27, 0.04);
    }
    .dark .vita-schedule-card.is-missed {
        background: rgba(208, 2, 27, 0.1);
    }
    .vita-schedule-card.is-due {
        border-color: rgba(245, 166, 35, 0.35);
        background: rgba(245, 166, 35, 0.06);
    }
    .dark .vita-schedule-card.is-due {
        background: rgba(245, 166, 35, 0.1);
    }
    .vita-schedule-card.is-given {
        border-color: rgba(80, 227, 194, 0.35);
    }
    .vita-card-main {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.875rem;
    }
    .vita-card-meta {
        min-width: 0;
        flex: 1;
    }
    .vita-card-title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #4A4A4A;
        line-height: 1.25;
    }
    .dark .vita-card-title {
        color: #E0E0E0;
    }
    .vita-card-sub {
        margin-top: 0.25rem;
        font-size: 0.8125rem;
        color: rgba(74, 74, 74, 0.65);
        line-height: 1.35;
    }
    .dark .vita-card-sub {
        color: rgba(224, 224, 224, 0.65);
    }
    .vita-card-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.375rem;
        flex-shrink: 0;
    }
    .vita-edit-panel {
        display: none;
        padding: 0 0.875rem 0.875rem;
        border-top: 1px solid #EAEAEA;
        background: rgba(244, 244, 244, 0.55);
    }
    .dark .vita-edit-panel {
        border-top-color: #3D3D3D;
        background: rgba(26, 26, 26, 0.45);
    }
    .vita-edit-panel.is-open {
        display: block;
        padding-top: 0.875rem;
    }
    .vita-edit-row {
        display: none;
    }
    .vita-edit-row.is-open {
        display: table-row;
    }
    .vita-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        min-height: 2rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        line-height: 1;
        border: 1px solid transparent;
    }
    .vita-btn-primary {
        background: #4A90E2;
        color: #fff;
    }
    .vita-btn-secondary {
        background: transparent;
        color: #4A90E2;
        border-color: rgba(74, 144, 226, 0.35);
    }
    .dark .vita-btn-secondary {
        color: #7eb6ef;
        border-color: rgba(74, 144, 226, 0.45);
    }
    .vita-btn-ghost {
        background: transparent;
        color: rgba(74, 74, 74, 0.7);
        border-color: #EAEAEA;
    }
    .dark .vita-btn-ghost {
        color: rgba(224, 224, 224, 0.7);
        border-color: #3D3D3D;
    }

    @media (min-width: 768px) {
        .health-records-mobile { display: none !important; }
        .health-records-desktop { display: block; }
    }

    .immunization-schedule-rows {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .immunization-schedule-row {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: minmax(0, 1fr);
    }

    @media (min-width: 768px) {
        .immunization-schedule-row--2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .immunization-schedule-row--3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    .immunization-vaccine-card {
        height: 100%;
    }

    @media (max-width: 768px) {
        #healthModalPanel {
            max-width: 100%;
            height: 100%;
            max-height: 100%;
            border-radius: 0;
        }

        #healthModal.active {
            padding: 0;
        }
    }

    .monitoring-list-footer {
        flex-shrink: 0;
        padding-top: 0.75rem;
        margin-top: 0.75rem;
        border-top: 1px solid #EAEAEA;
    }
    .dark .monitoring-list-footer { border-top-color: #3D3D3D; }

    .monitoring-page-title {
        font-size: 1.125rem;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -0.01em;
    }
    @media (min-width: 768px) {
        .monitoring-page-title { font-size: 1.25rem; }
    }

    .monitoring-filter-field {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 0;
    }

    .monitoring-filter-field label {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: rgba(74, 74, 74, 0.55);
    }
    .dark .monitoring-filter-field label {
        color: rgba(224, 224, 224, 0.55);
    }

    .monitoring-view-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        min-height: 2.25rem;
        padding: 0.375rem 0.875rem;
        border-radius: 0.625rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #4A90E2;
        background: rgba(74, 144, 226, 0.08);
        border: 1px solid rgba(74, 144, 226, 0.2);
        transition: background-color 0.15s ease;
    }
    .monitoring-view-btn:hover {
        background: rgba(74, 144, 226, 0.14);
    }

    @media (max-width: 1023px) {
        main.monitoring-main {
            overflow: visible;
        }

        .page-content.monitoring-page-scroll {
            height: auto;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .monitoring-panel {
            overflow: visible;
            flex: none;
        }

        .monitoring-table-wrap {
            overflow: visible;
            flex: none;
            min-height: 0;
        }
    }

    @media (max-width: 767px) {
        .monitoring-page-layout {
            max-width: none;
            margin: 0;
        }

        .monitoring-panel {
            padding: 0 !important;
            border: none;
            box-shadow: none;
            background: transparent !important;
            border-radius: 0;
        }

        .monitoring-toolbar {
            padding: 0.75rem 1rem 0.625rem;
            margin-bottom: 0;
            background: #FFFFFF;
            border-bottom: 1px solid #EAEAEA;
        }
        .dark .monitoring-toolbar {
            background: #2C2C2C;
            border-bottom-color: #3D3D3D;
        }

        .monitoring-table-wrap {
            border: none;
            margin-top: 0 !important;
        }
        .monitoring-table-wrap table { min-width: 0 !important; }
        .monitoring-table-wrap thead { display: none; }
        .monitoring-table-wrap tbody {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .monitoring-table-wrap .masterlist-row {
            display: block;
            border: none;
            border-bottom: 1px solid #EAEAEA;
            border-radius: 0;
            background: #FFFFFF;
            padding: 0;
            box-shadow: none;
        }
        .dark .monitoring-table-wrap .masterlist-row {
            border-bottom-color: #3D3D3D;
            background: #2C2C2C;
        }

        .masterlist-mobile-toggle {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.75rem 1rem;
            margin: 0;
            border: none;
            background: transparent;
            color: inherit;
            text-align: left;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        .masterlist-mobile-toggle:active {
            background: rgba(74, 144, 226, 0.06);
        }

        .masterlist-mobile-toggle .masterlist-chevron {
            margin-left: auto;
            flex-shrink: 0;
            font-size: 1.25rem;
            color: rgba(74, 74, 74, 0.45);
            transition: transform 0.2s ease;
        }
        .dark .masterlist-mobile-toggle .masterlist-chevron {
            color: rgba(224, 224, 224, 0.45);
        }
        .masterlist-row.is-expanded .masterlist-chevron {
            transform: rotate(180deg);
        }

        .masterlist-mobile-status {
            flex-shrink: 0;
            max-width: 5.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.6875rem;
            font-weight: 600;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
        }
        .masterlist-row.is-expanded .masterlist-mobile-status {
            display: none;
        }

        .masterlist-child-desktop { display: none; }

        .masterlist-mobile-extra {
            display: none;
            padding: 0 1rem 0.625rem;
            font-size: 0.75rem;
            color: rgba(74, 74, 74, 0.65);
        }
        .dark .masterlist-mobile-extra {
            color: rgba(224, 224, 224, 0.65);
        }
        .masterlist-row.is-expanded .masterlist-mobile-extra {
            display: block;
        }

        .masterlist-row-details {
            display: none;
            padding: 0 1rem 0.875rem;
            border-top: 1px solid #EAEAEA;
        }
        .dark .masterlist-row-details {
            border-top-color: #3D3D3D;
        }
        .masterlist-row.is-expanded .masterlist-row-details {
            display: block;
        }

        .masterlist-detail-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.5rem 0;
            font-size: 0.8125rem;
        }
        .masterlist-detail-item + .masterlist-detail-item {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        .dark .masterlist-detail-item + .masterlist-detail-item {
            border-top-color: rgba(255, 255, 255, 0.06);
        }

        .masterlist-detail-label {
            flex-shrink: 0;
            width: 34%;
            max-width: 6.25rem;
            font-size: 0.625rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: rgba(74, 74, 74, 0.55);
            padding-top: 0.125rem;
        }
        .dark .masterlist-detail-label {
            color: rgba(224, 224, 224, 0.55);
        }

        .masterlist-detail-value {
            flex: 1;
            min-width: 0;
            text-align: right;
        }

        .masterlist-detail-item--health {
            flex-direction: column;
            align-items: stretch;
            gap: 0.5rem;
        }
        .masterlist-detail-item--health .masterlist-detail-value {
            text-align: left;
        }
        .masterlist-detail-item--health .flex {
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        .masterlist-detail-item--action {
            padding-top: 0.625rem;
            border-top: none;
        }
        .masterlist-detail-item--action .monitoring-view-btn {
            width: 100%;
        }

        .monitoring-table-wrap .masterlist-row td.masterlist-desktop-cell {
            display: none !important;
        }

        .monitoring-table-wrap .masterlist-row td.masterlist-mobile-cell {
            display: block;
            height: auto !important;
            padding: 0;
            border: none;
        }
        .monitoring-table-wrap .masterlist-row td.masterlist-mobile-cell::before {
            display: none;
        }

        .monitoring-table-wrap .masterlist-row.hidden {
            display: none !important;
        }

        #masterlistEmptyRow td,
        #monitoringNoResults td {
            display: block !important;
            border: none;
            border-radius: 0;
            background: #FFFFFF;
            padding: 1.5rem 1rem !important;
        }
        .dark #masterlistEmptyRow td,
        .dark #monitoringNoResults td {
            border-color: #3D3D3D;
            background: #2C2C2C;
        }
        #masterlistEmptyRow td::before,
        #monitoringNoResults td::before {
            display: none !important;
        }

        .monitoring-list-footer {
            border-top: 1px solid #EAEAEA;
            padding: 0.75rem 1rem 0.25rem;
            margin-top: 0;
            text-align: left;
            background: #FFFFFF;
        }
        .dark .monitoring-list-footer {
            border-top-color: #3D3D3D;
            background: #2C2C2C;
        }
    }

    @media (min-width: 768px) {
        .masterlist-child-desktop { display: flex !important; }
        .masterlist-mobile-toggle,
        .masterlist-mobile-extra,
        .masterlist-row-details,
        .masterlist-mobile-cell { display: none !important; }
        .masterlist-desktop-cell { display: table-cell !important; }

        .monitoring-filter-field {
            flex-direction: row;
            align-items: center;
            gap: 0.5rem;
        }

        .monitoring-filter-field label {
            text-transform: none;
            letter-spacing: 0;
            font-size: 0.75rem;
            white-space: nowrap;
        }
    }

    .monitoring-table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #FFFFFF;
    }
    .dark .monitoring-table-wrap thead th { background: #2C2C2C; }
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
                fontFamily: { "display": ["Manrope", "sans-serif"] },
            },
        },
    }
</script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@include('sidebars.bns')

<div id="bnsContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.bns-mobile-topbar')

    <main class="monitoring-main flex-1 flex flex-col min-h-0 pt-3 sm:pt-4 px-0 sm:px-5 md:px-6 lg:px-8 lg:pt-6 pb-24 lg:pb-6 overflow-hidden">
        <div class="page-content monitoring-page-scroll">
            <div class="monitoring-page-layout sm:py-0">
                @php
                    $monitoredChildren = collect($monitoredChildren ?? []);
                    $healthSummaries = $healthSummaries ?? [];
                    $purokOptions = $monitoredChildren->pluck('purok')->unique()->sort()->values();
                    $genderOptions = $monitoredChildren->pluck('gender')->filter(fn ($g) => $g !== '—')->unique()->sort()->values();
                    $ageGroupOptions = $monitoredChildren->pluck('age_group')->unique()->sort()->values();
                    $totalChildren = $monitoredChildren->count();
                @endphp

                <div class="monitoring-panel bg-card-light dark:bg-card-dark rounded-none sm:rounded-xl shadow-none sm:shadow-sm border-0 sm:border border-border-light dark:border-border-dark p-0 sm:p-4 flex-1 min-h-0">
                    <div class="monitoring-toolbar flex flex-col gap-2.5">
                        <h1 class="monitoring-page-title text-text-light dark:text-text-dark">Child monitoring</h1>
                        <div class="monitoring-toolbar-row">
                            <div class="monitoring-search-wrap">
                                <div class="relative flex-1 min-w-0">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50 text-lg pointer-events-none">search</span>
                                    <input id="masterlistSearch" type="search" maxlength="100" autocomplete="off" placeholder="Search by name, parent, or purok..." class="w-full h-10 pl-10 pr-3 rounded-xl bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm"/>
                                </div>
                                <button type="button" id="toggleFiltersBtn" class="md:hidden h-10 px-3.5 rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-xs font-semibold inline-flex items-center justify-center gap-1.5 shrink-0" aria-expanded="false" aria-controls="filtersPanel">
                                    <span class="material-symbols-outlined text-base">tune</span>
                                    <span class="sr-only">Toggle </span>Filters
                                </button>
                            </div>
                            <div id="filtersPanel" class="collapsed w-full md:w-auto">
                            <div class="monitoring-filter-field">
                                <label for="masterlistPurokFilter">Purok</label>
                                <select id="masterlistPurokFilter" class="monitoring-filter-control">
                                    <option value="">All puroks</option>
                                    @foreach ($purokOptions as $purokName)
                                        <option value="{{ $purokName }}">{{ $purokName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="monitoring-filter-field">
                                <label for="masterlistGenderFilter">Gender</label>
                                <select id="masterlistGenderFilter" class="monitoring-filter-control">
                                    <option value="">All genders</option>
                                    @foreach ($genderOptions as $gender)
                                        <option value="{{ $gender }}">{{ $gender }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="monitoring-filter-field">
                                <label for="masterlistAgeGroupFilter">Age group</label>
                                <select id="masterlistAgeGroupFilter" class="monitoring-filter-control">
                                    <option value="">All age groups</option>
                                    @foreach ($ageGroupOptions as $ageGroup)
                                        <option value="{{ $ageGroup }}">{{ $ageGroup }}</option>
                                    @endforeach
                                </select>
                            </div>
                            </div>
                        </div>
                    </div>

                    <div class="monitoring-table-wrap mt-3 sm:mt-3">
                        <table class="w-full md:min-w-[640px]" id="monitoringTable">
                                <thead>
                                    <tr class="border-b border-border-light dark:border-border-dark">
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider">Child Name</th>
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider">Age</th>
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider hidden sm:table-cell">Purok</th>
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider">Health Status</th>
                                    <th class="px-4 py-3 text-right text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider"></th>
                                </tr>
                            </thead>
                            <tbody id="monitoringTableBody">
                                @forelse ($monitoredChildren as $child)
                                    @php
                                        $hs = $healthSummaries[$child['id']] ?? null;
                                    @endphp
                                    <tr
                                        class="masterlist-row border-b border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark"
                                        data-child-key="{{ $child['id'] }}"
                                        data-search="{{ strtolower(implode(' ', [$child['full_name'], $child['father_name'], $child['mother_name'], $child['parent_name'], $child['contact'], $child['purok']])) }}"
                                        data-purok="{{ $child['purok'] }}"
                                        data-gender="{{ $child['gender'] }}"
                                        data-age-group="{{ $child['age_group'] }}"
                                        data-row="{{ json_encode($child) }}"
                                    >
                                        @php
                                            $nameParts = preg_split('/\s+/', trim($child['full_name'])) ?: [];
                                            $initials = strtoupper(substr($nameParts[0] ?? 'C', 0, 1).substr($nameParts[count($nameParts) - 1] ?? 'C', 0, 1));
                                            $parentLine = collect([$child['father_name'] ?? null, $child['mother_name'] ?? null])->filter()->implode(' · ');
                                            $nutrition = $hs['nutrition'] ?? null;
                                            $nutSeverity = $nutrition['severity'] ?? 'unknown';
                                            $nutClass = match ($nutSeverity) {
                                                'critical' => 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-200',
                                                'warning' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200',
                                                'normal' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300',
                                                default => 'bg-background-light dark:bg-background-dark text-text-light/60 dark:text-text-dark/60',
                                            };
                                            $mobileStatusLabel = ($nutrition && ($nutrition['has_data'] ?? false))
                                                ? ($nutrition['summary_label'] ?? $nutrition['label'])
                                                : 'No record';
                                        @endphp

                                        <td class="masterlist-mobile-cell md:hidden" colspan="5">
                                            <button type="button" class="masterlist-mobile-toggle" aria-expanded="false" aria-label="Show details for {{ $child['full_name'] }}">
                                                <div class="size-9 rounded-full bg-primary/10 text-primary text-xs font-bold flex items-center justify-center shrink-0">{{ $initials }}</div>
                                                <span class="text-sm font-semibold text-text-light dark:text-text-dark truncate min-w-0 flex-1">{{ $child['full_name'] }}</span>
                                                <span class="masterlist-mobile-status {{ $nutClass }}">{{ $mobileStatusLabel }}</span>
                                                <span class="material-symbols-outlined masterlist-chevron" aria-hidden="true">expand_more</span>
                                            </button>
                                            <div class="masterlist-mobile-extra">
                                                <p>{{ $parentLine !== '' ? $parentLine : ($child['parent_name'] ?: '—') }}</p>
                                                <p class="text-[11px] mt-0.5 opacity-80">{{ $child['category_label'] }}</p>
                                            </div>
                                            <div class="masterlist-row-details">
                                                <div class="masterlist-detail-item">
                                                    <span class="masterlist-detail-label">Age</span>
                                                    <span class="masterlist-detail-value font-medium text-text-light dark:text-text-dark">{{ $child['age_label'] }}</span>
                                                </div>
                                                <div class="masterlist-detail-item">
                                                    <span class="masterlist-detail-label">Purok</span>
                                                    <span class="masterlist-detail-value">{{ $child['purok'] }}</span>
                                                </div>
                                                <div class="masterlist-detail-item masterlist-detail-item--health">
                                                    <span class="masterlist-detail-label">Health</span>
                                                    <div class="masterlist-detail-value">
                                                        <div class="flex flex-col gap-1">
                                                            @if ($nutrition && ($nutrition['has_data'] ?? false))
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $nutClass }}">{{ $nutrition['summary_label'] ?? $nutrition['label'] }}</span>
                                                            @else
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-background-light dark:bg-background-dark text-text-light/60 dark:text-text-dark/60">No weight record</span>
                                                            @endif
                                                            @if ($hs && ($hs['fully_immunized'] ?? false))
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100/80 dark:bg-green-900/30 text-green-800 dark:text-green-300">Fully immunized</span>
                                                            @elseif ($hs && ($hs['immunization_missed'] ?? 0) > 0)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100/80 dark:bg-red-900/30 text-red-700 dark:text-red-200">{{ $hs['immunization_missed'] }} vaccine missed</span>
                                                            @elseif ($hs && ($hs['immunization_given'] ?? 0) > 0)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-background-light dark:bg-background-dark text-text-light/60 dark:text-text-dark/60">{{ $hs['immunization_given'] }}/{{ $hs['immunization_total'] }} vaccines</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="masterlist-detail-item masterlist-detail-item--action">
                                                    <button type="button" class="view-profile-btn open-health-btn monitoring-view-btn" data-child-key="{{ $child['id'] }}">
                                                        View profile
                                                        <span class="material-symbols-outlined text-base">chevron_right</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="masterlist-desktop-cell h-[72px] px-4 py-2 text-sm font-medium" data-label="Child">
                                            <div class="masterlist-child-desktop items-center gap-3 min-w-0">
                                                <div class="size-9 rounded-full bg-primary/10 text-primary text-xs font-bold flex items-center justify-center shrink-0">{{ $initials }}</div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-semibold text-text-light dark:text-text-dark break-words">{{ $child['full_name'] }}</p>
                                                    <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5 break-words">{{ $parentLine !== '' ? $parentLine : ($child['parent_name'] ?: '—') }}</p>
                                                    <p class="text-[11px] text-text-light/50 dark:text-text-dark/50 mt-0.5 truncate">{{ $child['category_label'] }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="masterlist-desktop-cell h-[72px] px-4 py-2 text-sm text-text-light/70 dark:text-text-dark/70 whitespace-nowrap" data-label="Age">
                                            <span class="font-medium text-text-light dark:text-text-dark md:font-normal">{{ $child['age_label'] }}</span>
                                        </td>
                                        <td class="masterlist-desktop-cell h-[72px] px-4 py-2 text-sm text-text-light/70 dark:text-text-dark/70" data-label="Purok">{{ $child['purok'] }}</td>
                                        <td class="masterlist-desktop-cell h-[72px] px-4 py-2 child-health-status-cell" data-label="Health">
                                            <div class="flex flex-col gap-1">
                                                @if ($nutrition && ($nutrition['has_data'] ?? false))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $nutClass }}">{{ $nutrition['summary_label'] ?? $nutrition['label'] }}</span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-background-light dark:bg-background-dark text-text-light/60 dark:text-text-dark/60">No weight record</span>
                                                @endif
                                                @if ($hs && ($hs['fully_immunized'] ?? false))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100/80 dark:bg-green-900/30 text-green-800 dark:text-green-300">Fully immunized</span>
                                                @elseif ($hs && ($hs['immunization_missed'] ?? 0) > 0)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100/80 dark:bg-red-900/30 text-red-700 dark:text-red-200">{{ $hs['immunization_missed'] }} vaccine missed</span>
                                                @elseif ($hs && ($hs['immunization_given'] ?? 0) > 0)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-background-light dark:bg-background-dark text-text-light/60 dark:text-text-dark/60">{{ $hs['immunization_given'] }}/{{ $hs['immunization_total'] }} vaccines</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="masterlist-desktop-cell h-[72px] px-4 py-2 text-right whitespace-nowrap" data-label="Action">
                                            <button type="button" class="view-profile-btn open-health-btn monitoring-view-btn" data-child-key="{{ $child['id'] }}">
                                                View profile
                                                <span class="material-symbols-outlined text-base">chevron_right</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="masterlistEmptyRow">
                                        <td colspan="5" class="px-4 py-10 text-center">
                                            <span class="material-symbols-outlined text-4xl text-text-light/50 dark:text-text-dark/50 mb-2 block">child_care</span>
                                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">No children to monitor yet. Records appear here after BHW accepts household verification requests with children aged 0–5.</p>
                                        </td>
                                    </tr>
                                @endforelse
                                <tr id="monitoringNoResults" class="hidden">
                                    <td colspan="5" class="px-4 py-10 text-center">
                                        <span class="material-symbols-outlined text-4xl text-text-light/50 dark:text-text-dark/50 mb-2 block">search_off</span>
                                        <p class="text-sm text-text-light/70 dark:text-text-dark/70">No children match your search or filters.</p>
                                    </td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>

                    <nav class="monitoring-list-footer sm:px-0" aria-label="Results">
                        <p class="text-xs sm:text-sm text-text-light/70 dark:text-text-dark/70">
                            Showing
                            <span id="statVisible" class="font-semibold text-text-light dark:text-text-dark">{{ number_format($totalChildren) }}</span>
                            of
                            <span class="font-semibold text-text-light dark:text-text-dark">{{ number_format($totalChildren) }}</span>
                            children
                        </p>
                    </nav>
                </div>
            </div>
        </div>
    </main>

    @include('partials.bns-mobile-nav')
</div>

<div id="healthModal" class="fixed inset-0 z-[60] p-3 sm:p-4 md:p-6 bg-black/50">
    <div id="healthModalPanel" class="bg-card-light dark:bg-card-dark rounded-xl shadow-xl border border-border-light dark:border-border-dark overflow-hidden">
        <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3 sm:py-4 border-b border-border-light dark:border-border-dark shrink-0">
            <div class="min-w-0">
                <h2 id="healthModalTitle" class="text-lg sm:text-xl font-bold truncate">Child Profile</h2>
                <p id="healthModalSubtitle" class="text-xs sm:text-sm text-text-light/70 dark:text-text-dark/70 truncate">Loading profile...</p>
            </div>
            <button type="button" id="closeHealthModal" class="p-2 rounded-lg hover:bg-primary/10 shrink-0" aria-label="Close profile">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div id="healthModalMain">
            <div id="healthModalProfile" class="hidden px-4 sm:px-5 py-4 border-b border-border-light dark:border-border-dark bg-background-light/60 dark:bg-background-dark/60">
                <button type="button" id="profileInfoToggle" class="profile-info-toggle lg:hidden" aria-expanded="false" aria-controls="profileInfoDetails">
                    <span class="inline-flex items-center gap-2 min-w-0">
                        <span class="material-symbols-outlined text-primary text-lg">info</span>
                        <span>Child information</span>
                    </span>
                    <span class="material-symbols-outlined profile-info-chevron" aria-hidden="true">expand_more</span>
                </button>
                <dl id="profileInfoDetails" class="profile-info-details space-y-2.5">
                    <div class="profile-detail-row"><dt>Birthdate</dt><dd id="profileBirthdate"></dd></div>
                    <div class="profile-detail-row"><dt>Category</dt><dd id="profileCategory"></dd></div>
                    <div class="profile-detail-row profile-parent-row"><dt>Parent</dt><dd id="profileParent"></dd></div>
                    <div class="profile-detail-row profile-father-row hidden"><dt>Father</dt><dd id="profileFather"></dd></div>
                    <div class="profile-detail-row profile-mother-row hidden"><dt>Mother</dt><dd id="profileMother"></dd></div>
                    <div class="profile-detail-row"><dt>Contact</dt><dd id="profileContact"></dd></div>
                    <div class="profile-detail-row"><dt>Purok</dt><dd id="profilePurok"></dd></div>
                    <div class="profile-detail-row"><dt>Family number</dt><dd id="profileFamilyNumber"></dd></div>
                </dl>
            </div>

            <div id="healthModalContent">
                <div class="px-4 sm:px-5 pt-3 pb-2 border-b border-border-light dark:border-border-dark shrink-0">
                    <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60 mb-2">Health records</p>
                    <div class="health-tabs-scroll flex gap-2">
                        <button type="button" class="health-tab active shrink-0 px-3 py-2 rounded-lg border border-border-light dark:border-border-dark text-xs sm:text-sm font-semibold whitespace-nowrap" data-tab="weight">Weight</button>
                        <button type="button" class="health-tab shrink-0 px-3 py-2 rounded-lg border border-border-light dark:border-border-dark text-xs sm:text-sm font-semibold whitespace-nowrap" data-tab="deworming">Deworming</button>
                        <button type="button" class="health-tab shrink-0 px-3 py-2 rounded-lg border border-border-light dark:border-border-dark text-xs sm:text-sm font-semibold whitespace-nowrap" data-tab="vitamin-a">Vitamin A</button>
                        <button type="button" class="health-tab shrink-0 px-3 py-2 rounded-lg border border-border-light dark:border-border-dark text-xs sm:text-sm font-semibold whitespace-nowrap" data-tab="immunization">Immunization</button>
                    </div>
                </div>

                <div id="healthModalBody" class="p-4 sm:p-5 space-y-4">
                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Loading health record...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="dewormingDeleteModal" class="fixed inset-0 z-[70] p-4 bg-black/50" aria-hidden="true">
    <div class="w-full max-w-sm rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark shadow-xl p-5" role="dialog" aria-modal="true" aria-labelledby="dewormingDeleteTitle">
        <div class="flex items-start gap-3 mb-4">
            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-accent-red/10 text-accent-red">
                <span class="material-symbols-outlined">delete</span>
            </span>
            <div class="min-w-0">
                <h3 id="dewormingDeleteTitle" class="text-base font-bold text-text-light dark:text-text-dark">Delete deworming record?</h3>
                <p id="dewormingDeleteMessage" class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">This will permanently remove this deworming record.</p>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" id="dewormingDeleteCancel" class="vita-btn vita-btn-ghost">Cancel</button>
            <button type="button" id="dewormingDeleteConfirm" class="h-10 px-4 rounded-lg bg-accent-red text-white text-sm font-semibold hover:opacity-90">Delete</button>
        </div>
    </div>
</div>

<div id="immunizationNotifyModal" class="fixed inset-0 z-[70] p-4 bg-black/50" aria-hidden="true">
    <div class="w-full max-w-sm rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark shadow-xl p-5" role="dialog" aria-modal="true" aria-labelledby="immunizationNotifyTitle">
        <div class="flex items-start gap-3 mb-4">
            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-secondary/15 text-secondary">
                <span class="material-symbols-outlined">notifications</span>
            </span>
            <div class="min-w-0">
                <h3 id="immunizationNotifyTitle" class="text-base font-bold text-text-light dark:text-text-dark">Notify consultation date</h3>
                <p id="immunizationNotifyMessage" class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">
                    Residents will get an inbox message and a date-only announcement (no exact time).
                </p>
            </div>
        </div>
        <div class="mb-4">
            <label for="immunizationNotifyDate" class="block text-xs font-semibold text-text-light/60 dark:text-text-dark/60 mb-1">Consultation date</label>
            <input id="immunizationNotifyDate" type="date" class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 py-2 text-sm text-text-light dark:text-text-dark focus:outline-none focus:ring-2 focus:ring-secondary/30 focus:border-secondary">
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" id="immunizationNotifyCancel" class="vita-btn vita-btn-ghost">Cancel</button>
            <button type="button" id="immunizationNotifyConfirm" class="h-10 px-4 rounded-lg bg-secondary text-white text-sm font-semibold hover:opacity-90">Send notify</button>
        </div>
    </div>
</div>

<script>
    const bnsHealthRoutes = {
        health: @json(route('bns.monitoring.health', ['childKey' => '__KEY__'])),
        weight: @json(route('bns.monitoring.weight.store', ['childKey' => '__KEY__'])),
        weightUpdate: @json(route('bns.monitoring.weight.update', ['childKey' => '__KEY__', 'log' => '__ID__'])),
        deworming: @json(route('bns.monitoring.deworming.store', ['childKey' => '__KEY__'])),
        dewormingUpdate: @json(route('bns.monitoring.deworming.update', ['childKey' => '__KEY__', 'log' => '__ID__'])),
        dewormingDelete: @json(route('bns.monitoring.deworming.delete', ['childKey' => '__KEY__', 'log' => '__ID__'])),
        vitaminA: @json(route('bns.monitoring.vitamin-a.store', ['childKey' => '__KEY__'])),
        vitaminAUpdate: @json(route('bns.monitoring.vitamin-a.update', ['childKey' => '__KEY__', 'log' => '__ID__'])),
        immunization: @json(route('bns.monitoring.immunization.update', ['childKey' => '__KEY__', 'immunization' => '__ID__'])),
        immunizationNotify: @json(route('bns.monitoring.immunization.notify', ['childKey' => '__KEY__', 'immunization' => '__ID__'])),
    };

    const immunizationDisplayLayout = @json(\App\Support\BnsImmunizationSchedule::displayLayout());

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let activeChildKey = null;
    let activeHealthData = null;
    let activeHealthTab = 'weight';

    function healthRoute(template, childKey, immunizationId = null) {
        let url = template.replace('__KEY__', encodeURIComponent(childKey));
        if (immunizationId !== null) {
            url = url.replace('__ID__', encodeURIComponent(String(immunizationId)));
        }
        return url;
    }

    async function fetchHealth(childKey) {
        const response = await fetch(healthRoute(bnsHealthRoutes.health, childKey), {
            headers: { 'Accept': 'application/json' },
        });
        if (!response.ok) {
            throw new Error('Unable to load health record.');
        }
        return response.json();
    }

    async function postHealth(url, body) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(body),
        });
        const data = await response.json();
        if (!response.ok) {
            const message = data.message || Object.values(data.errors || {}).flat().join(' ') || 'Save failed.';
            throw new Error(message);
        }
        return data;
    }

    async function deleteHealth(url) {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        const data = await response.json();
        if (!response.ok) {
            const message = data.message || Object.values(data.errors || {}).flat().join(' ') || 'Delete failed.';
            throw new Error(message);
        }
        return data;
    }

    function childInitials(name) {
        const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
        if (!parts.length) return 'C';
        if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    function renderChildProfile(child, data) {
        const profile = document.getElementById('healthModalProfile');
        if (!profile) return;

        profile.classList.remove('hidden');
        document.getElementById('profileBirthdate').textContent = child.birthdate_label || '—';
        document.getElementById('profileCategory').textContent = child.category_label || '—';
        const parentName = (child.parent_name || child.representative_name || '').trim();
        const parentRow = document.querySelector('.profile-parent-row');
        const fatherName = (child.father_name || '').trim();
        const motherName = (child.mother_name || '').trim();
        const fatherRow = document.querySelector('.profile-father-row');
        const motherRow = document.querySelector('.profile-mother-row');

        if (fatherName || motherName) {
            parentRow?.classList.add('hidden');
        } else {
            parentRow?.classList.remove('hidden');
            document.getElementById('profileParent').textContent = parentName || '—';
        }

        if (fatherName) {
            document.getElementById('profileFather').textContent = fatherName;
            fatherRow?.classList.remove('hidden');
        } else {
            fatherRow?.classList.add('hidden');
        }

        if (motherName) {
            document.getElementById('profileMother').textContent = motherName;
            motherRow?.classList.remove('hidden');
        } else {
            motherRow?.classList.add('hidden');
        }

        document.getElementById('profileContact').textContent = child.contact || '—';
        document.getElementById('profilePurok').textContent = child.purok || '—';
        document.getElementById('profileFamilyNumber').textContent = child.family_number || '—';

        const summary = data?.immunization_summary || {};
        const nutrition = data?.nutrition || {};
        document.getElementById('healthModalTitle').textContent = child.full_name || 'Child Profile';
        document.getElementById('healthModalSubtitle').textContent = [
            child.purok,
            child.age_label,
            nutrition.has_data ? nutrition.summary_label : null,
            summary.fully_immunized ? 'Fully immunized' : `Immunization ${summary.given || 0}/${summary.total || 0}`,
        ].filter(Boolean).join(' · ');

    }

    function nutritionBadgeClass(severity) {
        if (severity === 'critical') return 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-200';
        if (severity === 'warning') return 'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200';
        if (severity === 'normal') return 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300';
        return 'bg-background-light dark:bg-background-dark text-text-light/60 dark:text-text-dark/60';
    }

    function nutritionBadgeHtml(nutrition) {
        if (!nutrition?.has_data) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-background-light dark:bg-background-dark text-text-light/60 dark:text-text-dark/60">No weight record</span>';
        }
        const label = nutrition.summary_label || nutrition.label || 'Unknown';
        return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${nutritionBadgeClass(nutrition.severity)}">${label}</span>`;
    }

    function immunizationSummaryBadgeHtml(summary) {
        if (summary.fully_immunized) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100/80 dark:bg-green-900/30 text-green-800 dark:text-green-300">Fully immunized</span>';
        }
        if ((summary.missed || 0) > 0) {
            return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100/80 dark:bg-red-900/30 text-red-700 dark:text-red-200">${summary.missed} vaccine missed</span>`;
        }
        if ((summary.given || 0) > 0) {
            return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-background-light dark:bg-background-dark text-text-light/60 dark:text-text-dark/60">${summary.given}/${summary.total} vaccines</span>`;
        }
        return '';
    }

    function healthStatusRowHtml(data) {
        const nutrition = data?.nutrition || {};
        const summary = data?.immunization_summary || {};
        const immunizationBadge = immunizationSummaryBadgeHtml(summary);

        return `<div class="flex flex-col gap-1">${nutritionBadgeHtml(nutrition)}${immunizationBadge}</div>`;
    }

    function logMuacBadgeHtml(muac) {
        if (!muac) return '—';
        return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${nutritionBadgeClass(muac.severity)}">${muac.summary_label || muac.label}</span>`;
    }

    function logNutritionBadgeHtml(nutrition) {
        if (!nutrition?.has_data) return '—';
        return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${nutritionBadgeClass(nutrition.severity)}">${nutrition.summary_label || nutrition.label}</span>`;
    }

    function immunizationRowHtml(summary) {
        if (summary.fully_immunized) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300">Fully immunized</span>';
        }
        if ((summary.missed || 0) > 0) {
            return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-200">${summary.missed} missed</span>`;
        }
        if ((summary.given || 0) > 0) {
            return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200">${summary.given}/${summary.total} vaccines</span>`;
        }
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-background-light dark:bg-background-dark text-text-light/60 dark:text-text-dark/60">No record</span>';
    }

    function immunizationBadgeClass(status, isOverdue = false) {
        if (status === 'given') return 'bg-secondary/15 text-secondary border border-secondary/20';
        if (status === 'missed') return 'bg-accent-red/10 text-accent-red border border-accent-red/20';
        if (isOverdue) return 'bg-accent-orange/10 text-accent-orange border border-accent-orange/20';
        return 'bg-background-light dark:bg-background-dark text-text-light/70 dark:text-text-dark/70 border border-border-light dark:border-border-dark';
    }

    function immunizationCardClass(status, isOverdue = false) {
        if (status === 'given') return 'border-secondary/30 bg-secondary/5';
        if (status === 'missed') return 'border-accent-red/30 bg-accent-red/5';
        if (isOverdue) return 'border-accent-orange/30 bg-accent-orange/5';
        return 'border-border-light dark:border-border-dark';
    }

    function renderImmunizationCardHtml(row, today) {
        const canNotify = row.status !== 'given';
        const defaultConsult = (row.due_date_raw && row.due_date_raw >= today) ? row.due_date_raw : today;

        return `
            <article class="immunization-vaccine-card rounded-xl border p-3 sm:p-4 ${immunizationCardClass(row.status, row.is_overdue)}">
                <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
                    <div class="min-w-0 flex-1">
                        <h4 class="text-sm sm:text-base font-bold leading-snug">${row.vaccine_name}</h4>
                        <p class="text-xs text-text-light/70 dark:text-text-dark/70 mt-1">
                            Due when <strong>${row.due_age_label || '—'}</strong> · <span class="whitespace-nowrap">${row.due_date || '—'}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        ${canNotify ? `
                            <button
                                type="button"
                                class="weight-edit-icon-btn immunization-notify-btn"
                                data-id="${row.id}"
                                data-vaccine="${escapeHtml(row.vaccine_name || '')}"
                                data-due="${escapeHtml(defaultConsult || today)}"
                                aria-label="Notify consultation date"
                                title="Notify consultation date"
                            >
                                <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
                            </button>
                        ` : ''}
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold ${immunizationBadgeClass(row.status, row.is_overdue)}">${row.status_label}</span>
                    </div>
                </div>

                ${row.status === 'given' && row.given_date ? `
                    <div class="flex items-center gap-2 text-xs sm:text-sm text-secondary bg-secondary/10 rounded-lg px-3 py-2 mb-3">
                        <span class="material-symbols-outlined text-base">check_circle</span>
                        <span>Recorded as given on <strong>${row.given_date}</strong></span>
                    </div>
                ` : ''}

                ${row.status === 'missed' ? `
                    <div class="flex items-center gap-2 text-xs sm:text-sm text-accent-red bg-accent-red/10 rounded-lg px-3 py-2 mb-3">
                        <span class="material-symbols-outlined text-base">event_busy</span>
                        <span>Marked missed — due date was <strong>${row.missed_date || row.due_date || '—'}</strong></span>
                    </div>
                ` : ''}

                ${row.is_overdue && row.status === 'pending' ? `
                    <div class="flex items-center gap-2 text-xs sm:text-sm text-accent-orange bg-accent-orange/10 rounded-lg px-3 py-2 mb-3">
                        <span class="material-symbols-outlined text-base">schedule</span>
                        <span>Past due — update this record as <strong>Given</strong> or <strong>Missed</strong>.</span>
                    </div>
                ` : ''}

                <form class="immunization-update-form space-y-3 pt-3 border-t border-border-light/80 dark:border-border-dark/80" data-id="${row.id}">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60 mb-2">Update status</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="immunization-status-option cursor-pointer">
                                <input type="radio" name="status" value="pending" class="peer sr-only" ${row.status === 'pending' ? 'checked' : ''}>
                                <span class="flex flex-col items-center justify-center gap-1 h-full min-h-[3.75rem] rounded-lg border border-border-light dark:border-border-dark px-2 py-2 text-center text-xs font-semibold peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary">Pending</span>
                            </label>
                            <label class="immunization-status-option cursor-pointer">
                                <input type="radio" name="status" value="given" class="peer sr-only" ${row.status === 'given' ? 'checked' : ''}>
                                <span class="flex flex-col items-center justify-center gap-1 h-full min-h-[3.75rem] rounded-lg border border-border-light dark:border-border-dark px-2 py-2 text-center text-xs font-semibold peer-checked:border-secondary peer-checked:bg-secondary/10 peer-checked:text-secondary">
                                    <span class="material-symbols-outlined text-base">vaccines</span>
                                    Given
                                </span>
                            </label>
                            <label class="immunization-status-option cursor-pointer">
                                <input type="radio" name="status" value="missed" class="peer sr-only" ${row.status === 'missed' ? 'checked' : ''}>
                                <span class="flex flex-col items-center justify-center gap-1 h-full min-h-[3.75rem] rounded-lg border border-border-light dark:border-border-dark px-2 py-2 text-center text-xs font-semibold peer-checked:border-accent-red peer-checked:bg-accent-red/10 peer-checked:text-accent-red">
                                    <span class="material-symbols-outlined text-base">close</span>
                                    Missed
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="immunization-date-wrap space-y-1">
                        <label class="block text-xs font-semibold text-text-light/70 dark:text-text-dark/70">Date given</label>
                        <input type="date" name="given_date" value="${row.given_date_raw || ''}" max="${today}" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 text-sm">
                        <p class="immunization-date-hint text-xs text-text-light/60 dark:text-text-dark/60"></p>
                    </div>

                    <button type="submit" class="w-full h-10 rounded-lg bg-primary text-white text-sm font-semibold inline-flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-base">save</span>
                        Save record
                    </button>
                </form>
            </article>
        `;
    }

    function renderImmunizationScheduleHtml(immunizations, today) {
        const byCode = Object.fromEntries((immunizations || []).map((row) => [row.vaccine_code, row]));

        return immunizationDisplayLayout.map((group) => {
            const cards = group.codes
                .map((code) => byCode[code])
                .filter(Boolean)
                .map((row) => renderImmunizationCardHtml(row, today))
                .join('');

            if (!cards) {
                return '';
            }

            return `<div class="immunization-schedule-row immunization-schedule-row--${group.cols}">${cards}</div>`;
        }).join('');
    }

    function bindImmunizationForms() {
        document.querySelectorAll('.immunization-update-form').forEach((form) => {
            const statusInputs = form.querySelectorAll('[name="status"]');
            const dateWrap = form.querySelector('.immunization-date-wrap');
            const dateInput = form.querySelector('[name="given_date"]');
            const dateHint = form.querySelector('.immunization-date-hint');

            const selectedStatus = () => form.querySelector('[name="status"]:checked')?.value || 'pending';

            const syncDateField = () => {
                const status = selectedStatus();
                const showDate = status === 'given';
                dateWrap?.classList.toggle('hidden', !showDate);
                if (dateInput) {
                    dateInput.required = showDate;
                    if (showDate && !dateInput.value) {
                        dateInput.value = new Date().toISOString().slice(0, 10);
                    }
                }
                if (dateHint) {
                    dateHint.textContent = status === 'given'
                        ? 'Required — enter the date this vaccine was administered.'
                        : status === 'missed'
                            ? 'No date needed. This marks the dose as not received by the due date.'
                            : 'No date needed while the dose is still pending.';
                }
            };

            statusInputs.forEach((input) => input.addEventListener('change', syncDateField));
            syncDateField();

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const immunizationId = form.dataset.id;
                const status = selectedStatus();
                const submitBtn = form.querySelector('[type="submit"]');
                const originalLabel = submitBtn?.textContent || 'Save record';

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Saving...';
                }

                try {
                    activeHealthData = await postHealth(healthRoute(bnsHealthRoutes.immunization, activeChildKey, immunizationId), {
                        status,
                        given_date: status === 'given' ? (form.given_date.value || null) : null,
                        notes: null,
                    });
                    updateMasterlistRowSummary(activeChildKey, activeHealthData);
                    renderHealthTab();
                    showHealthToast('Immunization record saved.');
                } catch (error) {
                    alert(error.message);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalLabel;
                    }
                }
            });
        });

        document.querySelectorAll('.immunization-notify-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                openImmunizationNotifyModal({
                    immunizationId: btn.dataset.id,
                    vaccineName: btn.dataset.vaccine || 'vaccine',
                    dueDate: btn.dataset.due || '',
                });
            });
        });
    }

    let pendingImmunizationNotify = null;

    function openImmunizationNotifyModal({ immunizationId, vaccineName, dueDate }) {
        pendingImmunizationNotify = { immunizationId, vaccineName };
        const modal = document.getElementById('immunizationNotifyModal');
        const message = document.getElementById('immunizationNotifyMessage');
        const dateInput = document.getElementById('immunizationNotifyDate');
        if (message) {
            message.textContent = `Notify residents about the consultation date for ${vaccineName}. This creates a date-only announcement (no exact time) and inbox messages.`;
        }
        if (dateInput) {
            const today = new Date().toISOString().slice(0, 10);
            dateInput.min = today;
            dateInput.value = dueDate && dueDate >= today ? dueDate : today;
        }
        modal?.classList.add('active');
        modal?.setAttribute('aria-hidden', 'false');
    }

    function closeImmunizationNotifyModal() {
        pendingImmunizationNotify = null;
        const modal = document.getElementById('immunizationNotifyModal');
        modal?.classList.remove('active');
        modal?.setAttribute('aria-hidden', 'true');
    }

    async function confirmImmunizationNotify() {
        if (!pendingImmunizationNotify?.immunizationId || !activeChildKey) {
            return;
        }

        const dateInput = document.getElementById('immunizationNotifyDate');
        const consultationDate = dateInput?.value || '';
        if (!consultationDate) {
            showHealthToast('Please choose a consultation date.', 'error');
            return;
        }

        const confirmBtn = document.getElementById('immunizationNotifyConfirm');
        const originalLabel = confirmBtn?.textContent || 'Send notify';
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Sending...';
        }

        try {
            const response = await postHealth(
                healthRoute(bnsHealthRoutes.immunizationNotify, activeChildKey, pendingImmunizationNotify.immunizationId),
                { consultation_date: consultationDate }
            );
            closeImmunizationNotifyModal();
            showHealthToast(response?.message || 'Residents notified about the consultation date.');
        } catch (error) {
            showHealthToast(error.message || 'Unable to send notify.', 'error');
        } finally {
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.textContent = originalLabel;
            }
        }
    }

    function showHealthToast(message, type = 'success') {
        let stack = document.getElementById('health-alert-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'health-alert-stack';
            stack.className = 'fixed top-4 inset-x-5 z-[100] flex flex-col gap-2 sm:inset-x-4 sm:left-auto sm:right-4 sm:max-w-md sm:ml-auto pointer-events-none';
            document.body.appendChild(stack);
        }

        const isError = type === 'error';
        const alert = document.createElement('div');
        alert.className = isError
            ? 'pointer-events-auto flex items-start gap-2 sm:gap-3 rounded-lg sm:rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-red-900 shadow-lg transition-all duration-300 dark:border-red-800 dark:bg-red-950/90 dark:text-red-100'
            : 'pointer-events-auto flex items-start gap-2 sm:gap-3 rounded-lg sm:rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-emerald-900 shadow-lg transition-all duration-300 dark:border-emerald-800 dark:bg-emerald-950/90 dark:text-emerald-100';
        alert.setAttribute('role', isError ? 'alert' : 'status');
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-6px)';
        alert.innerHTML = `
            <span class="material-symbols-outlined shrink-0 text-base sm:text-lg ${isError ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'}">${isError ? 'error' : 'check_circle'}</span>
            <p class="flex-1 pt-0.5"></p>
            <button type="button" class="shrink-0 rounded-lg p-1 ${isError ? 'text-red-700 hover:bg-red-100 dark:text-red-300 dark:hover:bg-red-900/50' : 'text-emerald-700 hover:bg-emerald-100 dark:text-emerald-300 dark:hover:bg-emerald-900/50'}" aria-label="Dismiss notification">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        `;
        alert.querySelector('p').textContent = message;

        const removeAlert = () => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-6px)';
            window.setTimeout(() => {
                alert.remove();
                if (stack && !stack.children.length) {
                    stack.remove();
                }
            }, 300);
        };

        alert.querySelector('button')?.addEventListener('click', removeAlert);
        stack.appendChild(alert);

        window.requestAnimationFrame(() => {
            alert.style.opacity = '1';
            alert.style.transform = 'translateY(0)';
        });

        window.setTimeout(removeAlert, 2000);
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function healthRecordDetailHtml(label, value) {
        return `
            <div class="health-record-detail">
                <span class="health-record-label">${escapeHtml(label)}</span>
                <span class="health-record-value">${escapeHtml(value)}</span>
            </div>
        `;
    }

    function healthRecordCardHtml(title, detailsHtml, badgesHtml = '') {
        return `
            <article class="health-record-card">
                <div class="health-record-card-head">
                    <p class="health-record-card-title">${escapeHtml(title)}</p>
                    ${badgesHtml ? `<div class="health-record-card-badges">${badgesHtml}</div>` : ''}
                </div>
                <div>${detailsHtml}</div>
            </article>
        `;
    }

    function healthRecordsEmptyHtml(message) {
        return `<div class="health-records-empty">${escapeHtml(message)}</div>`;
    }

    function healthFormFieldHtml(label, inputHtml) {
        return `
            <div class="health-form-field">
                <span class="health-form-label">${escapeHtml(label)}</span>
                ${inputHtml}
            </div>
        `;
    }

    function weightEditIconButton(panelId) {
        return `
            <button type="button" class="weight-edit-icon-btn vita-toggle-edit" data-panel="${panelId}" aria-label="Edit weight record" title="Edit">
                <span class="material-symbols-outlined" aria-hidden="true">edit</span>
            </button>
        `;
    }

    function renderWeightLogCard(log, birthMin, today) {
        const panelId = `weight-edit-${log.id}`;
        const recordedRaw = escapeHtml(log.recorded_on_raw || '');
        const weightVal = Number(log.weight_kg).toFixed(2);
        const heightVal = log.height_cm != null ? Number(log.height_cm).toFixed(1) : '';
        const muacVal = log.muac_cm != null ? Number(log.muac_cm).toFixed(1) : '';
        const inputClass = 'w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 text-sm';

        let details = [
            healthRecordDetailHtml('Age', `${log.age_in_months ?? '—'} mo`),
            healthRecordDetailHtml('Weight', `${weightVal} kg`),
            healthRecordDetailHtml('Height', log.height_cm ? `${Number(log.height_cm).toFixed(1)} cm` : '—'),
            healthRecordDetailHtml('MUAC', log.muac_cm ? `${Number(log.muac_cm).toFixed(1)} cm` : '—'),
        ].join('');

        details += `
            <div class="vita-edit-panel mt-3" id="${panelId}">
                <form class="weight-edit-form grid grid-cols-1 gap-3" data-log-id="${log.id}">
                    ${healthFormFieldHtml('Weight (kg)', `<input type="number" name="weight_kg" step="0.01" min="0.1" max="50" required value="${weightVal}" class="${inputClass}">`)}
                    ${healthFormFieldHtml('Height (cm)', `<input type="number" name="height_cm" step="0.1" min="1" max="200" value="${heightVal}" placeholder="e.g. 85.0" class="${inputClass}">`)}
                    ${healthFormFieldHtml('MUAC (cm)', `<input type="number" name="muac_cm" step="0.1" min="5" max="25" value="${muacVal}" placeholder="e.g. 13.5" class="${inputClass}">`)}
                    ${healthFormFieldHtml('Date recorded', `<input type="date" name="recorded_on" value="${recordedRaw}" min="${escapeHtml(birthMin || '')}" max="${today}" required class="${inputClass}">`)}
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="vita-btn vita-btn-primary">Save changes</button>
                        <button type="button" class="vita-btn vita-btn-ghost vita-cancel-edit" data-panel="${panelId}">Cancel</button>
                    </div>
                </form>
            </div>
        `;

        const badgesHtml = `${weightEditIconButton(panelId)}${logNutritionBadgeHtml(log.nutrition)}`;

        return healthRecordCardHtml(log.recorded_on, details, badgesHtml);
    }

    function scrollEditPanelIntoView(panel) {
        if (!panel) return;
        const isMobile = window.matchMedia('(max-width: 767px)').matches;
        const scrollPanel = () => {
            panel.scrollIntoView({ behavior: 'smooth', block: isMobile ? 'center' : 'nearest', inline: 'nearest' });
        };
        requestAnimationFrame(() => {
            scrollPanel();
            if (!isMobile) return;
            const firstField = panel.querySelector('input:not([type="hidden"]), textarea, select');
            if (!firstField) return;
            setTimeout(() => {
                firstField.focus({ preventScroll: true });
                scrollPanel();
            }, 120);
        });
    }

    function bindWeightForms() {
        document.querySelectorAll('.vita-edit-panel.is-open, .vita-edit-row.is-open').forEach((el) => {
            el.classList.remove('is-open');
        });

        document.querySelectorAll('.vita-toggle-edit').forEach((btn) => {
            btn.addEventListener('click', () => {
                const panel = document.getElementById(btn.dataset.panel);
                if (!panel) return;
                const willOpen = !panel.classList.contains('is-open');
                document.querySelectorAll('.vita-edit-panel.is-open, .vita-edit-row.is-open').forEach((el) => {
                    el.classList.remove('is-open');
                });
                if (willOpen) {
                    panel.classList.add('is-open');
                    scrollEditPanelIntoView(panel);
                }
            });
        });

        document.querySelectorAll('.vita-cancel-edit').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById(btn.dataset.panel)?.classList.remove('is-open');
            });
        });

        document.querySelectorAll('.weight-edit-form').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                try {
                    activeHealthData = await postHealth(healthRoute(bnsHealthRoutes.weightUpdate, activeChildKey, form.dataset.logId), {
                        weight_kg: form.weight_kg.value,
                        height_cm: form.height_cm.value || null,
                        muac_cm: form.muac_cm.value || null,
                        recorded_on: form.recorded_on.value,
                    });
                    updateMasterlistRowSummary(activeChildKey, activeHealthData);
                    renderHealthTab();
                } catch (error) {
                    alert(error.message);
                }
            });
        });
    }

    function renderDewormingLogCard(log, today) {
        const panelId = `deworm-edit-${log.id}`;
        const givenRaw = escapeHtml(log.given_on_raw || '');
        const doseValue = log.medicine || '';
        const inputClass = 'w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 text-sm';

        let details = [
            healthRecordDetailHtml('Dose received', doseValue || '—'),
        ].join('');

        details += `
            <div class="vita-edit-panel mt-3" id="${panelId}">
                <form class="deworming-edit-form grid grid-cols-1 gap-3" data-log-id="${log.id}">
                    ${healthFormFieldHtml('Date given', `<input type="date" name="given_on" value="${givenRaw}" max="${today}" required class="${inputClass}">`)}
                    ${healthFormFieldHtml('Dose received', doseReceivedSelectHtml(doseValue, inputClass))}
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="vita-btn vita-btn-primary">Save changes</button>
                        <button type="button" class="vita-btn vita-btn-ghost vita-cancel-edit" data-panel="${panelId}">Cancel</button>
                    </div>
                </form>
            </div>
        `;

        const badgesHtml = `
            <button type="button" class="weight-edit-icon-btn vita-toggle-edit" data-panel="${panelId}" aria-label="Edit deworming record" title="Edit">
                <span class="material-symbols-outlined" aria-hidden="true">edit</span>
            </button>
            <button type="button" class="weight-edit-icon-btn is-danger deworming-delete-btn" data-log-id="${log.id}" data-label="${escapeHtml(log.given_on || '')}" aria-label="Delete deworming record" title="Delete">
                <span class="material-symbols-outlined" aria-hidden="true">delete</span>
            </button>
        `;

        return healthRecordCardHtml(log.given_on, details, badgesHtml);
    }

    let pendingDewormingDeleteId = null;

    function openDewormingDeleteModal(logId, label) {
        pendingDewormingDeleteId = logId;
        const modal = document.getElementById('dewormingDeleteModal');
        const message = document.getElementById('dewormingDeleteMessage');
        if (message) {
            message.textContent = label
                ? `This will permanently remove the deworming record from ${label}.`
                : 'This will permanently remove this deworming record.';
        }
        modal?.classList.add('active');
        modal?.setAttribute('aria-hidden', 'false');
    }

    function closeDewormingDeleteModal() {
        pendingDewormingDeleteId = null;
        const modal = document.getElementById('dewormingDeleteModal');
        modal?.classList.remove('active');
        modal?.setAttribute('aria-hidden', 'true');
        const confirmBtn = document.getElementById('dewormingDeleteConfirm');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Delete';
        }
    }

    function bindDewormingForms() {
        document.querySelectorAll('.vita-edit-panel.is-open, .vita-edit-row.is-open').forEach((el) => {
            el.classList.remove('is-open');
        });

        document.querySelectorAll('.vita-toggle-edit').forEach((btn) => {
            btn.addEventListener('click', () => {
                const panel = document.getElementById(btn.dataset.panel);
                if (!panel) return;
                const willOpen = !panel.classList.contains('is-open');
                document.querySelectorAll('.vita-edit-panel.is-open, .vita-edit-row.is-open').forEach((el) => {
                    el.classList.remove('is-open');
                });
                if (willOpen) {
                    panel.classList.add('is-open');
                    scrollEditPanelIntoView(panel);
                }
            });
        });

        document.querySelectorAll('.vita-cancel-edit').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById(btn.dataset.panel)?.classList.remove('is-open');
            });
        });

        document.querySelectorAll('.deworming-edit-form').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                try {
                    activeHealthData = await postHealth(healthRoute(bnsHealthRoutes.dewormingUpdate, activeChildKey, form.dataset.logId), {
                        given_on: form.given_on.value,
                        medicine: form.medicine.value || null,
                    });
                    renderHealthTab();
                } catch (error) {
                    alert(error.message);
                }
            });
        });

        document.querySelectorAll('.deworming-delete-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                openDewormingDeleteModal(btn.dataset.logId, btn.dataset.label || '');
            });
        });
    }

    function vitaminStatusLabel(status) {
        return ({
            given: 'Given',
            missed: 'Missed',
            due: 'Due now',
            upcoming: 'Upcoming',
        })[status] || status;
    }

    function doseReceivedSelectHtml(selected = '', inputClass = 'w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 text-sm') {
        const selectedValue = String(selected || '');
        const doses = ['1/2 tablet', '1 tablet'];
        const options = [
            `<option value="">Select dose</option>`,
            ...doses.map((dose) => `<option value="${escapeHtml(dose)}" ${selectedValue === dose ? 'selected' : ''}>${escapeHtml(dose)}</option>`),
        ];
        if (selectedValue && !doses.includes(selectedValue)) {
            options.splice(1, 0, `<option value="${escapeHtml(selectedValue)}" selected>${escapeHtml(selectedValue)}</option>`);
        }
        return `<select name="medicine" required class="${inputClass}">${options.join('')}</select>`;
    }

    function renderVitaminScheduleCard(row, birthMin, today) {
        const status = row.status || 'upcoming';
        const statusBadge = `<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${immunizationBadgeClass(status === 'given' ? 'given' : status === 'missed' ? 'missed' : 'pending')}">${escapeHtml(vitaminStatusLabel(status))}</span>`;
        const canCatchUp = status === 'missed' || status === 'due';
        const canEdit = status === 'given' && row.log_id;
        const defaultDate = row.given_on_raw || (canCatchUp ? (row.due_date_raw && row.due_date_raw <= today ? row.due_date_raw : today) : today);
        const doseValue = row.notes || '';
        let actionHtml = '';
        let panelHtml = '';

        if (canEdit) {
            actionHtml = `<button type="button" class="vita-btn vita-btn-secondary vita-toggle-edit" data-panel="vita-schedule-edit-${row.due_month}">Edit date</button>`;
            panelHtml = `
                <div class="vita-edit-panel" id="vita-schedule-edit-${row.due_month}">
                    <form class="vitamin-a-edit-form grid grid-cols-1 gap-3" data-log-id="${row.log_id}">
                        <div class="health-form-field vita-date-field">
                            <span class="health-form-label">Date given</span>
                            <input type="date" name="given_on" value="${escapeHtml(defaultDate || '')}" min="${escapeHtml(birthMin || '')}" max="${today}" required class="w-full rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 text-sm">
                        </div>
                        <div class="health-form-field">
                            <span class="health-form-label">Dose received</span>
                            ${doseReceivedSelectHtml(doseValue)}
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="vita-btn vita-btn-primary flex-1">Save date</button>
                            <button type="button" class="vita-btn vita-btn-ghost vita-cancel-edit" data-panel="vita-schedule-edit-${row.due_month}">Cancel</button>
                        </div>
                    </form>
                </div>
            `;
        } else if (canCatchUp) {
            actionHtml = `<button type="button" class="vita-btn vita-btn-primary vita-toggle-edit" data-panel="vita-schedule-add-${row.due_month}">Add date</button>`;
            panelHtml = `
                <div class="vita-edit-panel" id="vita-schedule-add-${row.due_month}">
                    <form class="vitamin-a-create-form grid grid-cols-1 gap-3">
                        <div class="health-form-field vita-date-field">
                            <span class="health-form-label">Date given</span>
                            <input type="date" name="given_on" value="${escapeHtml(defaultDate || today)}" min="${escapeHtml(birthMin || '')}" max="${today}" required class="w-full rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 text-sm">
                        </div>
                        <div class="health-form-field">
                            <span class="health-form-label">Dose received</span>
                            ${doseReceivedSelectHtml()}
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="vita-btn vita-btn-primary flex-1">Save Vitamin A</button>
                            <button type="button" class="vita-btn vita-btn-ghost vita-cancel-edit" data-panel="vita-schedule-add-${row.due_month}">Cancel</button>
                        </div>
                    </form>
                </div>
            `;
        }

        const givenLine = row.given_on
            ? `Given on <strong>${escapeHtml(row.given_on)}</strong>${doseValue ? ` · ${escapeHtml(doseValue)}` : ''}`
            : `Due ${escapeHtml(row.due_date || '—')}`;

        return `
            <article class="vita-schedule-card is-${escapeHtml(status)}">
                <div class="vita-card-main">
                    <div class="vita-card-meta">
                        <p class="vita-card-title">${escapeHtml(String(row.due_month))} months</p>
                        <p class="vita-card-sub">${givenLine}</p>
                    </div>
                    <div class="vita-card-actions">
                        ${statusBadge}
                        ${actionHtml}
                    </div>
                </div>
                ${panelHtml}
            </article>
        `;
    }

    function bindVitaminAForms() {
        const closeAllVitaminPanels = () => {
            document.querySelectorAll('.vita-edit-panel.is-open, .vita-edit-row.is-open').forEach((el) => {
                el.classList.remove('is-open');
            });
        };

        document.querySelectorAll('.vita-toggle-edit').forEach((btn) => {
            btn.addEventListener('click', () => {
                const panel = document.getElementById(btn.dataset.panel);
                if (!panel) return;
                const opening = !panel.classList.contains('is-open');
                closeAllVitaminPanels();
                if (opening) {
                    panel.classList.add('is-open');
                    scrollEditPanelIntoView(panel);
                }
            });
        });

        document.querySelectorAll('.vita-cancel-edit').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById(btn.dataset.panel)?.classList.remove('is-open');
            });
        });

        const saveCreate = async (form) => {
            activeHealthData = await postHealth(healthRoute(bnsHealthRoutes.vitaminA, activeChildKey), {
                given_on: form.given_on.value,
                medicine: form.medicine.value || null,
            });
            renderHealthTab();
            updateMasterlistRowSummary(activeChildKey, activeHealthData);
        };

        const saveUpdate = async (form) => {
            activeHealthData = await postHealth(healthRoute(bnsHealthRoutes.vitaminAUpdate, activeChildKey, form.dataset.logId), {
                given_on: form.given_on.value,
                medicine: form.medicine.value || null,
            });
            renderHealthTab();
            updateMasterlistRowSummary(activeChildKey, activeHealthData);
        };

        document.getElementById('vitaminAForm')?.addEventListener('submit', async (event) => {
            event.preventDefault();
            try {
                await saveCreate(event.target);
            } catch (error) {
                alert(error.message);
            }
        });

        document.querySelectorAll('.vitamin-a-create-form').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                try {
                    await saveCreate(event.target);
                } catch (error) {
                    alert(error.message);
                }
            });
        });

        document.querySelectorAll('.vitamin-a-edit-form').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                try {
                    await saveUpdate(event.target);
                } catch (error) {
                    alert(error.message);
                }
            });
        });
    }

    function renderHealthTab() {
        const body = document.getElementById('healthModalBody');
        if (!activeHealthData || !body) return;

        const data = activeHealthData;
        const today = new Date().toISOString().slice(0, 10);

        if (activeHealthTab === 'weight') {
            const weightLogs = data.weight_logs || [];
            const inputClass = 'w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 text-sm';
            const birthMin = (data.child && data.child.birthdate) ? String(data.child.birthdate).slice(0, 10) : '';

            body.innerHTML = `
                <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                    <p class="health-section-title">Add weight record</p>
                    <form id="weightForm" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        ${healthFormFieldHtml('Weight (kg)', `<input type="number" name="weight_kg" step="0.01" min="0.1" max="50" required placeholder="e.g. 12.5" class="${inputClass}">`)}
                        ${healthFormFieldHtml('Height (cm)', `<input type="number" name="height_cm" step="0.1" min="1" max="200" placeholder="e.g. 85.0" class="${inputClass}">`)}
                        ${healthFormFieldHtml('MUAC (cm)', `<input type="number" name="muac_cm" step="0.1" min="5" max="25" placeholder="e.g. 13.5" title="Mid-upper arm circumference" class="${inputClass}">`)}
                        ${healthFormFieldHtml('Date recorded', `<input type="date" name="recorded_on" value="${today}" min="${escapeHtml(birthMin)}" max="${today}" required class="${inputClass}">`)}
                        <div class="sm:col-span-3">
                            <button type="submit" class="health-form-submit h-10 px-4 rounded-lg bg-primary text-white text-sm font-semibold">Save record</button>
                        </div>
                    </form>
                </div>
                <div class="mt-4">
                    <p class="health-section-title">Weight history</p>
                    <div class="health-records-mobile">
                        ${weightLogs.length
                            ? weightLogs.map((log) => renderWeightLogCard(log, birthMin, today)).join('')
                            : healthRecordsEmptyHtml('No weight records yet.')}
                    </div>
                    <div class="health-records-desktop overflow-x-auto rounded-lg border border-border-light dark:border-border-dark">
                        <table class="w-full min-w-[720px]">
                            <thead><tr class="border-b border-border-light dark:border-border-dark">
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Date</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Age (mo)</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Weight</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Height</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">MUAC</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Status</th>
                            </tr></thead>
                            <tbody>
                                ${weightLogs.length ? weightLogs.map((log) => {
                                    const panelId = `weight-desk-edit-${log.id}`;
                                    const recordedRaw = escapeHtml(log.recorded_on_raw || '');
                                    const weightVal = Number(log.weight_kg).toFixed(2);
                                    const heightVal = log.height_cm != null ? Number(log.height_cm).toFixed(1) : '';
                                    const muacVal = log.muac_cm != null ? Number(log.muac_cm).toFixed(1) : '';
                                    return `
                                    <tr class="border-b border-border-light dark:border-border-dark">
                                        <td class="px-3 py-2 text-sm">${log.recorded_on}</td>
                                        <td class="px-3 py-2 text-sm">${log.age_in_months ?? '—'}</td>
                                        <td class="px-3 py-2 text-sm">${weightVal} kg</td>
                                        <td class="px-3 py-2 text-sm">${log.height_cm ? Number(log.height_cm).toFixed(1) + ' cm' : '—'}</td>
                                        <td class="px-3 py-2 text-sm">${log.muac_cm ? Number(log.muac_cm).toFixed(1) + ' cm' : '—'}</td>
                                        <td class="px-3 py-2 text-sm">
                                            <div class="inline-flex items-center gap-1.5 flex-wrap">
                                                ${weightEditIconButton(panelId)}
                                                ${logNutritionBadgeHtml(log.nutrition)}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="vita-edit-row border-b border-border-light dark:border-border-dark bg-background-light/60 dark:bg-background-dark/40" id="${panelId}">
                                        <td colspan="6" class="px-3 py-3">
                                            <form class="weight-edit-form grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end" data-log-id="${log.id}">
                                                ${healthFormFieldHtml('Weight (kg)', `<input type="number" name="weight_kg" step="0.01" min="0.1" max="50" required value="${weightVal}" class="${inputClass}">`)}
                                                ${healthFormFieldHtml('Height (cm)', `<input type="number" name="height_cm" step="0.1" min="1" max="200" value="${heightVal}" class="${inputClass}">`)}
                                                ${healthFormFieldHtml('MUAC (cm)', `<input type="number" name="muac_cm" step="0.1" min="5" max="25" value="${muacVal}" class="${inputClass}">`)}
                                                ${healthFormFieldHtml('Date recorded', `<input type="date" name="recorded_on" value="${recordedRaw}" min="${escapeHtml(birthMin)}" max="${today}" required class="${inputClass}">`)}
                                                <div class="flex flex-wrap gap-2">
                                                    <button type="submit" class="vita-btn vita-btn-primary">Save</button>
                                                    <button type="button" class="vita-btn vita-btn-ghost vita-cancel-edit" data-panel="${panelId}">Cancel</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>`;
                                }).join('') : `<tr><td colspan="6" class="px-3 py-4 text-sm text-center text-text-light/60">No weight records yet.</td></tr>`}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            const weightForm = document.getElementById('weightForm');

            weightForm?.addEventListener('submit', async (event) => {
                event.preventDefault();
                const form = event.target;
                try {
                    activeHealthData = await postHealth(healthRoute(bnsHealthRoutes.weight, activeChildKey), {
                        weight_kg: form.weight_kg.value,
                        height_cm: form.height_cm.value || null,
                        muac_cm: form.muac_cm.value || null,
                        recorded_on: form.recorded_on.value,
                    });
                    updateMasterlistRowSummary(activeChildKey, activeHealthData);
                    renderHealthTab();
                } catch (error) {
                    alert(error.message);
                }
            });

            bindWeightForms();
            return;
        }

        if (activeHealthTab === 'deworming') {
            const dewormingLogs = data.deworming_logs || [];
            const inputClass = 'w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 text-sm';

            body.innerHTML = `
                <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                    <p class="health-section-title">Add deworming record</p>
                    <form id="dewormingForm" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        ${healthFormFieldHtml('Date given', `<input type="date" name="given_on" value="${today}" max="${today}" required class="${inputClass}">`)}
                        ${healthFormFieldHtml('Dose received', doseReceivedSelectHtml('', inputClass))}
                        <div class="sm:col-span-2">
                            <button type="submit" class="health-form-submit h-10 px-4 rounded-lg bg-primary text-white text-sm font-semibold">Save deworming</button>
                        </div>
                    </form>
                </div>
                <div class="mt-4">
                    <p class="health-section-title">Deworming history</p>
                    <div class="health-records-mobile">
                        ${dewormingLogs.length
                            ? dewormingLogs.map((log) => renderDewormingLogCard(log, today)).join('')
                            : healthRecordsEmptyHtml('No deworming records yet.')}
                    </div>
                    <div class="health-records-desktop overflow-x-auto rounded-lg border border-border-light dark:border-border-dark">
                        <table class="w-full min-w-[420px]">
                            <thead><tr class="border-b border-border-light dark:border-border-dark">
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Date given</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Dose received</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Action</th>
                            </tr></thead>
                            <tbody>
                                ${dewormingLogs.length ? dewormingLogs.map((log) => `
                                    <tr class="border-b border-border-light dark:border-border-dark">
                                        <td class="px-3 py-2 text-sm">${log.given_on}</td>
                                        <td class="px-3 py-2 text-sm">${log.medicine || '—'}</td>
                                        <td class="px-3 py-2 text-sm">
                                            <div class="inline-flex items-center gap-1.5">
                                                <button type="button" class="weight-edit-icon-btn vita-toggle-edit" data-panel="deworm-desk-edit-${log.id}" aria-label="Edit deworming record" title="Edit">
                                                    <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                                                </button>
                                                <button type="button" class="weight-edit-icon-btn is-danger deworming-delete-btn" data-log-id="${log.id}" data-label="${escapeHtml(log.given_on || '')}" aria-label="Delete deworming record" title="Delete">
                                                    <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="vita-edit-row border-b border-border-light dark:border-border-dark bg-background-light/60 dark:bg-background-dark/40" id="deworm-desk-edit-${log.id}">
                                        <td colspan="3" class="px-3 py-3">
                                            <form class="deworming-edit-form grid grid-cols-1 sm:grid-cols-3 gap-3 items-end" data-log-id="${log.id}">
                                                ${healthFormFieldHtml('Date given', `<input type="date" name="given_on" value="${escapeHtml(log.given_on_raw || '')}" max="${today}" required class="${inputClass}">`)}
                                                ${healthFormFieldHtml('Dose received', doseReceivedSelectHtml(log.medicine || '', inputClass))}
                                                <div class="flex gap-2">
                                                    <button type="submit" class="vita-btn vita-btn-primary">Save</button>
                                                    <button type="button" class="vita-btn vita-btn-ghost vita-cancel-edit" data-panel="deworm-desk-edit-${log.id}">Cancel</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                `).join('') : `<tr><td colspan="3" class="px-3 py-4 text-sm text-center text-text-light/60">No deworming records yet.</td></tr>`}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            document.getElementById('dewormingForm')?.addEventListener('submit', async (event) => {
                event.preventDefault();
                const form = event.target;
                try {
                    activeHealthData = await postHealth(healthRoute(bnsHealthRoutes.deworming, activeChildKey), {
                        given_on: form.given_on.value,
                        medicine: form.medicine.value || null,
                    });
                    renderHealthTab();
                } catch (error) {
                    alert(error.message);
                }
            });

            bindDewormingForms();
            return;
        }

        if (activeHealthTab === 'vitamin-a') {
            const eligible = data.eligible_vitamin_a;
            const vitaminSchedule = data.vitamin_a_schedule || [];
            const birthMin = (data.child && data.child.birthdate) ? String(data.child.birthdate).slice(0, 10) : '';
            const needsCatchUp = vitaminSchedule.some((row) => row.status === 'missed' || row.status === 'due');
            const canRecord = eligible || needsCatchUp;
            const inputClass = 'w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 text-sm';
            const dateInputClass = `${inputClass} font-semibold text-base h-11`;

            body.innerHTML = `
                <div class="vita-tip">
                    <span class="material-symbols-outlined vita-tip-icon" aria-hidden="true">calendar_month</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-text-light dark:text-text-dark">Record the date Vitamin A was given</p>
                        <p class="text-xs text-text-light/70 dark:text-text-dark/70 mt-0.5 leading-relaxed">Rounds at 6, 12, 18, 24, 30, 36, 42, 48, and 54 months. The schedule below is also the giving history—use <strong>Add date</strong> or <strong>Edit date</strong> when needed.</p>
                    </div>
                </div>

                <div class="rounded-xl border border-border-light dark:border-border-dark p-4 ${canRecord ? '' : 'opacity-70'}">
                    <p class="health-section-title">Give Vitamin A</p>
                    ${canRecord
                        ? ''
                        : `<p class="text-sm text-text-light/70 dark:text-text-dark/70 mb-3">This child is not yet eligible (must be 6–59 months old).</p>`}
                    <form id="vitaminAForm" class="grid grid-cols-1 gap-3" ${canRecord ? '' : 'inert'}>
                        <div class="health-form-field vita-date-field">
                            <span class="health-form-label">Date given</span>
                            <input type="date" name="given_on" value="${today}" min="${escapeHtml(birthMin)}" max="${today}" required class="${dateInputClass}">
                        </div>
                        ${healthFormFieldHtml('Dose received', doseReceivedSelectHtml('', inputClass))}
                        <div>
                            <button type="submit" class="health-form-submit h-11 px-4 rounded-lg bg-primary text-white text-sm font-semibold" ${canRecord ? '' : 'disabled'}>Save date given</button>
                        </div>
                    </form>
                </div>

                <div class="mt-1">
                    <p class="health-section-title">Vitamin A schedule & history</p>
                    <div class="health-records-mobile mb-4">
                        ${vitaminSchedule.length
                            ? vitaminSchedule.map((row) => renderVitaminScheduleCard(row, birthMin, today)).join('')
                            : healthRecordsEmptyHtml('No schedule available.')}
                    </div>
                    <div class="health-records-desktop overflow-x-auto rounded-lg border border-border-light dark:border-border-dark mb-4">
                        <table class="w-full min-w-[640px]">
                            <thead><tr class="border-b border-border-light dark:border-border-dark">
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Due at</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Due date</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Date given</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Dose received</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Action</th>
                            </tr></thead>
                            <tbody>
                                ${vitaminSchedule.map((row) => {
                                    const canCatchUp = row.status === 'missed' || row.status === 'due';
                                    const canEdit = row.status === 'given' && row.log_id;
                                    let action = '—';
                                    if (canEdit) {
                                        action = `<button type="button" class="vita-btn vita-btn-secondary vita-toggle-edit" data-panel="vita-desk-edit-${row.due_month}">Edit</button>`;
                                    } else if (canCatchUp) {
                                        action = `<button type="button" class="vita-btn vita-btn-primary vita-toggle-edit" data-panel="vita-desk-add-${row.due_month}">Add date</button>`;
                                    }
                                    const defaultDate = row.given_on_raw || (canCatchUp ? (row.due_date_raw && row.due_date_raw <= today ? row.due_date_raw : today) : today);
                                    let panel = '';
                                    if (canEdit) {
                                        panel = `
                                            <tr class="vita-edit-row border-b border-border-light dark:border-border-dark bg-background-light/60 dark:bg-background-dark/40" id="vita-desk-edit-${row.due_month}">
                                                <td colspan="6" class="px-3 py-3">
                                                    <form class="vitamin-a-edit-form grid grid-cols-1 sm:grid-cols-3 gap-3 items-end" data-log-id="${row.log_id}">
                                                        <div class="health-form-field vita-date-field">
                                                            <span class="health-form-label">Date given</span>
                                                            <input type="date" name="given_on" value="${escapeHtml(defaultDate || '')}" min="${escapeHtml(birthMin)}" max="${today}" required class="${dateInputClass}">
                                                        </div>
                                                        <div class="health-form-field">
                                                            <span class="health-form-label">Dose received</span>
                                                            ${doseReceivedSelectHtml(row.notes || '', inputClass)}
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="submit" class="vita-btn vita-btn-primary">Save</button>
                                                            <button type="button" class="vita-btn vita-btn-ghost vita-cancel-edit" data-panel="vita-desk-edit-${row.due_month}">Cancel</button>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        `;
                                    } else if (canCatchUp) {
                                        panel = `
                                            <tr class="vita-edit-row border-b border-border-light dark:border-border-dark bg-background-light/60 dark:bg-background-dark/40" id="vita-desk-add-${row.due_month}">
                                                <td colspan="6" class="px-3 py-3">
                                                    <form class="vitamin-a-create-form grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                                        <div class="health-form-field vita-date-field">
                                                            <span class="health-form-label">Date given</span>
                                                            <input type="date" name="given_on" value="${escapeHtml(defaultDate || today)}" min="${escapeHtml(birthMin)}" max="${today}" required class="${dateInputClass}">
                                                        </div>
                                                        <div class="health-form-field">
                                                            <span class="health-form-label">Dose received</span>
                                                            ${doseReceivedSelectHtml('', inputClass)}
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="submit" class="vita-btn vita-btn-primary">Save</button>
                                                            <button type="button" class="vita-btn vita-btn-ghost vita-cancel-edit" data-panel="vita-desk-add-${row.due_month}">Cancel</button>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        `;
                                    }
                                    return `
                                        <tr class="border-b border-border-light dark:border-border-dark">
                                            <td class="px-3 py-2 text-sm font-semibold">${row.due_month} months</td>
                                            <td class="px-3 py-2 text-sm">${row.due_date}</td>
                                            <td class="px-3 py-2 text-sm"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${immunizationBadgeClass(row.status === 'given' ? 'given' : row.status === 'missed' ? 'missed' : 'pending')}">${vitaminStatusLabel(row.status)}</span></td>
                                            <td class="px-3 py-2 text-sm">${row.given_on || '—'}</td>
                                            <td class="px-3 py-2 text-sm">${row.notes || '—'}</td>
                                            <td class="px-3 py-2 text-sm">${action}</td>
                                        </tr>
                                        ${panel}
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            bindVitaminAForms();
            return;
        }

        if (activeHealthTab === 'immunization') {
            const summary = data.immunization_summary || {};
            const progress = summary.total ? Math.round((summary.given / summary.total) * 100) : 0;

            body.innerHTML = `
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
                    <div class="rounded-xl border border-primary/20 bg-primary/5 p-3 sm:p-4">
                        <div class="flex items-start gap-2 sm:gap-3">
                            <span class="material-symbols-outlined text-primary text-xl shrink-0">info</span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-text-light dark:text-text-dark mb-1">How to record immunization</p>
                                <ol class="text-xs text-text-light/80 dark:text-text-dark/80 space-y-1 list-decimal list-inside">
                                    <li><strong>Given</strong> — enter date administered, then save.</li>
                                    <li><strong>Missed</strong> — child did not receive by due date.</li>
                                    <li><strong>Pending</strong> — not yet due or not yet given.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-border-light dark:border-border-dark p-3 sm:p-4">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60">Progress</p>
                                <p class="text-base font-bold mt-0.5">${summary.fully_immunized ? 'Fully immunized' : 'Incomplete'}</p>
                            </div>
                            <p class="text-xl font-bold text-primary">${summary.given || 0}<span class="text-sm text-text-light/50 dark:text-text-dark/50 font-semibold"> / ${summary.total || 0}</span></p>
                        </div>
                        <div class="h-2 rounded-full bg-background-light dark:bg-background-dark overflow-hidden mb-2">
                            <div class="h-full rounded-full bg-secondary transition-all" style="width: ${progress}%"></div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-lg bg-secondary/10 px-2 py-1.5"><p class="text-[10px] uppercase font-semibold text-secondary">Given</p><p class="text-base font-bold text-secondary">${summary.given || 0}</p></div>
                            <div class="rounded-lg bg-accent-orange/10 px-2 py-1.5"><p class="text-[10px] uppercase font-semibold text-accent-orange">Pending</p><p class="text-base font-bold text-accent-orange">${summary.pending || 0}</p></div>
                            <div class="rounded-lg bg-accent-red/10 px-2 py-1.5"><p class="text-[10px] uppercase font-semibold text-accent-red">Missed</p><p class="text-base font-bold text-accent-red">${summary.missed || 0}</p></div>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mb-3">DOH EPI schedule (0–59 months). Update each vaccine record below.</p>

                <div class="immunization-schedule-rows">
                    ${renderImmunizationScheduleHtml(data.immunizations || [], today)}
                </div>
            `;

            bindImmunizationForms();
        }
    }

    function updateMasterlistRowSummary(childKey, data) {
        const row = document.querySelector(`.masterlist-row[data-child-key="${CSS.escape(childKey)}"]`);
        if (!row || !data) return;

        const immCell = row.querySelector('.child-health-status-cell');
        if (immCell) {
            immCell.innerHTML = healthStatusRowHtml(data);
        }

        if (activeHealthData?.child) {
            renderChildProfile(activeHealthData.child, activeHealthData);
        }
    }

    async function openHealthModal(childKey) {
        activeChildKey = childKey;
        activeHealthTab = 'weight';
        document.querySelectorAll('.health-tab').forEach((tab) => {
            tab.classList.toggle('active', tab.dataset.tab === 'weight');
        });

        const modal = document.getElementById('healthModal');
        modal?.classList.add('active');
        document.getElementById('healthModalProfile')?.classList.add('hidden');
        document.getElementById('healthModalSubtitle').textContent = 'Loading profile...';
        document.getElementById('healthModalBody').innerHTML = '<p class="text-sm text-text-light/70 dark:text-text-dark/70">Loading health record...</p>';

        try {
            activeHealthData = await fetchHealth(childKey);
            const child = activeHealthData.child || {};
            renderChildProfile(child, activeHealthData);
            renderHealthTab();
        } catch (error) {
            document.getElementById('healthModalBody').innerHTML = `<p class="text-sm text-accent-red">${escapeHtml(error.message)}</p>`;
        }
    }

    function closeHealthModal() {
        document.getElementById('healthModal')?.classList.remove('active');
        const details = document.getElementById('profileInfoDetails');
        const toggle = document.getElementById('profileInfoToggle');
        details?.classList.remove('is-open');
        toggle?.setAttribute('aria-expanded', 'false');
        activeChildKey = null;
        activeHealthData = null;
    }

    document.getElementById('profileInfoToggle')?.addEventListener('click', () => {
        const details = document.getElementById('profileInfoDetails');
        const toggle = document.getElementById('profileInfoToggle');
        if (!details || !toggle) return;

        const willOpen = !details.classList.contains('is-open');
        details.classList.toggle('is-open', willOpen);
        toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });

    document.querySelectorAll('.health-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            activeHealthTab = tab.dataset.tab || 'weight';
            document.querySelectorAll('.health-tab').forEach((item) => {
                item.classList.toggle('active', item === tab);
            });
            renderHealthTab();
        });
    });

    document.getElementById('closeHealthModal')?.addEventListener('click', closeHealthModal);
    document.getElementById('healthModal')?.addEventListener('click', (event) => {
        if (event.target === event.currentTarget) closeHealthModal();
    });

    document.getElementById('dewormingDeleteCancel')?.addEventListener('click', closeDewormingDeleteModal);
    document.getElementById('dewormingDeleteModal')?.addEventListener('click', (event) => {
        if (event.target === event.currentTarget) closeDewormingDeleteModal();
    });
    document.getElementById('dewormingDeleteConfirm')?.addEventListener('click', async () => {
        if (!pendingDewormingDeleteId || !activeChildKey) return;
        const confirmBtn = document.getElementById('dewormingDeleteConfirm');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Deleting...';
        }
        try {
            activeHealthData = await deleteHealth(healthRoute(bnsHealthRoutes.dewormingDelete, activeChildKey, pendingDewormingDeleteId));
            closeDewormingDeleteModal();
            renderHealthTab();
        } catch (error) {
            alert(error.message);
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Delete';
            }
        }
    });

    document.getElementById('immunizationNotifyCancel')?.addEventListener('click', closeImmunizationNotifyModal);
    document.getElementById('immunizationNotifyModal')?.addEventListener('click', (event) => {
        if (event.target === event.currentTarget) closeImmunizationNotifyModal();
    });
    document.getElementById('immunizationNotifyConfirm')?.addEventListener('click', confirmImmunizationNotify);

    document.getElementById('monitoringTableBody')?.addEventListener('click', (event) => {
        const button = event.target.closest('.view-profile-btn');
        if (button?.dataset.childKey) {
            openHealthModal(button.dataset.childKey);
        }
    });
</script>

<script>
    const toggleFiltersBtn = document.getElementById('toggleFiltersBtn');
    const filtersPanel = document.getElementById('filtersPanel');
    const monitoringNoResults = document.getElementById('monitoringNoResults');

    toggleFiltersBtn?.addEventListener('click', () => {
        const isOpen = filtersPanel?.classList.contains('open');
        filtersPanel?.classList.toggle('open', !isOpen);
        filtersPanel?.classList.toggle('collapsed', isOpen);
        toggleFiltersBtn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        toggleFiltersBtn.classList.toggle('active', !isOpen);
    });

    if (window.innerWidth >= 768) {
        filtersPanel?.classList.add('open');
        filtersPanel?.classList.remove('collapsed');
    }

    const masterlistSearch = document.getElementById('masterlistSearch');
    const masterlistPurokFilter = document.getElementById('masterlistPurokFilter');
    const masterlistGenderFilter = document.getElementById('masterlistGenderFilter');
    const masterlistAgeGroupFilter = document.getElementById('masterlistAgeGroupFilter');
    const statVisible = document.getElementById('statVisible');
    const masterlistRows = Array.from(document.querySelectorAll('.masterlist-row'));

    function filterMasterlist() {
        collapseMasterlistRows();

        const query = (masterlistSearch?.value || '').trim().toLowerCase();
        const purok = masterlistPurokFilter?.value || '';
        const gender = masterlistGenderFilter?.value || '';
        const ageGroup = masterlistAgeGroupFilter?.value || '';
        let visibleCount = 0;

        masterlistRows.forEach((row) => {
            const matchesSearch = !query || (row.dataset.search || '').includes(query);
            const matchesPurok = !purok || row.dataset.purok === purok;
            const matchesGender = !gender || row.dataset.gender === gender;
            const matchesAgeGroup = !ageGroup || row.dataset.ageGroup === ageGroup;
            const visible = matchesSearch && matchesPurok && matchesGender && matchesAgeGroup;

            row.classList.toggle('hidden', !visible);

            if (visible) {
                visibleCount += 1;
            }
        });

        if (monitoringNoResults) {
            monitoringNoResults.classList.toggle('hidden', visibleCount > 0 || masterlistRows.length === 0);
        }

        if (statVisible) {
            statVisible.textContent = String(visibleCount);
        }
    }

    masterlistSearch?.addEventListener('input', filterMasterlist);
    masterlistPurokFilter?.addEventListener('change', filterMasterlist);
    masterlistGenderFilter?.addEventListener('change', filterMasterlist);
    masterlistAgeGroupFilter?.addEventListener('change', filterMasterlist);

    const mobileMonitoringQuery = window.matchMedia('(max-width: 767px)');

    function collapseMasterlistRows() {
        document.querySelectorAll('.masterlist-row.is-expanded').forEach((row) => {
            row.classList.remove('is-expanded');
            row.querySelector('.masterlist-mobile-toggle')?.setAttribute('aria-expanded', 'false');
        });
    }

    function toggleMasterlistRow(row) {
        if (!mobileMonitoringQuery.matches) {
            return;
        }

        const isExpanded = row.classList.contains('is-expanded');

        collapseMasterlistRows();

        if (!isExpanded) {
            row.classList.add('is-expanded');
            row.querySelector('.masterlist-mobile-toggle')?.setAttribute('aria-expanded', 'true');
        }
    }

    document.querySelectorAll('.masterlist-mobile-toggle').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const row = button.closest('.masterlist-row');
            if (row) {
                toggleMasterlistRow(row);
            }
        });
    });

    mobileMonitoringQuery.addEventListener('change', (event) => {
        if (!event.matches) {
            collapseMasterlistRows();
        }
    });
</script>
</body>
</html>
