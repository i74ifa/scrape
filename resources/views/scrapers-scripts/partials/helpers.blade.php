<script>
    const TLABOO_USER_ID = "{{ auth('sanctum')?->id() ?? '' }}";
    const TLABOO_BASE_URL = "{{ url('/api/v2') }}";
    const tlaboo_sleep = (ms = 1000) => new Promise(resolve => setTimeout(resolve, ms));


    const addToCartAppendCode = window.tlabooAppendCode?.addToCart || {};
    const initialCode = window.tlabooAppendCode?.initial || {};

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
            const el = document.querySelector(this.selector);

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

    async function addToCart(wrapper) {

        try {
            if (typeof addToCartAppendCode?.before === 'function') {
                addToCartAppendCode.before();
            }
        } catch (e) {
            console.log(e);
        }

        const btn = wrapper.querySelector('#tlaboo-add-to-cart');
        btn.classList.add('tlaboo-loading');
        btn.setAttribute('disabled', 'disabled');

        let data = scrapeData();

        try {
            if (typeof addToCartAppendCode?.onScrape === 'function') {
                data = await addToCartAppendCode.onScrape(data);
            }

            console.log(data);
            const res = await window.flutter_inappwebview.callHandler('addToCart', data);

            if (typeof addToCartAppendCode?.after === 'function') {
                addToCartAppendCode.after(data);
            }
        } catch (e) {
            console.log(e);
        }

        btn.classList.remove('tlaboo-loading');
        btn.removeAttribute('disabled');

    }


    const waitFor = async (selector, timeout = 3000) => {
        const start = Date.now();
        while (Date.now() - start < timeout) {
            const el = document.querySelector(selector);
            if (el) return el;
            await tlaboo_sleep(50);
        }
        return null;
    };



    function tlabooRemoveDoms(selectors) {
        if (window.__DOMS_REMOVED__) return;
        window.__DOMS_REMOVED__ = true;

        document.documentElement.style.visibility = 'hidden';

        const observerMethod = function() {
            let removed = false;

            selectors.forEach(selector => {
                document.querySelectorAll(selector).forEach(el => {
                    el.remove();
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


    const initialButton = {
        onExists: (e) => {
            const btn = document.querySelector('#tlaboo-add-to-cart');
            const priceEl = document.querySelector('#tlaboo-price');

            // hide button and price initially
            btn.style.display = 'none';
            priceEl.style.display = 'none';

            try {
                const data = scrapeData();
                const priceMatch = data.find(i => i.name === 'price');
                if (priceMatch && priceMatch.data && priceMatch.data !== "") {
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
            priceEl.style.display = 'none';
            btn.setAttribute('disabled', 'disabled');
        }
    }


    async function initShopini(watchers) {

        if (typeof initialCode?.before === 'function') {
            initialCode.before();
        }

        // convert it to node
        const el = document.createElement('div');
        el.innerHTML = `{!! $html !!}`;
        document.body.appendChild(el);

        const wrapper = el.querySelector('#tlaboo-add-to-cart-wrapper');
        const button = wrapper.querySelector('#tlaboo-add-to-cart');



        button.addEventListener('click', async e => {
            addToCart(wrapper)
        });

        watchers.forEach(watcher => {
            new Watcher(watcher, initialButton)
        })

        if (typeof initialCode?.after === 'function') {
            initialCode.after();
        }
    }

    (function() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initShopini(watchers);
            });
        } else {
            initShopini(watchers);
        }


    })();
</script>
