@extends('layouts.app')

@section('boton')
  @if(auth()->user()->modo_lectura())
      <div class="alert alert-warning text-left" role="alert">
          <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
          <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
      </div>
  @else
    <a href="{{route('notascredito.imprimir.nombre',['id' => $nota->nro, 'name'=> 'Nota Credito No. '.$nota->nro.'.pdf'])}}" target="_blanck" class="btn btn-outline-primary btn-sm "title="Imprimir" target="_blank"><i class="fas fa-print"></i> Imprimir</a>
    <a href="{{route('notascredito.enviar',$nota->nro)}}" class="btn btn-outline-primary btn-sm "title="Enviar"><i class="far fa-envelope"></i> Enviar por Correo Al Cliente</a>
    <a href="{{route('notascredito.edit',$nota->nro)}}" class="btn btn-outline-primary btn-sm "title="Imprimir" target="_blank"><i class="fas fa-edit"></i> Editar</a>
    <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
      <button type="button" class="close" data-dismiss="alert">×</button>
      <strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
    </div>
  @endif
@endsection   

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
  <div class="row card-description">
  <div class="col-md-12">
    <div class="table-responsive">
      <table class="table table-striped table-bordered table-sm info">
        <tbody>
          <tr>
            <th width="20%">Código</th> <td>{{$nota->nro}}</td>
          </tr>
          <tr>
            <th>Tipo de nota crédito</th> <td>{{$nota->tipo()}}</td>
          </tr>
          <tr>
            <th>Cliente</th> <td><a href="{{route('contactos.show',$nota->cliente()->id)}}" target="_blanck">{{$nota->cliente()->nombre}} {{$nota->cliente()->apellidos()}}</a></td>
          </tr>
          <tr>
            <th>Creación</th> <td>{{date('d/m/Y', strtotime($nota->fecha))}}</td>
          </tr>
          <tr>
            <th>Total</th> <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($nota->total()->total)}}</td>
          </tr>
          <tr>
            <th>Por aplicar</th> <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($nota->por_aplicar())}}</td>
          </tr>
          <tr>
            <th>Observaciones </th> <td>{{$nota->observaciones}}</td>
          </tr>
          <tr>
            <th>Notas</th> <td>{{$nota->notas}}</td>
          </tr>
          <tr>
            <th>Lista de precios</th>
            <td>{{$nota->lista_precios()}}</td>
          </tr>
          <tr>
            <th>Bodega</th>
            <td>{{$nota->bodega()}}</td>
          </tr>          
        </tbody>
      </table>
    </div>
  </div>
  </div>

    <div class="paper mx-3">
        <!-- Membrete -->
        
        <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>
		
        <!-- Desgloce -->
        <div class="row" style="padding: 2% 6%;">
            <div class="col-md-12 fact-table">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm pagos"  width="100%">
                        <thead>
                            <tr>
                                <th>Nro</th>
                                <th width="20%">Cuenta contable</th>
                                <th width="20%">tecrero</th>
                                <th width="20%">Detalle</th>
                                <th width="20%">Descripción</th>
                                <th width="10%">Débito</th>
                                <th width="10%">Crédito</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = 1 @endphp
                            @foreach($movimientos as $mov)
                                <tr>
                                    <td>{{$i}}</td>
                                    <td>{{$mov->codigo_cuenta}} - {{$mov->cuenta()->nombre}}</td>
                                    <td>{{$mov->cliente->nombre}}</td>
                                    <td>{{$mov->asociadoA()}}</td>
                                    <td>{{$mov->descripcion}}</td>
                                    <td>{{$mov->debito}}</td>
                                    <td>{{$mov->credito}}</td>
                                </tr>
                                @php $i++; @endphp
                            @endforeach
                            <tr>
                                <td style="border:none;"></td>
                                <td style="border:none;"></td>
                                <td style="border:none;"></td>
                                <td style="border:none;"></td>
                                <td style="border:none;">Total:</td>
                                <td>{{$mov->totalDebito()->total}}</td>
                                <td>{{$mov->totalCredito()->total}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
