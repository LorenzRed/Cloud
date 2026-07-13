<script>
(function () {
    if (window.__submitGuardInstalled) {
        return;
    }
    window.__submitGuardInstalled = true;

    const inflightFetches = new Map();

    function disableFormControls(form) {
        form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]').forEach((btn) => {
            if (btn.disabled) {
                return;
            }
            btn.dataset.wasDisabled = '0';
            btn.disabled = true;
            btn.setAttribute('aria-busy', 'true');
        });
    }

    function enableFormControls(form) {
        form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]').forEach((btn) => {
            if (btn.dataset.wasDisabled === '0') {
                btn.disabled = false;
                btn.removeAttribute('aria-busy');
                delete btn.dataset.wasDisabled;
            }
        });
        delete form.dataset.submitting;
    }

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        if (form.dataset.allowParallel === '1' || form.dataset.noSubmitGuard === '1') {
            return;
        }

        if (form.dataset.submitting === '1') {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        form.dataset.submitting = '1';
        disableFormControls(form);

        setTimeout(() => {
            if (event.defaultPrevented) {
                enableFormControls(form);
            }
        }, 0);
    }, true);

    const originalFetch = window.fetch.bind(window);

    function bodyFingerprint(body) {
        if (!body) {
            return '';
        }
        if (typeof body === 'string') {
            return body;
        }
        if (body instanceof URLSearchParams) {
            return body.toString();
        }
        if (body instanceof FormData) {
            const parts = [];
            body.forEach((value, key) => {
                if (value instanceof File) {
                    parts.push(key + '=' + value.name + ':' + value.size);
                } else {
                    parts.push(key + '=' + String(value));
                }
            });
            return parts.join('&');
        }
        return String(body);
    }

    window.fetch = function (input, init) {
        const options = init ? { ...init } : {};
        const method = String(options.method || (typeof input !== 'string' && input && input.method) || 'GET').toUpperCase();

        if (!['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
            return originalFetch(input, init);
        }

        const url = typeof input === 'string' ? input : (input && input.url) || '';
        const key = method + '|' + url + '|' + bodyFingerprint(options.body);

        if (inflightFetches.has(key)) {
            return inflightFetches.get(key);
        }

        const promise = originalFetch(input, init).finally(() => {
            inflightFetches.delete(key);
        });

        inflightFetches.set(key, promise);
        return promise;
    };
})();
</script>
