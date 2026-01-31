{{-- before loaded --}}
<script>
    // ==UserScript==
    // @name        New script Nike
    // @namespace   Violentmonkey Scripts
    // @match *://*.nike.com/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==

    const watchers = [
        "#pdp_product_title"
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
            '.smart-banner',
            '#singular-banner',
            '#floating-atb-wrapper',
            '[data-testid="favorite-button"]',
            '[data-testid="sign-in-button"]',
            '[data-testid="user-tools-container"] a',
            '.sign-in-wrapper',
            '#buyTools',
        ]) // remove doms
    } catch (error) {
        console.info(error);
    }

    const selectors = {
        name: {
            selector: "#pdp_product_title",
            type: "text",
            data: "",
        },

        description: {
            selector: "",
            type: "text",
            data: "",
        },

        price: {
            selector: '[data-testid="currentPrice-container"], [data-test="product-price"]',
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: '[data-testid="currentPrice-container"], [data-test="product-price"]',
            type: "text",
            data: "",
        },


        image: {
            selector: '[data-testid="mobile-image-carousel-list"] img',
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: '[data-testid="mobile-image-carousel-list"]',
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
            selector: ".q",
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
        
        
        let attr = document.querySelector('.nds-radio input:checked + label')?.textContent ?? '';
        let size = document.querySelector('[data-testid="size-selector"] input:checked + label')?.textContent ?? '';


        data.push(attr);
        data.push(size);
        data.filter((a) => a !== '');

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

        selectors.name.data = document.querySelector(selectors.name.selector).textContent.trim();
        selectors.images.data = [];
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
