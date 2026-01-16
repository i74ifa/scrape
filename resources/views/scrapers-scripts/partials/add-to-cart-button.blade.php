<div id="shopini-add-to-cart-wrapper">

    <!-- price aria -->
     <!-- <div style="display: flex; justify-content: center;">
         <div id="shopini-price">
             $100
         </div>
     </div> -->


    <button class="btn btn-primary" id="shopini-add-to-cart">
        <span class="shopini-add-to-cart">{{ __('Add To Cart') }}</span>
        <div class="shopini-loader"></div>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path fill="currentColor" fill-rule="evenodd" d="M3.04 2.292a.75.75 0 0 0-.497 1.416l.261.091c.668.235 1.107.39 1.43.549c.303.149.436.27.524.398c.09.132.16.314.2.677c.04.38.042.875.042 1.615V9.64c0 2.942.063 3.912.93 4.826c.866.914 2.26.914 5.05.914h5.302c1.561 0 2.342 0 2.893-.45c.552-.45.71-1.214 1.025-2.742l.5-2.425c.347-1.74.52-2.609.076-3.186S18.816 6 17.131 6H6.492a9 9 0 0 0-.043-.738c-.054-.497-.17-.95-.452-1.362c-.284-.416-.662-.682-1.103-.899c-.412-.202-.936-.386-1.552-.603zM13 8.25a.75.75 0 0 1 .75.75v1.25H15a.75.75 0 0 1 0 1.5h-1.25V13a.75.75 0 0 1-1.5 0v-1.25H11a.75.75 0 0 1 0-1.5h1.25V9a.75.75 0 0 1 .75-.75" clip-rule="evenodd" />
            <path fill="currentColor" d="M7.5 18a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3m9 0a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3" />
        </svg>
    </button>
</div>


<style>
    @import url('https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@100..900&display=swap');

    :root {
        --icon-size: 35px;
        --add-to-cart-size: 60px;
        --add-to-cart-bg: #000000c9;
        --add-to-cart-bg-hover: #000000e1;
    }

    #shopini-add-to-cart-wrapper button {
        all: unset;
        /* reset everything */
        box-sizing: border-box;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        cursor: pointer;
        user-select: none;
    }

    #shopini-add-to-cart-wrapper button:hover {
        all: unset;
    }

    #shopini-add-to-cart-wrapper button:active {
        all: unset;
    }

    #shopini-add-to-cart-wrapper button:focus-visible {
        all: unset;

    }

    #shopini-add-to-cart-wrapper button:disabled {
        all: unset;

    }


    #shopini-add-to-cart-wrapper {
        display: grid;
        gap: 10px;
        transform: translate(50%, 50%);
        white-space: nowrap;
        position: fixed;
        bottom: 99px;
        right: 51%;
        z-index: 9999;

    }

    #shopini-add-to-cart-wrapper #shopini-add-to-cart {
        display: none;
        gap: 10px;
        align-items: center;
        justify-content: center;
        background: var(--add-to-cart-bg);
        position: relative;
        font-size: 16px;
        font-weight: 600;
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 37px;
        cursor: pointer;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.4);
        transition: all 0.3s ease;
        overflow: hidden;
        font-family: "Noto Kufi Arabic", sans-serif;
        /* width: var(--add-to-cart-size); */
        /* height: var(--add-to-cart-size); */
        padding: 5px 14px;
    }

    #shopini-add-to-cart-wrapper #shopini-add-to-cart svg {
        display: inline-block;
        width: var(--icon-size);
        height: var(--icon-size);
    }

    #shopini-add-to-cart-wrapper #shopini-add-to-cart:hover {
        background: var(--add-to-cart-bg-hover);
        border-color: rgba(255, 255, 255, 0.5);
        box-shadow: 0 12px 48px rgba(0, 0, 0, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.6);
        transform: translateY(-2px);
    }


    #shopini-add-to-cart-wrapper #shopini-add-to-cart:active {
        scale: 0.98;
        transition: transform 0.2s ease;
    }

    #shopini-add-to-cart-wrapper #shopini-add-to-cart.shopini-loading {
        pointer-events: none;
        cursor: default;
        color: transparent !important;
    }

    #shopini-add-to-cart-wrapper #shopini-add-to-cart .shopini-loader {
        display: none;
        width: var(--icon-size);
        aspect-ratio: 1;
        border-radius: 50%;
        background:
            radial-gradient(farthest-side, #ffffff 94%, #0000) top/4px 4px no-repeat,
            conic-gradient(#0000 30%, #ffffff);
        -webkit-mask: radial-gradient(farthest-side, #0000 calc(100% - 4px), #000 0);
        animation: l13 1s infinite linear;
    }

    #shopini-add-to-cart-wrapper #shopini-add-to-cart.shopini-loading .shopini-loader {
        display: block;
    }


    #shopini-price {
        display: flex;
        align-items: center;
        color: white;
        font-size: 18px;
        font-weight: 600;
        z-index: 9999;
        background: var(--add-to-cart-bg);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 37px;
        padding: 8px 12px;
    }

    @keyframes l13 {
        100% {
            transform: rotate(1turn);
        }
    }
</style>