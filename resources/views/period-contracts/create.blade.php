@extends('layouts.app')

@php
    $message = 'Primero selecciona el plan a activar (Debe tener un minimo de 1 periodo de facturación)';
    if ($periods->count() > 0) {
        $message = 'Ahora selecciona la empresa a la que se le activará el plan';
    }
@endphp


@section('boton')
    <x-modules-header titleModule='Activar plan a empresa' description="{{ $message }}" hideActions>
        <x-slot name="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path
                    d="M12.0896 2.24994C12.5236 2.24956 12.8806 2.24925 13.1934 2.33305C14.0345 2.55844 14.6915 3.21546 14.9169 4.05662C15.0007 4.36936 15.0004 4.7264 15 5.16038V5.16038L15 7.5C15 8.05153 15.0016 8.38973 15.0345 8.63449C15.0634 8.84963 15.1045 8.88555 15.1094 8.88981L15.1102 8.89055C15.1144 8.89543 15.1503 8.93657 15.3655 8.9655C15.6102 8.9984 15.9484 9 16.5 9L18.8396 8.99994C19.2736 8.99956 19.6306 8.99925 19.9433 9.08305C20.7845 9.30844 21.4415 9.96546 21.6669 10.8066C21.7507 11.1194 21.7504 11.4764 21.75 11.9104V12.0896C21.7504 12.5236 21.7507 12.8806 21.6669 13.1934C21.4415 14.0345 20.7845 14.6916 19.9433 14.9169C19.6306 15.0007 19.2736 15.0004 18.8396 15L16.5 15C15.9484 15 15.6102 15.0016 15.3655 15.0345C15.1503 15.0634 15.1144 15.1046 15.1102 15.1094L15.1094 15.1102C15.1045 15.1144 15.0634 15.1504 15.0345 15.3655C15.0016 15.6103 15 15.9485 15 16.5L15 18.8396C15.0004 19.2736 15.0007 19.6306 14.9169 19.9434C14.6915 20.7845 14.0345 21.4416 13.1934 21.6669C12.8806 21.7507 12.5236 21.7504 12.0896 21.75H11.9104C11.4764 21.7504 11.1194 21.7507 10.8066 21.6669C9.96545 21.4415 9.30842 20.7845 9.08304 19.9434C8.99924 19.6306 8.99955 19.2736 8.99993 18.8396L8.99998 16.5C8.99998 15.9485 8.99839 15.6103 8.96548 15.3655C8.93655 15.1504 8.89542 15.1144 8.89054 15.1102L8.88979 15.1094C8.88553 15.1046 8.84961 15.0634 8.63448 15.0345C8.38971 15.0016 8.05151 15 7.49998 15L5.16037 15H5.16036C4.72639 15.0004 4.36935 15.0007 4.05661 14.9169C3.21545 14.6916 2.55842 14.0345 2.33304 13.1934C2.24924 12.8806 2.24955 12.5236 2.24993 12.0896V11.9104C2.24955 11.4764 2.24924 11.1194 2.33304 10.8066C2.55842 9.96546 3.21545 9.30844 4.05661 9.08305C4.36935 8.99925 4.72639 8.99956 5.16037 8.99994L7.49998 9C8.05151 9 8.38971 8.9984 8.63448 8.9655C8.84961 8.93657 8.88553 8.89543 8.88979 8.89055L8.89054 8.88981C8.89542 8.88555 8.93656 8.84963 8.96548 8.63449C8.99839 8.38973 8.99998 8.05153 8.99998 7.49999L8.99993 5.16038C8.99955 4.72641 8.99924 4.36937 9.08304 4.05662C9.30842 3.21546 9.96545 2.55844 10.8066 2.33305C11.1194 2.24925 11.4764 2.24956 11.9104 2.24994H12.0896Z"
                    fill="var(--gestoru-secundario)" />
            </svg>
        </x-slot>
    </x-modules-header>
@endsection


@section('content')


    @if (Session::has('success'))
        <div class="alert alert-success">
            {{ Session::get('success') }}
        </div>

        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
            }, 5000);
        </script>
    @endif

    @if (Session::has('info'))
        <div class="alert alert-info">
            {{ Session::get('info') }}
        </div>

        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
            }, 10000);
        </script>
    @endif

    @if (Session::has('error'))
        <div class="alert alert-danger">
            {{ Session::get('error') }}
        </div>

        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
            }, 10000);
        </script>
    @endif

    <style>
        .cards-plans {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 0 2% 2% 2%;

            a {
                width: 100%;
                text-decoration: none;
            }
        }

        .cards-plans__container {
            display: flex;
            flex: 1;
            border-radius: 22px;
            min-width: 370px;
            position: relative;
            background: linear-gradient(180deg, #ffffff 0%, #f0fdf8 100%);
            color: var(--gestoru-principal);
            padding: 18px 21px;
            flex-direction: column;
            border: 1px solid #8ff1cb;
            justify-content: center;
            align-items: flex-start;
            gap: 16px;
            flex-shrink: 0;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);

            .precio {
                color: #000;
                font-family: Poppins;
                font-size: 34px;
                font-style: normal;
                font-weight: 600;
                line-height: normal;
            }

            .separator {
                width: 100%;
                border-radius: 69px;
                height: 1px;
                background: #0112274d;
            }

            &:hover {
                transform: scale(1.02);
                transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);

                .cards-plans--icon svg {
                    transition: all 200ms cubic-bezier(0.25, 0.8, 0.25, 1);
                    color: var(--gestoru-secundario);
                    transform: translateY(-2px);
                }
            }

            &:active {
                transform: scale(0.99);
                transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
                border: 1px solid #8ff1cb;
            }

            &:focus {
                border: 1px solid #8ff1cb;
            }
        }

        svg {
            transition: all 300ms cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .cards-plans--title {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;

            >span {
                color: #000;
                font-size: 22px;
                font-weight: 700;
                max-width: 90%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }

        .cards-plans--icon {
            background: #8ff1cb;
            border-radius: 69px;
            color: var(--gestoru-secundario);
            padding: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cards-plans--two {
            display: grid;
            width: 100%;
            gap: 5px;

            article {
                display: flex;
                align-items: center;
                font-size: 14px;
                border-radius: 14px;
                font-weight: 400;
                color: black;

                label {
                    font-size: 14px;
                    font-weight: 600;
                }

            }
        }

        #leer-mas {
            display: block;
            text-wrap: nowrap;
            width: fit-content;
            color: #50b28f;
        }

        .cards-plans--descripcion {
            display: flex;
            width: 100%;

            p {
                color: #252525;
                font-size: 14px;
                max-width: 80%;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                font-weight: 400;
                margin: 0;
            }

            a {
                color: #8862e0;
            }
        }

        /* Abajo estilos para el formulario */

        .form-container {
            max-width: 1200px;
            margin: 20px auto;
        }

        .form-container form {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(2, 1fr);
        }

        .terminos {
            grid-column: 1 / span 2;
        }

        .dropdown.bootstrap-select.form-control.buscar {
            height: 35px;
            display: grid;
        }

        .dropdown.bootstrap-select.form-control.buscar::after {
            content: "\25BC";
            position: absolute;
            color: #1f1f20;
            right: 10px;
            top: 50%;
            z-index: 1;
            transform: translateY(-50%);
            font-size: 12px;
        }

        .dropdown-menu.show {
            border: 1px solid #b2b2b2;
            border-radius: 14px;
            background: #f8f8ff;
            font-size: 13px;
        }

        div .bootstrap-select .dropdown-toggle .filter-option-inner-inner {
            overflow: hidden;
            color: #000000 !important;
            font-weight: 400 !important;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #000000;
        }

        .form-group input[type="number"],
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea,
        .filter-option {
            width: 100%;
            padding: 10px;
            background: #ffff;
            border: 1px solid #bbbbbb;
            border-radius: 8px;
            font-size: 16px;
            color: #000000;
            transition: all 250ms ease;
        }

        .form-group input[type="number"]:focus,
        .form-group input[type="text"]:focus,
        .form-group input[type="date"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            transition: all 250ms ease;
            outline: none;
            box-shadow: 0 0 0 1px #7e7e7e75;
        }

        .form-group textarea {
            resize: vertical;
        }


        @media (max-width: 1280px) {
            .form-container form {
                display: flex;
                flex-direction: column;
                padding: 2%;
            }
        }

        @media(width < 1601px) {
            .form-container {
                margin: 0 3rem;
            }
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .btn-actions.cancel {
            background: #ffff;
            color: #333 !important;
            float: left;
            cursor: pointer;
            border: 1px solid #ccc !important;
            transition: all 250ms ease;
            text-decoration: none;

            &:hover {
                background: #e3e3e3;
                transition: all 250ms ease;
            }
        }

        .btn-actions.create[disabled] {
            background-image: linear-gradient(182deg, #a0a4a8 31.03%, #73777a 99.96%);
            cursor: not-allowed;
            opacity: 0.5;
        }
    </style>


    <div>
        @if ($periods->count() > 0)
            <div class="form-container">
                <form method="POST" action="{{ route('period.contract.store') }}" id="form-period-contract"
                    class="modern-form">
                    @csrf
                    <input type="hidden" name="tipo" value="{{ request()->type }}">
                    <input type="hidden" value="{{ request()->period_contract }}" name="current_period"
                        id="current_period">
                    {{-- Primera parte  --}}

                    <div class="form-group">
                        <label for="empresa_id">Empresa</label>
                        <select class="form-control selectpicker" data-size="5" data-live-search="true" id="empresa_id"
                            name="company_id" required onchange="getPeriodsEmpresa()"
                            {{ $companySelected ? 'disabled' : '' }}>
                            <option value="">Seleccionar</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id }}"
                                    {{ $empresa->id == $companySelected ? 'selected' : '' }}>
                                    {{ $empresa->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="period_id">Periodo fact.</label>
                        <select class="form-control selectpicker" id="period_id" name="period_id" required
                            onchange="getPeriodInfo(this.value)">
                            <option value="">Seleccionar</option>
                            @foreach ($periods as $period)
                                <option value="{{ $period->id }}">
                                    {{ $period->months . ' Meses - descuento: ' . $period->discount . '%' }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- Segunda parte --}}

                    <div class="form-group">
                        <label for="value_contract">Valor contrato</label>
                        <input id="value_contract" type="number" name="value" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="limit_invoices">Limite de Facturas</label>
                        <input id="limit_invoices" type="number" name="limit_invoices" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="limit_billing">Limite de Monto</label>
                        <input id="limit_billing" type="number" name="limit_billing" required>
                    </div>





                    <div class="form-group">
                        <label for="contract_id">Contract ID</label>
                        <input id="contract_id" type="number" name="contract_id" value="{{ $contract }}" readonly>
                    </div>

                    <div class="form-group">
                        <label for="dueDate">Fecha vencimiento</label>
                        <input id="dueDate" type="date" name="dueDate" required>
                    </div>

                    <div class="form-group">
                        <label for="consultant">Asesor que vende</label>
                        <input id="consultant" type="text" name="consultant" value="" required>
                    </div>


                    {{-- Tercera parte --}}



                    <div class="form-group terminos">
                        <label for="terms">Términos y condiciones</label>
                        <textarea id="terms" name="description" rows="5"></textarea>
                    </div>


                    {{-- Final --}}

                    <div class="form-group">
                        <a href="{{ route('period.contract.index') }}" class="btn-actions cancel">Cancelar</a>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-actions create" style="float: right;">Guardar periodo y activar
                            plan</button>
                    </div>

                </form>
            </div>
        @else
            <div class="cards-plans">
                @foreach ($plans as $plane)
                    <a class="cards-plans__container"
                        href="{{ route(request()->route()->getName()) }}?plan={{ $plane->id }}{{ request()->period_contract ? '&period_contract=' . request()->period_contract : '' }}&type={{ $plane->isPos == 1 ? 2 : ($plane->isNomina == 1 ? 3 : 1) }}">
                        {{-- Titulo --}}
                        <article title="{{ $plane->name }}" class="cards-plans--title">
                            <div class="cards-plans--icon">
                                <svg xmlns="http://www.w3.org/2000/svg" height="18" viewBox="0 0 24 24" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M16.875 1.125C17.4273 1.125 17.875 1.57272 17.875 2.125L17.875 5.125H20.875C21.4273 5.125 21.875 5.57272 21.875 6.125C21.875 6.67728 21.4273 7.125 20.875 7.125H17.875L17.875 10.125C17.875 10.6773 17.4273 11.125 16.875 11.125C16.3227 11.125 15.875 10.6773 15.875 10.125L15.875 7.125H12.875C12.3227 7.125 11.875 6.67728 11.875 6.125C11.875 5.57272 12.3227 5.125 12.875 5.125H15.875L15.875 2.125C15.875 1.57272 16.3227 1.125 16.875 1.125Z"
                                        fill="currentColor" />
                                    <path
                                        d="M14.375 2.125V3.025C14.375 3.30784 14.375 3.44926 14.2871 3.53713C14.1993 3.625 14.0578 3.625 13.775 3.625H12.875C11.4943 3.625 10.375 4.74429 10.375 6.125C10.375 7.50571 11.4943 8.625 12.875 8.625H13.775C14.0578 8.625 14.1993 8.625 14.2871 8.71287C14.375 8.80074 14.375 8.94216 14.375 9.225V10.125C14.375 11.5057 15.4943 12.625 16.875 12.625C17.8424 12.625 18.6814 12.0756 19.0971 11.2717C19.147 11.1751 19.2442 11.1096 19.353 11.1096C19.5032 11.1096 19.625 11.2314 19.625 11.3817V18.1708C19.6251 19.2684 19.6251 20.1803 19.5197 20.8655C19.4106 21.5745 19.1564 22.2645 18.4554 22.6389C17.8303 22.9727 17.1426 22.9048 16.5596 22.7278C15.9699 22.5488 15.3705 22.2239 14.8217 21.8771C14.2676 21.527 13.6688 21.0868 13.1965 20.7377C12.7337 20.3956 12.3619 20.1208 12.0781 19.9499C11.6785 19.7093 11.4263 19.5585 11.2217 19.4621C11.033 19.3732 10.9413 19.359 10.875 19.359C10.8087 19.359 10.717 19.3732 10.5283 19.4621C10.3238 19.5585 10.0715 19.7093 9.6719 19.9499C9.38808 20.1208 9.01636 20.3956 8.55354 20.7377C8.0812 21.0868 7.48239 21.527 6.92836 21.8771C6.37954 22.2239 5.78014 22.5488 5.19044 22.7278C4.60741 22.9048 3.91976 22.9727 3.29458 22.6389C2.59357 22.2645 2.33938 21.5745 2.23033 20.8655C2.12494 20.1803 2.12497 19.2684 2.125 18.1709L2.12501 9.77443C2.12499 8.00706 2.12497 6.6015 2.27865 5.50025C2.43743 4.36243 2.773 3.43931 3.52622 2.71363C4.27552 1.99172 5.22212 1.67353 6.38958 1.52231C7.527 1.37498 8.98108 1.37499 10.8204 1.375H10.9296C12.0057 1.37499 12.95 1.37499 13.7762 1.40449C14.0519 1.41434 14.1897 1.41926 14.2691 1.48662C14.2868 1.5016 14.2962 1.51135 14.3105 1.52952C14.375 1.61125 14.375 1.7825 14.375 2.125Z"
                                        fill="currentColor" />
                                </svg>
                            </div>
                            <span>{{ ucfirst(strtolower($plane->name)) }}</span>
                        </article>
                        {{-- Precio  --}}
                        <span class="precio">${{ number_format($plane->price) }}</span>
                        {{-- Descripcion --}}
                        <div class="cards-plans--descripcion">
                            <p class="descripcion">{{ $plane->description }}
                            </p>

                        </div>
                        <div class="separator"></div>
                        {{-- Periodos de facturacion  y mtodos de pago --}}
                        <div class="cards-plans--two">
                            {{-- <h5>Características</h5> --}}
                            <article>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24"
                                    height="24" color="#000000" fill="none">
                                    <path d="M5 14.5C5 14.5 6.5 14.5 8.5 18C8.5 18 14.0588 8.83333 19 7"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                {{ $plane->periodsBillings->count() }} periodo(s) de facturacion
                            </article>
                            <article>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24"
                                    height="24" color="#000000" fill="none">
                                    <path d="M5 14.5C5 14.5 6.5 14.5 8.5 18C8.5 18 14.0588 8.83333 19 7"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                {{ '0' }} metodo(s) de pago
                            </article>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script src="{{ asset('plans/period-contract.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const descriptions = document.querySelectorAll('.descripcion');

            descriptions.forEach(description => {
                if (description.scrollWidth > description.clientWidth) {

                    const readMoreBtn = document.createElement('a');
                    readMoreBtn.id = 'leer-mas';
                    readMoreBtn.textContent = 'Leer mas';
                    readMoreBtn.style.display = 'block';

                    description.parentNode.insertBefore(readMoreBtn, description.nextSibling);

                    readMoreBtn.addEventListener('click', function() {
                        event.preventDefault();
                        event.stopPropagation();

                        if (description.style.whiteSpace === 'nowrap' || description.style
                            .whiteSpace === '') {
                            description.style.whiteSpace = 'normal';
                            readMoreBtn.textContent = 'Ocultar';
                        } else {
                            description.style.whiteSpace = 'nowrap';
                            readMoreBtn.textContent = 'Leer mas';
                        }
                    });
                }
            });
        });

        document.querySelector('.create').addEventListener('click', function(event) {
            const form = document.getElementById('form-period-contract');
            if (form.checkValidity()) {
                const button = event.target;
                const buttonWidth = window.getComputedStyle(button).width;

                button.style.width = buttonWidth;

                button.textContent = "";
                button.classList.add("sending");
            }
        });
    </script>

@endsection
