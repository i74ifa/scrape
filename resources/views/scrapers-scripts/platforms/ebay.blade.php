{{-- before loaded --}}
<script>
    const watchers = [
        ".x-item-title h1"
    ];
</script>

{{-- on page started --}}
@include('scrapers-scripts.partials.helpers')

<script id="shopini-script">
    // ==UserScript==
    // @name        New script ebay.com
    // @namespace   Violentmonkey Scripts
    // @match *://*.ebay.com/*
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

    /* retry modal open */
    const openGlowModal = async (maxRetry = 5) => {
        for (let i = 0; i < maxRetry; i++) {
            const trigger = await waitFor('#glow-ingress-block', 1000);
            if (!trigger) continue;

            trigger.click();

            // wait for input to appear
            const input = await waitFor('#GLUXZipUpdateInput', 1500);
            if (input) return input;

            // click ignored → retry
            await shopini_sleep(300);
        }

        throw 'Failed to open ZIP modal';
    };

    const shopini_html = `{!! $html !!}`;

    try {
        shopiniRemoveDoms([
            '.gh-module-with-target',
            'a[data-testid="ux-call-to-action"]',
            '.ux-call-to-action',
            '.x-watch-heart-btn',
            '[data-testid="x-bin-action"]',
            '[data-testid="x-atc-action"]',
            'a[href*="/atc/"]',
            '.gh-mweb-header__right a:has(svg.icon--profile)',
            '.gh-mweb-header__right a:has(svg.icon--cart)',
            '.x-buybox-cta-wrapper',
            '.x-volume-pricing__pill'
        ])
    } catch (e) {
        //
    }


    (function initBtnObserver(retryDelay = 300, maxTries = 50) {
        let tries = 0;

        const start = () => {
            const elements = document.querySelectorAll('#attrPromotionWrap [role="listitem"]');

            if (elements.length) {
                elements.forEach(el => {
                    if (el.dataset.observed) return;

                    el.dataset.observed = '1';
                    observeBtnText(el, 200);
                });
                return;
            }

            // retry if not found
            if (++tries < maxTries) {
                setTimeout(start, retryDelay);
            }
        };

        start();
    })();


    function observeBtnText(el, delay = 150) {
        let last = el.innerHTML;
        let timer;

        const observer = new MutationObserver(() => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                if (el.innerHTML !== last) {
                    last = el.innerHTML;
                    document.dispatchEvent(
                        new CustomEvent('btnTextChanged', {
                            detail: {
                                element: el,
                                value: last
                            }
                        })
                    );
                }
            }, delay);
        });

        observer.observe(el, {
            childList: true,
            subtree: true,
            characterData: true,
        });
    }

    const selectors = {
        name: {
            selector: "h1.x-item-title__mainTitle span.ux-textspans",
            type: "text",
            data: "",
        },

        description: {
            selector: ".ux-chevron__body .ux-layout-section__textual-display--shortDescriptionWithQuote .ux-textspans",
            type: "text",
            data: "",
        },

        price: {
            selector: ".x-price-primary span.ux-textspans",
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: ".x-price-primary span.ux-textspans",
            type: "text",
            data: "",
        },


        image: {
            selector: ".ux-image-carousel-item.active img",
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: "div.ux-image-carousel.zoom.img-transition-medium",
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
            selector: ".seo-breadcrumbs-container li:last-child a span",
            type: "text",
            data: "",
        },

        brand: {
            selector: ".ux-labels-values--brand .ux-labels-values__values-content span",
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
            selector: ".vim.x-sku button .btn__text",
            type: "html",
            data: "",
        },

        id: {
            selector: null,
            type: "attr",
            data: "",
            attr: "data-clipboard-text",
        },
    };


    function getSelectedVariant() {
        // Assuming selectors is available in the scope
        const doc = document.querySelectorAll(selectors.selectedVariant.selector);

        if (!doc || doc.length === 0) {
            return null;
        }

        const data = [];

        doc.forEach(el => {
            data.push(el.textContent || null);
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