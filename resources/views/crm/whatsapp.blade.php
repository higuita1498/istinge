@extends('layouts.app')

@section('style')
    <style>
        .whatsapp_main--container {
            display: grid;
            width: 100%;
            height: 100vh;
            background-color: #26d366;
            place-items: center;
        }

        .whatsapp_main--container>section {
            background-color: #fff;
            display: flex;
            flex-direction: column;
            padding: 5em 5em;
        }

        .whatsapp_main--container>section.row {
            flex-direction: row;
        }

        .whatsapp_main--container>section.not_instance {
            max-width: 40em;
            gap: 2em;
        }

        input.is-invalid {
            border-color: #dc3545 !important;
        }



        button.get-qr {
            position: absolute;
            padding: 8px 16px;
            z-index: 1;
            background: #26d366;
            border: none;
            outline: none;
            border-radius: 2px;
            cursor: pointer;
        }

        .button:active {
            background: #17a24a;
        }

        .button__text {
            font: bold 20px;
            color: #ffffff;
            transition: all 0.2s;
        }

        .button--loading .button__text {
            visibility: hidden;
            opacity: 0;
        }

        .button--loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            border: 4px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: button-loading-spinner 1s ease infinite;
        }

        @keyframes button-loading-spinner {
            from {
                transform: rotate(0turn);
            }

            to {
                transform: rotate(1turn);
            }
        }

        div#qr-container {
            width: 100%;
            height: 100%;
            filter: blur(2px);
            opacity: .5;
        }
    </style>
@endsection

@section('content')
    <div class="whatsapp_main--container">
        @if (!$instance)
            <section class="not_instance">
                <h2>
                    No se encontró una instancia.
                </h2>
                <form action="{{ route('instances.store') }}" method="POST"
                    style="display: flex; flex-direction: column; gap: 1em;">
                    @csrf
                    @error('instance_id')
                        <div class="alert alert-danger" role="alert">
                            {{ $message }}
                        </div>
                    @enderror
                    Si ya cuenta con una instancia, por favor ingrese el numero de identificación.
                    <span>
                        <input class="form-control @error('instance_id') is-invalid @enderror" required type="text"
                            name="instance_id" value="{{ old('instance_id') }}" placeholder="id de la instancia"
                            id="instance_id">
                    </span>
                    <button type="submit" style="width: 100%;" class="btn btn-primary">
                        Enviar
                    </button>
                </form>
            </section>
        @endif

        @if($instance)
            <input type="hidden" name="instance-key" id="instance-key" value="{{ $instance->api_key }}">
        @endif

        @if ($instance && !$instance->isPaired())

            @csrf
            <section class="row">
                <div style="flex: 1;max-width: 24em;display: flex;flex-direction: column;justify-content: center;">
                    <h1>Whatsapp</h1>
                    <ul class="ml-4">
                        <li>
                            <p>Abre WhatsApp en tu teléfono</p>
                        </li>
                        <li>
                            <p>
                                Toca Menú <i class="fas fa-ellipsis-v"></i> o configuración
                                <i class="fa fa-cog" aria-hidden="true"></i> y selecciona
                                "Dispositivos vinculados"
                            </p>
                        </li>
                        <li>
                            <p>
                                Toca "Vincular un dispositivo y apunta tu teléfono hacía el
                                código qr que se muestra en pantalla para escanearlo"
                            </p>
                        </li>
                    </ul>
                </div>
                <div id="qrcode"
                    style="min-width: 17em;min-height: 17em;display: grid;place-items: center;padding: 0em; flex: 2;">
                    <button id="btn-get-qr" type="button" class="get-qr">
                        <span class="button__text">
                            Obtener qr
                        </span>
                    </button>
                    <div id="qr-container" style="width: 100%; height: 100%;">
                        <img style="width: 100%" src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(200)->generate('https://wa.me/6281234567890')) !!} ">
                    </div>
                </div>
            </section>
        @endif

        @if ($instance && $instance->isPaired())

            <section>
                <h1>Whatsapp</h1>
                <p>Tu cuenta ya se encuntra vinculada, ya puedes enviar facturas por whatsapp!</p>
            </section>
        @endif
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

    <script defer>
        const getQr = document.querySelector('#btn-get-qr');
        const qrContainer = document.querySelector('#qr-container');
        const qrCode = document.querySelector('#qrcode');

        getQr.addEventListener('click', async () => {
            if (getQr.classList.contains('button--loading')) {
                return;
            }
            setLoading(getQr);
            const response = await fetch("/empresa/instances");
            const instance = await response.json();

            if (instance.id) {
                const session = await fetch(`/empresa/instances/${instance.id}/pair`);
                const sessionData = await session.json();
                if (sessionData.status == "error") {
                    removeLoading(getQr);
                    swal({
                        title: 'Ha ocurrido un error iniciando sesión, comuniquse con el administrador',
                        type: 'error',
                        showCancelButton: false,
                        showConfirmButton: true,
                        cancelButtonColor: '#00ce68',
                        cancelButtonText: 'Aceptar',
                    });
                }
            }
        });

        const setLoading = (btn) => {
            if (!btn.classList.contains('button--loading')) {
                btn.classList.add('button--loading');
            }
        }

        const removeLoading = (btn) => {
            if (btn.classList.contains('button--loading')) {
                btn.classList.remove('button--loading');
            }
        }
    </script>

    <script defer>

        const apiKey = document.querySelector('#instance-key').value || "";
        const socket = io("{{ env('WAPI_URL') }}", {
            extraHeaders: {
                "Authorization": `Bearer ${apiKey}`
            }
        });

        socket.on("whatsappSession", (arg) => {
            const {
                session
            } = arg;
            if (session.status == "QRCODE") {
                document.getElementById("qrcode").innerHTML = "";

                new QRCode(document.getElementById("qrcode"), {
                    text: session.qrcode,
                    colorDark: "#052e16",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
                return;
            }

            if (session.status == "PAIRED") {
                fetch(`/empresa/instances/${session.instanceId}`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status == "error") {
                            swal({
                                title: 'Ha ocurrido un error iniciando sesión, comuniquse con el administrador',
                                type: 'error',
                                showCancelButton: false,
                                showConfirmButton: true,
                                cancelButtonColor: '#00ce68',
                                cancelButtonText: 'Aceptar',
                            });
                        }

                        swal({
                            title: 'Sesión iniciada correctamente',
                            type: 'success',
                            showCancelButton: false,
                            showConfirmButton: true,
                            cancelButtonColor: '#00ce68',
                            cancelButtonText: 'Aceptar',
                        });

                        window.location.reload();
                    }).catch(error => {
                        swal({
                            title: 'Ha ocurrido un error iniciando sesión, comuniquse con el administrador',
                            type: 'error',
                            showCancelButton: false,
                            showConfirmButton: true,
                            cancelButtonColor: '#00ce68',
                            cancelButtonText: 'Aceptar',
                        });
                    });
            }
        })
    </script>
@endsection
