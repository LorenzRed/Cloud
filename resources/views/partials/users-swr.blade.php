@auth
@if (Auth::user()->role === 'users')
<meta name="users-swr-uid" content="{{ Auth::id() }}" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<script>
(function () {
    if (window.__usersSwrInstalled) {
        return;
    }
    window.__usersSwrInstalled = true;

    const uid = document.querySelector('meta[name="users-swr-uid"]')?.content || 'anon';
    const csrfUrl = @json(url('/users/session/csrf'));

    function updateCsrfToken(token) {
        if (!token) {
            return;
        }
        let meta = document.querySelector('meta[name="csrf-token"]');
        if (!meta) {
            meta = document.createElement('meta');
            meta.setAttribute('name', 'csrf-token');
            document.head.appendChild(meta);
        }
        meta.setAttribute('content', token);
        document.querySelectorAll('input[name="_token"]').forEach((input) => {
            input.value = token;
        });
    }

    async function refreshCsrf() {
        try {
            const response = await fetch(csrfUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Users-SWR': 'revalidate',
                },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                return;
            }
            const data = await response.json();
            updateCsrfToken(data.token);
        } catch (error) {
            // ignore
        }
    }

    function samePage(urlString) {
        try {
            const next = new URL(urlString, window.location.origin);
            return next.pathname.replace(/\/+$/, '') === window.location.pathname.replace(/\/+$/, '');
        } catch (error) {
            return false;
        }
    }

    async function softRevalidate(urlString) {
        if (!samePage(urlString)) {
            return;
        }

        try {
            const response = await fetch(urlString, {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Users-SWR': 'revalidate',
                },
                credentials: 'same-origin',
                cache: 'no-store',
            });
            if (!response.ok) {
                return;
            }

            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const nextContent = doc.querySelector('#usersContent');
            const currentContent = document.querySelector('#usersContent');

            const nextToken =
                doc.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                doc.querySelector('input[name="_token"]')?.value;
            updateCsrfToken(nextToken);
            await refreshCsrf();

            if (!nextContent || !currentContent) {
                return;
            }

            if (currentContent.innerHTML === nextContent.innerHTML) {
                return;
            }

            const replacement = nextContent.cloneNode(true);
            currentContent.replaceWith(replacement);

            replacement.querySelectorAll('script').forEach((oldScript) => {
                const script = document.createElement('script');
                if (oldScript.src) {
                    script.src = oldScript.src;
                } else {
                    script.textContent = oldScript.textContent;
                }
                oldScript.replaceWith(script);
            });

            if (doc.title) {
                document.title = doc.title;
            }
        } catch (error) {
            // ignore soft refresh failures
        }
    }

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker
            .register(`/sw-users.js?uid=${encodeURIComponent(uid)}`, { scope: '/users' })
            .catch(() => {});

        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'users-swr-updated' && event.data.url) {
                softRevalidate(event.data.url);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        refreshCsrf();
    });

    document.addEventListener(
        'submit',
        (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }
            if (!/\/logout\/?$/i.test(form.action)) {
                return;
            }
            if (navigator.serviceWorker?.controller) {
                navigator.serviceWorker.controller.postMessage({ type: 'CLEAR_USERS_SWR' });
            }
            if (window.caches) {
                caches.keys().then((keys) => {
                    keys.filter((key) => key.startsWith('users-swr-')).forEach((key) => caches.delete(key));
                });
            }
        },
        true
    );
})();
</script>
@endif
@endauth
