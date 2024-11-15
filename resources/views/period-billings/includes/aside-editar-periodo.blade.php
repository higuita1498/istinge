<style>
    .crear-plan__aside {
        position: fixed;
        text-wrap: nowrap;
        top: 0;
        right: 0;
        z-index: 1200;
        width: 35vw;
        height: 100vh;
        overflow-y: auto;
        padding: 20px 20px 0 20px;
        background: #ffffffa3;
        backdrop-filter: blur(8px);
        gap: 20px;
        box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
        background-color: #fffff;
        border-radius: 33px 0 0 33px;
        border: 1px solid rgba(255, 255, 255, 0.18);

        textarea {
            field-sizing: content;
            line-height: 1.5;
        }
    }

    @media (width < 1139px) {
        .crear-plan__aside {
            width: 100vw;
            text-wrap: wrap;
        }
    }

    @media (width < 1139px) {
        .crear-plan__aside {
            width: 100vw;
        }
    }

    .crear-plan__aside label {
        color: black;
        display: block;
        margin: 0 0 4px 0;
        font-size: 14px;
    }

    .crear-plan__aside input,
    .crear-plan__aside select,
    .crear-plan__aside textarea {
        font-weight: 500;
        font-size: 16px;
        width: 100%;
        border-radius: 8px;
        padding-left: 10px;
        border: 1px solid #b2b2b2 !important;
        outline: none;
    }

    .crear-plan__aside div {
        margin-bottom: 8px;
    }

    .crear-plan__aside input:focus,
    select:focus,
    textarea:focus {
        box-shadow: 0 0 1px 1px #001128 !important;
        -webkit-transition: 0.1s;
        transition: 0.2s;
    }

    .crear-plan__aside button {
        position: relative;
        overflow: hidden;
        width: 100%;
        cursor: pointer;
        padding: 14px 17px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        color: #fff;
        background: linear-gradient(182deg, #1b3354 31.03%, #001128 99.96%);
        font-size: 15px;
        font-weight: 700;
        border-radius: 10px;
        box-shadow: 0 4px 8px #00207026;
        transition: all .3s ease;

        &:hover {
            transition: all .3s ease;
            opacity: .8;
        }
    }

    .crear-plan__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 150px;
        margin: 1rem 0;

        >div h1 {
            color: #000;
            font-size: clamp(1.4rem, 0.5999999999999996rem + 1.0000000000000004vw, 1.8rem);
            font-weight: 800;
            margin-bottom: -3px;
        }

        >div span {
            color: #515151;
            font-size: clamp(0.8rem, 0.40000000000000013rem + 0.49999999999999994vw, 1rem);
            font-weight: 400;
            padding: 0 0 0 4rem;
        }

        button {
            all: unset;
            cursor: pointer;
            width: 36px;
            display: flex;
            height: 36px;
            padding: 0 0 0 4rem;

            &:hover {
                opacity: .7;
            }
        }
    }

    @keyframes mostrarFiltros {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes hideFiltros {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
</style>

<section class="crear-plan__aside" id="crear-plan" style="display: none;">
    <header class="crear-plan__header">
        <div>
            <h1>Editar periodo</h1>
        </div>
        <button onclick="abrirFiltrador();" title="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" class="m-auto" width="24" height="24" viewBox="0 0 24 24"
                fill="none">
                <path d="M19 5L5 19M5 5L19 19" stroke="#010101" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </button>
    </header>

    <form method="POST" action="{{ route('plans.billings.update', $periodBilling->id) }}" id="editar-periodo-form">
        @csrf

        <section class="crear-plan">
            <div>
                <label for="extension_days">Prorroga</label>
                <input class="form-control" id="extension_days" type="number" name="extension_days"
                    value="{{ $periodBilling->extension_days }}" required autofocus>
            </div>

            <div>
                <label for="months">Cantidad de meses</label>
                <input class="form-control" id="months" type="number" name="months"
                    value="{{ $periodBilling->months }}" required>
            </div>

            <div>
                <label for="discount">Descuento</label>
                <input class="form-control" id="discount" type="number" name="discount"
                    value="{{ $periodBilling->discount }}">
            </div>

            <div>
                <label for="is_fav">Es favorito?</label>
                <select class="form-control" id="is_fav" name="is_fav">
                    <option value="1" @if ($periodBilling->is_fav) selected @endif>Si</option>
                    <option value="0" @unless ($periodBilling->is_fav) selected @endunless>No</option>
                </select>
            </div>

            <article style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                <div>
                    <label for="additon_invoices">Adicion de facturas</label>
                    <input class="form-control" id="additon_invoices" type="number" name="additon_invoices"
                        value="{{ $periodBilling->additon_invoices }}">
                </div>

                <div>
                    <label for="additon_billing">Adicion de monto</label>
                    <input class="form-control" id="additon_billing" type="number" name="additon_billing"
                        value="{{ $periodBilling->additon_billing }}">
                </div>
            </article>

            <div>
                <label for="is_active">¿Habilitado?</label>
                <select class="form-control" id="is_active" name="is_active">
                    <option value="1" @if ($periodBilling->is_active) selected @endif>Si</option>
                    <option value="0" @unless ($periodBilling->is_active) selected @endunless>No</option>
                </select>
            </div>

        </section>


        <div style="flex: 100%; margin: 5% 0;">
            <button class="submit-new-plan" type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" height="16" viewBox="0 0 16 15" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M3.47299 0.230384C6.48178 -0.0767946 9.51822 -0.0767946 12.527 0.230384C14.1929 0.402364 15.5369 1.6107 15.7324 3.15046C16.0892 5.96093 16.0892 8.80013 15.7324 11.6106C15.6335 12.3588 15.2631 13.0543 14.6813 13.5843C14.0995 14.1143 13.3404 14.4478 12.527 14.5307C9.51827 14.8388 6.48172 14.8388 3.47299 14.5307C2.65964 14.4478 1.90052 14.1143 1.31872 13.5843C0.736911 13.0543 0.366526 12.3588 0.267611 11.6106C-0.0892037 8.80043 -0.0892037 5.96153 0.267611 3.15135C0.366484 2.4033 0.736728 1.70793 1.31834 1.17792C1.89995 0.647922 2.65884 0.314353 3.47202 0.231279L3.47299 0.230384ZM8 2.90861C8.19344 2.90861 8.37896 2.97939 8.51575 3.10537C8.65253 3.23136 8.72938 3.40224 8.72938 3.58041V6.70918H12.1263C12.3198 6.70918 12.5053 6.77996 12.6421 6.90595C12.7789 7.03194 12.8557 7.20281 12.8557 7.38098C12.8557 7.55915 12.7789 7.73003 12.6421 7.85601C12.5053 7.982 12.3198 8.05278 12.1263 8.05278H8.72938V11.1816C8.72938 11.3597 8.65253 11.5306 8.51575 11.6566C8.37896 11.7826 8.19344 11.8534 8 11.8534C7.80655 11.8534 7.62104 11.7826 7.48425 11.6566C7.34747 11.5306 7.27062 11.3597 7.27062 11.1816V8.05278H3.87366C3.68022 8.05278 3.4947 7.982 3.35791 7.85601C3.22113 7.73003 3.14428 7.55915 3.14428 7.38098C3.14428 7.20281 3.22113 7.03194 3.35791 6.90595C3.4947 6.77996 3.68022 6.70918 3.87366 6.70918H7.27062V3.58041C7.27062 3.40224 7.34747 3.23136 7.48425 3.10537C7.62104 2.97939 7.80655 2.90861 8 2.90861Z"
                        fill="#63ECBC" />
                </svg>
                Confirmar edición</button>
        </div>
    </form>
</section>

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




<script>
    const abrirFiltrador = () => {
        const crearPlan = document.getElementById('crear-plan');
        let isShowing = crearPlan.style.display !== 'none';

        const handleAnimationEnd = (event) => {
            crearPlan.removeEventListener('animationend', handleAnimationEnd);
            crearPlan.style.animation = '';
            // After the hide animation, set display to 'none'.
            if (event.animationName === 'hideFiltros') {
                crearPlan.style.display = 'none';
            }
        };

        crearPlan.addEventListener('animationend', handleAnimationEnd);

        if (!isShowing) {
            crearPlan.style.display = 'block';
            crearPlan.style.animation = 'mostrarFiltros 400ms cubic-bezier(0.25, 0.8, 0.25, 1) forwards';
        } else {
            crearPlan.style.animation = 'hideFiltros 400ms cubic-bezier(0.25, 0.8, 0.25, 1) forwards';
        }

        isShowing = !isShowing;
    };

    document.querySelector('.submit-new-plan').addEventListener('click', function(event) {
        const form = document.getElementById('editar-periodo-form');
        if (form.checkValidity()) {
            const button = event.target;
            const buttonWidth = window.getComputedStyle(button).width;

            button.style.width = buttonWidth;

            button.textContent = "";
            button.classList.add("sending");
        }
    });
</script>
