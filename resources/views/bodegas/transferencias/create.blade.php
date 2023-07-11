@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('transferencia.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-transferencia" >
   {{ csrf_field() }}
  <div class="row">

    <div class="col-md-4 offset-md-1">
      <div class="row form-group">
        <label class="control-label">Bodega de Origen<span class="text-danger">*</span></label>
        <select class="form-control selectpicker no-padding"  title="Seleccione" name="bodega_origen" id="bodega_origen" required="">
          @foreach($bodegas as $bob)
            <option value="{{$bob->id}}" {{$bob->id==old('bodega_origen')?'selected':''}}  >{{$bob->bodega}}</option>
          @endforeach
        </select>
        <label id="bodega_origen-error" class="error" for="bodega_origen"></label>

      </div>

      <div class="row form-group">
        <label class="control-label">Fecha<span class="text-danger">*</span></label>
        <input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y')}}" name="fecha" disabled=""  >
      </div>
    </div>
    <div class="col-md-4 offset-md-2">
      <div class="row form-group">
        <label class="control-label">Bodega de Destino<span class="text-danger">*</span></label>
        <select class="form-control selectpicker no-padding"  title="Seleccione" name="bodega_destino" id="bodega_destino" required="">
          @foreach($bodegas as $bob)
            <option value="{{$bob->id}}" {{$bob->id==old('bodega_destino')?'selected':''}}  >{{$bob->bodega}}</option>
          @endforeach
        </select>
        <label id="bodega_destino-error" class="error" for="bodega_destino"></label>
      </div>
      <div class="row form-group">
        <label class="control-label">Observaciones</label>
        <textarea class="form-control" name="observaciones" >{{old('observaciones')}}</textarea>
      </div>
      
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <table class="table table-striped table-sm" width="100%" id="table_form_transferencia">
        <thead class="thead-dark">
          <tr>
            <th width="30%">Ítem</th>
            <th width="10%" class="text-center">Referencia</th>
            <th width="10%" class="text-center">Cantidad Inicial</th>
            <th width="15%" class="text-center">Cantidad Total</th>
            <th width="20%" class="text-center">Cantidad a transferir</th>
            <th width="20%" class="text-center">Cantidad total en <br> <span id="nombre_bodega">bodega de origen</span></th>
            <th width="5%"></th>
          </tr>
        </thead>
        <tbody>
          <tr id="1">
            <td>
              <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item1" required="" onchange="ritemtrans(1, this.value);"></select>
              <label id="item1-error" class="error" for="item1"></label>
            </td>
            <td id="ref1"></td>
            <td id="cantI1"></td>
            <td id="cantT1"></td>
            <td><input type="number" class="form-control form-control-sm" id="cant1" name="cant[]" placeholder="Cantidad" onchange="total_transferir(1);" min="0" required=""></td>
            <td id="cantTB1"></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
    <button class="btn btn-outline-primary" onclick="createRowTransferencia();" type="button" style="margin-top: 5%">Agregar línea</button>
  </div>

  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('transferencia.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success" id="boton_guardar">Guardar</button>
    </div>
	</div>
</form>
<input type="hidden" id="json_inventario" value="[]">
<input type="hidden" id="url" value="{{url('/')}}">
@endsection