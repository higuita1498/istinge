 @extends('layouts.app')
@section('content')
  @if(Session::has('error'))
    <div class="alert alert-danger" >
      {{Session::get('error')}}
    </div>
  @endif

	<form method="POST" action="{{ route('configuracion.numeraciones') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-numeros" >

	<p>Indica el número con el cual vas a crear tus próximos documentos.</p>
   {{ csrf_field() }}
  <div class="row">
  	<div class="col-sm-7 form-group">
  		<div class="row">
  			<label class="col-sm-8 col-form-label" style="text-align: right;">Siguiente número de recibos de caja <span class="text-danger">*</span></label>
  			<div class="col-sm-4">
  				<input type="text" class="form-control form-control-sm"  id="caja" name="caja"  required="" value="{{$numeracion->caja}}" maxlength="15" disabled>
  			</div>
  		</div>
      <div class="row">
        <label class="col-sm-8 col-form-label" style="text-align: right;">Siguiente número de recibos de caja para remisiones <span class="text-danger">*</span></label>
        <div class="col-sm-4">
          <input type="text" class="form-control form-control-sm"  id="cajar" name="cajar"  required="" value="{{$numeracion->cajar}}" maxlength="10" disabled>
        </div>
      </div>

  		<div class="row">
  			<label class="col-sm-8 col-form-label" style="text-align: right;">Siguiente número de comprobantes de pago <span class="text-danger">*</span></label>
  			<div class="col-sm-4">
  				<input type="text" class="form-control form-control-sm"  id="pago" name="pago"  required="" value="{{$numeracion->pago}}" maxlength="10" disabled>
  			</div>
  		</div>

  		<div class="row">
  			<label class="col-sm-8 col-form-label" style="text-align: right;">Siguiente número de nota crédito <span class="text-danger">*</span></label>
  			<div class="col-sm-4">
  				<input type="text" class="form-control form-control-sm"  id="credito" name="credito"  required="" value="{{$numeracion->credito}}" maxlength="10" disabled>
  			</div>
  		</div>

  		<div class="row">
  			<label class="col-sm-8 col-form-label" style="text-align: right;">Siguiente número de remisiones <span class="text-danger">*</span></label>
  			<div class="col-sm-4">
  				<input type="text" class="form-control form-control-sm"  id="remision" name="remision"  required="" value="{{$numeracion->remision}}" maxlength="10" disabled>
  			</div>
  		</div>

  		<div class="row">
  			<label class="col-sm-8 col-form-label" style="text-align: right;">Siguiente número de cotizaciones <span class="text-danger">*</span></label>
  			<div class="col-sm-4">
  				<input type="text" class="form-control form-control-sm"  id="cotizacion" name="cotizacion"  required="" value="{{$numeracion->cotizacion}}" maxlength="10" disabled>
  			</div>
  		</div>


  		<div class="row">
  			<label class="col-sm-8 col-form-label" style="text-align: right;">Siguiente número de órdenes de compra <span class="text-danger">*</span></label>
  			<div class="col-sm-4">
  				<input type="text" class="form-control form-control-sm"  id="orden" name="orden"  required="" value="{{$numeracion->orden}}" maxlength="10" disabled>
  			</div>
  		</div>
  	</div>
  </div>

	<hr>
	<div class="row" style="display: none;" id="modificar" >
    <div class="col-sm-12" style="text-align: right;">
      <a class="btn btn-outline-secondary" onclick="nodisabled('form-numeros', false);">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar</button>
    </div>
	</div>
	<div class="row"  >
    <div class="col-sm-12" style="text-align: right;" id="boton">
      <button type="button" class="btn btn-primary" onclick="nodisabled('form-numeros');">Modificar</button>
    </div>
	</div>
</form>
<div class="row">
  <div class="col-md-7" style="text-align: left; padding-left: 3%;">
    <h2>Numeración para Facturas de Venta</h2>
  </div>
  <div class="col-md-5" style="text-align: left;">
    <a href="{{route('numeraciones.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva numeración</a>


  </div>
</div>

<p style=" padding-left:2%">Indica el prefijo y número con el cual deben crearse tus facturas de venta. Puedes configurar múltiples numeraciones.</p>
@if(Session::has('success'))
		<div class="alert alert-success" >
			{{Session::get('success')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>


	@endif
	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
	              <th>Nombre</th>
	              <th>Preferida</th>
	              <th>Estado</th>
	              <th>Resolución</th>
	              <th>Prefijo</th>
	              <th>Siguiente número</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
        @foreach($numeraciones as $num)
          <tr @if($num->id==Session::get('numeracion_id')) class="active_table" @endif>
            <td >{{$num->nombre}}</td>
            <td>{{$num->preferida()}}</td>
            <td>{{$num->estado()}}</td>
            <td>{{$num->nroresolucion}}</td>
            <td>{{$num->prefijo}}</td>
            <td>{{$num->inicio}}</td>
            <td><a href="{{route('numeraciones.edit',$num->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
              @if($num->usado()==0)
                <form action="{{ route('numeraciones.destroy',$num->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-num">
                    {{ csrf_field() }}
                <input name="_method" type="hidden" value="DELETE">
                </form>
                <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-num', '¿Estas seguro que deseas eliminar la numeración?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
              @endif
              <form action="{{ route('numeraciones.act_desc',$num->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-num{{$num->id}}">
                    {{ csrf_field() }}
                </form>

                @if($num->estado==1)
                  <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-num{{$num->id}}', '¿Estas seguro que deseas desactivar esta numeración?', 'No aparecera para seleccionar en las facturas');"><i class="fas fa-power-off"></i></button>
                @else
                  <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-num{{$num->id}}', '¿Estas seguro que deseas activar esta numeración?', 'Aparecera para seleccionar en las facturas');"><i class="fas fa-power-off"></i></button>
                @endif


            </td>
          </tr>
        @endforeach
			</tbody>
		</table>
		</div>
	</div>

@endsection
