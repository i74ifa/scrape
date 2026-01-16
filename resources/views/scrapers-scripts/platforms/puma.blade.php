{{-- before loaded --}}
<script>
    const watchers = [
        "#size-picker",
        "#style-picker"
    ];
</script>


{{-- on page started --}}
@include('scrapers-scripts.partials.helpers')
<script id="shopini-script">
    // ==UserScript==
    // @name        New script puma.com
    // @namespace   Violentmonkey Scripts
    // @match *://*.puma.com/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==
    const selectorTypes = {
        text: 'text',
        src: 'src',
        html: 'innerHTML',
        outer: 'outerHTML',
        attr: 'getAttribute',
    };

    const shopini_html = `{!! $html !!}`;


    try {
        shopiniRemoveDoms([
            "#branch-banner-iframe", // for install app popup
            '[data-test-id="newsletter-sign-up-form-button"]',
            '[data-uds-child="container"]:has([data-test-id="add-to-cart-sticky-button"])',
            '[data-test-id="nav-cart-link"]',
            '#account-button',
            '[data-test-id="login-button"]',
            '[data-test-id="register-button"]',
            '#add-to-wishlist',
            '.tw-1s8viu2',
        ])
    } catch (error) {
        console.info(error);
    }

    const selectors = {
        name: {
            selector: "#pdp-product-title",
            type: "text",
            data: "",
        },

        description: {
            selector: "div.tw-1c9uoyw:nth-child(1) > div:nth-child(2)",
            type: "text",
            data: "",
        },

        price: {
            selector: '[data-test-id="item-price-pdp"]',
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: '[data-test-id="item-price-pdp"]',
            type: "text",
            data: "",
        },


        image: {
            selector: "div.flex-none:nth-child(2) > img:nth-child(1)",
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: 'div.flex-none:nth-child(2) > img',
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
            selector: "",
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
        let doc = document.querySelectorAll('#size-picker label');
        const data = [];
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
        selectors.brand.data = 'Puma';

        return Object.entries(selectors).map(([name, value]) => ({
            name,
            data: value.data,
        }));
    }
</script>