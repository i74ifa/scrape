{{-- before loaded --}}

<script>
    // ==UserScript==
    // @name        New script Light in the box
    // @namespace   Violentmonkey Scripts
    // @match *://*.lightinthebox.com/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==

    const watchers = [
        '#product-main'
    ];

    window.tlabooAppendCode = {
        addToCart: {
            before: () => {

            },
            onScrape: async (data) => {
                
                const shippingFeeModal = await document.querySelector('.shipping-fee-tips');
                if (!shippingFeeModal) {
                    return data;
                }

                const shippingFee = document.querySelector('.shipping-fee-table > table:nth-child(1) > tbody:nth-child(1) > tr:nth-child(2) > td:nth-child(2)').textContent;

                document.querySelector('#shipping-fee-modal').style.display = 'none'
                
                data.find(item => item.name === "shippingPrice").data = shippingFee;

                return data;
            },
        },
        initial: {
            before: () => {},
            after: () => {}
        }
    }

</script>

{{-- on page started --}}
@include('scrapers-scripts.partials.helpers')

<script id="tlaboo-script">
    const tlaboo_html = `{!! $html !!}`;

    try {
        tlabooRemoveDoms([
            '.btnwrap .add-to-cart',
            '.top_open_app',
            '.header-cart-icon',
            '.new-header-cart',
            '.account-entrance:has(.i-new-account)',
            '.fav-flag:has(.ifont-favorite)',
            '.app-price-wrap',
            '.i-new-cart-big',
            '.shopping-cart-sidebar',
            '.shopping-cart-icon'
        ])
    } catch (error) {
        console.info(error);
    }


    const selectors = {
        name: {
            selector: ".title-content",
            type: "text",
            data: "",
        },

        description: {
            selector: "",
            type: "text",
            data: "",
        },

        price: {
            selector: ".sale-price",
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: ".sale-price",
            type: "text",
            data: "",
        },


        image: {
            selector: "#product-main .slide-wrap img",
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: "#product-main .slide-wrap img",
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
        let doc = document.querySelectorAll('section .purchase-infos .attr-name');


        const data = [];
        doc.forEach(el => {
            data.push(el.textContent);
        })
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

        selectors.name.data = document.querySelector(selectors.name.selector).textContent.trim();
        selectors.images.data = getSelector(selectors.images.selector, 'src', false);
        selectors.image.data = document.querySelector(selectors.image.selector)?.src ?? selectors.images.data[0] ?? "";
        selectors.price.data = price.trim();
        selectors.originalPrice.data = price.trim();
        selectors.selectedVariant.data = getSelectedVariant();
        selectors.brand.data = "LightInTheBox";

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