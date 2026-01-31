{{-- before loaded --}}
<script>
    // ==UserScript==
    // @name        New script trendyol.com
    // @namespace   Violentmonkey Scripts
    // @match *://*.trendyol.com/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==
    const watchers = [
        ".product-info-content"
    ];


    function initialCountry() {
        const COOKIE_NAME = 'countryCode';
        const TARGET_CODE = 'TR';
        const ONE_MONTH = 30 * 24 * 60 * 60 * 1000;


        function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
            document.cookie =
                `${name}=${value}; expires=${date.toUTCString()}; path=/; domain=${window.location.hostname.replace('www', '')}`;
        }

        const currentCode = getCookie(COOKIE_NAME);

        if (currentCode !== TARGET_CODE) {
            setCookie('countryCode', 'TR', 30);
            setCookie('originalSelectedCountry', 'Türkiye', 30);
            setCookie('referrerPageType', 'selectCountry', 30);
            setCookie('language', 'ar', 30)

            location.reload();
        }
    }


    window.tlabooAppendCode = {
        initial: {
            before: () => {
                initialCountry();
            }
        }
    }
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



    function getCookie(name) {
        return document.cookie
            .split('; ')
            .find(row => row.startsWith(name + '='))
            ?.split('=')[1];
    }

    const tlaboo_html = `{!! $html !!}`;



    try {
        tlabooRemoveDoms([
            '.add-to-basket',
            '.add-to-basket-button',
            'button.add-to-basket',
            '.add-to-basket-button-text',

            // Buy now
            '.buy-now-button',
            '.buy-now',

            // Favorite/Wishlist
            '.favorite-button',
            '.fv-container',
            '.add-to-favorite',

            // Cart icon in header
            '.account-basket',
            '.basket-icon',
            '.header-basket',

            // Notify when available
            '.notify-me-button',
            '.notify-button',

            '.add-to-cart-button-wrapper',
            '#smartbanner-wrapper',
            '#new-user-banner',
            '.pc-favorite',
            '#app-price-tooltip',
            '.add-to-cart-button-wrapper',
            '.favorite-button'
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
            selector: "#goodsDetailSchema",
            type: "html",
            data: "",
        },

        name: {
            selector: ".product-title",
            type: "text",
            data: "",
        },

        description: {
            selector: null,
            type: "text",
            data: "",
        },

        price: {
            selector: "footer .ty-plus-price-discounted-price, footer .price-current-price, footer .ty-plus-price-discounted-price, footer .new-price",
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: "footer .ty-plus-price-discounted-price, footer .price-current-price, footer .ty-plus-price-discounted-price, footer .new-price",
            type: "text",
            data: "",
        },

        stock: {
            selector: null,
            type: "text",
            data: "",
        },

        image: {
            selector: ".product-image-gallery-container div:has(> img)",
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: ".product-image-gallery-container div:has(> img)",
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
            selector: '[data-testid="selected-color"], .selected-variant',
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
        const selected = [];

        // get all .selected-variant
        document.querySelectorAll('.selected-variant').forEach(el => {
            selected.push(el.textContent);

        });

        //  get all [data-testid="selected-color"]
        document.querySelectorAll('[data-testid="selected-color"]').forEach(el => {
            selected.push(el.textContent);
        });

        return selected.join(' - ');
    }


    function getSelector(selector) {
        const data = [];
        document.querySelector(selector).querySelectorAll('img').forEach(el => {
            const imageSrc = el.src;

            data.push(imageSrc);
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
</script>
