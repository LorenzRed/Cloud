<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    #bhwContent {
        min-height: 100dvh;
    }

    @media (min-width: 1024px) {
        html, body {
            overflow: hidden;
        }

        #bhwContent {
            height: 100vh;
        }
    }

    @media (max-width: 1023px) {
        html, body {
            overflow-x: hidden;
        }
    }

    @media print {
        #sidebar,
        #bhwMobileNav,
        .bhw-mobile-topbar {
            display: none !important;
        }

        #bhwContent {
            margin: 0 !important;
            width: 100% !important;
            min-height: auto !important;
            height: auto !important;
        }
    }
</style>
