@extends('layouts.app')

<style>
    .container {
        display: flex;
        flex-direction: column;
        gap: 20px;
        padding: 20px;
        background-color: #f5f5f5;
        border-radius: 8px;
        max-width: 600px;
        margin: auto;
    }

    .info-section {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .info-item {
        background-color: #ffffff;
        border: 1px solid rgb(199 199 199);
        color: rgb(17, 17, 17);
        padding: 10px;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 250ms ease;

        &:hover {
            background-color: #dfdfdf;
            transition: all 250ms ease;
        }
    }

    .info-item span {
        font-weight: 600;
    }

    .action-section {
        display: flex;
        justify-content: center;
        gap: 20px;
    }

    .action-link {
        color: #030303 !important;
        border: 1px solid #a7a7a7;
        padding: 10px 15px;
        font-weight: 600;
        border-radius: 12px !important;
        text-decoration: none;
        border-radius: 5px;
        transition: all 250ms ease;
    }

    .action-link:hover {
        text-decoration: none;
        background-color: rgb(199 199 199);
        transition: all 250ms ease;
    }
</style>

@include('period-billings.includes.aside-editar-periodo');

@section('boton')
    <x-modules-header titleModule='Ver periodo' hideActions>
        <x-slot name="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M6.74219 1C4.53305 1 2.74219 2.79086 2.74219 5C2.74219 5.07917 2.74449 5.15779 2.74902 5.23582V14.0564C2.74901 15.8942 2.749 17.3498 2.90216 18.489C3.05979 19.6614 3.3919 20.6104 4.14027 21.3588C4.88863 22.1071 5.83758 22.4392 7.01 22.5969C8.14921 22.75 9.60487 22.75 11.4426 22.75H12.5555C14.3932 22.75 15.8488 22.75 16.988 22.5969C18.1605 22.4392 19.1094 22.1071 19.8578 21.3588C20.6061 20.6104 20.9383 19.6614 21.0959 18.489C21.2491 17.3498 21.249 15.8942 21.249 14.0564V10C21.249 8.48122 20.0178 7.25 18.499 7.25L18.499 6.5C18.499 5.11929 17.3797 4 15.999 4H7.49902C6.94674 4 6.49902 4.44772 6.49902 5C6.49902 5.55228 6.94674 6 7.49902 6H15.999C16.2752 6 16.499 6.22386 16.499 6.5V7H6.74219C5.63762 7 4.74219 6.10457 4.74219 5C4.74219 3.89543 5.63762 3 6.74219 3H18.7422C19.2945 3 19.7422 2.55228 19.7422 2C19.7422 1.44772 19.2945 1 18.7422 1H6.74219ZM14.999 13.75C15.4132 13.75 15.749 13.4142 15.749 13C15.749 12.5858 15.4132 12.25 14.999 12.25H8.99902C8.58481 12.25 8.24902 12.5858 8.24902 13C8.24902 13.4142 8.58481 13.75 8.99902 13.75H14.999ZM12.749 17C12.749 17.4142 12.4132 17.75 11.999 17.75H8.99902C8.58481 17.75 8.24902 17.4142 8.24902 17C8.24902 16.5858 8.58481 16.25 8.99902 16.25H11.999C12.4132 16.25 12.749 16.5858 12.749 17Z"
                    fill="var(--gestoru-secundario)" />
            </svg>
        </x-slot>

        <x-slot name="buttonAditional">
            <a class="btn-actions create" href="javascript:abrirFiltrador();" style="text-decoration: none;">
                <svg xmlns="http://www.w3.org/2000/svg" height="18" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M17.9799 1.33814C18.6382 1.05395 19.3846 1.05395 20.043 1.33814C20.3076 1.45237 20.5299 1.61728 20.745 1.80587C20.9512 1.9866 21.1861 2.22154 21.4653 2.50074L21.4993 2.53471C21.7785 2.81389 22.0134 3.04883 22.1941 3.25496C22.3827 3.47007 22.5476 3.6924 22.6619 3.95701C22.9461 4.61536 22.9461 5.36178 22.6619 6.02013C22.5476 6.28474 22.3827 6.50707 22.1941 6.72218C22.0134 6.9283 21.7785 7.16322 21.4993 7.44237L21.4993 7.44243L16.3602 12.5815L16.3602 12.5815C15.2196 13.7225 14.5141 14.4282 13.6205 14.851C12.7269 15.2737 11.5924 15.3855 9.98688 15.5436L9.19865 15.6214C8.97513 15.6434 8.75349 15.5641 8.59467 15.4053C8.43585 15.2465 8.35657 15.0249 8.37863 14.8014L8.4564 14.0131C8.61455 12.4076 8.72631 11.2731 9.14905 10.3795C9.5718 9.48586 10.2775 8.78038 11.4185 7.63978L16.5575 2.50077C16.8367 2.22156 17.0717 1.9866 17.2778 1.80587C17.4929 1.61728 17.7153 1.45237 17.9799 1.33814Z"
                        fill="#63ECBC" />
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M1.125 16.625C1.125 15.1062 2.35622 13.875 3.875 13.875H6.125C6.67728 13.875 7.125 14.3227 7.125 14.875C7.125 15.4273 6.67728 15.875 6.125 15.875H3.875C3.46079 15.875 3.125 16.2108 3.125 16.625C3.125 17.0392 3.46079 17.375 3.875 17.375H13.375C14.8938 17.375 16.125 18.6062 16.125 20.125C16.125 21.6438 14.8938 22.875 13.375 22.875H11.125C10.5727 22.875 10.125 22.4273 10.125 21.875C10.125 21.3227 10.5727 20.875 11.125 20.875H13.375C13.7892 20.875 14.125 20.5392 14.125 20.125C14.125 19.7108 13.7892 19.375 13.375 19.375H3.875C2.35622 19.375 1.125 18.1438 1.125 16.625Z"
                        fill="#63ECBC" />
                </svg>
                Editar
            </a>
        </x-slot>
    </x-modules-header>
@endsection

@section('content')
    <div class="container">
        <div class="info-section">
            <div class="info-item"><span>Prorroga</span> {{ $periodBilling->extension_days }}</div>
            <div class="info-item"><span>Meses</span> {{ $periodBilling->months }}</div>
            <div class="info-item"><span>Descuento</span> {{ $periodBilling->discount }}</div>
            <div class="info-item"><span>¿Es favorito?</span> {{ $periodBilling->is_fav ? 'Si' : 'No' }}</div>
            <div class="info-item"><span>Adicion de facturas</span> {{ $periodBilling->additon_invoices }}</div>
            <div class="info-item"><span>Adicion de Monto</span> {{ $periodBilling->additon_billing }}</div>
            <div class="info-item"><span>Plan ID</span> {{ $periodBilling->plan->nombre }}</div>
            <div class="info-item"><span>¿Habilitado?</span> {{ $periodBilling->is_active ? 'Si' : 'No' }}</div>
        </div>
        <div class="action-section">
            <a href="{{ route('plans.show', $periodBilling->plan_id) }}" class="action-link">Regresar</a>
        </div>

        <div class="mostrar-periodo__acciones">
            <a class="btn-actions create" href="{{ route('plans.show', $periodBilling->plan_id) }}">Regresar</a>
            <a class="btn-actions create" href="{{ route('plans.billings.edit', $periodBilling->id) }}">Editar</a>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('openEdit') && urlParams.get('openEdit') === 'true') {
            abrirFiltrador();
        }
    });
</script>
