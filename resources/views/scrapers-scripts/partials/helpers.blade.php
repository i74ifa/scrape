<script>
    const TLABOO_USER_ID = "{{ auth('sanctum')?->id() ?? '' }}";
    const TLABOO_BASE_URL = "{{ url('/api/v2') }}";
    const tlaboo_sleep = (ms = 1000) => new Promise(resolve => setTimeout(resolve, ms));

    window.storeUserSearch = async (keyword) => {
        const body = {
            route: TLABOO_BASE_URL + '/sites/user-search',
            method: 'PUT',
            body: {
                keyword: keyword,
                user_id: TLABOO_USER_ID || '',
            }
        }
        const res = await window.flutter_inappwebview.callHandler('sendRequest', body);
    }

    // Slide-in toast notifications — replacement for native alert() in
    // scraper scripts. Stacked top-right, auto-dismiss with progress bar,
    // color-coded by type. Pure DOM/CSS so it works on every host site
    // regardless of their stylesheet.
    //
    //   tlabooToast('Pick a size first');                   // info (default)
    //   tlabooToast('Added to cart', { type: 'success' });
    //   tlabooToast('No variant selected', { type: 'error', duration: 4000 });
    //
    // Options:
    //   type:     'info' | 'success' | 'warning' | 'error'   (default 'info')
    //   duration: ms before auto-dismiss                     (default 3500)
    //   title:    optional bold title above the message
    function tlabooToast(message, { type = 'info', duration = 3500, title } = {}) {
        if (!document.body) {
            return setTimeout(() => tlabooToast(message, { type, duration, title }), 50);
        }

        if (!document.getElementById('tlaboo-toast-styles')) {
            const style = document.createElement('style');
            style.id = 'tlaboo-toast-styles';
            style.textContent = `
                #tlaboo-toast-stack {
                    position: fixed; top: 16px; right: 16px; z-index: 2147483647;
                    display: flex; flex-direction: column; gap: 10px;
                    pointer-events: none; max-width: calc(100vw - 32px);
                    direction: rtl;
                    font-family: "IBM Plex Sans Arabic", sans-serif;
                }
                .tlaboo-toast {
                    pointer-events: auto;
                    direction: rtl; text-align: right;
                    min-width: 260px; max-width: 360px;
                    background: #ffffff; color: #111827;
                    border-radius: 12px;
                    box-shadow: 0 10px 24px rgba(0,0,0,.12), 0 2px 6px rgba(0,0,0,.06);
                    padding: 12px 14px 12px 14px;
                    display: flex; gap: 10px; align-items: flex-start;
                    position: relative; overflow: hidden;
                    transform: translateX(120%); opacity: 0;
                    transition: transform .28s cubic-bezier(.2,.8,.2,1), opacity .28s ease;
                    border-right: 4px solid #3b82f6;
                }
                .tlaboo-toast.tlaboo-toast-show { transform: translateX(0); opacity: 1; }
                .tlaboo-toast.tlaboo-toast-hide { transform: translateX(120%); opacity: 0; }
                .tlaboo-toast-icon {
                    flex: 0 0 22px; height: 22px; width: 22px;
                    border-radius: 50%; display: inline-flex;
                    align-items: center; justify-content: center;
                    color: #fff; font-weight: 700; font-size: 13px; line-height: 1;
                    background: #3b82f6;
                }
                .tlaboo-toast-body { flex: 1; min-width: 0; }
                .tlaboo-toast-title {
                    font-weight: 600; font-size: 13.5px; line-height: 1.4;
                    margin: 0 0 2px 0; color: #111827;
                }
                .tlaboo-toast-msg {
                    font-size: 13px; line-height: 1.6; color: #374151;
                    word-wrap: break-word;
                }
                .tlaboo-toast-close {
                    flex: 0 0 auto; background: transparent; border: 0; cursor: pointer;
                    color: #9ca3af; font-size: 18px; line-height: 1;
                    padding: 0 2px; margin-right: 4px;
                }
                .tlaboo-toast-close:hover { color: #4b5563; }
                .tlaboo-toast-progress {
                    position: absolute; right: 0; bottom: 0; height: 3px;
                    background: #3b82f6; width: 100%;
                    transform-origin: right center;
                    animation: tlaboo-toast-progress linear forwards;
                }
                @keyframes tlaboo-toast-progress { from { transform: scaleX(1); } to { transform: scaleX(0); } }
                .tlaboo-toast-success { border-right-color: #10b981; }
                .tlaboo-toast-success .tlaboo-toast-icon,
                .tlaboo-toast-success .tlaboo-toast-progress { background: #10b981; }
                .tlaboo-toast-error { border-right-color: #ef4444; }
                .tlaboo-toast-error .tlaboo-toast-icon,
                .tlaboo-toast-error .tlaboo-toast-progress { background: #ef4444; }
                .tlaboo-toast-warning { border-right-color: #f59e0b; }
                .tlaboo-toast-warning .tlaboo-toast-icon,
                .tlaboo-toast-warning .tlaboo-toast-progress { background: #f59e0b; }
                .tlaboo-toast-info { border-right-color: #3b82f6; }
                .tlaboo-toast-info .tlaboo-toast-icon,
                .tlaboo-toast-info .tlaboo-toast-progress { background: #3b82f6; }
                @media (prefers-color-scheme: dark) {
                    .tlaboo-toast { background: #1f2937; color: #f9fafb; }
                    .tlaboo-toast-title { color: #f9fafb; }
                    .tlaboo-toast-msg { color: #d1d5db; }
                    .tlaboo-toast-close { color: #9ca3af; }
                }
            `;
            document.head.appendChild(style);
        }

        let stack = document.getElementById('tlaboo-toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'tlaboo-toast-stack';
            document.body.appendChild(stack);
        }

        const icons = { info: 'i', success: '✓', warning: '!', error: '×' };
        const safeType = ['info', 'success', 'warning', 'error'].includes(type) ? type : 'info';

        const toast = document.createElement('div');
        toast.className = `tlaboo-toast tlaboo-toast-${safeType}`;
        toast.innerHTML = `
            <span class="tlaboo-toast-icon">${icons[safeType]}</span>
            <div class="tlaboo-toast-body">
                ${title ? `<div class="tlaboo-toast-title"></div>` : ''}
                <div class="tlaboo-toast-msg"></div>
            </div>
            <button type="button" class="tlaboo-toast-close" aria-label="Close">×</button>
            <div class="tlaboo-toast-progress" style="animation-duration:${duration}ms;"></div>
        `;
        if (title) toast.querySelector('.tlaboo-toast-title').textContent = title;
        toast.querySelector('.tlaboo-toast-msg').textContent = message;

        const dismiss = () => {
            toast.classList.remove('tlaboo-toast-show');
            toast.classList.add('tlaboo-toast-hide');
            setTimeout(() => toast.remove(), 300);
        };
        toast.querySelector('.tlaboo-toast-close').addEventListener('click', dismiss);

        stack.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('tlaboo-toast-show'));
        const timer = setTimeout(dismiss, duration);
        toast.addEventListener('mouseenter', () => clearTimeout(timer));

        return { dismiss };
    }

    // Dedicated error class so callers can signal "this is a user-facing
    // validation problem, not a runtime exception". scrapeData() and
    // onScrape() can throw one of these to abort addToCart with a toast
    // instead of a console log.
    class TlabooValidationError extends Error {
        constructor(message, { toastType = 'error', title } = {}) {
            super(message);
            this.name = 'TlabooValidationError';
            this.toastType = toastType;
            this.title = title;
        }
    }

    // Throw a TlabooValidationError (and surface it as a toast) when
    // `condition` is falsy. Mirrors the shape of common assert helpers.
    //
    //   tlabooValidate(selectedSize, 'Please pick a size first');
    function tlabooValidate(condition, message, options = {}) {
        if (condition) return;
        tlabooToast(message, { type: options.toastType ?? 'error', title: options.title });
        throw new TlabooValidationError(message, options);
    }

    // Exchange-rate table from the backend keyed by currency code, plus the
    // scraper's own currency. Source: ProductDto::getOnPageStartedFile().
    //
    // The rates table is USD-based: rates['TRY']=44.894 means "1 USD =
    // 44.894 TRY". rates.IQD is set per-scraper (scraper.exchange_rate)
    // and we treat it as "IQD per 1 USD" — the baseline. For non-USD
    // scrapers, conversion goes through USD using the rates table so
    // the same baseline number works correctly across all currencies.
    // (Historically several non-USD scrapers were left with null
    // exchange_rate or with values intended as IQD/USD, so this is the
    // only interpretation that doesn't silently produce 20x-wrong IQD.)
    window.tlabooRates = @json($rates ?? []);
    window.tlabooCurrency = @json($currency ?? 'USD');

    const addToCartAppendCode = window.tlabooAppendCode?.addToCart || {};
    const initialCode = window.tlabooAppendCode?.initial || {};

    // Resolve a selector that can be either a CSS-selector string OR a RegExp
    // (which is matched against each element's opening tag via DomRegex).
    // If a regex is passed before DomRegex has loaded, we return a falsy result
    // — every call site here re-runs on a MutationObserver or polling loop, so
    // it will pick up once DomRegex is available.
    function tlabooResolveOne(selector, scope) {
        scope = scope || document;
        if (selector instanceof RegExp) {
            if (typeof DomRegex === 'undefined') return null;
            return scope === document
                ? DomRegex.one(selector)
                : DomRegex.one.inside(scope, selector);
        }
        return scope.querySelector(selector);
    }

    function tlabooResolveAll(selector, scope) {
        scope = scope || document;
        if (selector instanceof RegExp) {
            if (typeof DomRegex === 'undefined') return [];
            return scope === document
                ? DomRegex.all(selector)
                : DomRegex.all.inside(scope, selector);
        }
        return Array.from(scope.querySelectorAll(selector));
    }

    class Watcher {
        constructor(selector, {
            onExists = () => {},
            onRemoved = () => {}
        } = {}) {
            this.selector = selector;
            this.onExists = onExists;
            this.onRemoved = onRemoved;

            this.observer = new MutationObserver(this.check.bind(this));
            this.start();
        }

        start() {
            this.check(); // initial check

            this.observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }

        check() {
            const el = tlabooResolveOne(this.selector);

            if (el) {
                if (!this.exists) {
                    this.exists = true;
                    this.onExists(el);
                }
            } else {
                if (this.exists) {
                    this.exists = false;
                    this.onRemoved();
                }
            }
        }

        stop() {
            this.observer.disconnect();
        }
    }

    // Toggle the add-to-cart button's loading state.
    //
    // Two visual styles are supported via `options.style`:
    //   'spinner' (default) — the circular spinner.
    //   'text'              — the vertical text-swipe loader
    //                         ("Loading" → "Getting Data" → "Almost There").
    //
    // Pass `false` to clear the state. Safe to call before the button exists
    // (no-op) so the helper can be used from variant watchers that may fire
    // either before or after initTlaboo.
    function tlabooSetCartLoading(isLoading, { style = 'spinner' } = {}) {
        const btn = document.querySelector('#tlaboo-add-to-cart');
        if (!btn) return;

        if (isLoading) {
            btn.setAttribute('disabled', 'disabled');
            btn.classList.add('tlaboo-loading');
            btn.classList.toggle('tlaboo-loading-text', style === 'text');
        } else {
            btn.removeAttribute('disabled');
            btn.classList.remove('tlaboo-loading', 'tlaboo-loading-text');
        }
    }

    async function addToCart(wrapper) {

        try {
            if (typeof addToCartAppendCode?.before === 'function') {
                addToCartAppendCode.before();
            }
        } catch (e) {
            console.log(e);
        }

        tlabooSetCartLoading(true);

        try {
            let data = scrapeData();

            if (typeof addToCartAppendCode?.onScrape === 'function') {
                data = await addToCartAppendCode.onScrape(data);
            }

            console.log(data);
            const res = await window.flutter_inappwebview.callHandler('addToCart', data);

            if (typeof addToCartAppendCode?.after === 'function') {
                addToCartAppendCode.after(data);
            }
        } catch (e) {
            // Validation errors already surfaced their own toast — stay quiet
            // in the console so they read as expected control flow.
            if (e instanceof TlabooValidationError) {
                console.info('[tlaboo] validation:', e.message);
            } else {
                console.log(e);
            }
        }

        tlabooSetCartLoading(false);
    }


    const waitFor = async (selector, timeout = 3000) => {
        const start = Date.now();
        while (Date.now() - start < timeout) {
            const el = tlabooResolveOne(selector);
            if (el) return el;
            await tlaboo_sleep(50);
        }
        return null;
    };


    // Resolve once the target element has had at least one mutation
    // followed by `quietMs` of silence — i.e. it stopped changing.
    // Designed for "wait for the new price to render after a variant
    // pick" on sites like Amazon, where the price element re-renders in
    // waves (placeholder → in-flight value → settled value) and the
    // only reliable signal is the absence of further mutations.
    //
    // - `selectorOrEl` accepts a string/RegExp selector (waitFor'd up
    //   to `timeoutMs`) or an Element you already resolved.
    // - Falls through and resolves with the current element if
    //   `timeoutMs` elapses without ever seeing a mutation, so a
    //   stale/no-op variant pick can't hang the caller.
    async function tlabooWaitStable(selectorOrEl, { quietMs = 400, timeoutMs = 8000 } = {}) {
        const el = (selectorOrEl instanceof Element)
            ? selectorOrEl
            : await waitFor(selectorOrEl, timeoutMs);
        if (!el) return null;

        return new Promise(resolve => {
            let quietTimer;
            let hardTimer;

            const cleanup = () => {
                observer.disconnect();
                clearTimeout(quietTimer);
                clearTimeout(hardTimer);
            };

            const settle = () => { cleanup(); resolve(el); };

            const observer = new MutationObserver(() => {
                clearTimeout(quietTimer);
                quietTimer = setTimeout(settle, quietMs);
            });

            observer.observe(el, {
                childList: true,
                subtree: true,
                characterData: true,
                attributes: true,
            });

            hardTimer = setTimeout(settle, timeoutMs);
        });
    }



    // Hook history.pushState/replaceState exactly once so SPA route changes
    // emit a `tlaboo:locationchange` event we can listen to. popstate and
    // hashchange already fire natively; we re-dispatch them under the same
    // name for a single subscription point.
    function tlabooInstallLocationHook() {
        if (window.__TLABOO_LOCATION_HOOKED__) return;
        window.__TLABOO_LOCATION_HOOKED__ = true;

        const fire = () => window.dispatchEvent(new Event('tlaboo:locationchange'));
        const origPush = history.pushState;
        const origReplace = history.replaceState;
        history.pushState = function() {
            const r = origPush.apply(this, arguments);
            fire();
            return r;
        };
        history.replaceState = function() {
            const r = origReplace.apply(this, arguments);
            fire();
            return r;
        };
        window.addEventListener('popstate', fire);
        window.addEventListener('hashchange', fire);
    }

    // URL-level "middleware" — redirect away from pages we don't want the
    // user to land on (login, cart, checkout, etc.) before any scraping work
    // happens. Re-runs on SPA navigation (pushState/replaceState/popstate/
    // hashchange), not just initial load.
    //
    // Each rule is `{ from, to }`:
    //   from: string  — matches the current pathname exactly OR as a path prefix
    //                   (so `/cart` matches `/cart` and `/cart/items` but NOT `/cart-deals`).
    //   from: RegExp  — tested against both pathname and full href.
    //   to:   string  — destination, absolute (`https://...`) or relative (`/`).
    //   to:   fn      — `(href) => string`, computed at match time.
    // Returns true if a redirect was issued on the initial check. Self-
    // redirects are skipped to avoid infinite loops.
    function tlabooRedirectIf(rules) {
        const evaluate = () => {
            const path = window.location.pathname;
            const href = window.location.href;

            for (const rule of rules) {
                const { from, to } = rule || {};
                if (!from || !to) continue;

                let matched = false;
                if (from instanceof RegExp) {
                    matched = from.test(path) || from.test(href);
                } else if (typeof from === 'string') {
                    const prefix = from.endsWith('/') ? from : from + '/';
                    matched = path === from || path.startsWith(prefix);
                }
                if (!matched) continue;

                const dest = typeof to === 'function' ? to(href) : to;
                if (!dest) continue;

                try {
                    const destUrl = new URL(dest, href);
                    if (destUrl.href === href) continue; // would loop
                    window.location.replace(destUrl.href);
                    return true;
                } catch (e) {
                    console.info('[tlabooRedirectIf] bad destination:', dest, e);
                }
            }
            return false;
        };

        // Initial check on script load.
        if (evaluate()) return true;

        // Re-evaluate on every SPA navigation.
        tlabooInstallLocationHook();
        window.addEventListener('tlaboo:locationchange', evaluate);

        return false;
    }


    function tlabooRemoveDoms(selectors, remove = true) {
        if (window.__DOMS_REMOVED__) return;
        window.__DOMS_REMOVED__ = true;

        document.documentElement.style.visibility = 'hidden';

        const observerMethod = function() {
            let removed = false;

            selectors.forEach(selector => {
                tlabooResolveAll(selector).forEach(el => {
                    if (remove) {
                        el.remove();
                    } else {
                        el.style.display = 'none';
                    }
                    removed = true;
                });
            });


            if (removed) {
                document.documentElement.style.visibility = 'visible';
            }
        }

        observerMethod();


        const observer = new MutationObserver(() => {
            observerMethod();
        });

        function startObserver() {
            if (!document.body) return setTimeout(startObserver, 50);
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }

        startObserver();

        setTimeout(() => {
            document.documentElement.style.visibility = 'visible';
        }, 1000);
    }


    // Watch the given selectors and invoke `onChange` whenever one of
    // them has an actual variant pick.
    //
    // Two firing paths, both narrow on purpose:
    //   1. Native `click` / `change` on the watched element — covers
    //      sites that dispatch real form events.
    //   2. MutationObserver restricted to *selection-state attributes*
    //      (aria-checked, aria-selected, etc.) anywhere in the watched
    //      subtree — covers sites like Amazon where the swatch input's
    //      aria-checked flips without a form event.
    //
    // We deliberately do NOT watch `childList` or `characterData`. On
    // Amazon those fire constantly during scroll/lazy-load (especially
    // on broad selectors like `.a-size-base`), and the previous version
    // would lock the add-to-cart button into an 8s price-poll on every
    // burst of unrelated DOM noise.
    class TlabooVariantWatcher {
        static SELECTION_ATTRS = [
            'aria-checked',
            'aria-selected',
            'aria-pressed',
            'aria-current',
            'checked',
            'selected',
            'data-selected',
            'class',
        ];

        constructor(selectors, onChange, { debounceMs = 150, selectionAttrs } = {}) {
            this.selectors = Array.isArray(selectors) ? selectors : [selectors];
            this.onChange = typeof onChange === 'function' ? onChange : () => {};
            this.debounceMs = debounceMs;
            this.selectionAttrs = selectionAttrs ?? TlabooVariantWatcher.SELECTION_ATTRS;
            this._fireTimer = null;
            this._bodyObserver = null;
            this._start();
        }

        _fire() {
            clearTimeout(this._fireTimer);
            this._fireTimer = setTimeout(() => {
                try { this.onChange(); }
                catch (e) { console.info('[TlabooVariantWatcher]', e); }
            }, this.debounceMs);
        }

        _attach(el) {
            if (el.__TLABOO_VARIANT_WATCHED__) return;
            el.__TLABOO_VARIANT_WATCHED__ = true;

            const fire = () => this._fire();
            el.addEventListener('click', fire);
            el.addEventListener('change', fire);

            new MutationObserver(fire).observe(el, {
                attributes: true,
                attributeFilter: this.selectionAttrs,
                subtree: true,
                childList: false,
                characterData: false,
            });
        }

        _scan() {
            this.selectors.forEach(sel => {
                tlabooResolveAll(sel).forEach(el => this._attach(el));
            });
        }

        _start() {
            const tick = () => {
                if (!document.body) return setTimeout(tick, 50);
                this._scan();
                this._bodyObserver = new MutationObserver(() => this._scan());
                this._bodyObserver.observe(document.body, {
                    childList: true,
                    subtree: true,
                });
            };
            tick();
        }

        stop() {
            clearTimeout(this._fireTimer);
            this._bodyObserver?.disconnect();
        }
    }

    function tlabooWatchVariants(selectors, onChange, debounceMs = 150) {
        return new TlabooVariantWatcher(selectors, onChange, { debounceMs });
    }


    // Best-effort price parser. The PHP-side `ProductDto::toFloat` is the
    // authoritative parser for backend storage; this is a lightweight JS
    // mirror for live on-page conversion. Strategy: strip non-numeric noise,
    // then treat the *last* separator as the decimal — falling back to
    // "three digits after a separator = thousands" when only one separator
    // is present. Good enough for badge display, not for billing.
    function tlabooParsePrice(raw) {
        if (raw == null) return 0;
        const clean = String(raw).replace(/[^\d.,-]/g, '');
        if (!clean) return 0;

        const lastDot = clean.lastIndexOf('.');
        const lastComma = clean.lastIndexOf(',');

        if (lastDot === -1 && lastComma === -1) return parseFloat(clean) || 0;

        if (lastDot !== -1 && lastComma !== -1) {
            return lastDot > lastComma
                ? parseFloat(clean.replace(/,/g, '')) || 0
                : parseFloat(clean.replace(/\./g, '').replace(',', '.')) || 0;
        }

        const sep = lastDot !== -1 ? '.' : ',';
        const sepPos = lastDot !== -1 ? lastDot : lastComma;
        const digitsAfter = clean.length - sepPos - 1;

        if (digitsAfter === 3) return parseFloat(clean.replace(sep, '')) || 0;
        return parseFloat(clean.replace(sep, '.')) || 0;
    }


    // Convert a raw price (string or number) into a localised IQD string
    // like `"63,000 IQD"`. Uses the same two-step USD bridge as
    // TlabooIqdBadge, so the badge and the variant sheet always agree
    // on the displayed amount. Returns `null` for unparseable input.
    function tlabooFormatIqd(raw, {
        iqdPerUsd = window.tlabooRates?.IQD ?? 1500,
        scraperCurrency = window.tlabooCurrency ?? 'USD',
        rates = window.tlabooRates ?? {},
    } = {}) {
        const amount = typeof raw === 'number' ? raw : tlabooParsePrice(raw);
        if (!amount) return null;

        const cur = (scraperCurrency || 'USD').toUpperCase();
        let iqd;
        if (cur === 'USD') {
            iqd = amount * iqdPerUsd;
        } else {
            const perUsd = rates[cur];
            iqd = perUsd ? (amount / perUsd) * iqdPerUsd : amount * iqdPerUsd;
        }
        return Math.round(iqd).toLocaleString('en-US') + ' IQD';
    }


    // Resolve the *real* price a user will actually pay, ignoring temporary
    // promotional deals.
    //
    // Some stores (AliExpress) surface a limited-time / limited-stock deal as
    // the CURRENT price and the real pre-deal price as the ORIGINAL price. The
    // deal expires (or sells out) before we fulfil the order, so the user ends
    // up paying the original price. We therefore prefer the original price when
    // it's present, and fall back to the current price when there is no deal.
    //
    // Pure read — never mutates the page. Returns trimmed text, or null when
    // neither selector resolves. Selectors may be CSS strings or RegExps.
    function tlabooRealPrice(currentSelector, originalSelector) {
        const read = (selector) => {
            const el = tlabooResolveOne(selector);
            if (!el) return null;
            return (el.value ?? el.textContent ?? '').trim() || null;
        };

        return read(originalSelector) ?? read(currentSelector);
    }


    // Visually drop a store's promotional "deal" framing in favour of the real
    // price, so what the user sees matches what they'll be charged.
    //
    // Stores like AliExpress are React apps: removing or rewriting their own
    // price nodes makes React crash on its next render with
    // "removeChild: node is not a child of this node". So this helper NEVER
    // restructures the page — it only toggles inline styles, which React's
    // reconciliation tolerates:
    //   - deal active (original price present): hide the deal price + its promo
    //     chrome (`dealSelectors`) and un-strike the original so it reads as the
    //     real price.
    //   - no deal: leave the current price visible (it is the real price).
    //
    // Re-render safe: it re-applies on DOM/style changes (e.g. a variant pick).
    // Idempotent style writes don't emit mutations, so it never loops on its
    // own changes. Selectors may be CSS strings or RegExps.
    function tlabooDropDealPrice(currentSelector, originalSelector, dealSelectors = []) {
        const setDisplay = (selector, value) => {
            tlabooResolveAll(selector).forEach(el => { el.style.display = value; });
        };

        const apply = () => {
            const current = tlabooResolveOne(currentSelector);
            if (!current) return;

            const original = tlabooResolveOne(originalSelector);

            if (original) {
                original.style.textDecoration = 'none';
                current.style.display = 'none';
                dealSelectors.forEach(selector => setDisplay(selector, 'none'));
            } else {
                current.style.display = '';
            }
        };

        apply();

        const observer = new MutationObserver(apply);
        const start = () => {
            if (!document.body) return setTimeout(start, 50);
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class', 'style'],
            });
        };
        start();

        return { stop: () => observer.disconnect() };
    }


    // Rewrite a store's promotional "deal" price so the page shows the real
    // price the user will actually pay.
    //
    // Sites like AliExpress put a limited-time deal in the CURRENT price node
    // and the real pre-deal price (struck-through) in the ORIGINAL node. The
    // deal expires before we fulfil the order, so the user ends up paying the
    // original price. Rather than hide the deal node — which on some layouts
    // leaves no price visible at all — we copy the original price text into
    // the current node, but ONLY when an original price exists. With no deal
    // the current price already IS the real price, so it's left untouched.
    //
    // Pass `convertToIqd = true` to additionally run the original price
    // through `tlabooFormatIqd` — using the scraper's own exchange rate and
    // currency — so the current node shows the IQD amount instead of the
    // store's native currency. Defaults to false (write the raw original text).
    //
    // Text-only write keeps React's reconciliation happy, and the
    // already-in-sync guard makes it idempotent so it never loops on its own
    // mutations. Re-applies on DOM changes (e.g. a variant pick). Selectors
    // may be CSS strings or RegExps.
    function tlabooUseOriginalPrice(currentSelector, originalSelector, convertToIqd = false) {
        const apply = () => {
            const current = tlabooResolveOne(currentSelector);
            const original = tlabooResolveOne(originalSelector);
            if (!current || !original) return;

            const originalText = (original.textContent ?? '').trim();
            if (!originalText) return;

            const text = convertToIqd
                ? (tlabooFormatIqd(originalText) ?? originalText)
                : originalText;

            if (current.textContent !== text) {
                current.textContent = text;
            }
        };

        apply();

        const observer = new MutationObserver(apply);
        const start = () => {
            if (!document.body) return setTimeout(start, 50);
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                characterData: true,
            });
        };
        start();

        return { stop: () => observer.disconnect() };
    }


    // Renders the live page price converted to IQD into a fixed top-left
    // badge (#tlaboo-iqd-badge, styled in the add-to-cart partial).
    //
    // The badge re-reads its source on every document mutation, which sounds
    // expensive but `update()` short-circuits on an unchanged raw string —
    // so the steady-state cost is one querySelector + one string compare.
    // Each platform mounts its own instance with a platform-specific
    // price selector; the helper itself stays platform-agnostic.
    class TlabooIqdBadge {
        constructor(priceSelector, {
            iqdPerUsd = window.tlabooRates?.IQD ?? 1500,
            scraperCurrency = window.tlabooCurrency ?? 'USD',
            rates = window.tlabooRates ?? {},
        } = {}) {
            this.priceSelector = priceSelector;
            this.iqdPerUsd = iqdPerUsd;
            this.scraperCurrency = (scraperCurrency || 'USD').toUpperCase();
            this.rates = rates;
            this.lastRaw = null;
            this.badge = null;
            this._observer = null;
            this._start();
        }

        _ensureBadge() {
            if (this.badge && document.contains(this.badge)) return;
            let el = document.getElementById('tlaboo-iqd-badge');
            if (!el) {
                el = document.createElement('div');
                el.id = 'tlaboo-iqd-badge';
                document.body.appendChild(el);
            }
            this.badge = el;
        }

        // `priceSelector` may be a single selector or an array of selectors
        // tried in order — pass `[originalSelector, currentSelector]` so the
        // badge prefers the real (pre-deal) price and falls back to the
        // current one when there's no deal.
        _readPrice() {
            const selectors = Array.isArray(this.priceSelector)
                ? this.priceSelector
                : [this.priceSelector];

            for (const selector of selectors) {
                const el = tlabooResolveOne(selector);
                if (!el) continue;
                const raw = (el.value ?? el.textContent ?? '').trim();
                if (raw) return raw;
            }
            return null;
        }

        update() {
            const raw = this._readPrice();
            if (raw === this.lastRaw) return;
            this.lastRaw = raw;
            this._ensureBadge();

            const formatted = tlabooFormatIqd(raw, {
                iqdPerUsd: this.iqdPerUsd,
                scraperCurrency: this.scraperCurrency,
                rates: this.rates,
            });
            if (!formatted) {
                this.badge.style.display = 'none';
                return;
            }
            this.badge.style.display = 'inline-flex';
            this.badge.textContent = formatted;
        }

        _start() {
            const tick = () => {
                if (!document.body) return setTimeout(tick, 50);
                this.update();
                this._observer = new MutationObserver(() => this.update());
                this._observer.observe(document.body, {
                    childList: true,
                    subtree: true,
                    characterData: true,
                });
            };
            tick();
        }

        stop() {
            this._observer?.disconnect();
            this.badge?.remove();
        }
    }

    function tlabooMountIqdBadge(priceSelector, options) {
        return new TlabooIqdBadge(priceSelector, options);
    }


    // Optional bottom-sheet for variant selection. When a platform's
    // `tlabooAppendCode.appendVariants` is configured, tapping the
    // add-to-cart button opens a slide-up sheet listing the variant
    // groups (color, size, etc.) — each as chips driven by elements
    // that already exist in the page DOM. On confirm the selected
    // source nodes are "clicked" so the underlying site reacts
    // naturally (price/availability updates) before the normal
    // addToCart flow runs.
    //
    // Config shape (in tlabooAppendCode.appendVariants):
    //   appendVariants: [groupConfig, ...]
    // or
    //   appendVariants: {
    //       groups:        [...],
    //       title?:        string,
    //       confirmText?:  string,
    //       priceLabel?:   ({ selections }) => string,
    //       priceSelector?: string | RegExp,   // page price source for the
    //                                          // footer when no per-option
    //                                          // price is set (mirrors
    //                                          // tlabooMountIqdBadge)
    //       priceInIqd?:   boolean,            // run displayed prices through
    //                                          // tlabooFormatIqd (default
    //                                          // true — set false to keep
    //                                          // the raw text)
    //   }
    //
    // groupConfig:
    //   selector:       '.variant__options'            (container OR list,
    //                                                   string or RegExp)
    //   optionSelector: '.variant__option'             (children inside container;
    //                                                   omit if `selector` itself
    //                                                   already enumerates options)
    //   label:          'اختر اللون'                   (heading above the chips)
    //   name:           'color'                        (identifier exposed to
    //                                                   getSelectedVariant)
    //   valueAttr:      'data-value'                   (attribute holding the value)
    //   labelOf:        (el) => string                 (chip text, default: aria-label
    //                                                   then textContent then value)
    //   priceOf:        (el) => string|null            (per-option price for display
    //                                                   in the chip and footer;
    //                                                   default reads data-price)
    //   imageOf:        (el) => string|null            (optional image url; default
    //                                                   tries <img src>, then inline
    //                                                   `background-image: url(...)`,
    //                                                   then computed background-image
    //                                                   — covers sites like Koton's
    //                                                   `.pz-variant__option` where the
    //                                                   swatch is a CSS background)
    //   display:        'text'|'image'|'auto'          (chip layout; 'auto' picks
    //                                                   image tiles when every option
    //                                                   has a resolvable image,
    //                                                   otherwise falls back to text)
    //   disabledSelector: string | string[]            (extra selector(s) that mark an
    //                                                   option as disabled, on top of
    //                                                   the built-in `[disabled]`,
    //                                                   `.disabled`,
    //                                                   `[aria-disabled="true"]`)
    //   isDisabled:     (el) => boolean                (full custom check; wins over
    //                                                   the selector-based defaults)
    //   onSelect:       (el) => void                   (how to reflect selection
    //                                                   on the underlying page;
    //                                                   default el.click())
    //
    // Disabled options (matched by the rules above) render as a greyed-out
    // chip and can't be picked — sites usually mark sold-out sizes this way.
    class TlabooVariantSheet {
        static normalizeConfig(cfg) {
            if (Array.isArray(cfg)) cfg = { groups: cfg };
            return {
                title: cfg.title || 'خيارات المنتج',
                confirmText: cfg.confirmText || 'إضافة إلى السلة',
                groups: (cfg.groups || []).map(TlabooVariantSheet.normalizeGroup),
                priceLabel: cfg.priceLabel,
                priceSelector: cfg.priceSelector || null,
                priceInIqd: cfg.priceInIqd !== false,
                onConfirm: cfg.onConfirm || (() => {}),
                onCancel: cfg.onCancel || (() => {}),
            };
        }

        // Normalize a raw price string for display. When `priceInIqd` is
        // on (the default) we run it through `tlabooFormatIqd` so the
        // sheet, the badge, and any future surface all speak IQD.
        // Returns null when the raw text isn't parseable, so callers
        // can skip rendering the price slot entirely.
        _formatPrice(raw) {
            if (!raw) return null;
            if (this.config.priceInIqd) return tlabooFormatIqd(raw);
            return raw;
        }

        // Read the configured page-price selector and return its
        // formatted IQD string (or raw text when priceInIqd is off).
        // Used by the footer as a fallback when no per-option price
        // is available — same pattern as TlabooIqdBadge._readPrice.
        _readPriceFromSelector() {
            const sel = this.config.priceSelector;
            if (!sel) return null;
            const el = tlabooResolveOne(sel);
            if (!el) return null;
            const raw = (el.value ?? el.textContent ?? '').trim();
            return raw ? this._formatPrice(raw) : null;
        }

        // Pull a usable image URL off the option element. Three sources,
        // in order: a nested <img>, an inline `background-image: url(...)`
        // style, and the computed `background-image` (for sites that
        // apply the swatch via a stylesheet rule instead of inline).
        static _extractImage(el) {
            const imgEl = el.querySelector?.('img');
            if (imgEl?.src) return imgEl.src;

            const fromBg = (bg) => {
                if (!bg || bg === 'none') return null;
                const m = bg.match(/url\(["']?([^"')]+)["']?\)/);
                return m ? m[1] : null;
            };

            const inline = fromBg(el.style?.backgroundImage);
            if (inline) return inline;

            try {
                return fromBg(window.getComputedStyle(el).backgroundImage);
            } catch (_) {
                return null;
            }
        }

        static normalizeGroup(g) {
            const valueAttr = g.valueAttr || 'data-value';
            return {
                name: g.name || g.label || 'group',
                label: g.label || g.name || 'اختر',
                selector: g.selector,
                optionSelector: g.optionSelector,
                valueAttr,
                // 'text'  — always render label+price chip
                // 'image' — always render swatch tiles
                // 'auto'  — swatch tiles iff every option resolves to an image
                display: g.display || 'auto',
                // Extra selector(s) that mark an option as disabled, in
                // addition to the built-ins (`[disabled]`, `.disabled`,
                // `[aria-disabled="true"]`). String or array of strings:
                //   disabledSelector: '.pz-variant__option--disabled'
                //   disabledSelector: ['.is-soldout', '[data-stock="0"]']
                disabledSelector: g.disabledSelector || null,
                // Full escape hatch: `(el) => boolean`. When provided it
                // wins over `disabledSelector` and the built-ins.
                isDisabled: g.isDisabled || null,
                labelOf: g.labelOf || ((el) => {
                    return (el.getAttribute('aria-label') || el.textContent || '').trim()
                        || el.getAttribute(valueAttr) || '';
                }),
                priceOf: g.priceOf || ((el) => {
                    const raw = el.getAttribute('data-price');
                    return raw ? raw.trim() : null;
                }),
                imageOf: g.imageOf || TlabooVariantSheet._extractImage,
                onSelect: g.onSelect || ((el) => el.click()),
            };
        }

        constructor(config) {
            this.config = TlabooVariantSheet.normalizeConfig(config);
            this.renderable = [];
            this.selections = new Map();
            this.backdrop = null;
            this.sheet = null;
            this._onKeyDown = (e) => { if (e.key === 'Escape') this.close(); };
            this._injectStyles();
        }

        _injectStyles() {
            if (document.getElementById('tlaboo-variant-sheet-styles')) return;
            const style = document.createElement('style');
            style.id = 'tlaboo-variant-sheet-styles';
            style.textContent = `
                #tlaboo-vs-backdrop {
                    position: fixed; inset: 0; z-index: 2147483645;
                    background: rgba(0, 0, 0, 0);
                    backdrop-filter: blur(0px);
                    -webkit-backdrop-filter: blur(0px);
                    transition: background .28s ease, backdrop-filter .28s ease, -webkit-backdrop-filter .28s ease;
                    pointer-events: none;
                }
                #tlaboo-vs-backdrop.tlaboo-vs-show {
                    background: rgba(0, 0, 0, .45);
                    backdrop-filter: blur(8px);
                    -webkit-backdrop-filter: blur(8px);
                    pointer-events: auto;
                }
                #tlaboo-vs-sheet {
                    position: fixed; left: 0; right: 0; bottom: 0;
                    z-index: 2147483646;
                    direction: rtl; text-align: right;
                    font-family: "IBM Plex Sans Arabic", sans-serif;
                    background: var(--add-to-cart-bg);
                    color: #ffffff;
                    border-top-left-radius: 22px; border-top-right-radius: 22px;
                    border: 1px solid rgba(255, 255, 255, 0.3);
                    border-bottom: 0;
                    backdrop-filter: blur(18px) saturate(140%);
                    -webkit-backdrop-filter: blur(18px) saturate(140%);
                    box-shadow:
                        0 -12px 48px rgba(0, 0, 0, 0.35),
                        inset 0 1px 0 rgba(255, 255, 255, 0.45);
                    transform: translateY(100%);
                    transition: transform .32s cubic-bezier(.2,.8,.2,1);
                    max-height: 82vh; display: flex; flex-direction: column;
                    overflow: hidden;
                }
                #tlaboo-vs-sheet.tlaboo-vs-show { transform: translateY(0); }
                .tlaboo-vs-grab {
                    width: 44px; height: 4px;
                    background: rgba(255, 255, 255, 0.35);
                    border-radius: 999px; margin: 10px auto 2px;
                    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
                }
                .tlaboo-vs-header {
                    display: flex; align-items: center; justify-content: space-between;
                    padding: 10px 20px 6px 20px;
                }
                .tlaboo-vs-title {
                    font-size: 16px; font-weight: 700; color: #ffffff; margin: 0;
                    letter-spacing: 0.2px;
                }
                .tlaboo-vs-close {
                    background: rgba(255, 255, 255, 0.08);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    cursor: pointer;
                    color: #ffffff; font-size: 18px; line-height: 1;
                    width: 30px; height: 30px;
                    border-radius: 50%;
                    display: inline-flex; align-items: center; justify-content: center;
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    transition: background .2s ease, transform .15s ease;
                }
                .tlaboo-vs-close:hover {
                    background: rgba(255, 255, 255, 0.18);
                }
                .tlaboo-vs-close:active { transform: scale(.94); }
                .tlaboo-vs-body {
                    padding: 4px 20px 10px 20px; overflow-y: auto; flex: 1;
                }
                .tlaboo-vs-group { margin: 14px 0; }
                .tlaboo-vs-group-label {
                    font-size: 13px; font-weight: 600;
                    color: rgba(255, 255, 255, 0.75);
                    margin: 0 0 10px;
                    letter-spacing: 0.2px;
                }
                .tlaboo-vs-options {
                    display: flex; flex-wrap: wrap; gap: 8px;
                }
                .tlaboo-vs-option {
                    border: 1px solid rgba(255, 255, 255, 0.22);
                    background: rgba(255, 255, 255, 0.08);
                    color: #ffffff;
                    border-radius: 14px;
                    padding: 9px 14px;
                    cursor: pointer;
                    font-size: 13.5px; line-height: 1.3; text-align: right;
                    display: inline-flex; flex-direction: column; align-items: stretch;
                    gap: 4px; min-width: 56px; max-width: 100%;
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    box-shadow:
                        0 4px 12px rgba(0, 0, 0, 0.18),
                        inset 0 1px 0 rgba(255, 255, 255, 0.25);
                    transition: border-color .18s ease, background .18s ease,
                                color .18s ease, transform .12s ease,
                                box-shadow .18s ease;
                    font-family: inherit;
                }
                .tlaboo-vs-option:hover {
                    background: rgba(255, 255, 255, 0.14);
                    border-color: rgba(255, 255, 255, 0.4);
                }
                .tlaboo-vs-option:active { transform: scale(.97); }
                .tlaboo-vs-option.tlaboo-vs-disabled {
                    opacity: .38; cursor: not-allowed;
                    box-shadow: none;
                }
                .tlaboo-vs-option.tlaboo-vs-disabled:hover {
                    background: rgba(255, 255, 255, 0.08);
                    border-color: rgba(255, 255, 255, 0.22);
                }
                .tlaboo-vs-option.tlaboo-vs-selected {
                    background: rgba(255, 255, 255, 0.92);
                    color: #111111;
                    border-color: rgba(255, 255, 255, 0.85);
                    box-shadow:
                        0 8px 22px rgba(0, 0, 0, 0.28),
                        inset 0 1px 0 rgba(255, 255, 255, 0.85);
                }
                .tlaboo-vs-option-row {
                    display: flex; align-items: center; gap: 8px;
                }
                .tlaboo-vs-option-thumb {
                    width: 28px; height: 28px; border-radius: 8px;
                    object-fit: cover;
                    background: rgba(255, 255, 255, 0.1);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                }
                .tlaboo-vs-options-image {
                    gap: 10px;
                }
                .tlaboo-vs-option.tlaboo-vs-option-image {
                    padding: 6px;
                    align-items: center;
                    gap: 6px;
                    min-width: 76px;
                }
                .tlaboo-vs-option-swatch {
                    display: block;
                    width: 64px; height: 64px;
                    border-radius: 12px;
                    background-size: cover;
                    background-position: center;
                    background-repeat: no-repeat;
                    background-color: rgba(255, 255, 255, 0.08);
                    border: 1px solid rgba(255, 255, 255, 0.22);
                    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25);
                }
                .tlaboo-vs-option.tlaboo-vs-selected .tlaboo-vs-option-swatch {
                    border-color: rgba(0, 0, 0, 0.18);
                    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
                }
                .tlaboo-vs-option-caption {
                    font-size: 11.5px;
                    text-align: center;
                    color: inherit;
                    max-width: 80px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                .tlaboo-vs-option-price {
                    font-size: 11.5px;
                    color: rgba(255, 255, 255, 0.7);
                    font-weight: 500;
                }
                .tlaboo-vs-option.tlaboo-vs-selected .tlaboo-vs-option-price {
                    color: #333333;
                }
                .tlaboo-vs-footer {
                    padding: 14px 20px calc(14px + env(safe-area-inset-bottom));
                    border-top: 1px solid rgba(255, 255, 255, 0.15);
                    display: flex; align-items: center; justify-content: space-between;
                    gap: 12px;
                    background: rgba(0, 0, 0, 0.18);
                    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
                }
                .tlaboo-vs-price-block {
                    display: flex; flex-direction: column; min-width: 0;
                }
                .tlaboo-vs-price-caption {
                    font-size: 11.5px;
                    color: rgba(255, 255, 255, 0.65);
                    letter-spacing: 0.3px;
                }
                .tlaboo-vs-price-value {
                    font-size: 17px; font-weight: 700; color: #ffffff;
                    letter-spacing: 0.2px;
                }
                .tlaboo-vs-confirm {
                    background: rgba(255, 255, 255, 0.95);
                    color: #111111;
                    border: 1px solid rgba(255, 255, 255, 0.6);
                    border-radius: 999px;
                    padding: 12px 22px;
                    font-size: 14.5px; font-weight: 700;
                    cursor: pointer; min-width: 150px;
                    font-family: inherit;
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    box-shadow:
                        0 8px 24px rgba(0, 0, 0, 0.28),
                        inset 0 1px 0 rgba(255, 255, 255, 0.7);
                    transition: background .18s ease, transform .12s ease,
                                box-shadow .18s ease, color .18s ease;
                }
                .tlaboo-vs-confirm:hover {
                    background: #ffffff;
                    box-shadow:
                        0 12px 32px rgba(0, 0, 0, 0.32),
                        inset 0 1px 0 rgba(255, 255, 255, 0.85);
                }
                .tlaboo-vs-confirm:active { transform: scale(.97); }
                .tlaboo-vs-confirm[disabled] {
                    background: rgba(255, 255, 255, 0.18);
                    color: rgba(255, 255, 255, 0.55);
                    border-color: rgba(255, 255, 255, 0.2);
                    cursor: not-allowed;
                    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
                }
            `;
            document.head.appendChild(style);
        }

        _resolveOptions(group) {
            if (!group.selector) return [];
            // If the platform supplies BOTH selector and optionSelector we
            // treat `selector` as a container — but some groups may have
            // several containers on the page (e.g. one swatch row per axis
            // that share the same class). Enumerate all containers and
            // flatten the option lists.
            if (group.optionSelector) {
                const containers = tlabooResolveAll(group.selector);
                if (containers.length === 0) return [];
                const out = [];
                containers.forEach(c => {
                    tlabooResolveAll(group.optionSelector, c).forEach(o => out.push(o));
                });
                return out;
            }
            return tlabooResolveAll(group.selector);
        }

        _computeRenderable() {
            this.renderable = this.config.groups
                .map((group, idx) => ({ group, idx, options: this._resolveOptions(group) }))
                .filter(g => g.options.length > 0);
        }

        // Returns true if the sheet actually opened. False means there was
        // nothing meaningful to show (no options on the page), so the
        // caller should fall through to the normal addToCart() path.
        open() {
            if (this.sheet) return true;
            this._computeRenderable();
            if (this.renderable.length === 0) return false;
            this._build();
            document.addEventListener('keydown', this._onKeyDown);
            requestAnimationFrame(() => {
                this.backdrop.classList.add('tlaboo-vs-show');
                this.sheet.classList.add('tlaboo-vs-show');
            });
            return true;
        }

        close({ confirmed = false } = {}) {
            if (!this.sheet) return;
            document.removeEventListener('keydown', this._onKeyDown);
            this.backdrop.classList.remove('tlaboo-vs-show');
            this.sheet.classList.remove('tlaboo-vs-show');
            const sheet = this.sheet;
            const backdrop = this.backdrop;
            this.sheet = null;
            this.backdrop = null;
            setTimeout(() => { sheet.remove(); backdrop.remove(); }, 320);
            if (!confirmed) {
                try { this.config.onCancel(); }
                catch (e) { console.info('[tlaboo variant sheet] onCancel', e); }
            }
        }

        _build() {
            const backdrop = document.createElement('div');
            backdrop.id = 'tlaboo-vs-backdrop';
            backdrop.addEventListener('click', () => this.close());

            const sheet = document.createElement('div');
            sheet.id = 'tlaboo-vs-sheet';
            sheet.innerHTML = `
                <div class="tlaboo-vs-grab"></div>
                <div class="tlaboo-vs-header">
                    <h3 class="tlaboo-vs-title"></h3>
                    <button type="button" class="tlaboo-vs-close" aria-label="إغلاق">×</button>
                </div>
                <div class="tlaboo-vs-body"></div>
                <div class="tlaboo-vs-footer">
                    <div class="tlaboo-vs-price-block">
                        <span class="tlaboo-vs-price-caption">السعر</span>
                        <span class="tlaboo-vs-price-value">—</span>
                    </div>
                    <button type="button" class="tlaboo-vs-confirm" disabled></button>
                </div>
            `;
            sheet.querySelector('.tlaboo-vs-title').textContent = this.config.title;
            sheet.querySelector('.tlaboo-vs-confirm').textContent = this.config.confirmText;
            sheet.querySelector('.tlaboo-vs-close').addEventListener('click', () => this.close());
            sheet.querySelector('.tlaboo-vs-confirm').addEventListener('click', () => this._confirm());

            const body = sheet.querySelector('.tlaboo-vs-body');
            this.renderable.forEach(entry => body.appendChild(this._renderGroup(entry)));

            document.body.appendChild(backdrop);
            document.body.appendChild(sheet);
            this.backdrop = backdrop;
            this.sheet = sheet;
            this._refreshFooter();
        }

        _renderGroup({ group, idx, options }) {
            const wrap = document.createElement('div');
            wrap.className = 'tlaboo-vs-group';
            wrap.innerHTML = `
                <p class="tlaboo-vs-group-label"></p>
                <div class="tlaboo-vs-options"></div>
            `;
            wrap.querySelector('.tlaboo-vs-group-label').textContent = group.label;
            const list = wrap.querySelector('.tlaboo-vs-options');

            // Resolve all option data up front so we can decide on a
            // group-wide layout: a single image swatch is fine, but a
            // *partial* swatch row looks broken, so 'auto' only flips
            // to image mode when every option has an image.
            // Prices flow through `_formatPrice` so the sheet is
            // consistent with whatever tlabooMountIqdBadge is showing.
            const resolved = options.map(srcEl => ({
                srcEl,
                labelText: group.labelOf(srcEl),
                priceText: this._formatPrice(group.priceOf(srcEl)),
                imgSrc: group.imageOf(srcEl),
            }));

            const allHaveImage = resolved.length > 0 && resolved.every(o => o.imgSrc);
            const useImage = group.display === 'image'
                || (group.display === 'auto' && allHaveImage);

            if (useImage) list.classList.add('tlaboo-vs-options-image');

            resolved.forEach(({ srcEl, labelText, priceText, imgSrc }) => {
                const chip = useImage
                    ? this._buildImageChip({ labelText, priceText, imgSrc })
                    : this._buildTextChip({ labelText, priceText, imgSrc });

                const disabled = this._isOptionDisabled(group, srcEl);
                if (disabled) {
                    chip.classList.add('tlaboo-vs-disabled');
                    chip.disabled = true;
                } else {
                    chip.addEventListener('click', () => this._onChipClick(idx, srcEl, chip, list, priceText, labelText));
                }
                list.appendChild(chip);
            });

            return wrap;
        }

        // Resolve a group's "is this option disabled?" check. Order:
        //   1. `isDisabled(el)` if the platform supplied one;
        //   2. built-in selectors + the platform's `disabledSelector`
        //      (merged into a single `matches()` call so we never lose
        //      the defaults when a platform only overrides part of them).
        _isOptionDisabled(group, el) {
            if (typeof group.isDisabled === 'function') {
                try { return !!group.isDisabled(el); }
                catch (e) { console.info('[tlaboo variant sheet] isDisabled error', e); }
            }
            const base = '[disabled], .disabled, [aria-disabled="true"]';
            const extra = group.disabledSelector;
            const selector = extra
                ? base + ', ' + (Array.isArray(extra) ? extra.join(', ') : extra)
                : base;
            return !!el.matches?.(selector);
        }

        _buildTextChip({ labelText, priceText, imgSrc }) {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'tlaboo-vs-option';

            const row = document.createElement('span');
            row.className = 'tlaboo-vs-option-row';

            if (imgSrc) {
                const img = document.createElement('img');
                img.src = imgSrc;
                img.className = 'tlaboo-vs-option-thumb';
                row.appendChild(img);
            }
            const labelSpan = document.createElement('span');
            labelSpan.textContent = labelText;
            row.appendChild(labelSpan);
            chip.appendChild(row);

            if (priceText) {
                const priceSpan = document.createElement('span');
                priceSpan.className = 'tlaboo-vs-option-price';
                priceSpan.textContent = priceText;
                chip.appendChild(priceSpan);
            }
            return chip;
        }

        _buildImageChip({ labelText, priceText, imgSrc }) {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'tlaboo-vs-option tlaboo-vs-option-image';
            if (labelText) {
                chip.title = labelText;
                chip.setAttribute('aria-label', labelText);
            }

            const swatch = document.createElement('span');
            swatch.className = 'tlaboo-vs-option-swatch';
            if (imgSrc) swatch.style.backgroundImage = `url("${imgSrc}")`;
            chip.appendChild(swatch);

            if (labelText) {
                const cap = document.createElement('span');
                cap.className = 'tlaboo-vs-option-caption';
                cap.textContent = labelText;
                chip.appendChild(cap);
            }
            if (priceText) {
                const priceSpan = document.createElement('span');
                priceSpan.className = 'tlaboo-vs-option-price';
                priceSpan.textContent = priceText;
                chip.appendChild(priceSpan);
            }
            return chip;
        }

        _onChipClick(groupIdx, srcEl, chipEl, listEl, priceText, labelText) {
            this.selections.set(groupIdx, { srcEl, priceText, labelText });
            listEl.querySelectorAll('.tlaboo-vs-option').forEach(c => c.classList.remove('tlaboo-vs-selected'));
            chipEl.classList.add('tlaboo-vs-selected');
            this._refreshFooter();
        }

        _refreshFooter() {
            if (!this.sheet) return;
            const required = this.renderable.length;
            const allSelected = this.selections.size >= required;
            this.sheet.querySelector('.tlaboo-vs-confirm').disabled = !allSelected;

            const priceEl = this.sheet.querySelector('.tlaboo-vs-price-value');
            let label = '—';
            if (typeof this.config.priceLabel === 'function') {
                try {
                    const out = this.config.priceLabel({
                        selections: this._publicSelections(),
                    });
                    if (out) label = out;
                } catch (e) {
                    console.info('[tlaboo variant sheet] priceLabel error', e);
                }
            } else {
                // Default: the most recently picked option's price wins —
                // good enough for sites that put the per-SKU price on the
                // *last* axis the user picks (e.g. size after color).
                // When the platform doesn't expose per-option prices
                // (Koton's `.variant__option` for instance), fall back
                // to the page-level price selector so the footer still
                // shows the same number as the IQD badge.
                const last = Array.from(this.selections.values()).reverse().find(v => v.priceText);
                if (last) label = last.priceText;
                else {
                    const fromPage = this._readPriceFromSelector();
                    if (fromPage) label = fromPage;
                }
            }
            priceEl.textContent = label;
        }

        _publicSelections() {
            return Array.from(this.selections.entries()).map(([idx, v]) => ({
                group: this.config.groups[idx],
                name: this.config.groups[idx].name,
                element: v.srcEl,
                value: v.srcEl.getAttribute(this.config.groups[idx].valueAttr),
                label: v.labelText,
                priceText: v.priceText,
            }));
        }

        _confirm() {
            if (this.selections.size < this.renderable.length) {
                tlabooToast('يرجى اختيار جميع المتغيرات', { type: 'warning' });
                return;
            }

            const picked = this._publicSelections();

            // Reflect the picks on the underlying page so the site's own
            // price/availability logic updates before scrapeData runs.
            picked.forEach(p => {
                try { p.group.onSelect(p.element); }
                catch (e) { console.info('[tlaboo variant sheet] onSelect error', e); }
            });

            // Expose the selection for getSelectedVariant() / scrapeData()
            // — see tlabooGetAppendedVariants().
            window.__tlabooSelectedVariants = picked.map(p => ({
                name: p.name,
                value: p.value,
                label: p.label,
                priceText: p.priceText,
            }));

            this.close({ confirmed: true });

            try { this.config.onConfirm({ selections: picked }); }
            catch (e) { console.info('[tlaboo variant sheet] onConfirm error', e); }
        }
    }

    // Convenience wrapper. Returns true if the sheet opened (caller must
    // not also fire addToCart — the sheet's onConfirm will do it), false
    // if there was nothing to choose and the caller should proceed
    // straight to addToCart.
    function tlabooOpenVariantSheet(config) {
        return new TlabooVariantSheet(config).open();
    }

    // What the user picked in the most recent variant sheet. Each entry
    // is `{ name, value, label, priceText }`. Empty array if the sheet
    // was never opened or the platform doesn't use it — getSelectedVariant
    // implementations can safely call this as their first lookup.
    function tlabooGetAppendedVariants() {
        return Array.isArray(window.__tlabooSelectedVariants)
            ? window.__tlabooSelectedVariants
            : [];
    }

    const initialButton = {
        onExists: (e) => {
            const btn = document.querySelector('#tlaboo-add-to-cart');
            const priceEl = document.querySelector('#tlaboo-price');

            // hide button and price initially
            btn.style.display = 'none';
            if (priceEl) priceEl.style.display = 'none';

            try {
                const data = scrapeData();
                const priceMatch = data.find(i => i.name === 'price');
                if (priceEl && priceMatch && priceMatch.data && priceMatch.data !== "") {
                    priceEl.textContent = priceMatch.data;
                    priceEl.style.display = 'flex';
                }
            } catch (e) {
                console.log('failed to get price', e);
            }

            btn.style.display = 'inline-flex';
            btn.removeAttribute('disabled');
        },
        onRemoved: (e) => {
            const btn = document.querySelector('#tlaboo-add-to-cart');
            const priceEl = document.querySelector('#tlaboo-price');
            btn.style.display = 'none';
            if (priceEl) priceEl.style.display = 'none';
            btn.setAttribute('disabled', 'disabled');
        }
    }

    async function initTlaboo(watchers) {

        if (typeof initialCode?.before === 'function') {
            initialCode.before();
        }

        const el = document.createElement('div');
        el.innerHTML = `{!! $html !!}`;
        document.body.appendChild(el);

        const wrapper = el.querySelector('#tlaboo-add-to-cart-wrapper');
        const button = wrapper.querySelector('#tlaboo-add-to-cart');

        // Platforms that opt into `appendVariants` route the click through
        // a bottom-sheet first. The sheet falls through (returns false) if
        // no options are present on the page right now, so we can still
        // call addToCart in that case rather than blocking the user.
        button.addEventListener('click', async e => {
            const variantSheetConfig = window.tlabooAppendCode?.appendVariants;
            if (variantSheetConfig) {
                const base = Array.isArray(variantSheetConfig)
                    ? { groups: variantSheetConfig }
                    : variantSheetConfig;
                const opened = tlabooOpenVariantSheet({
                    ...base,
                    onConfirm: () => addToCart(wrapper),
                });
                if (opened) return;
            }
            addToCart(wrapper);
        });

        watchers.forEach(watcher => {
            new Watcher(watcher, initialButton)
        })

        if (typeof initialCode?.after === 'function') {
            initialCode.after({
                addToCartWrapper: el
            });
        }
    }

    (function() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initTlaboo(watchers);
            });
        } else {
            initTlaboo(watchers);
        }

    })();
</script>