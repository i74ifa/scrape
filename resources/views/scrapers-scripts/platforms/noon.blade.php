{{-- before loaded --}}

<script>
    // ==UserScript==
    // @name        New script {name}
    // @namespace   Violentmonkey Scripts
    // @match *://*.noon.com/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==

    const watchers = [
        '[data-app-install-product-url^="product/"]'
    ];

    function shopiniAppendScript() {
        const dom = document.createElement('script');
        dom.src = 'https://cdn.jsdelivr.net/npm/dom-regex@0.0.3/lib/dom-regex.js';
        document.body.appendChild(dom);
    }

    // if you want append before and after initial
    window.shopiniAppendCode = {
        initial: {
            before: () => {
                shopiniAppendScript()
            },
            after: (data) => {
                new Watcher('#shopini-add-to-cart-wrapper', {
                    onExists: () => {
                        //
                    },
                    onRemoved: () => {
                        document.body.appendChild(data.addToCartWrapper);
                    }
                })
            }
        },

        addToCart: {
            before: () => {

            },
            // onScrape: async (data) => {},
            after: () => {}
        }
    }
</script>

{{-- on page started --}}
@include('scrapers-scripts.partials.helpers')

<script id="shopini-script">
    const shopini_html = `{!! $html !!}`;

    try {
        shopiniRemoveDoms([
            ".AppInstallBanner-module-scss-module__BSeqdG__container",
            "#catalog-page-container > div.ProductDetailsMobile-module-scss-module__rPhIAG__pdpContainer > div.AddToCartCtaMobile-module-scss-module__CV4wca__ctaFixedWrapper > div.AddToCartCtaMobile-module-scss-module__CV4wca__atcCtaCtr",
            "#bottom-tabs > a:nth-child(5)",
            '#bottom-tabs > a:nth-child(4)'
        ])
    } catch (error) {
        console.info(error);
    }


    const selectors = {
        name: {
            selector: /ProductDetailsMobile-module-scss-module__[^_]+__productTitle/,
            type: "text",
            data: "",
        },

        description: {
            selector: "",
            type: "text",
            data: "",
        },

        price: {
            selector: /Price-module-scss-module__[^_]+__productPrice/,
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: /Price-module-scss-module__[^_]+__productPrice/,
            type: "text",
            data: "",
        },


        image: {
            // .GalleryMobileV2-module-scss-module__*__magicLensWrapper .ProductImage-module-scss-module__*__imageWrapper img
            selector: /GalleryMobileV2-module-scss-module__[^_]+__magicLensWrapper .ProductImage-module-scss-module__[^_]+__imageWrapper img/,
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: /GridAnimation-module-scss-module__[^_]+__magicImageElement|GalleryMobileV2-module-scss-module__[^_]+__productImage/,
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
            data: ".a",
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

        let price = DomRegex.one(selectors.price.selector)?.textContent || "";

        if (price === "") {
            price = DomRegex.one(selectors.price.selector)?.value;
        }

        console.log(price)

        selectors.name.data = DomRegex.one(selectors.name.selector)?.textContent || "";
        // selectors.images.data = document.querySelector(selectors.images.selector, 'src', false);
        selectors.image.data = DomRegex.one(selectors.image.selector)?.src;
        selectors.price.data = price;
        selectors.originalPrice.data = price;
        selectors.selectedVariant.data = getSelectedVariant();
        selectors.brand.data = "";

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
