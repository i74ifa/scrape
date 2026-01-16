{{-- before loaded --}}

<script>
    // ==UserScript==
    // @name        New script {name}
    // @namespace   Violentmonkey Scripts
    // @match *://*.flo.com.tr/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==

    const watchers = [
        '.page__product-detail'
    ];

    // if you want append before and after initial
    window.shopiniAppendCode = {
        initial: {
            before: () => {},
            after: () => {}
        },

        addToCart: {
            before: () => {

            },
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
            '.header__minibasket-icon',
            '[data-test="mobile-open-to-wishlist"]',
            '[data-test="mobile-open-to-login-form"]',
            '.add-wishlist:has(.fav-icon)',
            '.add-to-cart',
            '.product-detail-most',
            '#vl-side-coupon-container',
            '.product__actions:has(.js-list-add-to-cart)'
        ])
    } catch (error) {
        console.info(error);
    }


    const selectors = {
        name: {
            selector: ".js-product-name",
            type: "text",
            data: "",
        },

        description: {
            selector: "",
            type: "text",
            data: "",
        },

        price: {
            selector: ".product-pricing-one__price",
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: ".product-pricing-one__price",
            type: "text",
            data: "",
        },


        image: {
            selector: ".detail__images-wrapper .swiper-slide.swiper-slide-active img",
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: ".detail__images-wrapper .swiper-slide",
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
            selector: ".product-detail__brand",
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
        data.push(
            document.querySelector('.detail__section-title--value')?.textContent || ''
        )

        // size
        data.push(
            document.querySelector('.detail__sizes-head-number em')?.textContent || ''
        )

        return data.filter((a) => a !== '').join(' - ');
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

        selectors.name.data = document.querySelector(selectors.name.selector)?.textContent;
        selectors.name.data = selectors.name.data.trim();

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
