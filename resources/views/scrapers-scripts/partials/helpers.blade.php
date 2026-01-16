<script>
    const SHOPINI_USER_ID = "{{ auth('sanctum')?->id() ?? '' }}";
    const SHOPINI_BASE_URL = "{{ url('/api/v2') }}";
    const shopini_sleep = (ms = 1000) => new Promise(resolve => setTimeout(resolve, ms));


    const addToCartAppendCode = window.shopiniAppendCode?.addToCart || {};
    const initialCode = window.shopiniAppendCode?.initial || {};

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
            route: SHOPINI_BASE_URL + '/sites/user-search',
            method: 'PUT',
            body: {
                keyword: keyword,
                user_id: SHOPINI_USER_ID || '',
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

        wrapper.querySelector('.shopini-loader').style.display = "block";
        wrapper.querySelector('#shopini-add-to-cart').setAttribute('disabled', 'disabled');
        wrapper.querySelector('#shopini-add-to-cart svg').style.display = "none";

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

        wrapper.querySelector('.shopini-loader').style.display = "none";
        wrapper.querySelector('#shopini-add-to-cart').removeAttribute('disabled');
        wrapper.querySelector('#shopini-add-to-cart svg').style.display = "inline-block";

    }


    const waitFor = async (selector, timeout = 3000) => {
        const start = Date.now();
        while (Date.now() - start < timeout) {
            const el = document.querySelector(selector);
            if (el) return el;
            await shopini_sleep(50);
        }
        return null;
    };



    function shopiniRemoveDoms(selectors) {
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
            const btn = document.querySelector('#shopini-add-to-cart');
            btn.style.display = 'inline-flex';
            btn.removeAttribute('disabled');
        },
        onRemoved: (e) => {
            const btn = document.querySelector('#shopini-add-to-cart');
            btn.style.display = 'none';
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

        const wrapper = el.querySelector('#shopini-add-to-cart-wrapper');
        const button = wrapper.querySelector('#shopini-add-to-cart');



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
