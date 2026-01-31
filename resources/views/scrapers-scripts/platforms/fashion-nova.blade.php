{{-- before loaded --}}

<script>
    // ==UserScript==
    // @name        New script {name}
    // @namespace   Violentmonkey Scripts
    // @match *://*.fashionnova.com/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==

    const watchers = [
        '[data-testid="product-image-0"]',
        'footer:has([data-testid="app-store-logo-link" ])'
    ];


    // append script before initial
    // https://cdn.jsdelivr.net/npm/dom-regex@0.0.3/lib/dom-regex.js 

    function tlabooAppendScript() {
        const dom = document.createElement('script');
        dom.src = 'https://cdn.jsdelivr.net/npm/dom-regex@0.0.3/lib/dom-regex.js';
        document.body.appendChild(dom);
    }

    // if you want append before and after initial
    window.tlabooAppendCode = {
        initial: {
            before: () => {},
            after: () => {
                tlabooAppendScript();
            }
        }
    }
</script>

{{-- on page started --}}
@include('scrapers-scripts.partials.helpers')

<script id="tlaboo-script">
    const tlaboo_html = `{!! $html !!}`;

    try {
        tlabooRemoveDoms([
            '[data-testid="add-to-bag-button"]',
            '[data-testid="pdp-wishlist-button-inactive"]',
            '[data-testid="add-to-bag-sticky"]',
            '[data-testid="bag-plus-icon"]',
            '[data-testid="cart-bag-menu-tooltip"]',
            '[data-testid="heart-icon"]',
            '[data-testid="account-button-tooltip"]',
            '[data-testid="country-selection-button"]',
            'footer:has([data-testid="app-store-logo-link"])'
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
            selector: "[data-testid=\"product-price-regular\"]",
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: "[data-testid=\"product-price-regular\"]",
            type: "text",
            data: "",
        },


        image: {
            selector: '[data-testid="product-image-0"] img',
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

        let size;
        let color;
        try {
            size = DomRegex.one(/item-\d+-selected-in-stock/).textContent;
        } catch (error) {
            //
        }

        try {
            color = document.querySelector('[data-testid="swatch-color-title"]')?.textContent;
        } catch (error) {
            //
        }

        data.push(size);
        data.push(color);

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

        let price = document.querySelector(selectors.price.selector)?.textContent;

        if (price === "") {
            price = document.querySelector(selectors.price.selector)?.value;
        }

        selectors.name.data = document.querySelector(selectors.name.selector).textContent;
        selectors.images.data = getSelector(selectors.images.selector, 'src', false);
        selectors.image.data = document.querySelector(selectors.image.selector)?.src ?? selectors.images.data[0] ?? "";
        selectors.price.data = price;
        selectors.originalPrice.data = price;
        selectors.selectedVariant.data = getSelectedVariant();
        selectors.brand.data = document.querySelector(selectors.brand.selector)?.textContent ?? "";

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
