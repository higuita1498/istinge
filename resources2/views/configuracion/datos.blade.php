@extends('layouts.app')
@section('content')

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

	<form method="POST" action="{{ route('datos.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-termino" >
   {{ csrf_field() }} 
  <div class="row">
    <div class="col-md-6 form-group">
      <label class="control-label">Términos y condiciones <a><i data-tippy-content="Define las condiciones que informarás a tus clientes. Se agregarán por defecto a tus facturas de venta." class="icono far fa-question-circle"></i></a></label>
            <textarea  class="form-control form-control-sm min_max_100" name="terminos_cond" >{{$empresa->terminos_cond}}</textarea>
      <span class="help-block error">
        <strong>{{ $errors->first('terminos_cond') }}</strong>
      </span>
    </div>

    <div class="col-md-6 form-group">
      <label class="control-label">Notas de la factura de venta <a><i data-tippy-content="Agrega información importante que tus clientes verán. Estas notas se agregarán por defecto a todas tus facturas de venta." class="icono far fa-question-circle"></i></a></label>
            <textarea  class="form-control form-control-sm min_max_100" name="notas_fact" >{{$empresa->notas_fact}}</textarea>
      <span class="help-block error">
        <strong>{{ $errors->first('notas_fact') }}</strong>
      </span> 
    </div>

  </div>
    <div class="row">
    <div class="col-sm-12">
      <div class="form-check form-check-flat mt-0">
        <label class="form-check-label">
          <input type="checkbox" class="form-check-input" name="tirilla" value="1" @if($empresa->tirilla==1) checked="" @endif> Habilitar impresión de facturas en formato tirilla
        </label>
      </div>
    </div>
  </div>
  {{--<div class="row">
    <div class="col-sm-12">
      <div class="form-check form-check-flat mt-0">
        <label class="form-check-label">
          <input type="checkbox" class="form-check-input" name="edo_cuenta_fact" value="1" @if($empresa->edo_cuenta_fact==1) checked="" @endif> Incluir estado de cuenta cliente en cada envío de facturas de venta
          <a><i data-tippy-content="Activando esta opción se incluirá un enlace de cuenta cliente en cada estado" class="icono far fa-question-circle"></i></a>
        </label>

      </div>
    </div>
  </div>--}}
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('configuracion.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar</button>
    </div>
	</div>
</form>
@endsection