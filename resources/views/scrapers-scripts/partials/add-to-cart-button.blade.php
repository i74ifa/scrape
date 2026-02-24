<div id="tlaboo-add-to-cart-wrapper" dir="rtl">
    <div id="tlaboo-price" style="display: none;"></div>

    <button class="btn btn-primary" id="tlaboo-add-to-cart">
        <div class="tlaboo-loader"></div>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path fill="currentColor" fill-rule="evenodd" d="M3.04 2.292a.75.75 0 0 0-.497 1.416l.261.091c.668.235 1.107.39 1.43.549c.303.149.436.27.524.398c.09.132.16.314.2.677c.04.38.042.875.042 1.615V9.64c0 2.942.063 3.912.93 4.826c.866.914 2.26.914 5.05.914h5.302c1.561 0 2.342 0 2.893-.45c.552-.45.71-1.214 1.025-2.742l.5-2.425c.347-1.74.52-2.609.076-3.186S18.816 6 17.131 6H6.492a9 9 0 0 0-.043-.738c-.054-.497-.17-.95-.452-1.362c-.284-.416-.662-.682-1.103-.899c-.412-.202-.936-.386-1.552-.603zM13 8.25a.75.75 0 0 1 .75.75v1.25H15a.75.75 0 0 1 0 1.5h-1.25V13a.75.75 0 0 1-1.5 0v-1.25H11a.75.75 0 0 1 0-1.5h1.25V9a.75.75 0 0 1 .75-.75" clip-rule="evenodd" />
            <path fill="currentColor" d="M7.5 18a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3m9 0a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3" />
        </svg>
        <span id="tlaboo-add-to-cart-text">إضافة للسلة</span> 
    </button>
</div>


<style>
    @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@100;200;300;400;500;600;700&display=swap');

    :root {
        --icon-size: 28px;
        --add-to-cart-size: 54px;
        --add-to-cart-bg: rgba(255, 255, 255, 0.15);
        --add-to-cart-bg-hover: rgba(255, 255, 255, 0.85);
        --add-to-cart-text-color: rgba(0, 0, 0, 0.9);
        --add-to-cart-icon-color: #fff;
    --neon-color: rgba(255, 255, 255, 0.6);
    --neon-glow-spread: 15px;
    }

    #tlaboo-add-to-cart-wrapper button {
        all: unset;
        /* reset everything */
        box-sizing: border-box;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        cursor: pointer;
        user-select: none;
    }

    #tlaboo-add-to-cart-wrapper button:hover {
        all: unset;
    }

    #tlaboo-add-to-cart-wrapper button:active {
        all: unset;
    }

    #tlaboo-add-to-cart-wrapper button:focus-visible {
        all: unset;

    }

    #tlaboo-add-to-cart-wrapper button:disabled {
        all: unset;

    }


    #tlaboo-add-to-cart-wrapper {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: center;
        position: fixed;
        bottom: 99px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999999;
        white-space: nowrap;
        font-family: "IBM Plex Sans Arabic", sans-serif;
    }

    #tlaboo-add-to-cart-wrapper #tlaboo-add-to-cart {
        display: none;
        /* width: var(--add-to-cart-size); */
        height: var(--add-to-cart-size);
        align-items: center;
        justify-content: center;
        background: var(--add-to-cart-bg);
        position: relative;
        color: var(--add-to-cart-text-color);
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0px 23px;
        border-radius: 29px;
        cursor: pointer;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.4);
        transition: all 0.3s ease;
        overflow: hidden;
        animation: neon-pulse 2s infinite ease-in-out;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    #tlaboo-add-to-cart-wrapper #tlaboo-add-to-cart::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 150%;
        height: 300%;
        background: conic-gradient(transparent, #00d2ffba, #9d50bbba, transparent 20%);
        animation: rotate-neon 6s linear infinite;
        z-index: -2;
        filter: blur(16px);
    }

    #tlaboo-add-to-cart-wrapper #tlaboo-add-to-cart::after {
        content: "";
        position: absolute;
        inset: 2px;
        background: var(--add-to-cart-bg);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border-radius: 29px;
        z-index: -1;
    }

    #tlaboo-add-to-cart-wrapper #tlaboo-add-to-cart:hover {
        --neon-color: rgba(255, 255, 255, 0.9);
        animation: none;
        /* إيقاف النبض عند التحويم لثبات الإضاءة */
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.2),
            inset 0 0 10px rgba(255, 255, 255, 0.2);
    }

    #tlaboo-add-to-cart-wrapper #tlaboo-add-to-cart svg {
        display: inline-block;
        width: var(--icon-size);
        height: var(--icon-size);
        color: "#fff";
    }

    #tlaboo-add-to-cart-wrapper #tlaboo-add-to-cart:hover {
        background: var(--add-to-cart-bg-hover);
        border-color: rgba(255, 255, 255, 0.5);
        box-shadow: 0 12px 48px rgba(0, 0, 0, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.6);
        transform: translateY(-2px);
    }


    #tlaboo-add-to-cart-wrapper #tlaboo-add-to-cart:active {
        scale: 0.95;
        transition: transform 0.2s ease;
    }

    #tlaboo-add-to-cart-wrapper #tlaboo-add-to-cart.tlaboo-loading {
        pointer-events: none;
        cursor: default;
        color: transparent !important;
    }

    #tlaboo-add-to-cart-wrapper #tlaboo-add-to-cart.tlaboo-loading svg {
        display: none;
    }

    #tlaboo-add-to-cart-wrapper #tlaboo-add-to-cart .tlaboo-loader {
        display: none;
        width: calc(var(--icon-size) + 4px);
        aspect-ratio: 1;
        border-radius: 50%;
        background:
            radial-gradient(farthest-side, #ffffff 94%, #0000) top/4px 4px no-repeat,
            conic-gradient(#0000 30%, #ffffff);
        -webkit-mask: radial-gradient(farthest-side, #0000 calc(100% - 4px), #000 0);
        animation: l13 1s infinite linear;
    }

    #tlaboo-add-to-cart-wrapper #tlaboo-add-to-cart.tlaboo-loading .tlaboo-loader {
        display: block;
    }


    #tlaboo-price {
        display: flex;
        align-items: center;

        color: var(--add-to-cart-text-color);
        font-size: 16px;
        font-weight: 700;

        z-index: 9999;

        /* White glass background */
        background: var(--add-to-cart-bg);

        /* Soft glass border */
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 37px;

        padding: 5px 15px;
        height: var(--add-to-cart-size);

        /* Glass blur */
        backdrop-filter: blur(14px) saturate(180%);
        -webkit-backdrop-filter: blur(14px) saturate(180%);

        /* Glassy depth */
        box-shadow:
            0 8px 32px rgba(0, 0, 0, 0.12),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    #tlaboo-add-to-cart-text {
        font-size: 14px;
        font-weight: 600;
        color: var(--add-to-cart-text-color);
        margin-inline-start: 10px;
        opacity: 0.9;
    }

    @keyframes l13 {
        100% {
            transform: rotate(1turn);
        }
    }

    @keyframes rotate-neon {
        0% {
            transform: translate(-50%, -50%) rotate(0deg);
        }
        100% {
            transform: translate(-50%, -50%) rotate(360deg);
        }
    }

</style>