@extends('layouts.app')

@section('style')
<style>
    .stopwatch .controls {
        font-size: 12px;
    }
    .stopwatch .controls button{
        padding: 5px 15px;
        background :#EEE;
        border: 3px solid #06C;
        border-radius: 5px
    }
    .stopwatch .time {
        font-size: 2em;
    }
    .bg-th {
        background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
        color: #fff!important;
    }
    .table .thead-dark th {
        color: #fff;
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
        border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
    }
    .btn-dark {
	    background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
	    border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
	}
	.btn-dark:hover, .btn-dark:active {
	    background-color: #113951;
	    border-color: #113951;
	}
    .nav-tabs .nav-link {
        font-size: 1em;
    }
    .nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
        color: #fff!important;
    }
    .table .thead-light th {
        color: #fff!important;
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
        border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    }
    .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
        color: #fff!important;
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    }
    .nav-pills .nav-link {
        font-weight: 700!important;
    }
    .nav-pills .nav-link{
        color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
        background-color: #f9f9f9!important;
        margin: 2px;
        border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
        transition: 0.4s;
    }
    .nav-pills .nav-link:hover {
        color: #fff!important;
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    }
</style>
@endsection

@section('boton')
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
            <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
        </div>
    @else
    @if(isset($_SESSION['permisos']['5']))
        <a href="{{route('contactos.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Cliente</a>
    @endif
    @if(isset($_SESSION['permisos']['411']))
        <a href="{{route('contratos.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Contrato</a>
    @endif
    @if(isset($_SESSION['permisos']['202']))
        <a href="{{route('radicados.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Radicado</a>
    @endif
    @endif
@endsection

@section('content')

    @if(Session::has('success'))
        <div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
	    {{Session::get('success')}}
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif
    
    @if(Session::has('danger'))
        <div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
	    {{Session::get('danger')}}
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif
    
    <div class="row card-description">
    	<div class="col-md-12">
    		<ul class="nav nav-pills" id="myTab" role="tablist">
    			<li class="nav-item">
    				<a class="nav-link active" id="sin_gestionar-tab" data-toggle="tab" href="#sin_gestionar" role="tab" aria-controls="sin_gestionar" aria-selected="true">SIN GESTIONAR</a>
    			</li>
    			<li class="nav-item">
    				<a class="nav-link" id="gestionados-tab" data-toggle="tab" href="#gestionados" role="tab" aria-controls="gestionados" aria-selected="false">GESTIONADOS</a>
    			</li>
    			<li class="nav-item">
    				<a class="nav-link" id="retirados-tab" data-toggle="tab" href="#retirados" role="tab" aria-controls="retirados" aria-selected="false">RETIRADOS</a>
    			</li>
    			<li class="nav-item">
    				<a class="nav-link" id="retiradosTOTAL-tab" data-toggle="tab" href="#retiradosTOTAL" role="tab" aria-controls="retiradosTOTAL" aria-selected="false">RETIRADOS TOTAL</a>
    			</li>
    		</ul>
    		<hr style="border-top: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}; margin: .5rem 0rem;">
    		<div class="tab-content fact-table" id="myTabContent">
    			<div class="tab-pane fade show active" id="sin_gestionar" role="tabpanel" aria-labelledby="sin_gestionar-tab">
    			    <div class="text-right">
    			        <a href="javascript:getDataTable()" class="btn btn-success btn-sm my-1"><i class="fas fa-sync"></i>Actualizar</a>
    			        <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
    			    </div>
    			    
    			    <div class="container-fluid d-none" id="form-filter">
                    	<div class="card shadow-sm border-0 mb-3" style="background: #ffffff00 !important;">
                    		<div class="card-body py-0">
                    			<div class="row">
                    				<div class="col-md-3 pl-1 pt-1">
                    					<select title="Cliente" class="form-control rounded selectpicker" id="cliente" data-size="5" data-live-search="true">
                							@foreach ($clientes as $cliente)
                								<option value="{{ $cliente->id}}">{{$cliente->nombre}} {{$cliente->apellido1}} {{$cliente->apellido2}} - {{ $cliente->nit}}</option>
                							@endforeach
                						</select>
                    				</div>
                    				<div class="col-md-3 pl-1 pt-1 d-none">
                    					<select title="Gestionado" class="form-control rounded selectpicker" id="created_by" data-size="5" data-live-search="true">
                							@foreach ($usuarios as $usuario)
                								<option value="{{ $usuario->id}}">{{ $usuario->nombres}}</option>
                							@endforeach
                						</select>
                    				</div>
                    				<div class="col-md-3 pl-1 pt-1">
                    					<select title="Servidor" class="form-control rounded selectpicker" id="servidor" data-size="5" data-live-search="true">
                							@foreach ($servidores as $servidor)
                								<option value="{{ $servidor->id}}">{{ $servidor->name}}</option>
                							@endforeach
                						</select>
                    				</div>
                    				<div class="col-md-3 pl-1 pt-1">
                    					<select title="Estado" class="form-control rounded selectpicker" id="estado" data-size="5" data-live-search="true">
                							<option value="A">Sin Gestionar</option>
                							<option value="3">Gestionado/Sin Contestar</option>
                						</select>
                    				</div>
                    				<div class="col-md-2 pl-1 pt-1">
                    					<select title="Corte" class="form-control rounded selectpicker" id="fecha_corte" data-size="5" data-live-search="true">
                							<option value="15">15</option>
                							<option value="30">30</option>
                						</select>
                    				</div>
                    				<div class="col-md-1 pl-1 pt-1 text-left">
                    					<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
                    					<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
                    				</div>
                    			</div>
                    		</div>
                    	</div>
                    </div>
                    
    				<div class="table-responsive mt-3">
    				    <table class="table table-striped table-hover w-100" id="table_sin_gestionar">
    				        <thead class="thead-dark">
    				            <tr>
    				                <th>Nombre</th>
    				                <th class="text-center">Identificación</th>
    				                <th class="text-center">Teléfono</th>
    				                <th class="text-center">Estado</th>
    				                {{--<th>Gestionado por</th>--}}
    				                <th class="text-center">Acciones</th>
    				            </tr>
    				        </thead>
    				    </table>
				    </div>
    			</div>
    			<div class="tab-pane fade" id="gestionados" role="tabpanel" aria-labelledby="gestionados-tab">
    			    <div class="text-right">
    			        <a href="javascript:getDataTableG()" class="btn btn-success btn-sm my-1"><i class="fas fa-sync"></i>Actualizar</a>
    			        <a href="javascript:abrirFiltradorG()" class="btn btn-info btn-sm my-1" id="boton-filtrarG"><i class="fas fa-search"></i>Filtrar</a>
    			    </div>
    			    
    			    <div class="container-fluid d-none" id="form-filterG">
                    	<div class="card shadow-sm border-0 mb-3" style="background: #ffffff00 !important;">
                    		<div class="card-body py-0">
                    			<div class="row">
                    				<div class="col-md-3 pl-1 pt-1">
                    					<select title="Cliente" class="form-control rounded selectpicker" id="clienteG" data-size="5" data-live-search="true">
                							@foreach ($clientes as $cliente)
                								<option value="{{ $cliente->id}}">{{ $cliente->nombre}} - {{ $cliente->nit}}</option>
                							@endforeach
                						</select>
                    				</div>
                    				<div class="col-md-3 pl-1 pt-1">
                    					<select title="Gestionado" class="form-control rounded selectpicker" id="created_byG" data-size="5" data-live-search="true">
                							@foreach ($usuarios as $usuario)
                								<option value="{{ $usuario->id}}">{{ $usuario->nombres}}</option>
                							@endforeach
                						</select>
                    				</div>
                    				<div class="col-md-3 pl-1 pt-1">
                    					<select title="Servidor" class="form-control rounded selectpicker" id="servidorG" data-size="5" data-live-search="true">
                							@foreach ($servidores as $servidor)
                								<option value="{{ $servidor->id}}">{{ $servidor->name}}</option>
                							@endforeach
                						</select>
                    				</div>
                    				<div class="col-md-3 pl-1 pt-1">
                    					<select title="Estado" class="form-control rounded selectpicker" id="estadoG" data-size="5" data-live-search="true">
                							<option value="1">Gestionado</option>
                							<option value="2">Promesa Incumplida</option>
                							<option value="6">Gestionado / Nro Equivocado</option>
                						</select>
                    				</div>
                    				<div class="col-md-2 pl-1 pt-1">
                    					<select title="Corte" class="form-control rounded selectpicker" id="fecha_corteG" data-size="5" data-live-search="true">
                							<option value="15">15</option>
                							<option value="30">30</option>
                						</select>
                    				</div>
                    				<div class="col-md-1 pl-1 pt-1 text-left">
                    					<a href="javascript:cerrarFiltradorG()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
                    					<a href="javascript:void(0)" id="filtrarG" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
                    				</div>
                    			</div>
                    		</div>
                    	</div>
                    </div>
    				<div class="table-responsive mt-3">
    				    <table class="table table-striped table-hover w-100" id="table_gestionados">
    				        <thead class="thead-dark">
    				            <tr>
    				                <th>Nombre</th>
    				                <th class="text-center">Identificación</th>
    				                <th class="text-center">Teléfono</th>
    				                <th class="text-center">Estado</th>
    				                <th class="text-center">Gestionado por</th>
    				                <th class="text-center">Acciones</th>
    				            </tr>
    				        </thead>
    				    </table>
				    </div>
    			</div>
    			<div class="tab-pane fade" id="retirados" role="tabpanel" aria-labelledby="retirados-tab">
    			    <div class="text-right">
    			        <a href="javascript:getDataTableR()" class="btn btn-success btn-sm my-1"><i class="fas fa-sync"></i>Actualizar</a>
    			        <a href="javascript:abrirFiltradorR()" class="btn btn-info btn-sm my-1" id="boton-filtrarR"><i class="fas fa-search"></i>Filtrar</a>
    			    </div>
    			    
    			    <div class="container-fluid d-none" id="form-filterR">
                    	<div class="card shadow-sm border-0 mb-3" style="background: #ffffff00 !important;">
                    		<div class="card-body py-0">
                    			<div class="row">
                    				<div class="col-md-3 pl-1 pt-1">
                    					<select title="Cliente" class="form-control rounded selectpicker" id="clienteR" data-size="5" data-live-search="true">
                							@foreach ($clientes as $cliente)
                								<option value="{{ $cliente->id}}">{{ $cliente->nombre}} - {{ $cliente->nit}}</option>
                							@endforeach
                						</select>
                    				</div>
                    				<div class="col-md-3 pl-1 pt-1">
                    					<select title="Gestionado" class="form-control rounded selectpicker" id="created_byR" data-size="5" data-live-search="true">
                							@foreach ($usuarios as $usuario)
                								<option value="{{ $usuario->id}}">{{ $usuario->nombres}}</option>
                							@endforeach
                						</select>
                    				</div>
                    				<div class="col-md-3 pl-1 pt-1">
                    					<select title="Servidor" class="form-control rounded selectpicker" id="servidorR" data-size="5" data-live-search="true">
                							@foreach ($servidores as $servidor)
                								<option value="{{ $servidor->id}}">{{ $servidor->name}}</option>
                							@endforeach
                						</select>
                    				</div>
                    				<div class="col-md-2 pl-1 pt-1">
                    					<select title="Corte" class="form-control rounded selectpicker" id="fecha_corteR" data-size="5" data-live-search="true">
                							<option value="15">15</option>
                							<option value="30">30</option>
                						</select>
                    				</div>
                    				<div class="col-md-1 pl-1 pt-1 text-left">
                    					<a href="javascript:cerrarFiltradorR()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
                    					<a href="javascript:void(0)" id="filtrarR" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
                    				</div>
                    			</div>
                    		</div>
                    	</div>
                    </div>
    				<div class="table-responsive mt-3">
    				    <table class="table table-striped table-hover w-100" id="table_retirados">
    				        <thead class="thead-dark">
    				            <tr>
    				                <th>Nombre</th>
    				                <th class="text-center">Identificación</th>
    				                <th class="text-center">Teléfono</th>
    				                <th class="text-center">Estado</th>
    				                <th class="text-center">Gestionado por</th>
    				                <th class="text-center">Acciones</th>
    				            </tr>
    				        </thead>
    				    </table>
				    </div>
    			</div>
    			<div class="tab-pane fade" id="retiradosTOTAL" role="tabpanel" aria-labelledby="retiradosTOTAL-tab">
    			    <div class="text-right">
    			        <a href="javascript:getDataTableT()" class="btn btn-success btn-sm my-1"><i class="fas fa-sync"></i>Actualizar</a>
    			        <a href="javascript:abrirFiltradorT()" class="btn btn-info btn-sm my-1" id="boton-filtrarR"><i class="fas fa-search"></i>Filtrar</a>
    			    </div>
    			    
    			    <div class="container-fluid d-none" id="form-filterT">
                    	<div class="card shadow-sm border-0 mb-3" style="background: #ffffff00 !important;">
                    		<div class="card-body py-0">
                    			<div class="row">
                    				<div class="col-md-3 pl-1 pt-1">
                    					<select title="Cliente" class="form-control rounded selectpicker" id="clienteT" data-size="5" data-live-search="true">
                							@foreach ($clientes as $cliente)
                								<option value="{{ $cliente->id}}">{{ $cliente->nombre}} - {{ $cliente->nit}}</option>
                							@endforeach
                						</select>
                    				</div>
                    				<div class="col-md-3 pl-1 pt-1">
                    					<select title="Gestionado" class="form-control rounded selectpicker" id="created_byT" data-size="5" data-live-search="true">
                							@foreach ($usuarios as $usuario)
                								<option value="{{ $usuario->id}}">{{ $usuario->nombres}}</option>
                							@endforeach
                						</select>
                    				</div>
                    				<div class="col-md-3 pl-1 pt-1">
                    					<select title="Servidor" class="form-control rounded selectpicker" id="servidorT" data-size="5" data-live-search="true">
                							@foreach ($servidores as $servidor)
                								<option value="{{ $servidor->id}}">{{ $servidor->name}}</option>
                							@endforeach
                						</select>
                    				</div>
                    				<div class="col-md-2 pl-1 pt-1">
                    					<select title="Corte" class="form-control rounded selectpicker" id="fecha_corteT" data-size="5" data-live-search="true">
                							<option value="15">15</option>
                							<option value="30">30</option>
                						</select>
                    				</div>
                    				<div class="col-md-1 pl-1 pt-1 text-left">
                    					<a href="javascript:cerrarFiltradorT()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
                    					<a href="javascript:void(0)" id="filtrarT" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
                    				</div>
                    			</div>
                    		</div>
                    	</div>
                    </div>
    				<div class="table-responsive mt-3">
    				    <table class="table table-striped table-hover w-100" id="table_retiradosT">
    				        <thead class="thead-dark">
    				            <tr>
    				                <th>Nombre</th>
    				                <th class="text-center">Identificación</th>
    				                <th class="text-center">Teléfono</th>
    				                <th class="text-center">Estado</th>
    				                <th class="text-center">Gestionado por</th>
    				                <th class="text-center">Acciones</th>
    				            </tr>
    				        </thead>
    				    </table>
				    </div>
    			</div>
			</div>
    	</div>
    </div>
    
    <div class="modal fade" id="modal_gestion" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title font-weight-bold" id="exampleModalLabel">MODAL DE GESTIÓN</h4>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('crm.store') }}" style="padding: 0% 7%;" role="form" class="forms-sample" novalidate id="formulario">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-8 offset-md-2">
                                <div class="stopwatch" data-autostart="false" style="border-radius: 20px;background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};text-align: center;padding: 5%;color: #fff;">
                                    <div class="time">
                                        <span class="hours"></span> : 
                                        <span class="minutes"></span> : 
                                        <span class="seconds"></span>
                                    </div>
                                    <div class="controls d-none">
                                        <button class="toggle" data-pausetext="Pause" data-resumetext="Resume" id="btn_start">Start</button>
                                        <button class="reset" id="btn_reset">Reset</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group col-md-12 mt-3">
                                <label class="control-label"><strong>Nombre:</strong> <span id="modal_nombre"></span></label>
                                <br>
                                <label class="control-label"><strong>Identificación:</strong> <span id="modal_nit"></span></label>
                                <br>
                                <label class="control-label"><strong>Celular:</strong> <span id="modal_celular"></span></label>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="informacion"><strong>¿Contestó la llamada?</strong> <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" title="Seleccione" data-live-search="true" data-size="5" onchange="validarLlamada(this.value)" id="llamada" name="llamada">
                                    <option value="1">Si</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-6 d-none" id="div_equivocado">
                                <label for=""><strong>¿Número Equivocado?</strong> <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" title="Seleccione" data-live-search="true" data-size="5" onchange="validarEquivocado(this.value)" id="equivocado" name="equivocado">
                                    <option value="1">Si</option>
                                    <option value="0" selected>No</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-6 d-none" id="div_nuevo">
                                <label for=""><strong>Número Nuevo</strong></label>
                                <input type="number" class="form-control" id="numero_nuevo" min="0" name="numero_nuevo" autocomplete="off">
                            </div>
                            
                            <div class="form-group col-md-6 d-none" id="div_retirado">
                                <label for=""><strong>¿Cliente Retirado?</strong> <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" title="Seleccione" data-live-search="true" data-size="5" id="retirado" name="retirado">
                                    <option value="0" selected>No</option>
                                    <option value="1">Si</option>
                                    <option value="2">Si - Retirado Total</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-6 d-none" id="div_promesa">
                                <label for="informacion"><strong>Promesa de Pago</strong> <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" title="Seleccione" data-live-search="true" data-size="5" onchange="validarPromesa(this.value)" id="promesa_pago" name="promesa_pago">
                                    <option value="1">Si</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-6 d-none" id="div_fecha">
                                <label><strong>Fecha de Pago</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control datepicker" id="fecha" value="{{date('d-m-Y')}}" name="fecha" autocomplete="off">
                            </div>
                            
                            <div class="form-group col-md-12 d-none" id="div_informacion">
                                <label for="informacion"><strong>Información</strong> <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="informacion" name="informacion" rows="3"></textarea>
                            </div>
                            
                            <input type="hidden" class="form-control" id="idcliente" name="idcliente">
                            <input type="hidden" class="form-control" id="tiempo" name="tiempo">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="btn_cancel">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="store();">Guardar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    $("#formulario").submit(function() {
        return false;
    });
    
    var tabla = null;
    window.addEventListener('load',
    function() {
		$('#table_sin_gestionar').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "asc"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/cartera/0")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    {data: 'nombre'},
			    {data: 'nit'},
			    {data: 'celular'},
				{data: 'estado'},
				//{data: 'created_by'},
				{data: 'acciones'},
			]
		});
		
        tabla = $('#table_sin_gestionar');
        
        tabla.on('preXhr.dt', function(e, settings, data) {
            data.cliente = $('#cliente').val();
            data.estado = $('#estado').val();
            data.created_by = $('#created_by').val();
            data.fecha_corte = $('#fecha_corte').val();
            data.servidor = $('#servidor').val();
            data.filtro = true;
        });
        
        $('#filtrar').on('click', function(e) {
            getDataTable();
            return false;
        });
        
        $('#form-filter').on('keypress',function(e) {
            if(e.which == 13) {
                getDataTable();
                return false;
            }
        });
        
		$('#table_gestionados').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "asc"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/cartera/1")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    {data: 'nombre'},
			    {data: 'nit'},
			    {data: 'celular'},
				{data: 'estado'},
				{data: 'created_by'},
				{data: 'acciones'},
			]
		});
		
        table = $('#table_gestionados');
        
        table.on('preXhr.dt', function(e, settings, data) {
            data.cliente = $('#clienteG').val();
            data.estado = $('#estadoG').val();
            data.created_by = $('#created_byG').val();
            data.fecha_corte = $('#fecha_corteG').val();
            data.servidor = $('#servidorG').val();
            data.filtro = true;
        });
        
        $('#filtrarG').on('click', function(e) {
            getDataTableG();
            return false;
        });
        
        $('#form-filterG').on('keypress',function(e) {
            if(e.which == 13) {
                getDataTableG();
                return false;
            }
        });
        
        $('#table_retirados').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "asc"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/cartera/4")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    {data: 'nombre'},
			    {data: 'nit'},
			    {data: 'celular'},
				{data: 'estado'},
				{data: 'created_by'},
				{data: 'acciones'},
			]
		});
        
        tableR = $('#table_retirados');
        
        tableR.on('preXhr.dt', function(e, settings, data) {
            data.cliente = $('#clienteR').val();
            data.estado = $('#estadoR').val();
            data.created_by = $('#created_byR').val();
            data.fecha_corte = $('#fecha_corteR').val();
            data.servidor = $('#servidorR').val();
            data.filtro = true;
        });
        
        $('#filtrarR').on('click', function(e) {
            getDataTableR();
            return false;
        });
        
        $('#form-filterR').on('keypress',function(e) {
            if(e.which == 13) {
                getDataTableR();
                return false;
            }
        });
        
        $('#table_retiradosT').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "asc"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/cartera/5")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    {data: 'nombre'},
			    {data: 'nit'},
			    {data: 'celular'},
				{data: 'estado'},
				{data: 'created_by'},
				{data: 'acciones'},
			]
		});
        
        tableT = $('#table_retiradosT');
        
        tableT.on('preXhr.dt', function(e, settings, data) {
            data.cliente = $('#clienteT').val();
            data.estado = $('#estadoT').val();
            data.created_by = $('#created_byT').val();
            data.fecha_corte = $('#fecha_corteT').val();
            data.servidor = $('#servidorT').val();
            data.filtro = true;
        });
        
        $('#filtrarT').on('click', function(e) {
            getDataTableT();
            return false;
        });
        
        $('#form-filterT').on('keypress',function(e) {
            if(e.which == 13) {
                getDataTableT();
                return false;
            }
        });
    });

	function getDataTable() {
		tabla.DataTable().ajax.reload();
	}
	
	function getDataTableG() {
		table.DataTable().ajax.reload();
	}
	
	function getDataTableR() {
		tableR.DataTable().ajax.reload();
	}
	
	function getDataTableT() {
		tableT.DataTable().ajax.reload();
	}

	function abrirFiltrador() {
		if ($('#form-filter').hasClass('d-none')) {
			$('#boton-filtrar').html('<i class="fas fa-times"></i> Cerrar');
			$('#form-filter').removeClass('d-none');
		} else {
			$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
			cerrarFiltrador();
		}
	}

	function cerrarFiltrador() {
		$('#cliente').val('');
		$('#estado').val('').selectpicker('refresh');
		$('#created_by').val('').selectpicker('refresh');
		$('#fecha_corte').val('').selectpicker('refresh');
		$('#servidor').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
	
	function abrirFiltradorG() {
		if ($('#form-filterG').hasClass('d-none')) {
			$('#boton-filtrarG').html('<i class="fas fa-times"></i> Cerrar');
			$('#form-filterG').removeClass('d-none');
		} else {
			$('#boton-filtrarG').html('<i class="fas fa-search"></i> Filtrar');
			cerrarFiltradorG();
		}
	}

	function cerrarFiltradorG() {
		$('#clienteG').val('');
		$('#estadoG').val('').selectpicker('refresh');
		$('#created_byG').val('').selectpicker('refresh');
		$('#fecha_corteG').val('').selectpicker('refresh');
		$('#servidorG').val('').selectpicker('refresh');
		$('#form-filterG').addClass('d-none');
		$('#boton-filtrarG').html('<i class="fas fa-search"></i> Filtrar');
		getDataTableG();
	}
	
	function abrirFiltradorR() {
		if ($('#form-filterR').hasClass('d-none')) {
			$('#boton-filtrarR').html('<i class="fas fa-times"></i> Cerrar');
			$('#form-filterR').removeClass('d-none');
		} else {
			$('#boton-filtrarR').html('<i class="fas fa-search"></i> Filtrar');
			cerrarFiltradorR();
		}
	}

	function cerrarFiltradorR() {
		$('#clienteR').val('');
		$('#estadoR').val('').selectpicker('refresh');
		$('#created_byR').val('').selectpicker('refresh');
		$('#fecha_corteR').val('').selectpicker('refresh');
		$('#servidorR').val('').selectpicker('refresh');
		$('#form-filterR').addClass('d-none');
		$('#boton-filtrarR').html('<i class="fas fa-search"></i> Filtrar');
		getDataTableR();
	}
	
	function abrirFiltradorT() {
		if ($('#form-filterT').hasClass('d-none')) {
			$('#boton-filtrarT').html('<i class="fas fa-times"></i> Cerrar');
			$('#form-filterT').removeClass('d-none');
		} else {
			$('#boton-filtrarT').html('<i class="fas fa-search"></i> Filtrar');
			cerrarFiltradorT();
		}
	}

	function cerrarFiltradorT() {
		$('#clienteT').val('');
		$('#estadoT').val('').selectpicker('refresh');
		$('#created_byT').val('').selectpicker('refresh');
		$('#fecha_corteT').val('').selectpicker('refresh');
		$('#servidorT').val('').selectpicker('refresh');
		$('#form-filterT').addClass('d-none');
		$('#boton-filtrarT').html('<i class="fas fa-search"></i> Filtrar');
		getDataTableT();
	}
	
	function gestionar(idCliente) {
	    cargando(true);
	    if (window.location.pathname.split("/")[1] === "software") {
	        var url = '/software/empresa/crm/'+idCliente+'/contacto';
	    }else{
	        var url = '/empresa/crm/'+idCliente+'/contacto';
	    }
	    
	    $.ajax({
	        url: url,
	        success: function(data){
	            $("#btn_reset").click();
	            $('#fecha').val('');
	            $('#promesa_pago, #llamada, #retirado, #numero_nuevo, #equivocado').val('').selectpicker('refresh');
	            $("#div_fecha, #div_promesa, #div_informacion, #div_retirado, #div_equivocado, #div_nuevo").addClass('d-none');
	            
	            data=JSON.parse(data);
	            
                $("#modal_nit").empty().text(data[0].nit);
                $("#modal_nombre").empty().text(data[0].nombre);
                $("#modal_celular").empty().text(data[0].celular);
                $("#idcliente").val(data[0].id);
                
                /*if(data[0].contrato){
                    $("#modal_contrato").empty().text(data[0].contrato);
                }else{
                    $("#modal_contrato").empty().text('Sin contrato');
                }*/
	            
	            $('#modal_gestion').modal({
	                keyboard: false,
	                backdrop: 'static'
	            });
	            
	            cargando(false);
	            
	            $("#btn_start").click();
	            //$("#btn_reset").click();
	        },
	        error: function(data){
	            cargando(false);
	        }
	    });
	}
	
	function store(){
	    cargando(true);
	    let hou = $(".hours").text();
	    let min = $(".minutes").text();
	    let seg = $(".seconds").text();
	    
	    $("#tiempo").val(hou+':'+min+':'+seg);
	    $("#btn_start").click();
	    
	    if($('#llamada').val().length == 0){
	        cargando(false);
	        swal('INFORMACIÓN INCOMPLETA', 'COMPLETE LA INFORMACIÓN SOLICITADA PARA GESTIONAR EL CLIENTE', 'warning');
	        return false;
	    }
	    
        $.post($("#formulario").attr('action'), $("#formulario").serialize(), function(data) {
            $('#btn_cancel').click();
            $('#formulario').trigger("reset");
            cargando(false);
            swal(data.title, data.text, data.icon);
            getDataTable();
            getDataTableG();
            getDataTableR();
            getDataTableT();
        }, 'json');
	}
	
	function validarPromesa(value){
	    $("#fecha").val('');
	    if(value === '1'){
	        $("#div_fecha").removeClass('d-none');
	    }else if(value === '0'){
	        $("#div_fecha").addClass('d-none');
	    }
	}
	
	function validarLlamada(value){
	    $('#equivocado').val('').selectpicker('refresh');
	    if(value === '1'){
	        $("#div_equivocado").removeClass('d-none');
	    }else if(value === '0'){
	        $("#div_equivocado, #div_informacion, #div_retirado, #div_fecha").addClass('d-none');
	    }
	}

	function validarEquivocado(value){
	    $('#promesa_pago, #retirado, #informacion').val('').selectpicker('refresh');
	    $("#informacion, #numero_nuevo").val('');
	    if(value === '1'){
	        $("#div_nuevo").removeClass('d-none');
	        $("#div_promesa, #div_informacion, #div_retirado, #div_fecha").addClass('d-none');
	    }else if(value === '0'){
	        $("#div_nuevo").addClass('d-none');
	        $("#div_promesa, #div_informacion, #div_retirado, #div_fecha").removeClass('d-none');
	    }
	}
	
	$(function() {
	    $('.stopwatch').each(function() {
	        var element = $(this);
	        var running = element.data('autostart');
	        var hoursElement = element.find('.hours');
	        var minutesElement = element.find('.minutes');
	        var secondsElement = element.find('.seconds');
	        var millisecondsElement = element.find('.milliseconds');
	        var toggleElement = element.find('.toggle');
	        var resetElement = element.find('.reset');
	        var pauseText = toggleElement.data('pausetext');
	        var resumeText = toggleElement.data('resumetext');
	        var startText = toggleElement.text();
	        
	        var hours, minutes, seconds, milliseconds, timer;
	        
	        function prependZero(time, length) {
	            time = '' + (time | 0);
	            while (time.length < length) time = '0' + time;
	            return time;
	        }
	        
	        function setStopwatch(hours, minutes, seconds, milliseconds) {
	            hoursElement.text(prependZero(hours, 2));
	            minutesElement.text(prependZero(minutes, 2));
	            secondsElement.text(prependZero(seconds, 2));
	            millisecondsElement.text(prependZero(milliseconds, 3));
	        }
	        
	        function runTimer() {
	            var startTime = Date.now();
	            var prevHours = hours;
	            var prevMinutes = minutes;
	            var prevSeconds = seconds;
	            var prevMilliseconds = milliseconds;
	            
	            timer = setInterval(function() {
	                var timeElapsed = Date.now() - startTime;
	                
	            hours = (timeElapsed / 3600000) + prevHours;
	            minutes = ((timeElapsed / 60000) + prevMinutes) % 60;
	            seconds = ((timeElapsed / 1000) + prevSeconds) % 60;
	            milliseconds = (timeElapsed + prevMilliseconds) % 1000;
	            setStopwatch(hours, minutes, seconds, milliseconds);
	        }, 25);
	    }
	        
	        function run() {
	            running = true;
	            runTimer();
	            toggleElement.text(pauseText);
	        }
	        
	        function pause() {
	            running = false;
	            clearTimeout(timer);
	            toggleElement.text(resumeText);
	        }
	        
	        function reset() {
	            running = false;
	            pause();
	            hours = minutes = seconds = milliseconds = 0;
	            setStopwatch(hours, minutes, seconds, milliseconds);
	            toggleElement.text(startText);
	        }
	        
	        toggleElement.on('click', function() {
	            (running) ? pause(): run();
	        });
	        
	        resetElement.on('click', function() {
	            reset();
	        });
	        reset();
	        if (running) run();
	    });
	});
	
    function cambiarRetiroTotal(id) {
        swal({
	        title: '¿Está seguro que deseas cambiar al cliente a retiro total?',
	        type: 'question',
	        showCancelButton: true,
	        confirmButtonColor: '#00ce68',
	        cancelButtonColor: '#d33',
	        confirmButtonText: 'Aceptar',
	        cancelButtonText: 'Cancelar',
	    }).then((result) => {
	        if (result.value) {
	            $.ajax({
	                url: '{{config('app.url')}}/empresa/crm/status/'+id,
	                headers: {
	                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	                },
	                method: 'get',
	                success: function(data) {
	                    if (data.success) {
	                        swal({
            					title: data.title,
            					text: data.text,
            					type: data.icon
            				});
            				getDataTableR();
                            getDataTableT();
	                   }
	               }
	           });
	        }
	    })
	}
</script>
@endsection
