@extends('layouts.app')	
@section('content')
	<div class="card-body">
	<p>Usa esta opción permite crear nuevos contactos y/o modificar por el nro de Identificación. Puedes importar hasta 500 por cada archivo.
</p>
  	<h4>Tome en cuenta las siguientes reglas para cargar la data</h4>
  	<ul>
  		<li>Verifique que el orden de las columnas en su documento sea correcto. <small>Si no lo conoce entre <a href="{{ route('contactos.ejemplo') }}">aqui</a> para descargar el ejemplo</small></li>
  		<li>Verifique que el comienzo de la data sea a partir de la fila 4</li>
  		<li>Los campos obligatorios son <b> Tipo de Identificación , Identificación, Nombre, Teléfono, Tipo de Contacto, Tipo de Empresa</b></li>
  		<li>Las columna como Identificación debe ser de tipo numérica, no debe tener puntos o comas</li>
      <li>En las columnas de tipo telefonico pueden agregar el Prefijo Telefónico {{Auth::user()->empresa()->codigo}}</li>
      <li>los tipos de identificación son los siguientes:
        <div class="col-md-8">
          <div class="table-responsive">
            <table class="table table-striped importar">
              <tehead><th>Versión Corta</th><th>Versión Media</th><th>Versión Larga</th></tehead>
              <tbody>
                @foreach($identificaciones as $identificacion)
                  <tr>
                    <td>{{$identificacion->mini()}}</td>
                    <td>{{$identificacion->media()}}</td>
                    <td>{{$identificacion->identificacion}}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        <p class="nomargin">Si no se cumple los indicado se guardara el tipo de identificación por el primer valor de la tabla</p>
      </li>
  		<li>Los tipos de contactos son Cliente, Proveedor, Cliente/Proveedor. <br> Si escribe un valor que no sean los indicados el sistema por defecto creara o modificara el contacto por Cliente/Proveedor</li>
    	<li>Compruebe que tipo de empresa coincida con su registro de <a href="{{route('tiposempresa.index')}}" target="_blank">tipos de empresa</a>, ya que si no la consigue en el sistema, creara un nuevo registro de tipo de empresa</li>
    	<li>No debe dejar linea por medio entre registros</li>
      <li>El sistema comprobara si nro de Identificación esta registrado, de ser asi modificara el registro con los nuevos valores del Documento</li>
  		<li>El archivo debe ser extensión .xlsx</li>
  	</ul>
  	<form method="POST" action="{{ route('contactos.importar') }}" role="form" enctype="multipart/form-data">
  		{{ csrf_field() }}
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
				<a href="{{route('contactos.index')}}" class="btn btn-outline-light" >Cancelar</a>
  				<button type="submit" class="btn btn-success">Guardar</button>
  			</div>
  		</div>
  		
  	</form>

  </div>
@endsection