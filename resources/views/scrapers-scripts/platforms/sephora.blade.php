{{-- before loaded --}}
<script>
    // ==UserScript==
    // @name        New script sephora.com
    // @namespace   Violentmonkey Scripts
    // @match *://*.sephora.com/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==

    const watchers = [
        '[data-cnstrc-btn="add_to_cart"]'
    ];
</script>

{{-- on page started --}}
@include('scrapers-scripts.partials.helpers')

<script id="tlaboo-script">
    const selectorTypes = {
        text: 'text',
        src: 'src',
        html: 'innerHTML',
        outer: 'outerHTML',
        attr: 'getAttribute',
    };

    const tlaboo_html = `{!! $html !!}`;

    try {
        tlabooRemoveDoms([
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
        ])
    } catch (error) {
        console.info(error);
    }

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
            selector: "",
            type: "html",
            data: "",
        },

        name: {
            selector: '[data-at="product_name"]',
            type: "text",
            data: "",
        },

        description: {
            selector: null,
            type: "text",
            data: "",
        },

        price: {
            selector: "body > div:nth-child(3) > main > section > div.css-1ns0o97.e15t7owz0 > div:nth-child(1) > div.css-y5yat9.e15t7owz0 > p > span > span.css-18jtttk > b",
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: "body > div:nth-child(3) > main > section > div.css-1ns0o97.e15t7owz0 > div:nth-child(1) > div.css-y5yat9.e15t7owz0 > p > span > span.css-18jtttk > b",
            type: "text",
            data: "",
        },

        stock: {
            selector: null,
            type: "text",
            data: "",
        },

        image: {
            selector: ".css-1avyp1d ul li img",
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: ".css-1avyp1d ul li img",
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
            selector: '[data-at="brand_name"]',
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
            selector: '[data-at="sku_name_label"] span',
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

        const data = [];


        document.querySelectorAll(selectors.selectedVariant.selector).forEach(el => {
            data.push(el.textContent || '');
        })

        return data.join(' - ');
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
        selectors.name.data = document.querySelector(selectors.name.selector).textContent;
        selectors.images.data = getSelector(selectors.images.selector, 'src', false);
        selectors.image.data = selectors.images.data[0] ?? "";
        selectors.price.data = document.querySelector(selectors.price.selector)?.textContent;
        selectors.originalPrice.data = document.querySelector(selectors.originalPrice.selector)?.textContent;
        selectors.selectedVariant.data = getSelectedVariant();

        return Object.entries(selectors).map(([name, value]) => ({
            name,
            data: value.data,
        }));
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