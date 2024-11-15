<link rel="stylesheet" href="https://unpkg.com/vercel-toast/dist/vercel-toast.css" />
<script async src="https://unpkg.com/vercel-toast"></script>

<style>
    :root {
        --gestoru-principal: oklch(0.85 0.14 166.95);
        --gestoru-secundario: oklch(0.18 0.05 252.84);
        --color-alpha-hover: rgba(0, 0, 0, 0.07);
        --color-alpha-active: rgba(0, 0, 0, 0.130);
        --text-gray: rgba(255, 255, 255, 0.75);
    }


    .wrapper-buttons-header {
        float: right;
        display: flex;
        gap: 1rem;
        z-index: 4;

        @media screen and (max-width: 768px) {
            display: grid;
        }
    }

    .wrapper-header__button {
        a {
            text-decoration: none;
        }
    }

    .btn-actions {
        border-radius: 11.082px;
        display: flex;
        align-items: center;
        padding: 13px 20px;
        text-align: center;
        background: linear-gradient(182deg, #65ecbd 31.03%, #26c78f 99.96%);
        color: #011227 !important;
        border: 1px solid #60c2a0 !important;
        font-size: 16px;
        font-weight: 700;

        @media screen and (max-width: 768px) {
            width: 100%;
            justify-content: center;
        }
    }

    .btn-actions.create {
        cursor: pointer;
        color: #fff !important;
        justify-content: center;
        align-items: center;
        border: none !important;
        gap: 8px;
        border-radius: 12px;
        background-image: linear-gradient(182deg, #1b3354 31.03%, #001128 99.96%);
        transition: all 200ms cubic-bezier(.4, 0, .2, 1), opacity 150ms ease, transform 100ms cubic-bezier(.4, 0, .2, 1);

        &:hover {
            opacity: .9;
        }

        &:active {
            transform: scale(.95);
        }
    }

    .wrapper-header__button .icon {
        transition: all 0.4s ease;
    }

    .wrapper-header__button:hover .icon {
        transition: all 0.4s ease;
        transform: rotate(180deg);
    }

    .wrapper-header__button:hover .btn-actions__content {
        visibility: visible;
        opacity: 1;
        right: 0;
        transform: scale(1);
        transform-origin: right;
        transition: visibility 0s, opacity 250ms ease, transform 250ms ease;
    }

    .btn-actions__content {
        background: #ffff;
        text-align: left;
        color: black;
        min-width: 153px;
        max-width: 230px;
        visibility: hidden;
        right: 12px;
        position: absolute;
        border: 1px solid #b8b8b8;
        transition: all 150ms ease;
        transform-origin: right;
        box-shadow: 0px 0px 4px 0px rgba(197, 197, 197, 0.16);
        padding: 0.3rem;
        font-weight: 600;
        border-radius: 12px;
        transform: scale(.95);
        opacity: 0;

        >a,
        .option-menu {
            color: #000 !important;
            text-align: left;
            display: flex;
            gap: 4px;
            font-size: 14px;
            align-items: center;
            padding: 0.4rem;
            transition: all 250ms ease;
        }
        hr{
            border-top: 1px solid #b2b2b2;
        }
    }

    .sending::after {
        content: "";
        top: 50%;
        width: 24px;
        height: 24px;
        margin: auto;
        border: 4px solid #fff;
        border-top: 4px solid #63ecbc;
        border-radius: 50%;
        animation: rotarloader 1.2s cubic-bezier(0.175, 0.885, 0.32, 1.275) infinite;
    }

    @keyframes rotarloader {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    .sending {
        transition: all .3s ease;
        cursor: not-allowed;
    }

    .option-menu::before {
        content: '';
        position: absolute;
        display: none;
        left: 3px;
        height: 25px;
        width: 2px;
        background: gray;
        transition: all 250ms ease;
    }

    .option-menu:hover::before {
        display: block;
    }

    .option-menu {
        border-radius: 6px;

        &:hover {
            background-color: var(--color-alpha-hover);
        }

        &:active {
            background-color: var(--color-alpha-active);
        }

    }

    :where(.option-menu__ir-a--pickings, .option-menu__ir-a--items) {
        display: grid;
        place-items: center;
        place-content: center;
        background: var(--color-alpha-hover);
        border-radius: 8px;
        padding: 1rem;
        height: 100%;
        width: 100px;

        >span {
            color: black;
            text-align: center;
        }

        &:hover {
            background-color: var(--color-alpha-active);
        }
    }

    .option-menu__ir-a {
        display: flex;
        visibility: hidden;
        transform: scale(.95);
        opacity: 0;
        transition: visibility 0s linear 1.5s, opacity 0.5s linear;
        position: absolute;
        right: 144px;
        border: 1px solid #9e9e9e;
        background: white;
        box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.27);
        gap: 10px;
        padding: 0.5rem;
        font-weight: 600;
        border-radius: 12px;
    }

    .option-menu:hover>.option-menu__ir-a {
        visibility: visible;
        opacity: 1;
        transition-delay: 0s;
        transform: scale(1);
        transition: visibility 0s, opacity 250ms ease, transform 250ms ease;
    }

    .wrapper-header {
        width: 245%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        right: 146%;
        padding: 0 1rem;
        border-radius: 18px;
        background-color: lightcyan;

        @media screen and (max-width: 768px) {
            display: grid;
            width: 100%;
            margin: 2px;
            position: static;
            place-content: center;
        }
    }

    /* .wrapper-header::before {
        content: '';
        background: url({{ $urlImage ?? '' }}) no-repeat;
        position: absolute;
        top: 0;
        overflow: auto;
        width: 100%;
        height: 100%;
        background-size: 10em;
        background-position-y: center;
        background-position-x: 84%;
        z-index: 0;
    } */

    .wrapper-header__title {
        border-radius: 22px;
        display: flex;
        align-items: center;
        padding: 1rem;
        gap: 1rem;
        z-index: 1;

        @media screen and (max-width: 768px) {
            display: grid;
            place-items: center;
        }
    }

    .wrapper-header__vector {
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        border-radius: 5.95px;
        width: 41px;
        height: 41px;
        padding: 2px;
        background-color: #63ecbc;
    }

    .wrapper-header__module {
        display: flex;
        flex-direction: column;
        align-items: baseline;

        >h1 {
            all: unset;
            margin: 0;
            padding: 0;
            font-size: 26px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            max-width: 49vw;
            font-style: normal;
            font-weight: 700;

            @media screen and (max-width: 768px) {
                font-size: 22px;
                text-align: center;
            }
        }

    }

    .wrapper-header__module--description {

        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        max-width: 49vw;

        @media screen and (max-width: 768px) {
            display: none;
        }
    }

    .square-top-r {
        position: absolute;
        top: -9px;
        right: -10px;
        border-radius: 3.95px;
        height: 19px;
        width: 19px;
        background: #001128;
    }

    #titulo {
        display: none;
    }

    /* Para los tooltips */

    .tooltip-container {
        position: relative;
        display: flex;
        height: 16px;
        cursor: help;
   }

   .tooltip-text {
        visibility: hidden;
        width: 140px;
        text-wrap: pretty;
        background-color: #000000c0;
        color: #fff;
        text-align: center;
        border-radius: 14px;
        font-size: 11px;
        font-weight: 500;
        padding: 0.8rem;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        opacity: 0;
        text-align: left;
        transform: translateX(-50%) translateY(10px) scale(0.9);
        transition: opacity 0.2s cubic-bezier(0.47, 0.21, 0.02, 1.19);
        backdrop-filter: blur(3px);

        a{
            color: var(--gestoru-principal);
            text-decoration: underline;
        
        }
   }
    .tooltip-text::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #1d1d1d86 transparent transparent transparent;
        backdrop-filter: blur(5px);
    }

   .tooltip-container:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
        transform: translateX(-50%) translateY(0);
   }
</style>

<header class="wrapper-header">
    <article class="wrapper-header__title">
        <div class="wrapper-header__vector">
            <div class="square-top-r"></div>
            {{ $icon ?? '' }}
        </div>

        <div class="wrapper-header__module">
            <h1>{{ $titleModule ?? '' }}</h1>
            <span class="wrapper-header__module--description">{{ $description ?? '' }}</span>
        </div>
    </article>
    {{-- Section for add actions to the module --}}
    <article class="wrapper-buttons-header">
        {{ $buttonAditional ?? '' }}

        @if ($hideActions === false)
            <div class="wrapper-header__button">
                <button class="btn-actions">Acciones
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" height="22" viewBox="0 0 20 20"
                        fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>
                <div class="btn-actions__content">
                    {{ $actions ?? '' }}
                </div>
            </div>
        @endif
    </article>


</header>
