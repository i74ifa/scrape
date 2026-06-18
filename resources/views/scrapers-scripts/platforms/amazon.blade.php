{{-- before loaded --}}
<script>
    // ==UserScript==
    // @name        New script amazon.com
    // @namespace   Violentmonkey Scripts
    // @match *://*.amazon.com/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==

    const watchers = [
        "#title, #bond-title, #productDescription_feature_div, #productTitleGroupAnchor"
    ];

    const waitForCustom = async (doc, selector, timeout = 3000) => {
        const start = Date.now();
        while (Date.now() - start < timeout) {
            const el = doc.querySelector(selector);
            if (el) return el;
            await tlaboo_sleep(50);
        }
        return null;
    };


    window.tlabooAppendCode = {
        addToCart: {
            before: () => {
            new Watcher('#icdp-iFrame', {
                onExists: async (e) => {
                    try {
                        const doc = e.contentDocument || e.contentWindow.document;

                        await waitForCustom(doc, 'h1', 5000);
                        doc.querySelector('h1').click();
                    } catch (error) {
                        alert(error.message);
                    }
                },
                onRemoved: (e) => {
                    //
                }
            })
            },
            onScrape: async (data) => {
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
    const selectorTypes = {
        text: 'text',
        src: 'src',
        html: 'innerHTML',
        outer: 'outerHTML',
        attr: 'getAttribute',
    };

    const ZIP_CODE = '96162';

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
            await tlaboo_sleep(300);
        }

        throw 'Failed to open ZIP modal';
    };

    /* main */
    const tlaboo_changeZipCode = async () => {
        const nav = await waitFor('#nav-subnav-container', 25000);
        if (!nav) return;

        const text = nav.querySelector('#glow-ingress-single-line')?.textContent || '';
        if (text.includes(ZIP_CODE)) {
            nav.style.display = 'none';
            return;
        }

        const input = await openGlowModal();

        const submit = await waitFor('#GLUXMobileHiddenZipCode [type="submit"]');
        if (!submit) throw 'Submit button not found';

        input.focus();
        input.value = ZIP_CODE;
        input.dispatchEvent(new Event('input', {
            bubbles: true
        }));
        input.dispatchEvent(new Event('change', {
            bubbles: true
        }));

        await tlaboo_sleep(100);
        submit.click();
    };

    /* run */
    tlaboo_changeZipCode().catch(console.error);

    const tlaboo_html = `{!! $html !!}`;


    try {
        let lastPrice = '';
        const priceSelector = '#twister-plus-price-data-price, #apex_mobile_feature_div .a-price-whole, #attach-base-product-price';

        tlabooMountIqdBadge(priceSelector);

        tlabooWatchVariants(['#twister-plus-mobile-inline-twister', '.a-size-base', '#inline-twister-row-size_name'], async () => {
            // Lock add-to-cart while the new variant's price is in flight —
            // submitting now would attach stale price/variant data.
            tlabooSetCartLoading(true, { style: 'text' });

            try {
                const deadline = Date.now() + 8000;

                // 1. Poll until the price differs from the last captured value
                //    — that's when Amazon's variant request has landed.
                while (Date.now() < deadline) {
                    const el = document.querySelector(priceSelector);
                    const current = (el?.value ?? el?.textContent ?? '').trim();
                    if (el && current && current !== lastPrice) break;
                    await tlaboo_sleep(50);
                }

                // 2. Then wait for the price element to stop mutating
                //    (Amazon often writes a placeholder before the real number).
                const el = await tlabooWaitStable(priceSelector, { quietMs: 400, timeoutMs: deadline - Date.now() });
                lastPrice = (el?.value ?? el?.textContent ?? '').trim();
                console.log('new price:', lastPrice);
            } finally {
                tlabooSetCartLoading(false);
            }
        });
    } catch (error) {
        console.info(error);
    }

    try {
        tlabooRemoveDoms([
            '#add-to-cart-button',
            '#buy-now-button',

            '#buyNow',
            '#addToCart',
            '#add-to-wishlist-button-submit',

            '#addToCart_feature_div',
            '#buyNow_feature_div',
            '#nav-button-cart',
            '#nav-button-avatar',
            '#hmenu-header-account',
            '#buybox.addToCart',
            '#a-autoid-2',
            '#usedAccordionCaption_feature_div',
            '#a-autoid-4',
            '#usedAccordionRow',
            '.aod-offer-atc-column:has([name="submit.addToCart"])',
            '#nav-logobar-greeting',
            '#mobileQuantityDropDown',
            'select[name="quantity"]',
            '#haul-buybox-wrapper',
            '#geex-cpi-widget',
            '.haul-qs-link',
            '#bondBuyBox',
            '#heart',
            '.nav-a',
            '#buyBoxAccordion #snsAccordionRowMobileMiddle',
        ])
    } catch (error) {
        console.info(error);
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

    document.addEventListener('btnTextChanged', async e => {
        console.debug('test');
        const res = await window.flutter_inappwebview
            .callHandler('changeVariants', {
                test: 'data',
                userId: 5
            });
    });

    const selectors = {
        name: {
            selector: "#title, #bond-title",
            type: "text",
            data: "",
        },

        description: {
            selector: "meta[name=description]",
            type: "text",
            data: "",
        },

        price: {
            selector: "#attach-base-product-price, #twister-plus-price-data-price, #haul-buybox-price-tab .aok-offscreen, #haul-buybox-price-tab span, #apex_mobile_feature_div .a-price-whole",
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: "#attach-base-product-price, #twister-plus-price-data-price, #haul-buybox-price-tab .aok-offscreen, #haul-buybox-price-tab span, #apex_mobile_feature_div .a-price-whole",
            type: "text",
            data: "",
        },


        image: {
            selector: "#main-image",
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: "#image-block ol",
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
            selector: "#amznStoresBylineLogoTextContainer a, #bond-byLine",
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
            selector: "#twister-plus-mobile-inline-twister input[aria-checked=\'true\']",
            type: "html",
            data: "",
        },

        id: {
            selector: ".product-intro__head-sku [data-clipboard-text]",
            type: "attr",
            data: "",
            attr: "data-clipboard-text",
        },
    };


    function getSelectedVariant() {
        let doc = document.querySelectorAll('#twister-plus-mobile-inline-twister input[aria-checked="true"]');

        // get aria-label to arrayy
        const data = [];
        doc.forEach(el => {
            data.push(el.getAttribute('aria-label'));
        })

        if (data.length === 0) {
            doc = document.querySelectorAll('.a-button-selected input').forEach(el => {
                data.push(el.getAttribute('aria-label'));
            });
        }

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
        const priceSelectors = [
            '#attach-base-product-price',
            '#haul-buybox-price-tab .aok-offscreen',
            '#haul-buybox-price-tab span',
            '#apex_mobile_feature_div:has(.a-price-whole) .priceToPay span:nth-child(2)',
            '#apex_mobile_feature_div .a-price:has(.a-offscreen) span:nth-child(1)',
        ];

        let price = '';

        if (document.querySelector('#twister-plus-price-data-price')) {
            price = document.querySelector('#twister-plus-price-data-price').value;
        }

        if (price === "") {
            priceSelectors.forEach(selector => {
                console.log(selector);
                price = document.querySelector(selector)?.textContent;
                if (price !== "") return;
            });
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
