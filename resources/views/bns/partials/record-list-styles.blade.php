<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }

    main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }

    .record-page-content {
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .record-page-layout {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        width: 100%;
        max-width: 80rem;
        margin: 0 auto;
    }

    .record-panel {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    .record-toolbar { flex-shrink: 0; margin-bottom: 0.75rem; }

    .record-toolbar-row {
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
        width: 100%;
    }

    .record-search-wrap {
        display: flex;
        align-items: stretch;
        gap: 0.5rem;
        width: 100%;
        min-width: 0;
    }

    .record-page-title {
        font-size: 1.125rem;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -0.01em;
    }
    @media (min-width: 768px) {
        .record-page-title { font-size: 1.25rem; }
    }

    #filtersPanel {
        display: none;
    }
    #filtersPanel.open { display: flex; }
    @media (min-width: 768px) {
        .record-toolbar-row {
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: flex-end;
            gap: 0.75rem;
            width: 100%;
        }

        .record-search-wrap {
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

    .filter-field {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 0;
    }

    .filter-field label {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: rgba(74, 74, 74, 0.55);
    }
    .dark .filter-field label { color: rgba(224, 224, 224, 0.55); }

    .filter-control {
        height: 2.25rem;
        min-width: 0;
        padding: 0 0.5rem;
        border-radius: 0.5rem;
        border: 1px solid #EAEAEA;
        background: #FFFFFF;
        font-size: 0.8125rem;
    }
    .dark .filter-control {
        border-color: #3D3D3D;
        background: #1A1A1A;
        color: #E0E0E0;
    }
    @media (min-width: 768px) {
        .filter-control { width: auto; min-width: 7.5rem; }
        .filter-field {
            flex-direction: row;
            align-items: center;
            gap: 0.5rem;
        }
        .filter-field label {
            text-transform: none;
            letter-spacing: 0;
            font-size: 0.75rem;
            white-space: nowrap;
        }
        .filter-field--purok .filter-control { width: 8.5rem; }
        .filter-field--date .filter-control { width: 9.5rem; }
    }
    @media (max-width: 767px) {
        .filter-control { width: 100%; }
    }

    #toggleFiltersBtn.active {
        border-color: #4A90E2;
        background: rgba(74, 144, 226, 0.1);
        color: #4A90E2;
    }

    .record-export-actions {
        display: flex;
        flex-direction: row;
        gap: 0.5rem;
        width: 100%;
        flex-basis: 100%;
    }

    .record-export-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        flex: 1;
        min-width: 0;
        min-height: 2.5rem;
        padding: 0 0.75rem;
        border-radius: 0.75rem;
        font-size: 0.8125rem;
        font-weight: 600;
    }

    .record-export-btn:disabled,
    .record-export-btn[aria-disabled="true"] {
        opacity: 0.45;
        cursor: not-allowed;
        pointer-events: none;
    }

    @media (max-width: 767px) {
        .record-export-btn {
            font-size: 0.75rem;
            padding: 0 0.5rem;
        }
        .record-export-btn .material-symbols-outlined {
            font-size: 1.125rem;
        }
    }

    .record-table-wrap {
        flex: 1;
        min-height: 0;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 0.5rem;
        border: 1px solid #EAEAEA;
    }
    .dark .record-table-wrap { border-color: #3D3D3D; }

    .record-table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #FFFFFF;
    }
    .dark .record-table-wrap thead th { background: #2C2C2C; }

    .record-list-footer {
        flex-shrink: 0;
        padding-top: 0.75rem;
        margin-top: 0.75rem;
        border-top: 1px solid #EAEAEA;
    }
    .dark .record-list-footer { border-top-color: #3D3D3D; }

    @media (max-width: 1023px) {
        .record-page-content {
            height: auto;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        .record-panel {
            overflow: visible;
            flex: none;
        }
        .record-table-wrap {
            overflow: visible;
            flex: none;
        }
    }

    @media (max-width: 767px) {
        .record-page-layout {
            max-width: none;
            margin: 0;
        }

        .record-panel {
            padding: 0 !important;
            border: none;
            box-shadow: none;
            background: transparent !important;
            border-radius: 0;
        }

        .record-toolbar {
            padding: 0.75rem 1rem 0.625rem;
            margin-bottom: 0;
            background: #FFFFFF;
            border-bottom: 1px solid #EAEAEA;
        }
        .dark .record-toolbar {
            background: #2C2C2C;
            border-bottom-color: #3D3D3D;
        }

        .record-export-actions {
            padding-top: 0.25rem;
            gap: 0.625rem;
        }

        .record-table-wrap {
            border: none;
            margin-top: 0 !important;
        }
        .record-table-wrap table { min-width: 0 !important; }
        .record-table-wrap thead { display: none; }
        .record-table-wrap tbody {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .record-table-wrap .record-row {
            display: block;
            border: none;
            border-bottom: 1px solid #EAEAEA;
            border-radius: 0;
            background: #FFFFFF;
            padding: 0;
            box-shadow: none;
        }
        .dark .record-table-wrap .record-row {
            border-bottom-color: #3D3D3D;
            background: #2C2C2C;
        }

        .record-mobile-toggle {
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
        .record-mobile-toggle:active {
            background: rgba(74, 144, 226, 0.06);
        }

        .record-mobile-toggle .record-chevron {
            margin-left: auto;
            flex-shrink: 0;
            font-size: 1.25rem;
            color: rgba(74, 74, 74, 0.45);
            transition: transform 0.2s ease;
        }
        .dark .record-mobile-toggle .record-chevron {
            color: rgba(224, 224, 224, 0.45);
        }
        .record-row.is-expanded .record-chevron {
            transform: rotate(180deg);
        }

        .record-mobile-status {
            flex-shrink: 0;
            max-width: 9.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.6875rem;
            font-weight: 600;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            background: rgba(74, 144, 226, 0.1);
            color: #4A90E2;
        }
        .record-row.is-expanded .record-mobile-status {
            display: none;
        }

        .record-mobile-extra {
            display: none;
            padding: 0 1rem 0.625rem;
            font-size: 0.75rem;
            color: rgba(74, 74, 74, 0.65);
        }
        .dark .record-mobile-extra {
            color: rgba(224, 224, 224, 0.65);
        }
        .record-row.is-expanded .record-mobile-extra {
            display: block;
        }

        .record-row-details {
            display: none;
            padding: 0 1rem 0.875rem;
            border-top: 1px solid #EAEAEA;
        }
        .dark .record-row-details {
            border-top-color: #3D3D3D;
        }
        .record-row.is-expanded .record-row-details {
            display: block;
        }

        .record-detail-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.5rem 0;
            font-size: 0.8125rem;
        }
        .record-detail-item + .record-detail-item {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        .dark .record-detail-item + .record-detail-item {
            border-top-color: rgba(255, 255, 255, 0.06);
        }

        .record-detail-label {
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
        .dark .record-detail-label {
            color: rgba(224, 224, 224, 0.55);
        }

        .record-detail-value {
            flex: 1;
            min-width: 0;
            text-align: right;
            word-break: break-word;
        }

        .record-table-wrap .record-row td.record-desktop-cell {
            display: none !important;
        }

        .record-table-wrap .record-row td.record-mobile-cell {
            display: block;
            padding: 0;
            border: none;
        }

        .record-table-wrap .record-row.hidden {
            display: none !important;
        }

        #emptyRow td,
        #noResultsRow td {
            display: block !important;
            border: none;
            border-radius: 0;
            background: #FFFFFF;
            padding: 1.5rem 1rem !important;
        }
        .dark #emptyRow td,
        .dark #noResultsRow td {
            background: #2C2C2C;
        }
        #emptyRow td::before,
        #noResultsRow td::before {
            display: none !important;
        }

        .record-list-footer {
            border-top: 1px solid #EAEAEA;
            padding: 0.75rem 1rem 0.25rem;
            margin-top: 0;
            background: #FFFFFF;
        }
        .dark .record-list-footer {
            border-top-color: #3D3D3D;
            background: #2C2C2C;
        }
    }

    @media (min-width: 768px) {
        .record-mobile-cell,
        .record-mobile-toggle,
        .record-mobile-extra,
        .record-row-details { display: none !important; }
        .record-desktop-cell { display: table-cell !important; }
    }

    .record-giving-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        min-width: 0;
    }

    .record-giving-item {
        padding: 0.5rem 0.625rem;
        border-radius: 0.5rem;
        border: 1px solid #EAEAEA;
        background: rgba(244, 244, 244, 0.7);
    }

    .dark .record-giving-item {
        border-color: #3D3D3D;
        background: rgba(26, 26, 26, 0.45);
    }

    .record-detail-item--full {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
    }

    .record-detail-item--full .record-detail-label {
        margin-bottom: 0;
    }

    .record-detail-item--full .record-giving-list {
        width: 100%;
    }
</style>
