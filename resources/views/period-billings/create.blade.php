@extends('layouts.app')

@section('boton')
    <x-modules-header titleModule='Crear periodo de facturación' hideActions>
        <x-slot name="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M6.74219 1C4.53305 1 2.74219 2.79086 2.74219 5C2.74219 5.07917 2.74449 5.15779 2.74902 5.23582V14.0564C2.74901 15.8942 2.749 17.3498 2.90216 18.489C3.05979 19.6614 3.3919 20.6104 4.14027 21.3588C4.88863 22.1071 5.83758 22.4392 7.01 22.5969C8.14921 22.75 9.60487 22.75 11.4426 22.75H12.5555C14.3932 22.75 15.8488 22.75 16.988 22.5969C18.1605 22.4392 19.1094 22.1071 19.8578 21.3588C20.6061 20.6104 20.9383 19.6614 21.0959 18.489C21.2491 17.3498 21.249 15.8942 21.249 14.0564V10C21.249 8.48122 20.0178 7.25 18.499 7.25L18.499 6.5C18.499 5.11929 17.3797 4 15.999 4H7.49902C6.94674 4 6.49902 4.44772 6.49902 5C6.49902 5.55228 6.94674 6 7.49902 6H15.999C16.2752 6 16.499 6.22386 16.499 6.5V7H6.74219C5.63762 7 4.74219 6.10457 4.74219 5C4.74219 3.89543 5.63762 3 6.74219 3H18.7422C19.2945 3 19.7422 2.55228 19.7422 2C19.7422 1.44772 19.2945 1 18.7422 1H6.74219ZM14.999 13.75C15.4132 13.75 15.749 13.4142 15.749 13C15.749 12.5858 15.4132 12.25 14.999 12.25H8.99902C8.58481 12.25 8.24902 12.5858 8.24902 13C8.24902 13.4142 8.58481 13.75 8.99902 13.75H14.999ZM12.749 17C12.749 17.4142 12.4132 17.75 11.999 17.75H8.99902C8.58481 17.75 8.24902 17.4142 8.24902 17C8.24902 16.5858 8.58481 16.25 8.99902 16.25H11.999C12.4132 16.25 12.749 16.5858 12.749 17Z"
                    fill="var(--gestoru-secundario)" />
            </svg>
        </x-slot>
    </x-modules-header>
@endsection

<style>
    .form-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
    }

    .form-container form {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(2, 1fr);
    }

    .form-container div {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 14px;
        color: #000;
    }

    input[type="number"],
    select {
        width: 100%;
        padding: 10px;
        background: #ffff;
        border: 1px solid #bbbbbb;
        border-radius: 8px;
        font-size: 16px;
        color: #000000;
        transition: all 250ms ease;

        &:focus {
            transition: all 250ms ease;
            outline: none;
            box-shadow: 0 0 0 1px #3f3f3f75;
        }
    }

    button[type="submit"] {
        background-color: #0056b3;
        color: #ffffff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    button[type="submit"]:hover {
        background-color: #003d82;
    }

    a {
        text-decoration: none;
        color: #0056b3;
    }

    a:hover {
        text-decoration: underline;
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

    @media(width < 1000px) {
        .form-container form {
            grid-template-columns: 1fr;
        }
    }

    @media(width < 1601px) {
        .form-container {
            margin: 0 3rem;
        }
    }
</style>

@section('content')
    <div class="form-container">
        <form method="POST" action="{{ route('plans.billings.store') }}" id="form-crear-periodo">
            @csrf

            <div>
                <label for="extension_days">Dias de prorroga</label>
                <input id="extension_days" type="number" name="extension_days" required autofocus>
            </div>

            <div>
                <label for="months">Cantidad de meses</label>
                <input id="months" type="number" name="months" required>
            </div>

            <div>
                <label for="discount">Descuento</label>
                <input id="discount" type="number" name="discount" required>
            </div>

            <div>
                <label for="is_fav">Es favorito</label>
                <select id="is_fav" name="is_fav" required>
                    <option value="1">Si</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div>
                <label for="additon_invoices">Adicion de facturas</label>
                <input id="additon_invoices" type="number" name="additon_invoices" required>
            </div>

            <div>
                <label for="additon_billing">Adicion de monto</label>
                <input id="additon_billing" type="number" name="additon_billing" required>
            </div>

            <div>
                <label for="plan_id">Plan ID</label>
                <input id="plan_id" type="number" name="plan_id" value="{{ request()->idPlan }}" required>
            </div>

            <div>
                <label for="is_active">¿Habilitado?</label>
                <select id="is_active" name="is_active">
                    <option value="1">Si</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div>
                @php
                    $routeName = ($plan && $plan->isPos === 1) ? 'plansPos' : 'plans';
                    $route = request()->idPlan ? route($routeName . '.show', request()->idPlan) : route($routeName . '.index');
                @endphp
                <a class="btn-actions cancel" href="{{ $route }}">Cancelar</a>
            </div>

            <div>
                <button style="float: right;" class="btn-actions create" type="submit">Crear periodo</button>
            </div>
        </form>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.create').addEventListener('click', function(event) {
            const form = document.getElementById('form-crear-periodo');
            if (form.checkValidity()) {
                const button = event.target;
                const buttonWidth = window.getComputedStyle(button).width;

                button.style.width = buttonWidth;

                button.textContent = "";
                button.classList.add("sending");
            }
        });
    });
</script>

@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            vercelToast.createToast("{{ session('success') }}", {
                timeout: 4000,
                action: {
                    text: 'Cerrar',
                    callback: function(toast) {
                        toast.destroy();
                    }
                }
            });
        });
    </script>
@endif
