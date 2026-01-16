{{-- before loaded --}}

<script>
    // ==UserScript==
    // @name        New script {name}
    // @namespace   Violentmonkey Scripts
    // @match *://*.adidas.com/us/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==

    const watchers = [
        '[data-testid="product-description-mobile"]'
    ];

    // if you want append before and after initial
    // window.shopiniAppendInitial = {
    //     before: () => {},
    //     after: () => {}
    // }
</script>

{{-- on page started --}}
@include('scrapers-scripts.partials.helpers')

<script id="shopini-script">

    const shopini_html = `{!! $html !!}`;

        try {
        shopiniRemoveDoms([])
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
            selector: '[data-testid="main-price"] span:nth-child(2)',
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: '[data-testid="main-price"] span:nth-child(2)',
            type: "text",
            data: "",
        },


        image: {
            selector: "#navigation-target-gallery img:nth-child(1)",
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: "#navigation-target-gallery img",
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
        const size = document.querySelector('.color-chooser_size-value__RVU02')?.textContent ?? '';
        const color = document.querySelector('[data-testid="color-label"]')?.textContent ?? '';

        const data = [
            size,
            color
        ].filter((a) => a !== '');

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
        selectors.brand.data = 'adidas'

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