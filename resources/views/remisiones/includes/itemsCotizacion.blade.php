@php $cont=0; @endphp

@foreach($itemsCotizacion as $itemC)
    @php $cont++ @endphp

    <tr id="{{$cont}}">
        <td class="no-padding">

        </td>
        <td  class="no-padding" >
            <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item{{$cont}}" onchange="rellenar({{$cont}},this.value);"  onload="alert('ok')" required="">
                @foreach($inventario as $item)
                    <option value="{{$item->id}}"  {{$itemC->producto==$item->id?'selected':''}}>{{$item->producto}} - ({{$item->ref}})</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" id="ref{{$cont}}" name="ref[]" placeholder="Referencia" required="" value="{{$itemC->ref}}">
        </td>
        <td class="monetario">
            <input type="number" class="form-control form-control-sm" id="precio{{$cont}}" name="precio[]" placeholder="Precio Unitario" onkeyup="total({{$cont}})" required="" maxlength="24" min="0" value="{{$itemC->precio}}">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm nro" id="desc{{$cont}}" name="desc[]" placeholder="%"  value="{{$item->desc}}" onkeyup="total({{$cont}})" >
        </td>
        <td>
            <select class="form-control form-control-sm selectpicker" name="impuesto[]" id="impuesto{{$cont}}" title="Impuesto" onchange="totalall();" required="">
                @foreach($impuestos as $impuesto)
                    <option value="{{$impuesto->id}}" porc="{{$impuesto->porcentaje}}"   {{$itemC->id_impuesto==$impuesto->id?'selected':''}}>{{$impuesto->nombre}} - {{$impuesto->porcentaje}}%</option>
                @endforeach
            </select>
        </td>
        <td  style="padding-top: 1% !important;">
            <textarea  class="form-control form-control-sm" id="descripcion{{$cont}}" name="descripcion[]"  value="" placeholder="Descripción" ></textarea>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" id="cant{{$cont}}" name="cant[]"  value="{{$itemC->cant}}" placeholder="Cantidad" onchange="total({{$cont}});" min="1"  >
            <p class="text-danger nomargin" id="pcant{{$cont}}"></p>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm text-right" id="total{{$cont}}" value="{{App\Funcion::Parsear($itemC->total())}}" disabled="" >
        </td>
        <td>
            <button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar(1);">X</button>
        </td>
    </tr>
@endforeach
