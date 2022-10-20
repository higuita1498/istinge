@extends('layouts.app')

@section('content')
<div class="row card-description">
    <div class="col-md-12 offset-md-3">
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
                box-shadow: 0 3px 20px rgba(0, 0, 0, 0.27), 0 0 40px rgba(0, 0, 0, 0.08) inset;
                border-radius: 15px;
                padding: 20px;
            }
            .txt-center {
                text-align: -webkit-center;
            }
        </style>

        <div id="signature-pad" class="jay-signature-pad">
            <div class="jay-signature-pad--body">
                <canvas id="jay-signature-pad" style="border: 1px solid #333;margin-bottom: 5px;border-radius: 10px;width: 100%;height: 280px;"></canvas>
            </div>
            <div class="signature-pad--footer txt-center">
                <div class="signature-pad--actions txt-center">
                    <div>
                        <button type="button" class="button clear btn btn-warning" data-action="clear">Limpiar</button>
                        <button type="button" class="button" data-action="change-color" style="display: none;">Change color</button>
                    </div>
                    <div>
                        <form method="POST" action="{{ route('radicados.storefirma',$radicado->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample">{{ csrf_field() }}
                            <input type="hidden" id="dataImg" name="dataImg">
                            <button class="btn btn-success" data-action="save-png">Guardar</button>
                            <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success" style="display:none;">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

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
                    document.getElementById("submitcheck").click();
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
    </div>
</div>
@endsection