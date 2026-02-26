{{-- before loaded --}}

<script>
    // ==UserScript==
    // @name        New script {name}
    // @namespace   Violentmonkey Scripts
    // @match *://*.jarir.com/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==

    const watchers = [
        "#product"
    ];

    // if you want append before and after initial
    window.tlabooAppendCode = {
        initial: {
            before: () => { },
            after: () => { }
        },

        addToCart: {
            before: () => {

            },
            onScrape: async (data) => {
                return data;
            },
            after: () => { }
        }
    }
</script>

{{-- on page started --}}
@include('scrapers-scripts.partials.helpers')

<script id="tlaboo-script">

    const tlaboo_html = `{!! $html !!}`;

    try {
        tlabooRemoveDoms([])
    } catch (error) {
        console.info(error);
    }


    const selectors = {
        name: {
            selector: ".product-title .product-title__title",
            type: "text",
            data: "",
        },

        description: {
            selector: "",
            type: "text",
            data: "",
        },

        price: {
            selector: ".price-box__row  .price.price--pdp",
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: ".price-box__row  .price.price--pdp",
            type: "text",
            data: "",
        },


        image: {
            selector: ".image--card img:nth-child(2)",
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: ".image--card img",
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
            selector: ".brand-name",
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

    function trims(value) {
        if (value === null || value === undefined) {
            return "";
        }

        return value.replace(/\s+/g, ' ')
            .replace('/', '')
            .trim();
    }
    function getSelectedVariant() {
        let doc = document.querySelectorAll('[data-testid="variantsLabel"]');
        const data = [];
        doc.forEach(el => {
            data.push(trims(el.textContent));
        });
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

        selectors.name.data = trims(document.querySelector(selectors.name.selector).textContent);
        selectors.images.data = getSelector(selectors.images.selector, 'src', false);
        selectors.image.data = document.querySelector(selectors.image.selector)?.src ?? selectors.images.data[0] ?? "";
        selectors.price.data = trims(price);
        selectors.originalPrice.data = trims(price);
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