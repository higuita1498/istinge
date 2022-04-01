@extends('layouts.app')

@section('boton')
    <a href="{{ route('contratos.show',$contrato->id )}}"  class="btn btn-primary" title="Regresar"><i class="fas fa-step-backward"></i></i> Regresar al Contrato</a>
@endsection

@section('content')
    <style>
    	body > div.container-scroller > div > div > div.content-wrapper > div > div > div > div.row.card-description > div > div > table > tbody > tr:nth-child(10) > td > img{
    		width: 547px;
    		height: 297px;
    		border-radius: 0%;
    	}
    	.bg-th{
            background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;
            border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;
            color: #fff !important;
            text-align: center;
        }
        .info th {
            text-align: center;
        }
    </style>
    
    <div class="row card-description">
    	<div class="col-md-12">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm info">
    				<tbody>
    					<tr>
    						<th class="bg-th">CONTRATO</th>
    						<th class="bg-th">CLIENTE</th>
    						<th class="bg-th">DIRECCIÓN IP</th>
    						<th class="bg-th">INTERFAZ</th>
    						<th class="bg-th">SERVIDOR ASOCIADO</th>
    						<th class="bg-th">CONEXIÓN</th>
    					</tr>
    					<tr class="text-center">
    						<td>{{ $contrato->nro }}</td>
    						<td>{{ $contrato->cliente()->nombre }}</td>
    						<td>{{ $contrato->ip }}</td>
    						<td>{{ $contrato->interfaz }}</td>
    						<td>{{ $contrato->servidor()->nombre }}</td>
    						<td>{{ $contrato->conexion() }}</td>
    					</tr>
    				</tbody>
    			</table>
    		</div>
    	</div>
    	
    	<div class="col-md-8 offset-md-2 text center">
    	    <input type="hidden" value="0" id="nro">
    	    <div class="card mt-4" style="border-radius: 20px;background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;color: #fff;">
    	        <div class="card-body text-center">
    	            <h4 class="card-title font-weight-bold" style="color: #fff;">INFORMACIÓN</h4>
    	            <p class="card-text">El proceso de PING se ejecuta hasta <strong>30</strong> veces de manera automática</p>
    	            <center>
    	            <a href="javascript:detenerPing()" class="btn btn-secondary" id="btn_detener">Detener Proceso</a>
    	            <a href="javascript:iniciarPing()" class="btn btn-success disabled" id="btn_continuar" disabled>Continuar Proceso</a>
    	            <a href="javascript:reiniciarPing()" class="btn btn-warning disabled" id="btn_reiniciar" disabled>Reiniciar Proceso</a>
    	            </center>
    	        </div>
    	    </div>
    	</div>
    	
    	<div class="col-md-12 mt-4">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm info">
    				<tbody id="table_ping">
    					<tr>
    						<th class="bg-th" width="10%">#</th>
    						<th class="bg-th" width="20%">HOST</th>
    						<th class="bg-th" width="10%">TIEMPO</th>
    						<th class="bg-th" width="10%">TAMAÑO</th>
    						<th class="bg-th" width="10%">TTL</th>
    						<th class="bg-th" width="10%">ENVIADO</th>
    						<th class="bg-th" width="10%">RECIBIDO</th>
    						<th class="bg-th" width="20%">ESTADO</th>
    					</tr>
    				</tbody>
    			</table>
    		</div>
    	</div>
    </div>

@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            ping();
        });
        
        function ping(){
            var nro = $("#nro").val();
            if(nro<=29){
                $.ajax({
                    url: '{{url("/empresa/contratos/$contrato->id/ping_nuevo/")}}',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    method: 'get',
                    success: function(data){
                        if(data.data){
                            var opt = parseInt(nro)+parseInt(1);
                            $("#nro").val(opt);
                            
                            if(data.data[0].status){
                                var status = data.data[0].status;
                                var color = 'text-danger';
                            }else{
                                var status = 'Exitoso';
                                var color = 'text-success';
                            }
                            
                            if(data.data[0].time){
                                var time = data.data[0].time;
                            }else{
                                var time = '----';
                            }
                            
                            if(data.data[0].size){
                                var size = data.data[0].size;
                            }else{
                                var size = '----';
                            }
                            
                            if(data.data[0].ttl){
                                var ttl = data.data[0].ttl;
                            }else{
                                var ttl = '----';
                            }
                            
                            $("#table_ping").append(`
                                <tr class="text-center">
                                    <td>`+opt+`</td>
                                    <td>`+data.data[0].host+`</td>
                                    <td>`+time+`</td>
                                    <td>`+size+`</td>
                                    <td>`+ttl+`</td>
                                    <td>`+data.data[0].sent+`</td>
                                    <td>`+data.data[0].received+`</td>
                                    <td class="`+color+` font-weight-bold">`+status+`</td>
                                </tr>`);
                            
                            ping();
                        }else{
                            Swal.fire({
                                type: data.icon,
                                title: data.title,
                                html: data.text,
                                showConfirmButton: false
                            });
                        }
                        cargando(false);
                    },
                    error: function(data){
                        
                    }
                });
            }
        }
        
        function detenerPing(){
            window.stop ();
            $("#btn_detener").addClass('disabled').attr('disabled','true');
            $("#btn_continuar, #btn_reiniciar").removeClass('disabled').removeAttr('disabled');
        }
        
        function iniciarPing(){
            $("#btn_continuar, #btn_reiniciar").addClass('disabled').attr('disabled','true');
            $("#btn_detener").removeClass('disabled').removeAttr('disabled');
            ping();
        }
        
        function reiniciarPing(){
            $("#nro").val(0);
            $("#btn_continuar, #btn_reiniciar").addClass('disabled').attr('disabled','true');
            $("#btn_detener").removeClass('disabled').removeAttr('disabled');
            $("#table_ping").html('').html(`
                <tr>
                    <th class="bg-th" width="10%">#</th>
                    <th class="bg-th" width="20%">HOST</th>
                    <th class="bg-th" width="10%">TIEMPO</th>
                    <th class="bg-th" width="10%">TAMAÑO</th>
                    <th class="bg-th" width="10%">TTL</th>
                    <th class="bg-th" width="10%">ENVIADO</th>
                    <th class="bg-th" width="10%">RECIBIDO</th>
                    <th class="bg-th" width="20%">ESTADO</th>
                </tr>`);
            ping();
        }
    </script>
@endsection