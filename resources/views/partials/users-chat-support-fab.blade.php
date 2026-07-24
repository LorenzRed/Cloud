{{-- Fixed chat-support launcher: above Profile on mobile, bottom-left on desktop. --}}
<style>
    #usersChatSupportFab {
        position: fixed;
        z-index: 45;
        width: 3.25rem;
        height: 3.25rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: linear-gradient(145deg, #39e079 0%, #1f9d4f 100%);
        box-shadow:
            0 10px 24px rgba(15, 118, 55, 0.35),
            0 0 0 3px rgba(57, 224, 121, 0.25);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
        animation: usersChatFabEnter 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
        /* Mobile: sit above the Profile tab (rightmost bottom-nav item) */
        right: max(0.45rem, calc(10% - 1.625rem));
        left: auto;
        bottom: calc(4.75rem + env(safe-area-inset-bottom, 0px));
        cursor: pointer;
        text-decoration: none;
    }

    #usersChatSupportFab:hover {
        box-shadow:
            0 12px 26px rgba(15, 118, 55, 0.42),
            0 0 0 3px rgba(57, 224, 121, 0.35);
        transform: translateY(-1px);
    }

    #usersChatSupportFab:active {
        transform: scale(0.96);
    }

    @media (min-width: 1024px) {
        #usersChatSupportFab {
            left: auto;
            right: 1.5rem;
            bottom: 1.5rem;
            width: 3.5rem;
            height: 3.5rem;
        }
    }

    #usersChatSupportFab.is-launching {
        pointer-events: none;
        animation: usersChatFabLaunch 0.55s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    #usersChatSupportFab .fab-pulse {
        position: absolute;
        inset: -4px;
        border-radius: inherit;
        border: 2px solid rgba(57, 224, 121, 0.55);
        animation: usersChatFabPulse 2s ease-out infinite;
        pointer-events: none;
    }

    #usersChatSupportFab .material-symbols-outlined {
        font-size: 1.55rem;
        line-height: 1;
        font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
    }

    @media (min-width: 1024px) {
        #usersChatSupportFab .material-symbols-outlined {
            font-size: 1.65rem;
        }
    }

    #usersChatSupportFab .fab-label {
        position: absolute;
        white-space: nowrap;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        color: #0e1b13;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #d0e7d9;
        border-radius: 9999px;
        padding: 0.28rem 0.65rem;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s ease, transform 0.2s ease;
        /* Mobile: label to the left of the button (button is over Profile) */
        right: calc(100% + 0.5rem);
        left: auto;
        transform: translateX(6px);
    }

    @media (min-width: 1024px) {
        #usersChatSupportFab .fab-label {
            right: calc(100% + 0.55rem);
            left: auto;
            transform: translateX(6px);
        }
    }

    .dark #usersChatSupportFab .fab-label {
        color: #f3f4f6;
        background: rgba(31, 41, 55, 0.95);
        border-color: #4b5563;
    }

    #usersChatSupportFab:hover .fab-label,
    #usersChatSupportFab:focus-visible .fab-label {
        opacity: 1;
        transform: translateX(0);
    }

    @keyframes usersChatFabEnter {
        from {
            opacity: 0;
            transform: translateY(18px) scale(0.85);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes usersChatFabPulse {
        0% {
            transform: scale(1);
            opacity: 0.7;
        }
        70% {
            transform: scale(1.35);
            opacity: 0;
        }
        100% {
            transform: scale(1.35);
            opacity: 0;
        }
    }

    @keyframes usersChatFabLaunch {
        0% {
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
        35% {
            transform: scale(1.18) rotate(-8deg);
            opacity: 1;
        }
        100% {
            transform: scale(0.35) rotate(12deg) translateY(-28px);
            opacity: 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #usersChatSupportFab,
        #usersChatSupportFab .fab-pulse,
        #usersChatSupportFab.is-launching {
            animation: none !important;
        }
    }
</style>

<a
    id="usersChatSupportFab"
    href="{{ route('users.profile.chat-support') }}"
    aria-label="Open chat support"
    title="Chat support"
>
    <span class="fab-pulse" aria-hidden="true"></span>
    <span class="material-symbols-outlined" aria-hidden="true">support_agent</span>
    <span class="fab-label">Chat support</span>
</a>

<script>
(function () {
    const fab = document.getElementById('usersChatSupportFab');
    if (!fab) return;

    const chatUrl = fab.getAttribute('href');

    try {
        localStorage.removeItem('usersChatSupportFabPos');
    } catch (e) {}

    function launchChat(event) {
        if (event) event.preventDefault();
        if (fab.classList.contains('is-launching')) return;

        fab.classList.add('is-launching');
        window.setTimeout(function () {
            window.location.href = chatUrl;
        }, 480);
    }

    fab.addEventListener('click', launchChat);

    fab.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            launchChat(event);
        }
    });
})();
</script>
