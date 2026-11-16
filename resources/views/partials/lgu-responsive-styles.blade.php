<style>
    body.lgu-layout main {min-width:0;}
    body.lgu-layout #sidebar {flex-shrink:0;overflow-y:auto;height:100dvh;position:sticky;top:0;}
    .lgu-mobile-header,.lgu-mobile-nav {display:none;}
    @media screen and (max-width:1023px) {
        html:has(body.lgu-layout) {height:auto;overflow-x:clip;}
        body.lgu-layout {height:auto;min-height:100dvh;overflow:visible;}
        body.lgu-layout #sidebar,body.lgu-layout .report-sidebar {display:none!important;}
        body.lgu-layout .main-container {height:auto;min-height:100dvh;}
        body.lgu-layout main {height:auto;min-height:100dvh;width:100%;margin:0;padding:calc(80px + env(safe-area-inset-top,0px)) 16px calc(96px + env(safe-area-inset-bottom,0px));overflow:visible;}
        body.lgu-layout main .page-content {height:auto;overflow:visible;}
        body.lgu-layout main.lgu-barangays-main {padding-left:0;padding-right:0;}
        body.lgu-layout main.lgu-barangays-main>div {overflow:visible;padding-top:0;padding-bottom:0;}
        body.lgu-layout .mobile-back,body.lgu-layout .lgu-back-link {display:none;}
        body.lgu-layout main h1,body.lgu-layout main .text-3xl {font-size:24px;line-height:1.3;}
        body.lgu-layout input,body.lgu-layout select,body.lgu-layout textarea {font-size:16px;max-width:100%;}
        body.lgu-layout main button {min-height:44px;}
        .lgu-mobile-header {position:fixed;inset:0 0 auto;z-index:35;display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:64px;padding:calc(10px + env(safe-area-inset-top,0px)) 16px 10px;background:#fff;border-bottom:1px solid #e5e7eb;}
        .lgu-mobile-brand {display:flex;align-items:center;gap:10px;min-width:0;}
        .lgu-mobile-brand img {width:32px;height:32px;flex-shrink:0;}
        .lgu-mobile-brand strong {display:block;font-size:13px;color:#1f2937;}
        .lgu-mobile-brand p {max-width:58vw;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;color:#64748b;margin-top:2px;}
        .lgu-mobile-logout {display:flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:10px;color:#64748b;}
        .lgu-mobile-logout:hover {background:#f1f5f9;}
        .lgu-mobile-nav {display:grid;grid-template-columns:repeat(5,minmax(0,1fr));position:fixed;inset:auto 0 0;z-index:40;padding:6px 6px calc(6px + env(safe-area-inset-bottom,0px));border-top:1px solid #e5e7eb;background:#fffffff5;backdrop-filter:blur(12px);}
        .lgu-mobile-nav a {display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;min-height:54px;border-radius:10px;margin:0 2px;font-size:10px;font-weight:700;line-height:1.2;text-decoration:none;color:#64748b;}
        .lgu-mobile-nav .material-symbols-outlined {font-size:23px;}
        .lgu-mobile-nav a[aria-current="page"] {color:#287dbb;background:#eaf3fc;}
        .lgu-mobile-nav a[aria-current="page"] .material-symbols-outlined {font-variation-settings:'FILL' 1;}
        .lgu-mobile-nav a:focus-visible,.lgu-mobile-logout:focus-visible {outline:3px solid #93c5fd;outline-offset:1px;}
        body.lgu-keyboard-open .lgu-mobile-nav {display:none;}
        .dark .lgu-mobile-header,.dark .lgu-mobile-nav {background:#1f2937f5;border-color:#374151;}
        .dark .lgu-mobile-brand strong {color:#f3f4f6;}
        .dark .lgu-mobile-brand p,.dark .lgu-mobile-nav a,.dark .lgu-mobile-logout {color:#b6c3d1;}
        .dark .lgu-mobile-nav a[aria-current="page"] {background:#263c52;color:#93c5fd;}
        .lgu-mobile-table,.lgu-mobile-table tbody,.lgu-mobile-table tr,.lgu-mobile-table td {display:block;width:100%;}
        .lgu-mobile-table thead {display:none;}
        .lgu-mobile-table tbody tr {padding:14px 16px;}
        .lgu-mobile-table td {padding:7px 0;white-space:normal;overflow-wrap:anywhere;border:0;}
        .lgu-mobile-table td[data-label]::before {content:attr(data-label);display:block;margin-bottom:4px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#71808e;}
        .lgu-mobile-table td[colspan] {padding:24px 0;}
    }
    @media print {.lgu-mobile-header,.lgu-mobile-nav {display:none!important;}}
</style>
