@extends('layouts.app')

@section('boton')

@endsection

@section('content')
    <style>
        .readonly{ border: 0 !important; }
        .jay-signature-pad {
            position: relative;
            display: -ms-flexbox;
            -ms-flex-direction: column;
            width: 100%;
            height: 100%;
            max-width: 545px;
            max-height: 410px;
            border: 1px solid #e8e8e8;
            background-color: #fff;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0px;
        }
        .txt-center { text-align: -webkit-center; }
    </style>

    @if(Session::has('danger'))
        <div class="alert alert-danger" >
            {{Session::get('danger')}}
        </div>
        <script type="text/javascript">
            setTimeout(function(){
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 50000);
        </script>
    @endif

    @if(Session::has('success'))
        <div class="alert alert-success" >
            {{Session::get('success')}}
        </div>
        <script type="text/javascript">
            setTimeout(function(){
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 50000);
        </script>
    @endif

    <form method="POST" action="{{ route('asignaciones.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" id="form-asignacion" enctype="multipart/form-data">
        {{ csrf_field() }}
        @if(isset($contrato))
            <input type="hidden" value="{{ $contrato->id }}" name="contrato">
        @endif
        <div class="row">
            <div class="col-md-3 form-group">
                <label class="control-label">Cliente <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select class="form-control selectpicker" name="id" id="idCliente" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="cargarContratos()">
                        @foreach($clientes as $cliente)
                        <option value="{{$cliente->id}}" {{$cliente->id == $idCliente ? 'selected' : '' }}>{{$cliente->nombre}} {{$cliente->apellido1}} {{$cliente->apellido2}} - {{$cliente->nit}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label">Contrato <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select class="form-control selectpicker" name="id" id="id" required="" title="Seleccione" data-live-search="true" data-size="5">
                        {{-- @foreach($contratos as $contrato)
                        <option value="{{$contrato->id}}" {{$contrato->id == $idCliente ? 'selected' : '' }}>{{$contrato->nombre}} {{$contrato->apellido1}} {{$contrato->apellido2}} - {{$contrato->nit}}</option>
                        @endforeach --}}
                    </select>
                </div>
            </div>
            <div class="col-md-3 form-group offset-md-1">
                <label class="control-label" id="div_campo_1">{{$empresa->campo_1}} <span class="text-danger">*</span></label>
                <input type="file" class="form-control"  id="documento" name="documento"  required="" value="{{old('documento')}}" accept=".jpg, .jpeg, .png">
                <span style="color: red;">
                    <strong>{{ $errors->first('documento') }}</strong>
                </span>
            </div>

            <div class="col-md-3 form-group">
                <label class="control-label" id="div_campo_a">{{$empresa->campo_a}}</label>
                <input type="file" class="form-control"  id="imgA" name="imgA"  value="{{old('imgA')}}" accept=".jpg, .jpeg, .png">
                <span style="color: red;">
                    <strong>{{ $errors->first('imgA') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label" id="div_campo_b">{{$empresa->campo_b}}</label>
                <input type="file" class="form-control"  id="imgB" name="imgB"  value="{{old('imgB')}}" accept=".jpg, .jpeg, .png">
                <span style="color: red;">
                    <strong>{{ $errors->first('imgB') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label" id="div_campo_c">{{$empresa->campo_c}}</label>
                <input type="file" class="form-control"  id="imgC" name="imgC"  value="{{old('imgC')}}" accept=".jpg, .jpeg, .png">
                <span style="color: red;">
                    <strong>{{ $errors->first('imgC') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label" id="div_campo_d">{{$empresa->campo_d}}</label>
                <input type="file" class="form-control"  id="imgD" name="imgD"  value="{{old('imgD')}}" accept=".jpg, .jpeg, .png">
                <span style="color: red;">
                    <strong>{{ $errors->first('imgD') }}</strong>
                </span>
            </div>

            <div class="col-md-3 form-group">
                <label class="control-label" id="div_campo_e">{{$empresa->campo_e}}</label>
                <input type="file" class="form-control"  id="imgE" name="imgE"  value="{{old('imgE')}}" accept=".jpg, .jpeg, .png">
                <span style="color: red;">
                    <strong>{{ $errors->first('imgE') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label" id="div_campo_f">{{$empresa->campo_f}}</label>
                <input type="file" class="form-control"  id="imgF" name="imgF"  value="{{old('imgF')}}" accept=".jpg, .jpeg, .png">
                <span style="color: red;">
                    <strong>{{ $errors->first('imgF') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label" id="div_campo_g">{{$empresa->campo_g}}</label>
                <input type="file" class="form-control"  id="imgG" name="imgG"  value="{{old('imgG')}}" accept=".jpg, .jpeg, .png">
                <span style="color: red;">
                    <strong>{{ $errors->first('imgG') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label" id="div_campo_h">{{$empresa->campo_h}}</label>
                <input type="file" class="form-control"  id="imgH" name="imgH"  value="{{old('imgH')}}" accept=".jpg, .jpeg, .png">
                <span style="color: red;">
                    <strong>{{ $errors->first('imgH') }}</strong>
                </span>
            </div>

                <div class="col-md-3 form-group">
                    <label class="control-label" id="">Adjuntar audio</label>
                    <input type="file" class="form-control" name="adjunto_audio" accept="audio/*" id="adjunto_audio" >
                    {{-- <input type="file" class="form-control"  id="adjunto" name="adjunto4" value="{{$radicado->adjunto}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF" required> --}}
                    <span style="color: red;">
                        <strong>{{ $errors->first('adjunto_audio') }}</strong>
                    </span>
                </div>

        </div>

        <center>
            <div id="signature-pad" class="jay-signature-pad">
                <div class="jay-signature-pad--body">
                    <canvas id="jay-signature-pad" style="border: 1px solid #333;margin-bottom: 5px;border-radius: 10px;width: 100%;height: 280px;"></canvas>
                </div>
                <div class="signature-pad--footer txt-center">
                    <div class="signature-pad--actions txt-center">
                        <div>
                            <button type="button" class="button clear btn btn-warning" data-action="clear">Limpiar</button>
                            <button type="button" class="button" data-action="change-color" style="display: none;">Change color</button>
                            <button class="btn btn-success d-none" data-action="save-png" id="btnFirma">Guardar</button>
                        </div>
                        <div>
                            <input type="hidden" id="dataImg" name="firma_isp">
                        </div>
                    </div>
                </div>
            </div>
        </center>

        <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>

        <hr>

        <div class="row">
            <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
                <a href="{{route('asignaciones.index')}}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success d-none">Guardar</button>
                <button class="btn btn-success" onclick="btn_signature()">Guardar</button>
            </div>
        </div>
    </form>

    <script src="{{asset('vendors/signature_pad/2.3.2/signature_pad.min.js')}}"></script>
    <script src="{{asset('vendors/signature_pad/1.5.3/signature_pad.min.js')}}"></script>

    <script>
        var wrapper = document.getElementById("signature-pad");
        var clearButton = wrapper.querySelector("[data-action=clear]");
        var changeColorButton = wrapper.querySelector("[data-action=change-color]");
        var savePNGButton = wrapper.querySelector("[data-action=save-png]");
        // var saveJPGButton = wrapper.querySelector("[data-action=save-jpg]");
        // var saveSVGButton = wrapper.querySelector("[data-action=save-svg]");
        var canvas = wrapper.querySelector("canvas");
        var signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)'
        });
        function resizeCanvas() {
            var ratio =  Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }
        window.onresize = resizeCanvas;
        resizeCanvas();
        function download(dataURL, filename) {
            var blob = dataURLToBlob(dataURL);
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement("a");
            a.style = "display: none";
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            //a.click();
            window.URL.revokeObjectURL(url);
        }
        function dataURLToBlob(dataURL) {
            var parts = dataURL.split(';base64,');
            var contentType = parts[0].split(":")[1];
            var raw = window.atob(parts[1]);
            var rawLength = raw.length;
            var uInt8Array = new Uint8Array(rawLength);
            for (var i = 0; i < rawLength; ++i) {
                uInt8Array[i] = raw.charCodeAt(i);
            }
            return new Blob([uInt8Array], { type: contentType });
        }
        clearButton.addEventListener("click", function (event) {
            signaturePad.clear();
        });
        changeColorButton.addEventListener("click", function (event) {
            var r = Math.round(Math.random() * 255);
            var g = Math.round(Math.random() * 255);
            var b = Math.round(Math.random() * 255);
            var color = "rgb(" + r + "," + g + "," + b +")";
            signaturePad.penColor = color;
        });
        savePNGButton.addEventListener("click", function (event) {
            if (signaturePad.isEmpty()) {
                return false;
            } else {
                var dataURL = signaturePad.toDataURL();
                document.getElementById("dataImg").value = dataURL.replace(/^data:image\/png;base64,/, "+");
                console.log(dataImg);
                //document.getElementById("submitcheck").click();
            }
        });
    </script>
@endsection

@section('scripts')
    <script>
        $(document).on('change','input[type="file"]',function(){
            var fileName = this.files[0].name;
            var fileSize = this.files[0].size;

            if(fileSize > 5000000){
                this.value = '';
                Swal.fire({
                    title: 'La documentación adjuntada no puede exceder 5MB',
                    text: 'Intente nuevamente',
                    type: 'error',
                    showCancelButton: false,
                    showConfirmButton: false,
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Cancelar',
                    timer: 10000
                });
            }else{
                var ext = fileName.split('.').pop();
                switch (ext) {
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'pdf':
                    case 'JPG':
                    case 'JPEG':
                    case 'PNG':
                    case 'PDF':
                    case: 'mp3'
                        break;
                    default:
                        this.value = '';
                        Swal.fire({
                            title: 'La documentación adjuntada debe poseer una extensión apropiada. Sólo se aceptan archivos jpg, png o pdf',
                            text: 'Intente nuevamente',
                            type: 'error',
                            showCancelButton: false,
                            showConfirmButton: false,
                            cancelButtonColor: '#d33',
                            cancelButtonText: 'Cancelar',
                            timer: 10000
                        });
                }
            }
        });

        function btn_signature(){
            document.getElementById("btnFirma").click();
        }

        $(document).ready(function () {
            $('#cancelar').click(function () {
                $('#forma').trigger("reset");
            });
        });
    </script>
    <script>
         function cargarContratos() {
            // Obtén el valor seleccionado del cliente
            var selectedClientId = document.getElementById('idCliente').value;
            console.log();
            // Realiza la llamada al contrato utilizando AJAX
            $.ajax({
                url: '/clientes/contratos/' + selectedClientId,
                type: 'POST',
                data: { clientId: selectedClientId },
                success: function(response) {
                    // Maneja la respuesta del contrato y actualiza el segundo select
                    updateContratosSelect(response);
                    console.log('Contrato llamado con éxito:', response);
                },
                error: function(error) {
                    console.error('Error al llamar al contrato:', error);
                }
            });
        }

        // Función para actualizar dinámicamente el segundo select con el resultado del contrato
        function updateContratosSelect(contratos) {
            var selectContrato = document.getElementById('idContrato');
            selectContrato.innerHTML = ''; // Limpiar opciones existentes

            // Agregar nuevas opciones basadas en la respuesta del contrato
            contratos.forEach(function(contrato) {
                var option = document.createElement('option');
                option.value = contrato.id;
                option.textContent = contrato.nombre + ' ' + contrato.apellido1 + ' ' + contrato.apellido2 + ' - ' + contrato.nit;
                selectContrato.appendChild(option);
            });
        }
    </script>

@endsection
