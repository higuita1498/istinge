@extends('layouts.app')

@section('boton')
    {{-- <a href="{{route('numeraciones_nomina.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva numeración</a> --}}
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

  <style>
    td .elipsis-short-325 {
    width: 325px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
  </style>

  <div class="row">
    <div class="col-md-12" style="text-align: left; padding-left: 3%;">
      <p>Configura los cálculos fijos que se aplican a tus empleados (una vez se modifiquen los cálculos se tendrá que generar una nueva nómina para que los cambios sean tenidos en cuenta).</p>
    </div>
  </div>

	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover w-100" id="example">
        <thead class="thead-dark">
          <tr>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Simbolo</th>
            <th>Valor</th>
            <th width="30%">Observaciones</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach($calculos as $calculo)
            <tr>
              <td>{{$calculo->nombre}}</td>
              <td>{{$calculo->tipo()}}</td>
              <td>{{$calculo->simbolo}}</td>
              <td id="tdvalor-{{$calculo->id}}">{{$calculo->valor()}}</td>
              <td width="30%" title="{{$calculo->observaciones}}"><div class="elipsis-short-325">{{$calculo->observaciones}}</div></td>
              <td >
                @if(auth()->user()->modo_lectura())
                @else
                <a href="javascript:modificarValor({{$calculo->id}})" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
                @endif
                {{-- <form action="{{ route('numeraciones_nomina.act_desc',$calculo->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-num{{$calculo->id}}">
                      @csrf
                  </form>
                  @if($calculo->estado==1)
                    <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-num{{$calculo->id}}', '¿Estas seguro que deseas desactivar esta numeración?', 'No aparecera para seleccionar');"><i class="fas fa-power-off"></i></button>
                  @else
                    <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-num{{$calculo->id}}', '¿Estas seguro que deseas activar esta numeración?', 'Aparecera para seleccionar');"><i class="fas fa-power-off"></i></button>
                  @endif --}}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal notas -->
  <div class="modal fade" id="modalCalculoFijo" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">

  </div>
@endsection

@section('scripts')
<script>
  function modificarValor(id) {
    var valor;
    var tipo;

		$.ajax({
			url: `/empresa/configuracion/calculos_nomina/editcalculo/${id}`,
			method: 'GET',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function(response) {
				if (response) {
          tipo = response.tipo == 1 ? 'porcentaje' : 'valor';
					valor = response.valor;
					id = parseInt(id);
					$('#modalCalculoFijo').html('');
					$('#modalCalculoFijo').append(`<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="exampleModalLabel">Editar ${tipo}</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<div class="form-group">
										<label for="observaciones">Ingrese el valor sobre el que se calculará el ${tipo}</label>
                    <input type="number" name="valor" id="valor-${id}" class="form-control" value="${valor}">
									</div>
									<div id="custom-target"></div>
								</div>
								<div class="modal-footer">
									<a  class="btn btn-secondary" data-dismiss="modal">Cerrar</a>
									<a  class="btn btn-primary text-white" onclick="guardarCalculo(${id}, '${tipo}')">Guardar</a>
								</div>
							</div>
						</div>`);

          if(tipo == 'porcentaje'){
            $('#valor-'+id).on('keyup', function(){
              let value = parseFloat($(this).val());
              if(value > 100){
                $(this).val(100)
              }else if(value < 0){
                $(this).val(0);
              }
            });
          }

					$('#modalCalculoFijo').modal('show');
				}
			}
		});
	}

	function guardarCalculo(id, tipo) {
    Swal.fire({
      type: 'warning',
      title: 'ALERTA',
      text: 'Si actualiza los valores, se actualizará la nómina del mes actual, siempre y cuando no hayan documetos emitidos',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Aceptar',
    }).then((result) => {
      if (result.value) {
        var valor = $('#valor-' + id).val();

        if(tipo == 'porcentaje'){
          if(valor > 100){
            valor = 100;
          }else if(valor < 0){
            valor = 0;
          }
        }

        if (window.location.pathname.split("/")[1] === "software") {
        var url='/software/empresa';
      }else{
          var url = '/empresa';
      }

        $.ajax({
          url: url+`/configuracion/calculos_nomina/storecalculo`,
          method: 'POST',
          beforeSend: function() {
            cargando(true);
          },
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            id: id,
            valor: valor
          },
          success: function(response) {
            cargando(false);
            if (response) {
              console.log(response);
              $(`#tdvalor-${id}`).empty();
              $('#modalCalculoFijo').modal('hide');
              let valorFormateado = response.valorFormateado;
              $(`#tdvalor-${id}`).append(valorFormateado);
              // $(`#observacion-parrafo-${id}`).attr('title', observacion);
              // getDataTable();
            }
          }
        });
      }else{
        $('#modalCalculoFijo').modal('hide');
      }
    })
	}
</script>
@endsection
