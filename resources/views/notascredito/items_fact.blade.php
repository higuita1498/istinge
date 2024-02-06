
<div class="row">
    <div class="col-md-12">

<table class="table table-striped table-hover" id="table-form" >
    <thead class="thead-dark">
    <tr>
        <th width="5%"></th>
        <th width="24%">Ítem</th>
        <th width="10%">Referencia</th>
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
            <td class="no-padding">
            </td>
            <td  class="no-padding">
                <div class="resp-item">
                    <input type="hidden" name="item[]" value="{{$item->producto}}">
                    <input type="text" class="form-control form-control-sm" disabled value="{{$item->nombre}} - ({{$item->ref}})">
                </div>
            </td>
            <td>
                <div class="resp-refer">
                    <input type="text" class="form-control form-control-sm" id="ref{{$cont}}" name="ref[]" placeholder="Referencia" required disabled value="{{$item->ref}}">
                </div>
            </td>
            <td class="monetario">
                <div class="resp-precio">
                    <input type="number" class="form-control form-control-sm calcularLinea" cont="{{$cont}}" id="precio{{$cont}}" name="precio[]" placeholder="Precio Unitario" required maxlength="24" min="0" value="{{$item->precio}}">
                </div>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm nro calcularLinea " cont="{{$cont}}" id="desc{{$cont}}" name="desc[]" placeholder="%" value="{{$item->desc}}" disabled>
            </td>
            <td>
                <select class="form-control form-control-sm selectpicker calcularLinea impuestos" cont="{{$cont}}" name="impuesto[]" id="impuesto{{$cont}}" title="Impuesto" required disabled>
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
                <input type="number" class="form-control form-control-sm calcularLinea" cont="{{$cont}}" id="cant{{$cont}}" name="cant[]" placeholder="Cantidad" min="1"  max="{{$item->cant}}" required value="{{$item->cant}}">
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
        <div class="alert alert-danger" style="display: none;" id="error-items"></div>
    </div>
</div>
<button class="btn btn-outline-primary" onclick="createRow();" type="button" style="margin-top: 5%">Agregar línea</button>


<!-- Retenciones -->
<div class="row"  style="margin-top: 10%; display: none;">
    <div class="col-md-7 no-padding">
        <h5>RETENCIONES</h5>
        <table class="table table-striped table-sm" id="table-retencion">
            <thead class="thead-dark">
            <th width="60%">Tipo de Retención</th>
            <th width="34%">Valor</th>
            <th width="5%"></th>
            </thead>
            <tbody>
            @php $cont=0; @endphp
            @foreach($retencionesFacturas as $retencion)
                @php $cont+=1; @endphp
                <tr id="reten{{$cont}}">
                    <td  class="no-padding">
                        <select class="form-control form-control-sm selectpicker no-padding calcularRetencion" cont="{{$cont}}" title="Seleccione" data-live-search="true" data-size="5" name="retencion[]" id="retencion{{$cont}}" required  >
                            @foreach($retenciones as $reten)
                                <option value="{{$reten->id}}" tipo="{{$reten->tipo}}"  porc="{{$reten->porcentaje}}" {{$retencion->id_retencion==$reten->id?'selected':''}}>{{$reten->nombre}} - {{$reten->porcentaje}}%</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="monetario">
                        <input type="hidden" value="0" id="lock_reten{{$cont}}">
                        <input type="number" value="{{$retencion->valor}}" required style="display: inline-block; width: 80%;"
                               class="form-control form-control-sm" maxlength="24" onkeyup="total_categorias()"
                               id="precio_reten{{$cont}}" name="precio_reten[]" placeholder="Valor retenido"
                               onkeyup="total_linea({{$cont}})" required min="0" disabled>
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-secondary btn-icons eliminar3" cont="{{$cont}}">X</button></td>
                </tr>
            @endforeach
            </tbody>
            </tbody>
        </table>
        <button class="btn btn-outline-primary" onclick="CrearFilaRetencion2();" type="button" style="margin-top: 2%;">Agregar Retención</button><a><i data-tippy-content="Agrega nuevas retenciones haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a>
    </div>
</div>


<div class="row" style="margin-top: 10%;">
    <div class="col-md-4 offset-md-8">
        <table class="text-right widthtotal" >
            <tr>
                <td width="40%">Subtotal</td>
                <input type="hidden" id="subtotal_hidden" value="{{$factura->total()->subtotal - $factura->total()->descuento}}">
                <input type="hidden" id="subtotal_tmp" value="0">
                <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal">{{App\Funcion::Parsear($factura->total()->subtotal)}}</span></td>
            </tr>
            <tr>
                <input type="hidden" id="totalDescuento" value="{{$factura->total()->descuento}}">
                <td>Descuento</td><td id="descuento">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->descuento)}}</td>
            </tr>



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
        function CrearFilaRetencion2(){

            var nro=$('#table-retencion tbody tr').length +1 ;
            if ($('#reten'+nro).length > 0) {
                for (i = 1; i <= nro; i++) {
                    if ($('#reten'+i).length == 0) {
                        nro=i;
                        break;
                    }
                }
            }
            $('#table-retencion tbody').append(
                '<tr  id="reten'+nro+'">' +
                '<td  class="no-padding">' +
                '<select class="form-control form-control-sm selectpicker no-padding calcularRetencion" cont="'+nro+'" title="Seleccione" data-live-search="true" data-size="5" name="retencion[]" id="retencion'+nro+'" required>' +
                '</select>' +
                '</td>' +
                '<td class="monetario">' +
                '<input type="hidden" value="0" id="lock_reten'+nro+'">' +
                '<input type="number" required style="display: inline-block; width: 80%;" class="form-control form-control-sm" maxlength="24" id="precio_reten'+nro+'" name="precio_reten[]" placeholder="Valor retenido" min="0">' +
                '</td>' +
                '<td><button type="button" class="btn btn-outline-secondary btn-icons eliminar3" cont="'+nro+'">X</button></td></tr>'
            );
            var retenciones = JSON.parse($('#retenciones').val());

            var opciones = '';
            $.each( retenciones, function( key, value ){
                opciones = opciones + '<option value="'+value.id+'" tipo="'+value.tipo+'" porc="'+value.porcentaje+'">'+value.nombre+' - '+ value.porcentaje+'%'+'</option>';
            });

            $('#retencion'+nro).append(opciones);
            $('#retencion'+nro).selectpicker('refresh');

            $('.precio').mask('0000000000.00', {reverse: true});
            $("#precio_reten"+nro).attr("disabled", "disabled");
        }

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

        function createRow2() {
            $('#error-items').hide();
            var nro=$('#table-form tbody tr').length +1 ;
            if ($('#'+nro).length > 0) {
                for (i = 1; i <= nro; i++) {
                    if ($('#'+i).length == 0) {
                        nro=i;
                        break;
                    }
                }
            }
            var factura=true;
            var ref=true;
            if ($('#cotizacion_si').length > 0) {
                factura=false;
            }
            if ($('#orden_si').length > 0) {
                ref=false;
            }
            var datos='<tr  id="'+nro+'">' ;
            if (factura) {
                datos+='<td class="no-padding"><a class="btn btn-outline-secondary btn-icons" title="Actualizar" onclick="inventario('+nro+');"><i class="fas fa-sync"></i></a></td>';
            }
            datos+= '<td class="no-padding"><select required class="form-control form-control-sm rellenar selectpicker no-padding" cont="'+nro+'" title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item'+nro+'">' +
                '</select >';
            if (factura) {
                datos+= '<p style="text-align:left; margin: 0;">' +
                    '<a href="'+$('#url').val()+'/empresa/inventario/create" target="_blanck"><i class="fas fa-plus"></i> Nuevo Producto</a></p>';
            }

            if (ref) {
                datos+='<input type="hidden" name="camposextra[]" value="'+nro+'"></td>' +
                    '<td ><input type="text" class="form-control form-control-sm" id="ref'+nro+'" name="ref[]" placeholder="Referencia" required></td>';
            }
            datos+='<td class="monetario">' +
                '<input type="number" class="form-control form-control-sm calcularLinea" cont="'+nro+'" id="precio'+nro+'" maxlength="24" min="0" name="precio[]" placeholder="Precio Unitario" required>' +
                '</td>' +
                '<td>' +
                '<input type="text" class="form-control form-control-sm nro calcularLinea" cont="'+nro+'" id="desc'+nro+'" name="desc[]" maxlength="15" placeholder="%" >' +
                '</td>' +
                '<td>' +
                '<select class="form-control form-control-sm selectpicker calcularLinea impuestos" cont="'+nro+'" name="impuesto[]" id="impuesto'+nro+'" title="Impuesto" required>' +
                '</select>' +
                '</td>' +
                '<td  style="padding-top: 1% !important;">' +
                '<textarea  class="form-control form-control-sm" id="descripcion'+nro+'" name="descripcion[]" placeholder="Descripción"></textarea>' +
                '</td>' +
                '<td>' +
                '<input type="number" class="form-control form-control-sm calcularLinea" cont="'+nro+'" id="cant'+nro+'" name="cant[]" placeholder="Cantidad"  maxlength="24" min="1" required>' +
                '<p class="text-danger nomargin" id="pcant'+nro+'"></p></td>' +
                '<td>' +
                '<input type="text" class="form-control form-control-sm text-right" id="total'+nro+'" value="0" disabled=""></td>' +
                '<td><button type="button" cont="'+nro+'" class="btn btn-outline-secondary btn-icons eliminar2">X</button></td>' +
                '</tr>';

            $('#table-form tbody').append( datos);
            var impuestos = JSON.parse($('#impuestos').val());
            var opciones = '';
            $.each( impuestos, function( key, value ){
                opciones = opciones + '<option value="'+value.id+'" porc="'+value.porcentaje+'">'+value.nombre+'-'+ value.porcentaje+'%'+'</option>';
            });

            $('#impuesto'+nro).append(opciones);

            var obj = JSON.parse($('#allproductos').val());
            var optios='';
            if ($('#orden_si').length > 0) {
                optios+="<optgroup  label='Ítems inventariables'>";
            }

            $.each( obj, function( key, value ){
                optios+="<option  value='"+value.id+"'>"+value.producto+" </option>";
            });

            if ($('#orden_si').length > 0) {
                optios+=" </optgroup>";
                optios+=$('#allcategorias').val();

            }

            $('#item'+nro).append(optios);

            $('.precio').mask('0000000000.00', {reverse: true});
            $('.nro').mask('000');
            $('#item'+nro).selectpicker();
            $('#impuesto'+nro).selectpicker();

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
            calcularRetencion();

            var tmp = parseFloat(cant) *parseFloat(precio) ;
            var total = tmp-((tmp * desc)/100)  ;
            $('#total'+cont).val(number_format(total));

        }

        function calcularImpuestos() {
            var subtotal = 0;
            var subtotal_tmp = $('#subtotal_hidden').val();
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
                        if(desc>0){
                            impuesto = subtotal_tmp*(porc/100);
                        }else{
                            impuesto = cant*(precio*(porc/100));
                        }
                    }
                    create_imp2(impuesto, idImp, nombre);
                    subtotal = parseFloat(subtotal) + parseFloat(cant*precio);
                    totalImpuestos = parseFloat(totalImpuestos) + parseFloat(impuesto);
                    totalDescuento = parseFloat(totalDescuento) + parseFloat(desc);

                }
            });

            $('#subtotal').html(number_format(subtotal));

            if(totalDescuento > 0){
                $('#subtotal_hidden').val(subtotal-totalDescuento);
            }else{
                $('#subtotal_hidden').val(subtotal);
            }
            if(subtotal_tmp>0){

            }

            $('#subtotal_tmp').val(subtotal-totalDescuento);
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

            var tipo = $('#retencion'+id+' option:selected').attr("tipo");
            var porc = $('#retencion'+id+' option:selected').attr("porc");
            var total = 0;

            if(tipo!=1){

                total = (porc/100)*subtotal;
            }else{
                var tmp = subtotal * (19/100);
                total = (tmp * porc)/100;
            }

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
            var subsub = subtotal-totalRetenciones;
            var totalC = parseFloat(subsub) + parseFloat($('#totalImpuestos').val());
            $('#subsub').html(number_format(subsub));
            $('#total').html(number_format(totalC));
        }

        $(document).ready(function(){
            $('.selectpicker').selectpicker();


            $('#table-form').on('change','.calcularLinea',function () {
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
