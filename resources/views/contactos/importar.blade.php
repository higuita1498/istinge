@extends('layouts.app')	

@section('content')
	<div class="card-body">
        <p>Esta opción permite crear nuevos contactos y/o modificar por el nro de identificación. Puedes importar hasta 500 registros por cada archivo.</p>
        <h4>Tome en cuenta las siguientes reglas para cargar la data</h4>
        <ul>
            <li class="ml-3">Verifique que el orden de las columnas en su documento sea correcto. <small>Si no lo conoce haga clic <a href="{{ route('contactos.ejemplo') }}"><b>aqui</b></a> para descargar archivo Excel de ejemplo.</small></li>
            <li class="ml-3">Verifique que el comienzo de la data sea a partir de la fila 4.</li>
            <li class="ml-3">Los campos obligatorios son <b>Nombres, Apellido1, Tipo de Identificación, Identificación, Celular, Email, Tipo de Contacto.</b></li>
            <li class="ml-3">Las columna como identificación y celular debe ser de tipo numérica, no debe tener puntos o comas.</li>
            <li class="ml-3">En la columnas de celular <b>NO</b> agregar el prefijo telefónico del país {{Auth::user()->empresa()->codigo}}.</li>
            <li class="ml-3">Los tipos de identificación disponibles son los siguientes:
                <div class="col-md-8 my-2">
                    <div class="table-responsive">
                        <table class="table table-striped importar text-center" style="border: solid 2px {{Auth::user()->empresa()->color}} !important;">
                            <thead><tr style="background-color: {{Auth::user()->empresa()->color}} !important; color: #fff;"><th>Versión Corta</th><th>Versión Media</th><th>Versión Larga</th></tr></thead>
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
                <p class="nomargin">Si no se cumple los indicado se guardara el tipo de identificación por el primer valor de la tabla.</p>
            </li>
            <li class="ml-3">Los tipos de contactos son <b>Cliente, Proveedor, Cliente/Proveedor</b>. <br> Si escribe un valor que no sean los indicados el sistema por defecto creara o modificara el contacto por <b>Cliente/Proveedor</b>.</li>
            <li class="ml-3">No debe dejar linea por medio entre registros.</li>
            <li class="ml-3">El sistema comprobara si nro de identificación esta registrado, de ser asi modificara el registro con los nuevos valores del documento Excel que se cargue.</li>
            <li class="ml-3">El archivo debe ser extensión <b>.xlsx</b></li>
        </ul>

        <form method="POST" action="{{ route('contactos.importar') }}" role="form" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="form-group col-md-6 offset-md-3">
                    <label class="control-label">Archivo <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" name="archivo" required="" accept=".xlsx, .XLSX">
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