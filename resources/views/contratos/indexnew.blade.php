@extends('layouts.app')

@section('styles')
    <style>
        td .elipsis-short-325 {
            width: 325px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        @media all and (max-width: 768px) {
            td .elipsis-short-325 {
                width: 225px;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
            }
        }
    </style>
@endsection

@section('boton')
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
           <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
        </div>
    @else
        @if(isset($_SESSION['permisos']['5']))
        <a href="{{route('contactos.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Cliente</a>
        @endif
        @if(isset($_SESSION['permisos']['202']))
        <a href="{{route('radicados.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Radicado</a>
        @endif

       
        <a href="javascript:exportarContratos('{{route('contratos.exportar')}}');" class="btn btn-success btn-sm" ><i class="fas fa-file-excel"></i> Exportar a Excel</a>
        
        @if(isset($_SESSION['permisos']['411']))
        <a href="{{route('contratos.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Contrato</a>
        @endif
        <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
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

<form id="form-dinamic-action" method="GET">
    <div class="container-fluid d-none mb-3" id="form-filter">
        <fieldset>
            <legend>Filtro de Búsqueda</legend>
        	<div class="card shadow-sm border-0">
        		<div class="card-body py-3" style="background: #f9f9f9;">
        			<div class="row">
                        <div class="col-md-2 pl-1 pt-1">
                            <input type="text" class="form-control" id="nro" name="nro" placeholder="Nro">
                        </div>
                        <div class="col-md-2 pl-1 pt-1">
                            <input type="text" class="form-control" id="celular" name="celular" placeholder="Celular">
                        </div>
        				<div class="col-md-2 pl-1 pt-1">
        					<input type="text" class="form-control" id="email" name="email" placeholder="Email">
        				</div>
        				<div class="col-md-2 pl-1 pt-1">
        					<input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección">
        				</div>
                        <div class="col-md-2 pl-1 pt-1">
        					<input type="text" class="form-control" id="direccion_precisa" name="direccion_precisa" placeholder="Dirección exacta">
        				</div>
        				<div class="col-md-2 pl-1 pt-1">
        					<input type="text" class="form-control" id="barrio" name="barrio" placeholder="Barrio">
        				</div>
                        <div class="col-md-2 pl-1 pt-1">
                            <input type="text" class="form-control" id="ip" name="ip" placeholder="Dirección IP">
                        </div>
                        <div class="col-md-2 pl-1 pt-1">
                            <input type="text" class="form-control" id="mac" name="mac"  placeholder="MAC">
                        </div>
                        <div class="col-md-4 pl-1 pt-1">
                            <select title="Clientes" class="form-control selectpicker" id="client_id" name="client_id" data-size="5" data-live-search="true">
                                @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nombre }} {{ $cliente->apellido1 }} {{ $cliente->apellido2 }} - {{ $cliente->nit }}</option>
                                @endforeach
                            </select>
                        </div>
        				<div class="col-md-3 pl-1 pt-1">
        					<select title="Planes" class="form-control selectpicker" id="plan" name="plan" data-size="5" data-live-search="true">
        						@foreach ($planes as $plan)
        						<option value="{{ $plan->id }}">{{ $plan->name }}</option>
        						@endforeach
        					</select>
        				</div>
        				<div class="col-md-3 pl-1 pt-1">
        					<select title="Estado" class="form-control selectpicker" id="state" name="state">
        						<option value="enabled">Habilitado</option>
        						<option value="disabled">Deshabilitado</option>
        					</select>
        				</div>
        				<div class="col-md-3 pl-1 pt-1">
        					<select title="Grupo de Corte" class="form-control selectpicker" id="grupo_cort" name="grupo_cort">
        						@foreach ($grupos as $grupo)
        						<option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
        						@endforeach
        					</select>
        				</div>
        				<div class="col-md-3 pl-1 pt-1">
        					<select title="Conexión" class="form-control selectpicker" id="conexion_s" name="conexion_s">
        						<option value="1">PPPOE</option>
        						<option value="2">DHCP</option>
        						<option value="3">IP Estática</option>
        						<option value="4">VLAN</option>
        					</select>
        				</div>
        				<div class="col-md-3 pl-1 pt-1">
        					<select title="Servidor" class="form-control selectpicker" id="server_configuration_id_s" name="server_configuration_id_s">
        						@foreach ($servidores as $servidor)
        						<option value="{{ $servidor->id }}">{{ $servidor->nombre }}</option>
        						@endforeach
        					</select>
        				</div>
        				<div class="col-md-3 pl-1 pt-1">
        					<select title="Nodo" class="form-control selectpicker" id="nodo_s" name="nodo_s">
        						@foreach ($nodos as $nodo)
        						<option value="{{ $nodo->id }}">{{ $nodo->nombre }}</option>
        						@endforeach
        					</select>
        				</div>
        				<div class="col-md-3 pl-1 pt-1">
                            <select title="Access Point" class="form-control selectpicker" id="ap_s" name="ap_s">
                                @foreach ($aps as $ap)
                                <option value="{{ $ap->id }}">{{ $ap->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 pl-1 pt-1">
                            <select title="Vendedor" class="form-control selectpicker" id="vendedor" name="vendedor">
                                @foreach ($vendedores as $vendedor)
                                <option value="{{ $vendedor->id }}">{{ $vendedor->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 pl-1 pt-1">
                            <select title="Canal de Venta" class="form-control selectpicker" id="canal" name="canal">
                                @foreach ($canales as $canal)
                                <option value="{{ $canal->id }}">{{ $canal->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 pl-1 pt-1">
                            <select title="Tipo de Tecnología" class="form-control selectpicker" id="tecnologia_s" name="tecnologia_s">
                                <option value="1">Fibra</option>
                                <option value="2">Inalámbrica</option>
                            </select>
                        </div>
                        <div class="col-md-3 pl-1 pt-1">
                            <select title="Tipo de Facturción" class="form-control selectpicker" id="facturacion_s" name="facturacion_s">
                                <option value="1">Estándar</option>
                                <option value="3">Electrónica</option>
                            </select>
                        </div>
                        <div class="col-md-3 pl-1 pt-1">
                            <select title="Tipo Contrato" class="form-control selectpicker" id="tipo_contrato" name="tipo_contrato">
                                <option value="instalacion">Instalación</option>
                                <option value="reconexion">Reconexión</option>
                            </select>
                        </div>

                        <div class="col-md-8 pl-1 pt-1">
                            <div class="row">
                                <div class="col-md-4 pr-1">
                                    <input type="text" class="form-control" id="desde" name="fecha" placeholder="desde">
                                </div>
                                <div class="col-md-4 pl-1">
                                    <input type="text" class="form-control" id="hasta" name="hasta" placeholder="hasta">
                                </div>
                                <div class="col-md-4 pl-1">
                                    <input type="text" class="form-control" id="fecha-corte" name="fecha_corte" placeholder="Fecha de corte">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
        				<div class="col-md-12 pl-1 pt-1 text-center">
        					<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
        					<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
        					@if(isset($_SESSION['permisos']['799']))
        					<a href="javascript:exportar()" class="btn btn-icons mr-1 btn-outline-success rounded btn-sm p-1" title="Exportar"><i class="fas fa-file-excel"></i></a>
        					@endif
        				</div>
        			</div>
        		</div>
        	</div>
        </fieldset>
    </div>
</form>

    @if(isset($_SESSION['permisos']['405']))
    <div class="row card-description">
    	<div class="col-md-12">
    		<div class="container-filtercolumn form-inline">
                @if(auth()->user()->modo_lectura())
                @else
                @if(isset($_SESSION['permisos']['750']))
                <a href="{{route('campos.organizar', 2)}}" class="btn btn-warning my-1"><i class="fas fa-table"></i> Organizar Tabla</a>
                @endif
                @if(isset($_SESSION['permisos']['815']))
                <a href="{{route('contratos.importar')}}" class="btn btn-success mr-1"><i class="fas fa-file-upload"></i> Importar Contratos Internet</a>
                @endif
                @if(isset($_SESSION['permisos']['778']))
                <div class="dropdown mr-1">
                    <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Acciones en Lote
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        @if(auth()->user()->rol == 3)
                        <a class="dropdown-item d-none" href="{{ route('contratos.importarMK') }}" id="btn_mk_all"><i class="fas fa-server" style="margin-left:4px; "></i> Enviar Contratos a MK (En desarrollo)</a>
                        @endif
                        <a class="dropdown-item" href="javascript:void(0)" id="btn_mk"><i class="fas fa-server" style="margin-left:4px; "></i> Enviar Contratos a MK</a>
                        <a class="dropdown-item" href="javascript:void(0)" id="btn_enabled"><i class="fas fa-file-signature" style="margin-left:4px; "></i> Habilitar Contratos</a>
                        <a class="dropdown-item" href="javascript:void(0)" id="btn_disabled"><i class="fas fa-file-signature" style="margin-left:4px; "></i> Deshabilitar Contratos</a>
                        <a class="dropdown-item" href="javascript:void(0)" id="btn_planes"><i class="fas fa-exchange-alt" style="margin-left:4px; "></i> Cambiar Plan de Internet</a>
                    </div>
                </div>
                @endif
                @endif

                <a  onclick="filterOptions()" class="btn btn-secondary" value="0" id="buttonfilter">Filtrar  Campos<i class="fas fa-filter" style="margin-left:4px; "></i></a>
    			<ul class="options-search-columns"  id="columnOptions">
    				@foreach($tabla as $campo)
    				    <li><input type="button" class="btn btn-success btn-sm boton_ocultar_mostrar" value="{{$campo->nombre}}"></li>
    				@endforeach
				</ul>
			</div>
		</div>
    	<div class="col-md-12">
    		<table class="table table-striped table-hover w-100" id="tabla-contratos">
    			<thead class="thead-dark">
    				<tr>
    					@foreach($tabla as $campo)
    					    <th>{{$campo->nombre}}</th>
    					@endforeach
    					<th>Acciones</th>
    				</tr>
    			</thead>
    		</table>
    	</div>
    </div>

    <div class="modal fade" id="planModal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body px-0">
                    @include('contratos.modal.planes')
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('scripts')
<script>
    $("#formulario").submit(function () {
        return false;
    });

    $(document).ready(function() {
        $('#desde').datepicker({
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            locale: 'es-es',
            uiLibrary: 'bootstrap4',
            format: 'dd-mm-yyyy',
            maxDate: function () {
                return $('#hasta').val();
            }
        });
        $('#hasta').datepicker({
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            locale: 'es-es',
            uiLibrary: 'bootstrap4',
            format: 'dd-mm-yyyy',
            minDate: function () {
                return $('#desde').val();
            }
        });
        $('#fecha-corte').datepicker({
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            locale: 'es-es',
            uiLibrary: 'bootstrap4',
            format: 'dd-mm-yyyy',
            minDate: function () {
                return $('#fecha-corte').val();
            }
        });
        $('#servidor').change(function(){
            getPlanes($("#servidor").val());
        });
    });

    @foreach($tabla as $campo)
        @if($campo->campo == 'ip')
            var nro_orden = {{ $campo->orden }};
        @endif
    @endforeach
    var tabla = null;
    window.addEventListener('load',
    function() {
		var tabla = $('#tabla-contratos').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
            ordering:true,
			order: [
				[0, "desc"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/contratos/0")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    @foreach($tabla as $campo)
                {data: '{{$campo->campo}}'},
                @endforeach
				{ data: 'acciones' },
			],
            columnDefs: [{
                type: 'ip-address', targets: 'nro_orden'
            }],
            @if(isset($_SESSION['permisos']['778']))
            select: true,
            select: {
                style: 'multi',
            },
            dom: 'Blfrtip',
            buttons: [{
                text: '<i class="fas fa-check"></i> Seleccionar todos',
                action: function() {
                    tabla.rows({
                        page: 'current'
                    }).select();
                }
            },
            {
                text: '<i class="fas fa-times"></i> Deseleccionar todos',
                action: function() {
                    tabla.rows({
                        page: 'current'
                    }).deselect();
                }
            }]
            @endif
		});
		
        tabla.on('preXhr.dt', function(e, settings, data) {
            data.nro = $('#nro').val();
			data.cliente_id = $('#client_id').val();
            data.plan = $('#plan').val();
            data.state = $('#state').val();
			data.grupo_corte = $('#grupo_cort').val();
			data.ip = $('#ip').val();
			data.mac = $('#mac').val();
            data.conexion = $("#conexion_s").val();
            data.server_configuration_id = $("#server_configuration_id_s").val();
            data.interfaz = $("#interfaz_s").val();
            data.nodo = $("#nodo_s").val();
            data.ap = $("#ap_s").val();
            data.c_barrio = $("#barrio").val();
            data.c_direccion = $("#direccion").val();
            data.c_direccion_precisa = $("#direccion_precisa").val();
            data.c_celular = $("#celular").val();
            data.c_email = $("#email").val();
            data.vendedor = $("#vendedor").val();
            data.canal = $("#canal").val();
            data.tecnologia = $("#tecnologia_s").val();
            data.facturacion = $("#facturacion_s").val();
            data.desde = $("#desde").val();
            data.hasta = $("#hasta").val();
            data.tipo_contrato = $("#tipo_contrato").val();
            data.fecha_corte = $("#fecha-corte").val();
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

        $('#nro, #celular, #email, #direccion, #barrio, #ip, #mac').on('keyup',function(e) {
            if(e.which > 32 || e.which == 8) {
                getDataTable();
                return false;
            }
        });

        $('#client_id, #plan, #state, #grupo_cort, #conexion_s, #server_configuration_id_s, #nodo_s, #ap_s, #vendedor, #canal, #tecnologia_s, #facturacion_s, #desde, #hasta, #tipo_contrato').on('change',function() {
            getDataTable();
            return false;
        });

        $(".boton_ocultar_mostrar").on('click', function(){
        	var indice = $(this).index(".boton_ocultar_mostrar");
        	$(".boton_ocultar_mostrar").eq(indice).toggleClass("btn-danger");
        	var columna = tabla.column(indice);
        	columna.visible(!columna.visible());
        });

        $('#btn_enabled').click( function () {
            states('enabled');
        });

        $('#btn_disabled').click( function () {
            states('disabled');
        });

        $('#btn_mk').click( function () {
            mk_lote();
        });

        $('#btn_planes').click( function () {
            planes_lote();
        });

        $('#guardarc').click( function () {
            planes_lote_store();
        });
    });
    
    function getDataTable() {
        $('#tabla-contratos').DataTable().ajax.reload();
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
		$('#nro').val('');
        $('#client_id').val('').selectpicker('refresh');
		$('#plan').val('').selectpicker('refresh');
		$('#grupo_cort').val('').selectpicker('refresh');
		$('#state').val('').selectpicker('refresh');
		$('#ip').val('');
		$('#mac').val('');
        $("#conexion_s").val('').selectpicker('refresh');
        $("#server_configuration_id_s").val('').selectpicker('refresh');
        $("#interfaz_s").val('').selectpicker('refresh');
        $("#nodo_s").val('').selectpicker('refresh');
        $("#ap_s").val('').selectpicker('refresh');
        $('#barrio').val('');
        $('#direccion').val('');
        $('#celular').val('');
        $('#email').val('');
        $("#vendedor").val('').selectpicker('refresh');
        $("#canal").val('').selectpicker('refresh');
        $("#tecnologia_s").val('').selectpicker('refresh');
        $("#facturacion_s").val('').selectpicker('refresh');
        $("#desde").val('');
        $("#hasta").val('');
        $("#fecha-corte").val('');
        $("#tipo_contrato").val('').selectpicker('refresh');

		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
	
	function exportar() {
	    window.location.href = window.location.pathname+'/exportar?celular='+$('#celular').val()+'&email='+$('#email').val()+'&direccion='+$('#direccion').val()+'&barrio='+$('#barrio').val()+'&ip='+$('#ip').val()+'&mac='+$('#mac').val()+'&client_id='+$('#client_id').val()+'&plan='+$('#plan').val()+'&state='+$('#state').val()+'&grupo_cort='+$('#grupo_cort').val()+'&conexion_s='+$('#conexion_s').val()+'&server_configuration_id_s='+$('#server_configuration_id_s').val()+'&nodo_s='+$('#nodo_s').val()+'&ap_s='+$('#ap_s').val()+'&vendedor='+$('#vendedor').val()+'&canal='+$('#canal').val()+'&tecnologia_s='+$('#tecnologia_s').val()+'&facturacion_s='+$('#facturacion_s').val()+'&desde='+$('#desde').val()+'&hasta='+$('#hasta').val()+'&tipo_contrato='+$('#tipo_contrato').val()+'&nro='+$('#nro').val();
	}

    function states(state){
        var contratos = [];

        var table = $('#tabla-contratos').DataTable();
        var nro = table.rows('.selected').data().length;

        if(nro<=0){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar un contrato',
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            contratos.push(table.rows('.selected').data()[i]['id']);
        }

        if(state === 'enabled'){
            var states = 'habilitar';
        }else{
            var states = 'deshabilitar';
        }

        swal({
            title: '¿Desea '+states+' '+nro+' contratos en lote?',
            text: 'Esto puede demorar unos minutos. Al Aceptar, no podrá cancelar el proceso',
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00ce68',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.value) {
                cargando(true);

                if (window.location.pathname.split("/")[1] === "software") {
                    var url = `/software/empresa/contratos/`+contratos+`/`+state+`/state_lote`;
                }else{
                    var url = `/empresa/contratos/`+contratos+`/`+state+`/state_lote`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data) {
                        cargando(false);
                        swal({
                            title: 'PROCESO REALIZADO',
                            html: 'Exitosos: <strong>'+data.correctos+' contratos</strong><br>Fallidos: <strong>'+data.fallidos+' contratos</strong>',
                            type: 'success',
                            showConfirmButton: true,
                            confirmButtonColor: '#1A59A1',
                            confirmButtonText: 'ACEPTAR',
                        });
                        getDataTable();
                    }
                })
            }
        })
    }

    function mk_lote(){
        var contratos = [];

        var table = $('#tabla-contratos').DataTable();
        var nro = table.rows('.selected').data().length;

        if(nro<=0){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar un contrato',
                type: 'error',
            });
            return false;
        }

        if(nro>25){
            swal({
                title: 'ERROR',
                html: 'Sólo se permite ejecutar 25 contratos por lotes',
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            contratos.push(table.rows('.selected').data()[i]['id']);
        }

        swal({
            title: '¿Desea enviar a la mikrotik '+nro+' contratos en lote?',
            text: 'Esto puede demorar unos minutos. Al Aceptar, no podrá cancelar el proceso',
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00ce68',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.value) {
                cargando(true);

                if (window.location.pathname.split("/")[1] === "software") {
                    var url = `/software/empresa/contratos/`+contratos+`/enviar_mk_lote`;
                }else{
                    var url = `/empresa/contratos/`+contratos+`/enviar_mk_lote`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data) {
                        cargando(false);
                        swal({
                            title: 'PROCESO REALIZADO',
                            html: 'Contratos Enviados a la MK<br>'+data.contracts_correctos+'<br>Contratos Existentes en MK<br>'+data.contracts_fallidos,
                            type: 'success',
                            showConfirmButton: true,
                            confirmButtonColor: '#1A59A1',
                            confirmButtonText: 'ACEPTAR',
                        });
                        getDataTable();
                    }
                })
            }
        })
    }

    function planes_lote(){
        var contratos = [];

        var table = $('#tabla-contratos').DataTable();
        var nro = table.rows('.selected').data().length;

        if(nro<=0){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar un contrato',
                type: 'error',
            });
            return false;
        }

        if(nro>25){
            swal({
                title: 'ERROR',
                html: 'Sólo se permite ejecutar 25 contratos por lotes',
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            contratos.push(table.rows('.selected').data()[i]['id']);
        }

        $("#planModal").modal('show');

        // swal({
        //     title: '¿Desea enviar a la mikrotik '+nro+' contratos en lote?',
        //     text: 'Esto puede demorar unos minutos. Al Aceptar, no podrá cancelar el proceso',
        //     type: 'question',
        //     showCancelButton: true,
        //     confirmButtonColor: '#00ce68',
        //     cancelButtonColor: '#d33',
        //     confirmButtonText: 'Aceptar',
        //     cancelButtonText: 'Cancelar',
        // }).then((result) => {
        //     if (result.value) {
        //         cargando(true);

        //         if (window.location.pathname.split("/")[1] === "software") {
        //             var url = `/software/empresa/contratos/`+contratos+`/enviar_mk_lote`;
        //         }else{
        //             var url = `/empresa/contratos/`+contratos+`/enviar_mk_lote`;
        //         }

        //         $.ajax({
        //             url: url,
        //             method: 'GET',
        //             headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        //             success: function(data) {
        //                 cargando(false);
        //                 swal({
        //                     title: 'PROCESO REALIZADO',
        //                     html: 'Exitosos: <strong>'+data.correctos+' contratos</strong><br>Fallidos: <strong>'+data.fallidos+' contratos</strong>',
        //                     type: 'success',
        //                     showConfirmButton: true,
        //                     confirmButtonColor: '#1A59A1',
        //                     confirmButtonText: 'ACEPTAR',
        //                 });
        //                 getDataTable();
        //             }
        //         })
        //     }
        // })
    }

    function planes_lote_store(){
        var contratos = [];

        var table = $('#tabla-contratos').DataTable();
        var nro = table.rows('.selected').data().length;

        if(nro<=0){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar un contrato',
                type: 'error',
            });
            return false;
        }

        if(nro>25){
            swal({
                title: 'ERROR',
                html: 'Sólo se permite ejecutar 25 contratos por lotes',
                type: 'error',
            });
            return false;
        }

        if($('#server_configuration_id').val().length == 0 || $('#plan_id').val().length == 0){
            swal({
                title: 'ERROR',
                html: 'Debe seleccionar el plan de internet que desea cambiar',
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            contratos.push(table.rows('.selected').data()[i]['id']);
        }

        var server_configuration_id = $('#server_configuration_id').val();
        var plan_id = $('#plan_id').val();

        swal({
            title: '¿Desea cambiar de plan de internet a '+nro+' contratos en lote?',
            text: 'Esto puede demorar unos minutos. Al Aceptar, no podrá cancelar el proceso',
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00ce68',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.value) {
                cargando(true);

                if (window.location.pathname.split("/")[1] === "software") {
                    var url = `/software/empresa/contratos/`+contratos+`/`+server_configuration_id+`/`+plan_id+`/planes_lote`;
                }else{
                    var url = `/empresa/contratos/`+contratos+`/`+server_configuration_id+`/`+plan_id+`/planes_lote`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data) {
                        cargando(false);
                        swal({
                            title: 'PROCESO REALIZADO',
                            html: 'Plan de Internet: <strong>'+data.plan+'</strong><br>Exitosos: <strong>'+data.correctos+' contratos</strong><br>Fallidos: <strong>'+data.fallidos+' contratos</strong>',
                            type: 'success',
                            showConfirmButton: true,
                            confirmButtonColor: '#1A59A1',
                            confirmButtonText: 'ACEPTAR',
                        });
                        getDataTable();
                        cerrarFiltrador();
                        $('#server_configuration_id').val('').selectpicker('refresh');
                        $('#plan_id').val('').selectpicker('refresh');
                        $("#planModal").modal('hide');
                    }
                })
            }
        })
    }

	@if($tipo)
	    $('#state').val('{{ $tipo }}').selectpicker('refresh');
	    abrirFiltrador();
	@endif

    function exportarContratos(url){
        $('#form-dinamic-action').attr('action', url);
        $('#form-dinamic-action').submit();
        $('#form-dinamic-action').attr('action', '');
    }

</script>
@endsection
