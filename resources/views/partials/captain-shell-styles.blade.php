<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    #captainContent {
        min-height: 100dvh;
    }

    @media (min-width: 1024px) {
        html, body {
            overflow: hidden;
        }

        #captainContent {
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
        #captainMobileNav,
        .captain-mobile-topbar {
            display: none !important;
        }

        #captainContent {
            margin: 0 !important;
            width: 100% !important;
            min-height: auto !important;
            height: auto !important;
        }
    }
</style>
