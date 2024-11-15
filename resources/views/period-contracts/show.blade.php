@extends('layouts.app')

<style>
    .mostrar-contrato {
        background: linear-gradient(136deg, #d7d6fe, #fffeff);
        border-radius: 20px;
        position: relative;
        color: #1d1f38;
        padding: 2rem;
        overflow: hidden;
        animation: slide-arriba-sections 0.3s 0.2s cubic-bezier(0, 0, .2, 1) both;
    }

    .mostrar-contrato__bento::before {
        content: '';
        background: url('https://static.vecteezy.com/system/resources/previews/012/487/897/original/3d-paper-sheets-with-check-marks-on-transparent-confirmed-or-approved-document-icon-beige-clipboard-with-checklist-symbol-assignment-done-business-cartoon-style-3d-icon-render-illustration-free-png.png'), url('https://blog.gestoru.com/_astro/logo.Dt8eKq61_XGpAp.webp');
        background-position: center, center;
        background-repeat: no-repeat, no-repeat;
        position: absolute;
        top: 10%;
        left: 78%;
        right: 0;
        height: 19rem;
        transform: rotate(355deg);
        background-size: contain;
    }

    .mostrar-contrato__bento {
        display: grid;
        grid-gap: 2%;
        width: 80%;
        grid-template-areas: 'empresa periodo-facturacion valor-contrato' 'limite-facturas limite-monto contract-id' 'fecha-vencimiento asesor terminos';
        font-weight: 600;

        >article {
            color: #1d1f38;
        }

        h3 {
            font-weight: 700;
            font-size: 24px;
        }

        p {
            font-weight: 400;
            font-size: 18px;
            text-wrap: balance;
        }

        .empresa {
            grid-area: empresa;
        }

        .periodo-facturacion {
            grid-area: periodo-facturacion;
        }

        .valor-contrato {
            grid-area: valor-contrato;
        }

        .limite-facturas {
            grid-area: limite-facturas;
        }

        .limite-monto {
            grid-area: limite-monto;
        }

        .contract-id {
            grid-area: contract-id;
        }

        .fecha-vencimiento {
            grid-area: fecha-vencimiento;
        }

        .asesor {
            grid-area: asesor;
        }

        .terminos {
            grid-area: terminos;
        }
    }

    @media(width < 1440px) {
        .mostrar-contrato__bento {
            grid-template-areas: 'empresa periodo-facturacion' 'valor-contrato limite-facturas' 'limite-monto contract-id' 'fecha-vencimiento asesor' 'terminos terminos';
            width: 80%;
        }

        .mostrar-contrato__bento::before {
            left: 39%;
            right: 0;
            width: 100%;
            top: 6%;
            height: 12rem;
            bottom: 15rem;
            transform: rotate(355deg);
            background-size: contain;
        }

    }

    @media(width < 1000px) {

        .card-body {
            padding: 8px !important;
        }

        .mostrar-contrato__bento {
            grid-template-areas: 'empresa' 'periodo-facturacion' 'valor-contrato' 'limite-facturas' 'limite-monto' 'contract-id' 'fecha-vencimiento' 'asesor' 'terminos';
            width: 100%;
        }

        .mostrar-contrato {
            padding: 2rem;
            overflow: auto;
        }

        .mostrar-contrato__bento::before {
            display: none;
        }
    }

    @keyframes slide-arriba-sections {
        0% {
            transform: translateY(20px);
            opacity: 0;
        }

        100% {
            transform: translateY(0px);
            opacity: 1;
        }
    }
</style>

@section('boton')
    <x-modules-header titleModule='Ver contrato'>
        <x-slot name="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M6.74219 1C4.53305 1 2.74219 2.79086 2.74219 5C2.74219 5.07917 2.74449 5.15779 2.74902 5.23582V14.0564C2.74901 15.8942 2.749 17.3498 2.90216 18.489C3.05979 19.6614 3.3919 20.6104 4.14027 21.3588C4.88863 22.1071 5.83758 22.4392 7.01 22.5969C8.14921 22.75 9.60487 22.75 11.4426 22.75H12.5555C14.3932 22.75 15.8488 22.75 16.988 22.5969C18.1605 22.4392 19.1094 22.1071 19.8578 21.3588C20.6061 20.6104 20.9383 19.6614 21.0959 18.489C21.2491 17.3498 21.249 15.8942 21.249 14.0564V10C21.249 8.48122 20.0178 7.25 18.499 7.25L18.499 6.5C18.499 5.11929 17.3797 4 15.999 4H7.49902C6.94674 4 6.49902 4.44772 6.49902 5C6.49902 5.55228 6.94674 6 7.49902 6H15.999C16.2752 6 16.499 6.22386 16.499 6.5V7H6.74219C5.63762 7 4.74219 6.10457 4.74219 5C4.74219 3.89543 5.63762 3 6.74219 3H18.7422C19.2945 3 19.7422 2.55228 19.7422 2C19.7422 1.44772 19.2945 1 18.7422 1H6.74219ZM14.999 13.75C15.4132 13.75 15.749 13.4142 15.749 13C15.749 12.5858 15.4132 12.25 14.999 12.25H8.99902C8.58481 12.25 8.24902 12.5858 8.24902 13C8.24902 13.4142 8.58481 13.75 8.99902 13.75H14.999ZM12.749 17C12.749 17.4142 12.4132 17.75 11.999 17.75H8.99902C8.58481 17.75 8.24902 17.4142 8.24902 17C8.24902 16.5858 8.58481 16.25 8.99902 16.25H11.999C12.4132 16.25 12.749 16.5858 12.749 17Z"
                    fill="var(--gestoru-secundario)" />
            </svg>
        </x-slot>

        <x-slot name="buttonAditional">
            <a class="btn-actions create" href="{{ route('period.contract.status', $period->id) }}"
                style="text-decoration: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M6.5 2.25C4.15279 2.25 2.25 4.15279 2.25 6.5C2.25 8.84721 4.15279 10.75 6.5 10.75L17.5 10.75C19.8472 10.75 21.75 8.84721 21.75 6.5C21.75 4.15279 19.8472 2.25 17.5 2.25L6.5 2.25ZM6.5 9C7.88071 9 9 7.88071 9 6.5C9 5.11929 7.88071 4 6.5 4C5.11929 4 4 5.11929 4 6.5C4 7.88071 5.11929 9 6.5 9Z"
                        fill="#63ECBC" />
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M6.5 13.25C4.15279 13.25 2.25 15.1528 2.25 17.5C2.25 19.8472 4.15279 21.75 6.5 21.75L17.5 21.75C19.8472 21.75 21.75 19.8472 21.75 17.5C21.75 15.1528 19.8472 13.25 17.5 13.25L6.5 13.25ZM17.5 20C18.8807 20 20 18.8807 20 17.5C20 16.1193 18.8807 15 17.5 15C16.1193 15 15 16.1193 15 17.5C15 18.8807 16.1193 20 17.5 20Z"
                        fill="#63ECBC" />
                </svg>
                {{ $period->status ? 'Desactivar' : 'Activar' }}
            </a>
        </x-slot>
        <x-slot name="actions">
            <a href="{{ route('period.contract.index') }}" class="option-menu">Ver periodos contratados</a>
        </x-slot>

    </x-modules-header>
@endsection

@section('content')
    {{-- <div class="row mb-4">

            <a href="{{ route('period.contract.index') }}" class="btn btn-primary mr-3">Ver periodos contratados</a>
            <a href="{{ route('period.contract.status', $period->id) }}"
                class="btn btn-primary">{{ $period->status ? 'Desactivar' : 'Activar' }}</a>

        </div> --}}

    <div class="card-body">
        <section class="mostrar-contrato">
            <div class="mostrar-contrato__bento">
                <article class="empresa">
                    <h3>Empresa</h3>
                    <p>{{ $period->contract->company->nombre }}</p>
                </article>

                <article class="periodo-facturacion">
                    <h3>Periodo fact.</h3>
                    <p>{{ $period->periodBilling->months . ' Meses - Descuento: ' . $period->periodBilling->discount . '%' }}
                    </p>
                </article>

                <article class="valor-contrato">
                    <h3>Valor contrato</h3>
                    <p>$ {{ number_format($period->contract->value, 2) }}</p>
                </article>

                <article class="limite-facturas">
                    <h3>Limite de Facturas</h3>
                    <p>{{ $period->limit_invoices }}</p>
                </article>

                <article class="limite-monto">
                    <h3>Limite de Monto</h3>
                    <p>${{ number_format($period->limit_billing, 2) }}</p>
                </article>

                <article class="contract-id">
                    <h3>Contract ID</h3>
                    <p>{{ $period->contract->id }}</p>
                </article>

                <article class="fecha-vencimiento">
                    <h3>Fecha vencimiento</h3>
                    <p>{{ $period->dueDate }}</p>
                </article>

                <article class="asesor">
                    <h3>Asesor que vende</h3>
                    <p>{{ $period->contract->consultant }}</p>
                </article>

                <article class="terminos">
                    <h3>Términos y condiciones</h3>
                    <p>{{ $period->contract->description }}</p>
                </article>

            </div>
        </section>
    </div>
@endsection
