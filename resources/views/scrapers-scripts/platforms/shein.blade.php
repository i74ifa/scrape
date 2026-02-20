{{-- before loaded --}}
<script>
    // ==UserScript==
    // @name        New script shein.com
    // @namespace   Violentmonkey Scripts
    // @match *://*.shein.com/*
    // @run-at      document-body
    // @grant       none
    // @version     1.0
    // @author      -
    // @description 12/14/2025, 9:13:36 PM
    // ==/UserScript==
    const watchers = [
        ".goods-detail-top"
    ];


    // if not exists
    function setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie =
            `${name}=${value}; expires=${date.toUTCString()}; path=/; domain=${window.location.hostname.replace('www', '')}`;
    }


    function setLocationCurrent() {
        let locationCurrent = localStorage.getItem('LOCATION_CURRENT');
        locationCurrent = JSON.parse(locationCurrent);
        if (locationCurrent && locationCurrent?.value === 'SA') {
            return;
        }
        localStorage.setItem('LOCATION_CURRENT',
            `{"id":"186","country":"Saudi Arabia","value":"SA","phone_code":"+966","host":"m.shein.com/ar-en","lang":"ar-en","siteUid":"pwaren"}`
        );
    }

    function setIpCountry() {
        let ipCountry = localStorage.getItem('ipCountry');
        ipCountry = JSON.parse(ipCountry);
        if (ipCountry && ipCountry?.value?.countryId == 186) {
            return;
        }

        const now = new Date();
        const oneMonthLater = new Date(now);
        oneMonthLater.setMonth(now.getMonth() + 1);

        localStorage.setItem('ipCountry',
            `{"value":{"countryId":"186","countryAbbr":"SA","formatCountryAbbr":"SA"},"end":${oneMonthLater.getTime()}}`);


    }

    function setAddressCookie() {
        // set address
        let addressCookie = localStorage.getItem('addressCookie');
        addressCookie = JSON.parse(addressCookie);
        if (addressCookie && addressCookie?.countryId == 186) {
            return;
        }

        // localStorage.setItem('addressCookie',
        //     `{"addressId":"","countryName":"Saudi Arabia","value":"A","countryId":"224","state":"","stateId":"","cityId":"","city":"","district":"","districtId":"","postcode":"","memberId":"","siteUid":"pwaren","addrFromFlag":"2","isIpAddress":0,"ipDisplayLevel":"","address1":"","address2":"","street":"","displayAddress":"Saudi Arabia","displayAddressType":0,"displayAddressWithMinLevel":"Saudi Arabia","extraTraceLogs":{"messageText":"请求bff query_user_address获取地址信息","sourceFrom":"bff_query_user_address"},"traceId":"ffa6d27eee3ff6fb:d01f3f38992ce7e0:6eda914bd27e7be3:0"}`
        // );
    }


    function getCookie(name) {
        return document.cookie
            .split('; ')
            .find(row => row.startsWith(name + '='))
            ?.split('=')[1];
    }

    function setCurrency() {
        let currency = getCookie('currency');
        let setCurrency = false;
        if (!currency) {
            setCurrency = true;
        }

        if (currency && currency != 'USD') {
            setCurrency = true;
        }

        if (setCurrency) {
            setCookie('currency', 'USD', 365);
        }
    }

    window.tlabooAppendCode = {
        initial: {
            before: async () => {
                const shippingContainer = await waitFor('.shippingNewContent');
                if (shippingContainer) {
                    shippingContainer.style.display = "none";
                }
            },
            after: () => {
                // get locals
                try {
                    // setLocationCurrent();
                    // setIpCountry();
                    // setAddressCookie();
                    // setCurrency();
                    
                } catch (error) {
                    console.info(error);
                }
            }
        },
        addToCart: {
            onScrape: async (data) => {
                try {
                    const shippingBtn = document.querySelector('.shippingNewContent__detailInfoLeft-quickDesc');
                    if (shippingBtn) {
                        shippingBtn.click();
                        const shippingFee = await waitFor('.shipping-drawer__costs > span:nth-child(1)');
                        if (!shippingFee) {
                            document.querySelector('.sui-title-nav-item').click()
                            return data;
                        }

                        data.find(item => item.name === "shippingPrice").data = shippingFee?.textContent || 0;

                        // document.querySelector('.sui-title-nav-item').click()
                    }
                } catch (error) {
                    //
                }


                return data;
            }
        }
    }
</script>

{{-- on page started --}}
@include('scrapers-scripts.partials.helpers')

<script id="tlaboo-script">
    const tlaboo_html = `{!! $html !!}`;

    try {
        tlabooRemoveDoms([
            '#add-to-cart-button',
            '.product-add-btn',
            '.product-card__add-btn',
            '.header-icon .bs-c-swich__wrap',
            '.header-icon .bsc-user-container',
            '.header-icon .bsc-mini-cart',
            '.header-icon .bsc-wish-list-entry_wrap',
            '.sidebar-cate__footer',
            '.bsc-common-header__right .bsc-wish-list-entry_wrap',
            '.bsc-common-header__right .bsc-header-cart',
            '.bottom-wrapper__price-wrapper .product-card__add-btn',
            '.product-card__add-bag',
            '#branch-app',
            '.add-cart__animation-normal',
            '#buyBoxAccordion #snsAccordionRowMobileMiddle',
            '.productShippingTitle',
            '.shipping-drawer__to',
            '.footer-cart__icon:has(.footer-cart__icon-warp)',
            '[data-title="userPage"]',
            '[data-title="wishlist"]',
            '[data-title="cart"]',
            '[aria-label="Cart"]',
            '[aria-label="Save"]',
            '[aria-label="User"]',
        ], false)
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
        multiData: {
            selector: "#goodsDetailSchema",
            type: "html",
            data: "",
        },

        name: {
            selector: "#goods-name-main",
            type: "text",
            data: "",
        },

        description: {
            selector: null,
            type: "text",
            data: "",
        },

        price: {
            selector: "#productMainPriceId",
            type: "text",
            data: "",
        },

        originalPrice: {
            selector: "#productMainPriceId",
            type: "text",
            data: "",
        },

        stock: {
            selector: null,
            type: "text",
            data: "",
        },

        image: {
            selector: ".crop-image-container img",
            type: "attr",
            data: "",
            attr: "src",
        },

        images: {
            selector: ".crop-image-container img",
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

        customerRating: {
            selector: null,
            type: "text",
            data: "",
        },

        brand: {
            selector: null,
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
            selector: ".attrPromotionWrap",
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
        const doc = document.querySelector(selectors.selectedVariant.selector);

        // Find color elements
        const colorContainer = doc.querySelector('div #goods-color-main');
        const colors = colorContainer ? colorContainer.querySelectorAll('ul li') : [];

        let colorName = null;
        colors.forEach(color => {
            if (color.classList.contains('color-active')) {
                const link = color.querySelector('a');
                if (link) {
                    colorName = link.getAttribute('aria-label');
                }
            }
        });

        // Find size elements
        const sizeContainer = doc.querySelector('div .goods-size__wrapper');
        const sizes = sizeContainer ? sizeContainer.querySelectorAll('ul li') : [];

        let sizeName = null;
        sizes.forEach(size => {
            if (size.classList.contains('size-active')) {
                const p = size.querySelector('p');
                if (p) {
                    sizeName = p.textContent.trim();
                }
            }
        });

        return [colorName, sizeName].filter(Boolean).join(' - ');
    }

    function getSelector(selector, type, single = true) {
        const data = [];

        if (single) {
            return document.querySelector(selector)[type];
        }

        document.querySelectorAll(selector).forEach(el => {

            let field = el[type];

            //  this for shein only :
            // remove all image if name contain bg-logo
            console.debug(field);
            if (field.includes('bg-logo')) {
                return;
            }

            // remove // from start src
            if (type === 'src') {
                field = field.replace(/^\/\//, "");
            }

            data.push(field);
        });

        return data;
    }



    function scrapeData() {
        selectors.name.data = document.querySelector(selectors.name.selector).textContent;
        selectors.images.data = getSelector(selectors.images.selector, 'src', false);
        selectors.image.data = selectors.images.data[0] ?? "";
        selectors.price.data = document.querySelector(selectors.price.selector)?.textContent;
        selectors.originalPrice.data = document.querySelector(selectors.originalPrice.selector)?.textContent;
        selectors.selectedVariant.data = getSelectedVariant();

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
