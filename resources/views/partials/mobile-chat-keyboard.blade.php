{{--
  Keeps messenger shells above the mobile keyboard.
  Mark the page shell with data-chat-keyboard-shell.
  Message lists use .rbhw-chat-scroll (or data-chat-scroll).
--}}
<style>
    @media (max-width: 1023px) {
        html.chat-keyboard-active,
        html.chat-keyboard-active body {
            overflow: hidden !important;
            overscroll-behavior: none;
            height: 100%;
        }

        [data-chat-keyboard-shell] {
            height: var(--chat-vv-height, 100dvh) !important;
            max-height: var(--chat-vv-height, 100dvh) !important;
            min-height: 0 !important;
            overflow: hidden !important;
            transition: none;
        }
    }
</style>
<script>
(function () {
    const DESKTOP_MQ = window.matchMedia('(min-width: 1024px)');
    let rafId = 0;
    let lastHeight = 0;

    function shell() {
        return document.querySelector('[data-chat-keyboard-shell]');
    }

    function messagePanes() {
        return document.querySelectorAll('[data-chat-scroll], .rbhw-chat-scroll');
    }

    function scrollMessagesToBottom() {
        messagePanes().forEach(function (pane) {
            const apply = function () {
                pane.scrollTop = pane.scrollHeight;
            };
            apply();
            window.requestAnimationFrame(function () {
                apply();
                window.requestAnimationFrame(apply);
            });
        });
    }

    function clearShellInline() {
        const el = shell();
        if (!el) return;
        el.style.removeProperty('height');
        el.style.removeProperty('max-height');
        el.style.removeProperty('min-height');
        el.style.removeProperty('transform');
        document.documentElement.style.removeProperty('--chat-vv-height');
        document.documentElement.classList.remove('chat-keyboard-active');
    }

    function syncShellToVisualViewport() {
        if (DESKTOP_MQ.matches) {
            clearShellInline();
            return;
        }

        const el = shell();
        if (!el) return;

        const vv = window.visualViewport;
        const height = Math.round(vv ? vv.height : window.innerHeight);
        const offsetTop = vv ? Math.max(0, Math.round(vv.offsetTop)) : 0;

        document.documentElement.classList.add('chat-keyboard-active');
        document.documentElement.style.setProperty('--chat-vv-height', height + 'px');
        el.style.height = height + 'px';
        el.style.maxHeight = height + 'px';
        el.style.minHeight = height + 'px';
        el.style.transform = offsetTop ? 'translateY(' + offsetTop + 'px)' : '';

        const heightChanged = Math.abs(height - lastHeight) > 8;
        lastHeight = height;

        if (heightChanged || document.activeElement?.matches?.('textarea, input')) {
            scrollMessagesToBottom();
            window.setTimeout(scrollMessagesToBottom, 60);
            window.setTimeout(scrollMessagesToBottom, 180);
            window.setTimeout(scrollMessagesToBottom, 320);
        }
    }

    function scheduleSync() {
        if (rafId) cancelAnimationFrame(rafId);
        rafId = requestAnimationFrame(function () {
            rafId = 0;
            syncShellToVisualViewport();
        });
    }

    function onFocusIn(event) {
        const target = event.target;
        if (!target || !target.matches) return;
        if (!target.matches('textarea, input:not([type="file"]):not([type="checkbox"]):not([type="radio"])')) return;
        scheduleSync();
        window.setTimeout(scrollMessagesToBottom, 50);
        window.setTimeout(scrollMessagesToBottom, 200);
        window.setTimeout(scrollMessagesToBottom, 400);
    }

    function bind() {
        scheduleSync();

        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', scheduleSync);
            window.visualViewport.addEventListener('scroll', scheduleSync);
        }
        window.addEventListener('resize', scheduleSync);
        window.addEventListener('orientationchange', function () {
            window.setTimeout(scheduleSync, 100);
            window.setTimeout(scheduleSync, 350);
        });
        document.addEventListener('focusin', onFocusIn);

        if (typeof DESKTOP_MQ.addEventListener === 'function') {
            DESKTOP_MQ.addEventListener('change', scheduleSync);
        } else if (typeof DESKTOP_MQ.addListener === 'function') {
            DESKTOP_MQ.addListener(scheduleSync);
        }

        window.ChatKeyboard = {
            sync: syncShellToVisualViewport,
            scrollToBottom: scrollMessagesToBottom,
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();
</script>
