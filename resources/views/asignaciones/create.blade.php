@extends('layouts.app')
@section('content')
  <style>
    .readonly{ border: 0 !important; }
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
  
    <style>
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
                /*box-shadow: 0 3px 20px rgba(0, 0, 0, 0.27), 0 0 40px rgba(0, 0, 0, 0.08) inset;*/
                border-radius: 15px;
                padding: 20px;
                margin: 20px 0px;
            }
            .txt-center {
                text-align: -webkit-center;
            }
        </style>

  <form method="POST" action="{{ route('asignaciones.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" id="form-asignacion" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-md-5 form-group">
            <label class="control-label">Cliente <span class="text-danger">*</span></label>
            <div class="input-group">
                <select class="form-control selectpicker" name="id" id="id" required="" title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($clientes as $cliente)
                    <option value="{{$cliente->id}}">{{$cliente->nombre}} - {{$cliente->nit}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6 form-group offset-md-1">
            <label class="control-label">Adjunte la documentación <span class="text-danger">*</span></label>
            <input type="file" class="form-control"  id="documento" name="documento"  required="" value="{{old('documento')}}" accept=".jpg, .jpeg, .png, .pdf">
            <span style="color: red;">
                <strong>{{ $errors->first('documento') }}</strong>
            </span>
        </div>

        <div class="col-md-3 form-group">
            <label class="control-label">Imagen A</label>
            <input type="file" class="form-control"  id="imgA" name="imgA"  required="" value="{{old('imgA')}}" accept=".jpg, .jpeg, .png">
            <span style="color: red;">
                <strong>{{ $errors->first('imgA') }}</strong>
            </span>
        </div>
        <div class="col-md-3 form-group">
            <label class="control-label">Imagen B</label>
            <input type="file" class="form-control"  id="imgB" name="imgB"  required="" value="{{old('imgB')}}" accept=".jpg, .jpeg, .png">
            <span style="color: red;">
                <strong>{{ $errors->first('imgB') }}</strong>
            </span>
        </div>
        <div class="col-md-3 form-group">
            <label class="control-label">Imagen C</label>
            <input type="file" class="form-control"  id="imgC" name="imgC"  required="" value="{{old('imgC')}}" accept=".jpg, .jpeg, .png">
            <span style="color: red;">
                <strong>{{ $errors->first('imgC') }}</strong>
            </span>
        </div>
        <div class="col-md-3 form-group">
            <label class="control-label">Imagen D</label>
            <input type="file" class="form-control"  id="imgD" name="imgD"  required="" value="{{old('imgD')}}" accept=".jpg, .jpeg, .png">
            <span style="color: red;">
                <strong>{{ $errors->first('imgD') }}</strong>
            </span>
        </div>
    </div>
    <div class="d-none row">
        <div class="col-md-12 form-group">
            <label class="control-label">Contrato Único de Servicios Fijos</label>
            <div class="input-group">
                <textarea rows="15" class="form-control" readonly>Este contrato explica las condiciones para la prestación de los servicios entre usted y INTERCAR.NET, por el que pagará mínimo mensualmente $ . Este contrato tendrá vigencia indefinida, contada a partir del momento de instalación del servicio. El plazo máximo de instalación es de 15 días Hábiles. Acepto que mi contrato se renueve sucesiva y automáticamente por un plazo igual al inicial.

                EL SERVICIO
                Con este contrato nos comprometemos a prestarle los servicios que usted elija: Telefonía fija, Internet fijo o Televisión. Usted se compromete a pagar oportunamente el precio acordado.

                PRINCIPALES OBLIGACIONES DEL USUARIO
                1) Pagar oportunamente los servicios prestados, incluyendo los intereses de mora cuando haya incumplimiento;
                2) Suministrar información verdadera;
                3) Hacer uso adecuado de los equipos y los servicios;
                4) No divulgar ni acceder a pornografía infantil (Consultar anexo);
                5) Avisar a las autoridades cualquier evento de robo o hurto de elementos de la red, como el cable;
                6) No cometer o ser partícipe de actividades de fraude.

                CALIDAD Y COMPENSACIÓN
                Cuando se presente indisponibilidad del servicio o este se suspenda a pesar de su pago oportuno, lo compensaremos en su próxima factura. Debemos cumplir con las condiciones de calidad definidas por la CRC. Consúltelas en la página: www.intercarnet.com

                CESIÓN
                Si quiere ceder este contrato a otra persona, debe presentar una solicitud por escrito a través de nuestros Medios de Atención, acompañada de la aceptación por escrito de la persona a la que se hará la cesión. Dentro de los 15 días hábiles siguientes, analizaremos su solicitud y le daremos una respuesta. Si se acepta la cesión queda liberado de cualquier responsabilidad con nosotros.

                MODIFICACIÓN
                Nosotros no podemos modificar el contrato sin su autorización. Esto incluye que no podemos cobrarle servicios que no haya aceptado expresamente. Si esto ocurre tiene derecho a terminar el contrato, incluso estando vigente la cláusula de permanencia mínima, sin la obligación de pagar suma alguna por este concepto. No obstante, usted puede en cualquier momento modificar los servicios contratados. Dicha modificación se hará efectiva en el período de facturación siguiente, para lo cual deberá presentar la solicitud de modificación por lo menos con 3 días hábiles de anterioridad al corte de facturación.

                SUSPENSIÓN
                Usted tiene derecho a solicitar la suspensión del servicio por un máximo de 2 meses al año. Para esto debe presentar la solicitud antes del inicio del ciclo de facturación que desea suspender. Si existe una cláusula de permanencia mínima, su vigencia se prorrogará por el tiempo que dure la suspensión.

                TERMINACIÓN
                Usted puede terminar el contrato en cualquier momento sin penalidades. Para esto debe realizar una solicitud a través de cualquiera de nuestros Medios de Atención mínimo 3 días hábiles antes del corte de facturación (su corte de facturación es el día 30 de cada mes). Si presenta la solicitud con una anticipación menor, la terminación del servicio se dará en el siguiente periodo de facturación.
                Así mismo, usted puede cancelar cualquiera de los servicios contratados, para lo que le informaremos las condiciones en las que serán prestados los servicios no cancelados y actualizaremos el contrato. Así mismo, si el operador no inicia la prestación del servicio en el plazo acordado, usted puede pedir la restitución de su dinero y la terminación del contrato.

                PAGO Y FACTURACIÓN
                La factura le debe llegar como mínimo 5 días hábiles antes de la fecha de pago. Si no llega, puede solicitarla a través de nuestros Medios de Atención y debe pagarla oportunamente. Si no paga a tiempo, previo aviso, suspenderemos su servicio hasta que pague sus saldos pendientes. Contamos con 3 días hábiles luego de su pago para reconectarle el servicio. Si no paga a tiempo, también podemos reportar su deuda a las centrales de riesgo. Para esto tenemos que avisarle por lo menos con 20 días calendario de anticipación. Si paga luego de este reporte tenemos la obligación dentro del mes de seguimiento de informar su pago para que ya no aparezca reportado. Si tiene un reclamo sobre su factura, puede presentarlo antes de la fecha de pago y en ese caso no debe pagar las sumas reclamadas hasta que resolvamos su solicitud. Si ya pagó, tiene 6 meses para presentar la reclamación.

                CAMBIO DE DOMICILIO
                Usted puede cambiar de domicilio y continuar con el servicio siempre que sea técnicamente posible. Si desde el punto de vista técnico no es viable el traslado del servicio, usted puede ceder su contrato a un tercero o terminarlo pagando el valor de la cláusula de permanencia mínima si esta vigente.

                LARGA DISTANCIA
                Nos comprometemos a usar el operador de larga distancia que usted nos indique, para lo cual debe marcar el código de larga distancia del operador que elija.

                COBRO POR RECONEXIÓN DEL SERVICIO
                En caso de suspensión del servicio por mora en el pago, podremos cobrarle un valor por reconexión que corresponderá estrictamente a los costos asociados a la operación de reconexión. En caso de servicios empaquetados procede máximo un cobro de reconexión por cada tipo de conexión empleado en la prestación de los servicios.
                El usuario es el ÚNICO responsable por el contenido y la información que se curse a través de la red y del uso que se haga de los equipos o de los servicios.
                Los equipos de comunicaciones que ya no use son desechos que no deben ser botados a la caneca, consulte nuestra política de recolección de aparatos en desuso.

                Fecha inicio del plazo y fecha corte de facturación (*A) se informan luego de la instalación. La imposibilidad técnica da lugar a terminar el contrato. fijará y reajustará las tarifas de los servicios en cualquier tiempo, sin exceder ajustes supere dicho valor dentro del año. INTERCAR.NET anualmente 1 SDMLV y sin que el cómputo total de los entrega los equipos en calidad de comodato; para la prestación de servicios podrá entregar otros equipos en comodato, arrendamiento y/o cualquier otro tipo de tenencia. Devolución de equipos: Usted puede entregarlos en oficinas de atención o en una cita en el lugar de instalación, si Usted incumple deberá entregarlos en una oficina de atención dentro de los siguientes 20 días, si no los restituye se cobrará el valor comercial de estos o se podrá seguir cobrando el canon de arrendamiento. El hurto, pérdida o daño de equipos deberá reportarse a INTERCAR.NET en las 72 horas siguientes al hecho, con la copia de la denuncia por hurto. Ante la no devolución, deberá pagar a INTERCAR.NET. INTERCAR.NET podrá terminar el contrato por: i) Vencimiento del plazo ii) Incumplimiento del Usuario de obligaciones legales, regulatorias y contractuales tales como: a) no pagar b) modificar, alterar, o cambiar características técnicas (servicio o equipos) c) acceder a los Servicios fraudulentamente d) cualquier forma de comercialización, distribución, reventa o negociación de servicios, derechos o uso de la red. La terminación no exime al Suscriptor de la cancelación de obligaciones causadas. INTERCAR.NET podrá negar el servicio o suspenderlo si Usted figura como deudor moroso de INTERCAR.NET. El contrato y las facturas prestan mérito ejecutivo.
                </textarea>
            </div>
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

    <div class="row" >
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
            var saveJPGButton = wrapper.querySelector("[data-action=save-jpg]");
            var saveSVGButton = wrapper.querySelector("[data-action=save-svg]");
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
                    alert("Ingrese la firma del cliente.");
                    return false;
                } else {
                    var dataURL = signaturePad.toDataURL();
                    document.getElementById("dataImg").value = dataURL.replace(/^data:image\/png;base64,/, "+");
                    console.log(dataImg);
                    //document.getElementById("submitcheck").click();
                }
            });
            saveJPGButton.addEventListener("click", function (event) {
                if (signaturePad.isEmpty()) {
                    alert("Ingrese la firma del cliente.");
                    return false;
                } else {
                    var dataURL = signaturePad.toDataURL("image/jpeg");
                    document.getElementById("dataImg").value = dataURL;
                    document.getElementById("submitcheck").click();
                }
            });
            saveSVGButton.addEventListener("click", function (event) {
                if (signaturePad.isEmpty()) {
                    alert("Ingrese la firma del cliente.");
                    return false;
                } else {
                    var dataURL = signaturePad.toDataURL('image/svg+xml');
                    document.getElementById("dataImg").value = dataURL;
                    document.getElementById("submitcheck").click();
                }
            });
        </script>

@endsection

@section('scripts')
    <script>
        $(document).on('change','input[type="file"]',function(){
            var fileName = this.files[0].name;
            var fileSize = this.files[0].size;
            
            if(fileSize > 1000000){
                this.value = '';
                Swal.fire({
                    title: 'La documentación adjuntada no puede exceder 1MB',
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
                    case 'png':
                    case 'pdf':
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
    </script>
@endsection

