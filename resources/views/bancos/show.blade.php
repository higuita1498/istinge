@extends('layouts.app')
@section('boton')
	<input type="hidden" id="url_base" value="{{route('bancos.index')}}">
	
	<div class="row">
		<div class="col-md-4">
			<select class="form-control selectpicker d-none" name="cuenta" id="cambiar_cuenta" title="Seleccione"
                    onchange="location = this.value;">
		      @php $tipos_cuentas=\App\Banco::tipos();@endphp
		      @foreach($tipos_cuentas as $tipo_cuenta)
                      <optgroup label="{{$tipo_cuenta['nombre']}}">


		          @foreach($bancos as $cuenta)
		            @if($cuenta->tipo_cta==$tipo_cuenta['nro'])
		              <option value="{{route('bancos.show',$cuenta->nro)}}" {{$banco->id==$cuenta->id?'selected':''}}>
                          {{$cuenta->nombre}}
                      </option>
		            @endif
		          @endforeach
		        </optgroup>
		      @endforeach
		    </select>
		</div>
		<div class="col-md-8 nopadding">
			@if(Auth::user()->modo_lectura())
			@else
				@if($banco->estatus==1)
				    @if(isset($_SESSION['permisos']['732']))
					<a href="{{route('ingresos.create_cuenta',['0','0',$banco->nro])}}" class="btn btn-outline-primary btn-sm "title="Agregar Dinero" target="_blank"><i class="fas fa-plus"></i>Agregar Dinero</a>
					@endif
					@if(isset($_SESSION['permisos']['733']))
					<a href="{{route('pagos.create_cuenta',$banco->nro)}}" class="btn btn-outline-primary btn-sm "title="Retirar Dinero" target="_blank"><i class="fas fa-minus"></i>Retirar Dinero</a>
					@endif
					@if(isset($_SESSION['permisos']['734']))
					<button class="btn btn-outline-primary btn-sm "title="Transferir" onclick="modal_show('{{route('bancos.transferencia', $banco->nro)}}', 'small')"><i class="fas fa-minus"></i>Transferir </button>
					@endif
				@endif
				<div class="btn-group text-right" role="group" aria-label="Button group with nested dropdown">
				  <div class="btn-group" role="group">
					<button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					  Más acciones
					</button>
					<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
						@if($banco->estatus==1)
						    @if(isset($_SESSION['permisos']['284']))
							<a class="dropdown-item" href="{{route('bancos.edit',$banco->nro)}}" target="_blank">Editar</a>
							@endif
						@endif
						<form action="{{ route('bancos.act_desac',$banco->id) }}" method="POST" class="delete_form" style="display: none;" id="act_desac-cuenta{{$banco->id}}">
						{{ csrf_field() }}
						</form>
						@if(isset($_SESSION['permisos']['731']))
						@if($banco->estatus==1)
						<a class="dropdown-item" href="#" onclick="confirmar('act_desac-cuenta{{$banco->id}}', '¿Está seguro de que desea desactivar la cuenta?', 'Al desactivar esta cuenta no podrás realizar transacciones en ella, pero podrás consultar sus movimientos anteriores.');">Desactivar</a>
						@else
						<a class="dropdown-item" href="#" onclick="confirmar('act_desac-cuenta{{$banco->id}}', '¿Está seguro de que desea activar la cuenta?', ' ');">Activar</a>
						@endif
						@endif
					</div>
				  </div>
				</div>
			@endif
		</div>
	</div>
@endsection

@section('content')
<div class="row card-description">
    <a class="btn btn-success text-right" style="position: absolute; margin-left: 500px; margin-top:7px;" href="{{route('pagos.index')}}">Ir a Pagos</a>
    @if($banco->estatus==1)
		<a href="{{route('ingresos.create_cuenta',['3624','0',$banco->nro])}}" class="btn btn-primary" title="Agregar Base" target="_blank" style="position: absolute; margin-left: 620px; margin-top:7px;" >Agregar Base</a>
	@endif
	<div class="offset-md-9 col-md-3" style="background: #80808061;border: 1px solid #80808061;">
		<div class="row">
			<div class="col-md-4 text-right" style="    padding: 4%; font-weight: bold; color:#808080 ">Saldo</div>
			<div class="col-md-8 text-left text-{{$saldo>0?'success':'danger'}}" style="padding: 4%; font-weight: bold;">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($saldo)}}</div>			
		</div>
	</div>
</div>
<div class="row card-description">
		<div class="col-md-12">
			<input type="hidden" id="url-show-transaccion" value="{{route('bancos.movimientos.cuenta', $banco->id)}}">
			<table class="table table-striped table-hover" id="table-show-transaccion">
			<thead class="thead-dark">
				<tr>
	              <th>Fecha</th>
	              <th>Beneficiario</th>
	              <th>Conciliado</th>
	              <th>Categoría</th>
	              <th>Estado</th>
	              <th>Salida</th>
	              <th>Entrada</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>


			</tbody>
		</table>
	</div>
</div>
@endsection
