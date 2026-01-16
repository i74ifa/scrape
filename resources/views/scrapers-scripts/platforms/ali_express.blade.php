{{-- before loaded --}}

<script>
    // ==UserScript==
    // @name        New script aliexpress.com
    // @namespace   Violentmonkey Scripts
    // @match *://*.aliexpress.*/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==

    const watchers = [
        '[data-spm="detail"]'
    ];


    // append script before initial
    // https://cdn.jsdelivr.net/npm/dom-regex@0.0.3/lib/dom-regex.js 

    function isAepCookieMatch(key, expectedValue) {
        const cookieValue = getCookie('aep_usuc_f');
        if (!cookieValue) return false;

        const params = new URLSearchParams(cookieValue);
        return params.get(key) === expectedValue;
    }


    function getCookie(name) {
        const match = document.cookie
            .split('; ')
            .find(c => c.startsWith(name + '='));

        return match ?
            decodeURIComponent(match.substring(name.length + 1)) :
            null;
    }

    function updateAepCookie(updates = {}) {
        const cookieValue = getCookie('aep_usuc_f');
        if (!cookieValue) return null;

        const params = new URLSearchParams(cookieValue);

        Object.entries(updates).forEach(([key, value]) => {
            params.set(key, value);
        });

        return params.toString();
    }

    function setCookie(name, value, days = 30) {
        const date = new Date(Date.now() + days * 864e5).toUTCString();
        document.cookie =
            `${name}=${value}; expires=${date}; path=/; domain=${window.location.hostname.replace('www', '')}`;
    }

    function getCookieDomainFromWWW() {
        const host = window.location.hostname;

        // Only transform if it starts with www.
        if (host.startsWith('www.')) {
            return '.' + host.slice(4);
        }

        // Otherwise use current host with leading dot
        return '.' + host;
    }

    function shopiniAppendScript() {
        const dom = document.createElement('script');
        dom.src = 'https://cdn.jsdelivr.net/npm/dom-regex@0.0.3/lib/dom-regex.js';
        document.body.appendChild(dom);
    }

    // if you want append before and after initial
    window.shopiniAppendCode = {
        initial: {
            before: () => {

            },
            after: () => {
                shopiniAppendScript();

                if (!isAepCookieMatch('c_tp', 'AED')) {
                    const cookieValue = updateAepCookie({
                        c_tp: 'AED',
                        region: 'AE',
                        b_locale: 'en_US'
                    });

                    setCookie('aep_usuc_f','site=uae&ga_saved=yes&c_tp=AED&region=AE&b_locale=en_US&ae_u_p_s=2', 365);
                    // window.location.reload();
                }
            }
        }
    }
</script>

{{-- on page started --}}
@include('scrapers-scripts.partials.helpers')

<script id="shopini-script">
    const shopini_html = `{!! $html !!}`;

    try {
        shopiniRemoveDoms([
            'a:has(.comet-icon-locationanchor)',
            'a:has(.comet-icon-account)',
            'a:has(.comet-icon-shoppingcart)',
            '#header-float-banner',
            '#container-for-smart-banner',
        ])
    } catch (error) {
        console.info(error);
    }


    const selectors = {
        name: {
            selector: '[data-testid="product-title"]',
            type: "text",
            data: "",
        },

        description: {
            selector: "",
            type: "text",
            data: "",
        },

        price: {
            selector: /price-default--current--[^]+/,
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: /price-default--current--[^]+/,
            type: "text",
            data: "",
        },


        image: {
            selector: '.slides .slide img',
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: '[data-testid="product-image-0"] img',
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

        brand: {
            selector: ".a",
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
            selector: "",
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

        try {
            DomRegex.all(/sku-ui--skuValue--[^]+/).forEach(el => {
                data.push(el.textContent);
            })
        } catch (error) {
            //
        }
        return data.join(' - ');
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


        // try to get script
        let data = {};
        const script = document.querySelector('script[type="application/ld+json"]');
        if (script) {
            data = JSON.parse(script.textContent)[0] ?? {};

            selectors.name.data = data.name ?? "";
            selectors.images.data = data.image ?? "";
            selectors.image.data = document.querySelector(selectors.image.selector)?.src;
        }

        if (selectors.image.data === "") {
            selectors.image.data = document.querySelector(selectors.image.selector)?.src;
        }

        let price = DomRegex.one(selectors.price.selector)?.textContent;
        price = price.trim();

        selectors.price.data = price;
        selectors.originalPrice.data = price;
        selectors.selectedVariant.data = getSelectedVariant();
        // selectors.brand.data = document.querySelector(selectors.brand.selector)?.textContent ?? "";

        return Object.entries(selectors).map(([name, value]) => ({
            name,
            data: value.data,
        }));
    }

    function loadScript(src) {
        return new Promise(resolve => {
            const s = document.createElement("script");
            s.src = src;
            s.onload = resolve;
            document.body.appendChild(s);
        });
    }
</script>
