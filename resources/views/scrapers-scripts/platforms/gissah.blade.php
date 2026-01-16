{{-- before loaded --}}
<script>
    const watchers = [
        "#product_details"
    ];
</script>

<script id="shopini-script">
    // ==UserScript==
    // @name        New script gissah.com
    // @namespace   Violentmonkey Scripts
    // @match *://*.gissah.com/*
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
            ".open-cart-sidebar",
            "#add_to_cart_wrap",
            ".o_we_buy_now",
            ".gissah-wishlist-link",
            ".footer-mobile-selector:has(.country-select)",
            "#side-menu .open-cart-sidebar",
            "#side-menu .side-menu-icon:has(.fa-heart-o)",
        ])
    } catch (error) {
        console.info(error);
    }

    const selectors = {
        name: {
            selector: ".gissah-product-title",
            type: "text",
            data: "",
        },

        description: {
            selector: "",
            type: "text",
            data: "",
        },

        price: {
            selector: ".product_price .oe_currency_value",
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: ".product_price .oe_currency_value",
            type: "text",
            data: "",
        },


        image: {
            selector: ".gissah-mobile-slide img",
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: ".gissah-mobile-slide img",
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
        let doc = document.querySelectorAll('');
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
        // selectors.selectedVariant.data = getSelectedVariant();
        selectors.brand.data = "Gissah";

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