<script id="tlaboo-script">
    // ==UserScript==
    // @name        New script zara.com
    // @namespace   Violentmonkey Scripts
    // @match *://*.zara.com/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==
    const tlaboo_sleep = (ms = 1000) => new Promise(resolve => setTimeout(resolve, ms));

    const selectorTypes = {
        text: 'text',
        src: 'src',
        html: 'innerHTML',
        outer: 'outerHTML',
        attr: 'getAttribute',
    };


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



    const tlaboo_html = `{!! $html !!}`;


    const removedSelectors = [
        '#add-to-cart-button',
        '.product-add-btn',
        '.product-card__add-btn',
        '.header-icon .bs-c-swich__wrap',
        '.header-icon .bsc-user-container',
        '.header-icon .bsc-mini-cart',
        '.header-icon .bsc-wish-list-entry_wrap',
        '.sidebar-cate__footer',
        '.bsc-common-header__right .bsc-wish-list-entry_wrap',
        '.bsc-common-header__right .bsc-header-cart',
        '.bottom-wrapper__price-wrapper .product-card__add-btn',
        '.product-card__add-bag',
        '#branch-app',
        '.add-cart__animation-normal'
    ];

    (function() {
        if (window.__DOMS_REMOVED__) return;
        window.__DOMS_REMOVED__ = true;

        document.documentElement.style.visibility = 'hidden';

        function removeElements() {
            let removed = false;

            removedSelectors.forEach(selector => {
                document.querySelectorAll(selector).forEach(el => {
                    el.remove();
                    removed = true;
                });
            });


            if (removed) {
                document.documentElement.style.visibility = 'visible';
            }
        }

        removeElements();


        const observer = new MutationObserver(() => {
            removeElements();
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

    })();


    (function initBtnObserver(retryDelay = 300, maxTries = 50) {
        let tries = 0;

        const start = () => {
            const elements = document.querySelectorAll('#attrPromotionWrap [role="listitem"]');

            if (elements.length) {
                elements.forEach(el => {
                    if (el.dataset.observed) return;

                    el.dataset.observed = '1';
                    observeBtnText(el, 200);
                });
                return;
            }

            // retry if not found
            if (++tries < maxTries) {
                setTimeout(start, retryDelay);
            }
        };

        start();
    })();


    function observeBtnText(el, delay = 150) {
        let last = el.innerHTML;
        let timer;

        const observer = new MutationObserver(() => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                if (el.innerHTML !== last) {
                    last = el.innerHTML;
                    document.dispatchEvent(
                        new CustomEvent('btnTextChanged', {
                            detail: {
                                element: el,
                                value: last
                            }
                        })
                    );
                }
            }, delay);
        });

        observer.observe(el, {
            childList: true,
            subtree: true,
            characterData: true,
        });
    }

    document.addEventListener('btnTextChanged', async e => {
        console.debug('test');
        const res = await window.flutter_inappwebview
            .callHandler('changeVariants', {
                test: 'data',
                userId: 5
            });
    });

    const selectors = {
        multiData: {
            selector: "#goodsDetailSchema",
            type: "html",
            data: "",
        },

        name: {
            selector: ".product-detail-card-info__name",
            type: "text",
            data: "",
        },

        description: {
            selector: null,
            type: "text",
            data: "",
        },

        price: {
            selector: ".price-current .money-amount__main, .price__amount-wrapper .money-amount__main",
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: ".price-current .money-amount__main, .price__amount-wrapper .money-amount__main",
            type: "text",
            data: "",
        },

        stock: {
            selector: null,
            type: "text",
            data: "",
        },

        image: {
            selector: ".product-detail-card__main-content img",
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: ".product-detail-card__main-content img",
            type: "multi-attr",
            data: [],
            attr: "src",
        },

        review: {
            selector: null,
            type: "text",
            data: "",
        },

        category: {
            selector: null,
            type: "text",
            data: "",
        },

        customerRating: {
            selector: null,
            type: "text",
            data: "",
        },

        brand: {
            selector: null,
            type: "text",
            data: "",
        },

        averageRating: {
            selector: null,
            type: "text",
            data: "",
        },

        totalReviews: {
            selector: null,
            type: "text",
            data: "",
        },

        soldBy: {
            selector: null,
            type: "text",
            data: "",
        },

        shippingPrice: {
            selector: null,
            type: "text",
            data: "",
        },

        selectedVariant: {
            selector: ".product-detail-color-item__color-button--is-selected span, .product-detail-more-colors__current-color span",
            type: "html",
            data: "",
        },

        id: {
            selector: "",
            type: "attr",
            data: "",
            attr: "data-clipboard-text",
        },
    };


    function getSelectedVariant() {

        return document.querySelector(selectors.selectedVariant.selector)?.textContent ?? "";
    }

    function getSelector(selector, type, single = true) {
        const data = [];

        if (single) {
            return document.querySelector(selector)[type];
        }

        document.querySelectorAll(selector).forEach(el => {

            let field = el[type];

            //  this for shein only :
            // remove all image if name contain bg-logo
            console.debug(field);
            if (field.includes('bg-logo')) {
                return;
            }

            // remove // from start src
            if (type === 'src') {
                field = field.replace(/^\/\//, "");
            }

            data.push(field);
        });

        return data;
    }



    function scrapeData() {

        const price = document.querySelector('.price__amount-wrapper .price-current .money-amount__main') ?? document.querySelector('.price__amount--old-price-wrapper .money-amount__main') ?? document.querySelector('.price__amount-wrapper .money-amount__main');

        selectors.price.data = price?.textContent;
        selectors.originalPrice.data = price?.textContent;

        selectors.name.data = document.querySelector(selectors.name.selector).textContent;
        selectors.images.data = getSelector(selectors.images.selector, 'src', false);
        selectors.image.data = selectors.images.data[0] ?? "";
        selectors.selectedVariant.data = getSelectedVariant();

        return Object.entries(selectors).map(([name, value]) => ({
            name,
            data: value.data,
        }));
    }

    async function addToCart(wrapper) {

        //add loading
        wrapper.querySelector('.tlaboo-loader').style.display = "block";
        wrapper.querySelector('#tlaboo-add-to-cart').setAttribute('disabled', 'disabled');

        const data = scrapeData();

        try {
            console.log(data);
            const res = await window.flutter_inappwebview.callHandler('addToCart', data);
        } catch (e) {
            console.log(e)
            alert(e);
        }



        wrapper.querySelector('.tlaboo-loader').style.display = "none";
        wrapper.querySelector('#tlaboo-add-to-cart').removeAttribute('disabled');

    }

    function loadScript(src) {
        return new Promise(resolve => {
            const s = document.createElement("script");
            s.src = src;
            s.onload = resolve;
            document.body.appendChild(s);
        });
    }

    async function initShopini() {

        // convert it to node
        const el = document.createElement('div');
        el.innerHTML = `{!! $html !!}`;
        document.body.appendChild(el);

        const wrapper = el.querySelector('#tlaboo-add-to-cart-wrapper');
        const button = wrapper.querySelector('#tlaboo-add-to-cart');



        button.addEventListener('click', async e => {
            addToCart(wrapper)
        });

        new Watcher('.product-detail-card-info-size-selector-buttons__add-to-cart, [data-qa-action="direct-add-to-cart"]', {
            onExists: () => {
                const btn = document.querySelector('#tlaboo-add-to-cart');
                btn.style.display = 'inline-flex';
                btn.removeAttribute('disabled');
            },
            onRemoved: () => {
                const btn = document.querySelector('#tlaboo-add-to-cart');
                btn.style.display = 'none';
                btn.setAttribute('disabled', 'disabled');
            }
        })

    }


    // initial 
    (function() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initShopini);
        } else {
            initShopini();
        }

    })();
</script>
