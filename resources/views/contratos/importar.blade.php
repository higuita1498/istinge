@extends('layouts.app')

@section('content')
	<div class="card-body">
        <p>Esta opción permite crear nuevos contratos y/o modificarlos por el nro de identificación que posea el cliente que ya se encuentre registrado en el sistema.</p>
        <h4>Tome en cuenta las siguientes reglas para cargar la data</h4>
        <ul>
            <form action="{{ route('contratos.ejemplo') }}" method="post">
                <label for="conexion">Selecciona tipo de conexión para descargar archivo Excel de ejemplo.:</label>
                <select name="conexion" id="conexion">
                    <option value="1">PPPoE</option>
                    <option value="0">IP Estática</option>
                </select>
                <br><br>
                <input type="submit" value="Enviar">
            </form>

            {{-- <li class="ml-3">Verifique que el orden de las columnas en su documento sea correcto. <small>Si no lo conoce haga clic <a href="{{ route('contratos.ejemplo') }}"><b>aqui</b></a> para descargar archivo Excel de ejemplo.</small></li> --}}
            <li class="ml-3">Verifique que el comienzo de la data sea a partir de la fila 4.</li>
            <li class="ml-3">Los campos obligatorios son <b>Identificacion, Servicio, Mikrotik, Plan, Estado, IP, Conexion, Interfaz, Segmento, Grupo de Corte, Facturacion, Tecnologia</b>.</li>

            <li class="ml-3">Las mikrotik disponibles son los siguientes:
                <div class="col-md-6 my-2">
                    <div class="table-responsive">
                        <table class="table table-striped importar text-center" style="border: solid 2px {{Auth::user()->empresa()->color}} !important;">
                            <thead><tr style="background-color: {{Auth::user()->empresa()->color}} !important; color: #fff;"><th>Mikrotik</th></tr></thead>
                            <tbody>
                                @foreach($mikrotiks as $mikrotik)
                                <tr>
                                    <td>{{$mikrotik->nombre}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </li>

            <li class="ml-3">Los planes de velocidad disponibles son los siguientes:
                <div class="col-md-6 my-2">
                    <div class="table-responsive">
                        <table class="table table-striped importar text-center" style="border: solid 2px {{Auth::user()->empresa()->color}} !important;">
                            <thead><tr style="background-color: {{Auth::user()->empresa()->color}} !important; color: #fff;"><th>Planes</th></tr></thead>
                            <tbody>
                                @foreach($planes as $plan)
                                <tr>
                                    <td>{{$plan->name}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </li>

            <li class="ml-3">Los estados disponibles son los siguientes:
                <div class="col-md-6 my-2">
                    <div class="table-responsive">
                        <table class="table table-striped importar text-center" style="border: solid 2px {{Auth::user()->empresa()->color}} !important;">
                            <thead><tr style="background-color: {{Auth::user()->empresa()->color}} !important; color: #fff;"><th>Estados</th></tr></thead>
                            <tbody><tr><td>Habilitado</td></tr><tr><td>Deshabilitado</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </li>

            <li class="ml-3">Los tipos de conexion disponibles son los siguientes:
                <div class="col-md-6 my-2">
                    <div class="table-responsive">
                        <table class="table table-striped importar text-center" style="border: solid 2px {{Auth::user()->empresa()->color}} !important;">
                            <thead><tr style="background-color: {{Auth::user()->empresa()->color}} !important; color: #fff;"><th>Conexion</th></tr></thead>
                            <tbody><tr><td>IP Estatica</td></tr><tr><td>PPPOE</td></tr><tr><td>DHCP</td></tr><tr><td>VLAN</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </li>

            <li class="ml-3">Los grupos de corte disponibles son los siguientes:
                <div class="col-md-6 my-2">
                    <div class="table-responsive">
                        <table class="table table-striped importar text-center" style="border: solid 2px {{Auth::user()->empresa()->color}} !important;">
                            <thead><tr style="background-color: {{Auth::user()->empresa()->color}} !important; color: #fff;"><th>Grupos de Corte</th></tr></thead>
                            <tbody>
                                @foreach($grupos as $grupo)
                                <tr>
                                    <td>{{$grupo->nombre}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </li>

            <li class="ml-3">Los tipos de facturación disponibles son los siguientes:
                <div class="col-md-6 my-2">
                    <div class="table-responsive">
                        <table class="table table-striped importar text-center" style="border: solid 2px {{Auth::user()->empresa()->color}} !important;">
                            <thead><tr style="background-color: {{Auth::user()->empresa()->color}} !important; color: #fff;"><th>Facturacion</th></tr></thead>
                            <tbody><tr><td>Estandar</td></tr><tr><td>Electronica</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </li>

            <li class="ml-3">Los tipos de tecnología disponibles son los siguientes:
                <div class="col-md-6 my-2">
                    <div class="table-responsive">
                        <table class="table table-striped importar text-center" style="border: solid 2px {{Auth::user()->empresa()->color}} !important;">
                            <thead><tr style="background-color: {{Auth::user()->empresa()->color}} !important; color: #fff;"><th>Tecnología</th></tr></thead>
                            <tbody><tr><td>Fibra</td></tr><tr><td>Inalambrico</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </li>

            <li class="ml-3">No debe dejar linea por medio entre registros.</li>
            <li class="ml-3">El sistema comprobará si nro de identificación está registrado, de ser asi modificara el registro con los nuevos valores del documento Excel que se cargue.</li>
            <li class="ml-3">El archivo debe ser extensión <b>.xlsx</b></li>
        </ul>

        <form method="POST" action="{{ route('contratos.importar_cargando') }}" role="form" enctype="multipart/form-data">
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
                    <a href="{{route('contratos.index')}}" class="btn btn-outline-light" >Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </div>
        </form>
    </div>
@endsection
