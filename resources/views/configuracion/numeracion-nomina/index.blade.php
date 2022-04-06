@extends('layouts.app')

@section('boton')
    <a href="{{route('numeraciones_nomina.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva numeración</a>
@endsection

@section('content')
  @if(Session::has('error'))
    <div class="alert alert-danger" >
      {{Session::get('error')}}
    </div>
  @endif

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

  <div class="row">
    <div class="col-md-12" style="text-align: left; padding-left: 3%;">
      <p>Indica el prefijo y número con el cual deben crearse tus pagos equivalentes a la nomina electronica.
          Puedes configurar múltiples numeraciones.</p>
    </div>
  </div>

	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover w-100" id="example">
        <thead class="thead-dark">
          <tr>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Preferida</th>
            <th>Estado</th>
            <th>Prefijo</th>
            <th>Siguiente número</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach($numeraciones as $num)
            <tr @if($num->id==Session::get('numeracion_id')) class="active_table" @endif>
              <td>{{$num->nombre}}</td>
              <td>{{$num->tipo_nomina == 1 ? 'N. Electrónica' : 'N. Ajuste'}}</td>
              <td>{{$num->preferida()}}</td>
              <td>{{$num->estado()}}</td>
              <td>{{$num->prefijo}}</td>
              <td>{{$num->inicio}}</td>
              <td><a href="{{route('numeraciones_nomina.edit',$num->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
                @if($num->usado()==0)
                  <form action="{{ route('numeraciones_nomina.destroy',$num->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-num{{$num->id}}">
                      @csrf
                  <input name="_method" type="hidden" value="DELETE">
                  </form>
                  <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-num{{$num->id}}', '¿Estas seguro que deseas eliminar la numeración?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
                @endif
                <form action="{{ route('numeraciones_nomina.act_desc',$num->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-num{{$num->id}}">
                      @csrf
                  </form>
                  @if($num->estado==1)
                    <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-num{{$num->id}}', '¿Estas seguro que deseas desactivar esta numeración?', 'No aparecera para seleccionar');"><i class="fas fa-power-off"></i></button>
                  @else
                    <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-num{{$num->id}}', '¿Estas seguro que deseas activar esta numeración?', 'Aparecera para seleccionar');"><i class="fas fa-power-off"></i></button>
                  @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
