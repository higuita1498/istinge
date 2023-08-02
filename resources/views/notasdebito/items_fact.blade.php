<link rel="stylesheet" href="{{asset('vendors/iconfonts/mdi/css/materialdesignicons.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/vendor.bundle.base.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/vendor.bundle.addons.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('vendors/DataTables/datatables.min.css')}}"/>
<link rel="stylesheet" type="text/css" href="{{asset('vendors/bootstrap-selectpicker/css/bootstrap-select.min.css')}}"/>

<link href="{{asset('vendors/fontawesome/css/all.css')}}" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="{{asset('vendors/flag-icon-css/css/flag-icon.min.css')}}"/>
<link rel="stylesheet" type="text/css" href="{{asset('vendors/sweetalert2/sweetalert2.min.css')}}"/>

<table class="table table-striped table-hover" id="table-form" >
    <thead class="thead-dark">
    <tr>
        <th width="39%">Categoría/Ítem</th>
        <th width="12%">Precio</th>
        <th width="5%">Desc %</th>
        <th width="12%">Impuesto</th>
        <th width="13%">Descripción</th>
        <th width="7%">Cantidad</th>
        <th width="10%">Total</th>
        <th width="2%"></th>
    </tr>
    </thead>
    <tbody>
    @php $cont=0; @endphp
    @foreach($items as $item)
        @php $cont+=1; @endphp
        <tr id="{{$cont}}">
            <td  class="no-padding">
                <div class="resp-item">
                    <input type="hidden" name="item[]" value="{{$item->producto}}">
                    <input type="text" class="form-control form-control-sm" value="{{$item->producto}} - ({{$item->ref}})">
                </div>
            </td>
            <td class="monetario">
                <div class="resp-precio">
                    <input type="number" class="form-control form-control-sm calcularLinea" cont="{{$cont}}" id="precio{{$cont}}" name="precio[]" placeholder="Precio Unitario" required maxlength="24" min="0" value="{{$item->precio}}">
                </div>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm nro calcularLinea " cont="{{$cont}}" id="desc{{$cont}}" name="desc[]" placeholder="%" value="{{$item->desc}}">
            </td>
            <td>
                <select class="form-control form-control-sm selectpicker calcularLinea impuestos" cont="{{$cont}}" name="impuesto[]" id="impuesto{{$cont}}" title="Impuesto" required>
                    @foreach($impuestos as $impuesto)
                        <option value="{{$impuesto->id}}" porc="{{$impuesto->porcentaje}}" {{$item->id_impuesto==$impuesto->id?'selected':''}}>{{$impuesto->nombre}} - {{$impuesto->porcentaje}}%</option>
                    @endforeach
                </select>
            </td>
            <td  style="padding-top: 1% !important;">
                <div class="resp-descripcion">
                    <textarea  class="form-control form-control-sm" id="descripcion{{$cont}}" name="descripcion[]" placeholder="Descripción" >{{$item->descripcion}}</textarea>
                </div>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm calcularLinea" cont="{{$cont}}" id="cant{{$cont}}" name="cant[]" placeholder="Cantidad" min="1" required value="{{$item->cant}}">
                <p class="text-danger nomargin" id="pcant{{$cont}}"></p>
            </td>
            <td>
                <div class="resp-total">
                    <input type="text" class="form-control form-control-sm text-right" id="total{{$cont}}" value="{{App\Funcion::Parsear($item->total())}}" disabled="">
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-outline-secondary btn-icons eliminar2" cont="{{$cont}}">X</button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="row" style="margin-top: 10%;">
    <div class="col-md-4 offset-md-8">
        <table class="text-right widthtotal" >
            <tr>
                <td width="40%">Subtotal</td>
                <input type="hidden" id="subtotal_hidden" value="{{$factura->total()->subtotal}}">
                <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal">{{App\Funcion::Parsear($factura->total()->subtotal)}}</span></td>
            </tr>
            <tr>
                <input type="hidden" id="totalDescuento" value="{{$factura->total()->descuento}}">
                <td>Descuento</td><td id="descuento">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->descuento)}}</td>
            </tr>


        </table>
        <table class="text-right widthtotal"  id="totalesreten" style="width: 100%">
            <tbody>
            @php $cont=0; @endphp
            @if($factura->total()->reten)
                @foreach($factura->total()->reten as $reten)
                    @if(isset($reten->total))
                        @php $cont+=1; @endphp
                        <input type="hidden" id="retentotalmonto{{$reten->id}}" value="{{$reten->total}}">
                        <tr id="retentotal{{$reten->id}}"><td width="40%" style="font-size: 0.8em;">{{$reten->nombre}} - {{$reten->porcentaje}}%</td><td id="retentotalvalue{{$reten->id}}">-{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($reten->total)}}</td></tr>

                    @endif
                @endforeach
            @endif
            </tbody>
        </table>
        <table class="text-right widthtotal"  style="width: 100%" id="totales">

            <tr>
                <td>Subtotal</td>
                <td>{{Auth::user()->empresa()->moneda}} <span id="subsub">{{App\Funcion::Parsear($factura->total()->subsub)}}</span></td>
            </tr>

            @php $cont=0;  @endphp
            @php $total=0; @endphp
            @if($factura->total()->imp)
                @foreach($factura->total()->imp as $imp)
                    @if(isset($imp->total)) @php $cont+=1; @endphp @php $total+=$imp->total; @endphp
                    <tr id="imp{{$imp->id}}" class="totalimpuestos">
                        <td>{{$imp->nombre}} ({{$imp->porcentaje}}%)</td>
                        <input type="hidden" id="totalimpuesto{{$imp->id}}" value="{{$imp->total}}">
                        <td id="totalimp{{$imp->id}}">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
                    </tr>
                    @endif
                @endforeach
            @endif

        </table>
        <hr>
        <input type="hidden" id="totalImpuestos" value="{{$total}}">
        <table class="text-right widthtotal" style="font-size: 24px !important;">
            <tr>
                <td width="40%">TOTAL</td>
                <td>{{Auth::user()->empresa()->moneda}} <span id="total">{{App\Funcion::Parsear($factura->total()->total)}}</span></td>
            </tr>
        </table>
    </div>
</div>

<script>

    function rellenar2(id,selected){

        if (!$.isNumeric( selected )) { $('#precio'+id).focus(); return false;  }

        data={'precios':$('#lista_precios').val(), 'bodega': $('#bodega').val()};

        url=$('#url').val()+'/empresa/inventario/'+selected+'/json';
        $.ajax({
            data:data,
            url: url,
            success: function(data){

                $('#pcant'+id).html('');
                $('#cant'+id).removeAttr("max");
                data=JSON.parse(data);
                $('#ref'+id).val(data.ref);
                if (data.tipo_producto==1 && ('#orden_si').length==0) {
                    if (data.nro>0) {
                        $('#cant'+id).attr("max", data.nro);
                    }
                    if (data.nro<11) {
                        $('#pcant'+id).html('Disp '+data.nro);
                    }
                }

                $('#precio'+id).val(data.precio);
                if (data.precio_secun) {
                    $('#precio'+id).val(data.precio_secun);
                }
                $("#impuesto"+id+" option[value="+data.id_impuesto+"]").attr('selected', 'selected');
                $('#impuesto'+id).selectpicker('refresh');
                $('#cant'+id).val(1);
                calcularLinea(id);
            },
            error: function(data){
                alert('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
            }
        });

    }

    function create_imp2(total, key, name){

        if ($('#imp'+key).length > 0) {
            if (total>0) {
                if ($('#totalimp'+key).length > 0) {
                    total = parseFloat(total) + parseFloat($('#totalimpuesto'+key).val());
                }
                $('#totalimp'+key).html($('#simbolo').val()+' '+number_format(total));
                $('#totalimpuesto'+key).val(total);
            }
        }
        else{
            if (total>0) {
                $('#totales tbody').append(
                    '<tr  id="imp'+key+'" class="totalimpuestos">' +
                    '<input type="hidden" id="totalimpuesto'+key+'" value="'+total+'">' +
                    '<td >'+name+'</td><td id="totalimp'+key+'">'+$('#simbolo').val()+' '+number_format(total)+'</td></tr>');
            }
        }
    }

    function calcularLinea(cont) {
        var item = $('#item'+cont).val();
        var precio = $('#precio'+cont).val();
        var desc = $('#desc'+cont).val();
        var cant = $('#cant'+cont).val();

        calcularImpuestos();
        calcularRetencion(1);

        var total = parseFloat(cant) *parseFloat(precio);
        $('#total'+cont).val(number_format(total));
    }

    function calcularImpuestos() {
        var subtotal = 0;
        $('#subtotal').html(0);
        $('.totalimpuestos').remove();
        var totalImpuestos = 0;
        var totalDescuento = 0;

        $('#table-form .impuestos').each(function() {
            if($(this)[0].tagName!='DIV'){
                var idImp = $('#impuesto'+$(this).attr('cont')+' option:selected').val();
                var nombre = $('#impuesto'+$(this).attr('cont')+' option:selected').text();
                var porc = $('#impuesto'+$(this).attr('cont')+' option:selected').attr("porc");

                var precio = $('#precio'+$(this).attr('cont')).val();
                var cant = $('#cant'+$(this).attr('cont')).val();
                var desc = $('#desc'+$(this).attr('cont')).val();

                if(desc>0){
                    desc = precio*(desc/100);
                } else {
                    desc = 0;
                }

                var impuesto = 0;

                if(porc>0){
                    impuesto = cant*(precio*(porc/100));
                }
                create_imp2(impuesto, idImp, nombre+' ('+porc+'%)');
                subtotal = parseFloat(subtotal) + parseFloat(cant*precio);
                totalImpuestos = parseFloat(totalImpuestos) + parseFloat(impuesto);
                totalDescuento = parseFloat(totalDescuento) + parseFloat(desc);
            }
        });

        $('#subtotal').html(number_format(subtotal));
        $('#subtotal_hidden').val(subtotal);
        $('#totalImpuestos').val(totalImpuestos);
        $('#totalDescuento').val(totalDescuento);
        $('#descuento').html($('#simbolo').val()+' '+number_format(totalDescuento));
    }

    function eliminar2(id) {
        $("#" + id).remove();
        calcularLinea(1);
    }

    function eliminar3(id) {
        $("#reten" + id).remove();
        calcularRetencion(1);
    }

    function calcularRetencion(id) {
        $("#totalesreten tbody").html('');
        var subtotal = $('#subtotal_hidden').val();
        var porc = $('#retencion'+id+' option:selected').attr("porc");
        var total = (porc/100)*subtotal;

        if(isNaN(total)){
            total = 0;
        }
        var totalRetenciones = 0;
        $('#precio_reten'+id).val(total);

        $('#table-retencion .calcularRetencion').each(function() {
            if($(this)[0].tagName!='DIV'){
                var cont = $(this).attr('cont');
                var idRet = $(this).val();
                var nombre = $('#retencion'+cont+' option:selected').text();
                var total = $('#precio_reten'+cont).val();
                totalRetenciones = parseFloat(total) + parseFloat(totalRetenciones);

                if($('#retentotal'+idRet).length>0){
                    var retentotalmonto = $('#retentotalmonto'+idRet).val();
                    total = parseFloat(total) + parseFloat(retentotalmonto);
                    $('#retentotalmonto'+idRet).val(total);
                    $('#retentotalvalue'+idRet).html('-'+$('#simbolo').val()+' '+number_format(total));
                } else {
                    $("#totalesreten tbody").append(
                        '<tr id="retentotal'+idRet+'">' +
                        '<input type="hidden" id="retentotalmonto'+idRet+'" value="'+total+'">' +
                        '<td width="40%" style="font-size: 0.8em;">'+nombre+'</td>' +
                        '<td id="retentotalvalue'+idRet+'">-'+$('#simbolo').val()+' '+number_format(total) +
                        '</td></tr>'
                    );
                }
            }
        });
        var subsub = subtotal-totalRetenciones-$('#totalDescuento').val();
        var totalC = parseFloat(subsub) + parseFloat($('#totalImpuestos').val());
        $('#subsub').html(number_format(subsub));
        $('#total').html(number_format(totalC));
    }

    $(document).ready(function(){
        $('.selectpicker').selectpicker();

        $('#table-form').on('keyup','.calcularLinea',function () {
            if($(this)[0].tagName!='DIV'){
                calcularLinea($(this).attr('cont'));
            }
        });
        $('#table-form').on('change','.rellenar',function () {
            var value = $(this).val();
            if($(this)[0].tagName!='DIV'){
                rellenar2($(this).attr('cont'),value);
            }
        });
        $('#table-form').on('click','.eliminar2',function () {
            eliminar2($(this).attr('cont'));
        });
        $('#table-retencion').on('click','.eliminar3',function () {
            eliminar3($(this).attr('cont'));
        });
        $('#table-retencion').on('change','.calcularRetencion',function () {
            if($(this)[0].tagName!='DIV'){
                calcularRetencion($(this).attr('cont'));
            }
        });
    });
</script>

<!-- endinject -->
<!-- Plugin js for this page-->
<!-- End plugin js for this page-->
<!-- inject:js -->
<script src="{{asset('js/off-canvas.js')}}"></script>
<script src="{{asset('js/misc.js')}}"></script>

<script type="text/javascript" src="{{asset('vendors/DataTables/datatables.min.js')}}"></script>

<script type="text/javascript" src="{{asset('vendors/bootstrap-selectpicker/js/bootstrap-select.min.js')}}"></script>
<!-- Custom js for this page-->
<script type="text/javascript" src="{{asset('vendors/validation/jquery.validate.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/jquery.mask.min.js')}}"></script>
<script type="text/javascript" src="{{asset('vendors/sweetalert2/sweetalert2.min.js')}}"></script>


<!-- Light Gallery Plugin Js -->
<script src="{{asset('vendors/light-gallery/js/lightgallery-all.js')}}"></script>
<!-- endinject -->
<script src="{{asset('js/moment.js')}}"></script>
<script src="{{asset('js/function.js')}}"></script>
<script src="{{asset('js/custom.js')}}"></script>


