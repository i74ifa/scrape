{{-- before loaded --}}
<script>
    // ==UserScript==
    // @name        New script rarebeauty.com
    // @namespace   Violentmonkey Scripts
    // @match *://*.rarebeauty.com/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==

    const watchers = [
        ".pv-actions:has(#btnAddToBagText)"
    ];
</script>

{{-- on page started --}}
@include('scrapers-scripts.partials.helpers')

<script id="shopini-script">
    const shopini_html = `{!! $html !!}`;


    try {
        shopiniRemoveDoms([
            ".pi__bottom-wrapper:has(.pi__quick-add)",
            "#cartCloseBtn",
            ".nav__button:has(.icon--account)",
            '#localizationModal'
        ])
    } catch (error) {
        console.info(error);
    }

    const selectors = {
        name: {
            selector: ".pv-header .pv-title",
            type: "text",
            data: "",
        },

        description: {
            selector: "",
            type: "text",
            data: "",
        },

        price: {
            selector: ".js-price-original",
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: ".js-price-original",
            type: "text",
            data: "",
        },


        image: {
            selector: "#gallery img",
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: "#gallery img",
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
        // let doc = document.querySelectorAll('');
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

        price = price.trim();

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