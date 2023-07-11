@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('bancos.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-banco" >
   {{ csrf_field() }}
  <div class="row">
    <div class="col-md-4 form-group">
      <label class="control-label">Cliente<span class="text-danger">*</span></label>
      
      <select class="form-control selectpicker" name="cliente" id="cliente" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="showfacturas(this.value);">
        @foreach($clientes as $client)
          <option {{old('cliente')==$client->id?'selected':''}} value="{{$client->id}}">{{$client->nombre}}</option>
        @endforeach
      </select>
      <span class="help-block error">
        <strong>{{ $errors->first('cliente') }}</strong>
      </span>
    </div>

    <div class="col-md-4 form-group">
      <label class="control-label">Factura <span class="text-danger">*</span> <a><i data-tippy-content="Factura asociada al cliente" class="icono far fa-question-circle"></i></a></label>
      <select class="form-control selectpicker" name="factura" id="select_factura" required="" title="Seleccione" data-size="5" onchange="showitemsfactura(this.value);">
      </select>
    </div>

    <div class="col-md-4 form-group">
      <label class="control-label">Número de Guía <a><i data-tippy-content="Número escogido automaticamente" class="icono far fa-question-circle"></i></a></label>
      <input type="text" class="form-control"  id="nro_cta" name="nro_cta"  value="{{rand(11111, 99999)}}" disabled="">
    </div>

  </div>
  	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
			</div>
  <div class="row">
    <div class="col-sm-12 form-group fact-table">
        <label>Items de la factura a despachar</label>
        <table class="table table-striped table-sm" width="100%" id="items-factura-envio">
          <thead class="thead-dark">
            <tr>
              <th width="24%">Ítem</th>
              <th width="10%">Referencia</th>
              <th width="13%">Descripción</th>
              <th width="7%">Cantidad en envio</th>
              <th width="2%"></th>
            </tr>
          </thead>
          <tbody>
            
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" class="text-right">Total Productos</td>
              <td id="cantidadtotal">0</td>
            </tr>
          </tfoot>
        </table>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4 form-group">
      <label class="control-label">Foto de producto en su caja <span class="text-danger">*</span></label>
      <input type="file" class="form-control"  id="foto" name="foto" required="" >
      <span class="help-block error">
        <strong>{{ $errors->first('foto') }}</strong>
      </span>
    </div>
    <div class="col-md-8 form-group">
      <label class="control-label">Dirección <span class="text-danger">*</span></label>
      <textarea  class="form-control form-control-sm" name="direccion" id="direccion">{{old('direccion')}}</textarea>
    </div>
  </div>
  <div class="row">

    <div class="col-md-4 form-group">
      <label class="control-label">Nombre Receptor <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="receptor" name="receptor" required="" >
      <span class="help-block error">
        <strong>{{ $errors->first('receptor') }}</strong>
      </span>
    </div>
    <div class="col-md-4 form-group">
      <label class="control-label">Documento de Identidad<span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="nitreceptor" name="nitreceptor" required="" >
      <span class="help-block error">
        <strong>{{ $errors->first('nitreceptor') }}</strong>
      </span>
    </div>
    </div>
  <div class="row">
    <div class="col-md-4 form-group">
      <label class="control-label">Empresa de Envio</label>
      <textarea  class="form-control form-control-sm" name="empresa">{{old('empresa')}}</textarea>
    </div>
    <div class="col-md-4 form-group">
      <label class="control-label">Nro de guía de la Empresa de Envio</label>
      <textarea  class="form-control form-control-sm" name="guia_empresa">{{old('guia_empresa')}}</textarea>
    </div>
    <div class="col-md-4 form-group">
      <label class="control-label">Documento de la empresa envio </label>
      <input type="file" class="form-control"  id="guia_foto_empresa" name="guia_foto_empresa" required="" >
      <span class="help-block error">
        <strong>{{ $errors->first('foto') }}</strong>
      </span>
    </div>
  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('bancos.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar</button>
    </div>
	</div>
</form>
  <input type="hidden" id="url" value="{{url('/')}}">
@endsection 