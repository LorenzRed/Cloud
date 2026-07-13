<style>
    .bk-skeleton {
        position: relative;
        overflow: hidden;
        background: #e7f0ea;
        border-radius: 0.5rem;
    }

    .dark .bk-skeleton {
        background: #2a3740;
    }

    .bk-skeleton::after {
        content: '';
        position: absolute;
        inset: 0;
        transform: translateX(-100%);
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.45),
            transparent
        );
        animation: bk-skeleton-shimmer 1.25s ease-in-out infinite;
    }

    .dark .bk-skeleton::after {
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.08),
            transparent
        );
    }

    @keyframes bk-skeleton-shimmer {
        100% {
            transform: translateX(100%);
        }
    }

    .bk-skeleton-line {
        height: 0.75rem;
        border-radius: 9999px;
    }

    .bk-skeleton-block {
        border-radius: 0.75rem;
    }
</style>
