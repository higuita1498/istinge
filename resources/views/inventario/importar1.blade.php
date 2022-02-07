@extends('layouts.app')	
@section('content')
	<div class="card-body">
	<p>Usa esta opción permite crear nuevos items. Puedes importar hasta 500 por cada archivo.</p>
  	<h4>Tome en cuenta las siguientes reglas para cargar la datas</h4>
  	<ul>
  		<li>Verifique que el orden de las columnas en su documento sea correcto. <small>Si no lo conoce entre <a href="{{ route('inventario.ejemplo') }}">aqui</a> para descargar el ejemplo</small></li>
  		<li>Verifique que el comienzo de la data sea a partir de la fila 4</li>
  		<li>Los campos obligatorios son <b> Nombre del Producto, Referencia, Categoria, Precio de Venta</b></li>
    	<li>Compruebe que su categoria con su registro de <a href="{{route('categorias.index')}}" target="_blank">categorias</a>, ya que si no la consigue en el sistema, colocara por defecto en activos</li>
      <li>Si insertará items inventariables, asegurese de haber registrado una <a href="{{route('bodegas.index')}}" target="_blank">bodega</a> activa para tomarla por defecto. Usará la primera registrada</li>
    	<li>No debe dejar linea por medio entre registros</li>
  		<li>El archivo debe ser extensión .xlsx</li>
  	</ul> 
    @if(Session::has('success'))
      <div class="alert alert-success" >
        {{Session::get('success')}}
      </div>
    @endif 
  	<form method="POST" action="#" role="form" enctype="multipart/form-data">
  		{{ csrf_field() }}
      @if(Auth::user()->empresa()->carrito==1)
      <div class="row" >
        <div class="col-md-12">
          <div class="form-group row">
          <label for="publico" class="col-md-3 col-form-label">¿Estara el producto público en la web?</label>
            <div class="col-md-2">
              <div class="row">
              <div class="col-sm-6">
              <div class="form-radio">
                <label class="form-check-label">
                <input type="radio" class="form-check-input" name="publico" id="publico1" value="1" > Si
                <i class="input-helper"></i></label>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-radio">
                <label class="form-check-label">
                <input type="radio" class="form-check-input" name="publico" id="publico" value="0" checked=""> No
                <i class="input-helper"></i></label>
              </div>
            </div>
            </div>
            </div>        
          </div>
        </div>
      </div>
    @endif
  		<div class="row">
  			<div class="form-group col-md-12">
	  			<label class="control-label">Archivo <span class="text-danger">*</span></label>
				<input type="file" class="form-control" name="archivo" required="">
				<span class="help-block">
		        	<strong>{{ $errors->first('archivo') }}</strong>
		        </span>
  			</div>      
  		</div>

    
		<div class="row">

      <div class="col-md-12">
        @if(count($errors) > 0)
        <div class="alert alert-danger">
          <ul>
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
          </ul>
        </div>
      @endif 
      </div>
		</div>

  		<hr>
  		<div class="row">
  			<div class="col-md-12 text-right">
				<a href="{{route('inventario.index')}}" class="btn btn-outline-light" >Cancelar</a>
  				<button type="submit" class="btn btn-success">Guardar</button>
  			</div>
  		</div>
  		
  	</form>

  </div>
@endsection