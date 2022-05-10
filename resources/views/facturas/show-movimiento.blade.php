@extends('layouts.app')

@section('boton')

@endsection   

@section('content')
    <style>
        .paper:before {
            top: 0px;
            right: 0px;
            border-color: #f9fafd #f9f9f9 #eaedf7 #eaedf7;
        }

    .desgloce.mov tbody > tr > td {
    padding: 1.5% 3px !important;
    border: 1px solid #ccc;
    }
    </style>
       
    <!-- BANNER DE VALORES -->
    <div class="card-body">
        <div class="row" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:2% !important;">
            <div class="offset-md-1 offset-xl-1 offset-lg-1 col-xl-2 col-lg-2 col-md-2 col-sm-12  stretch-card" style="border: 1px solid #fff !important;">
                <div class="card card-statistics" style="background-color: #fff !important;">
                    <div class="clearfix">
                        <div class="float-center">
                            <p class="mb-0 text-center">Valor total</p>
                            <div class="fluid-container">
                                <h4 class="font-weight-medium text-center mb-0">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card" style="border: 1px solid #fff !important;">
                <div class="card card-statistics" style="background-color: #fff !important;">
                    <div class="clearfix">
                        <div class="float-center">
                            <p class="mb-0 text-center">Retenido</p>
                            <div class="fluid-container">
                                <h4 class="font-weight-medium text-center mb-0">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->retenido(true))}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card" style="border: 1px solid #fff !important;">
      <div class="card card-statistics" style="background-color: #fff !important;">
          <div class="clearfix">
            <div class="float-center">
              <p class="mb-0 text-center">Devoluciones</p>
              <div class="fluid-container">
                <h4 class="font-weight-medium text-center mb-0">{{Auth::user()->empresa()->moneda}}                   {{App\Funcion::Parsear($factura->devoluciones())}}</h4>
              </div>
            </div>
          </div>
      </div>
    </div>
    <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card" style="border: 1px solid #fff !important;">
      <div class="card card-statistics" style="background-color: #fff !important;">
          <div class="clearfix">
            <div class="float-center">
              <p class="mb-0 text-center">Cobrado</p>
              <div class="fluid-container">
                <h4 class="font-weight-medium text-center mb-0 text-success">{{Auth::user()->empresa()->moneda}} 
                {{App\Funcion::Parsear($factura->pagado())}}</h4>
              </div>
            </div>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card" style="border: 1px solid #fff !important;">
      <div class="card card-statistics" style="background-color: #fff !important;">
          <div class="clearfix">
            <div class="float-center">
              <p class="mb-0 text-center">Por cobrar</p>
              <div class="fluid-container">
                <h4 class="font-weight-medium text-center mb-0 text-danger">{{Auth::user()->empresa()->moneda}} 
                {{App\Funcion::Parsear($factura->porpagar())}}</h4>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- FIN BANNER DE VALORES -->

@if(Session::has('success') || Session::has('error'))
  @if(Session::has('success'))
  <div class="alert alert-success alert-view-show">
    {{Session::get('success')}}
  </div>
  @endif

  @if(Session::has('error'))
  <div class="alert alert-danger alert-view-show">
    {{Session::get('error')}}
  </div>
  @endif
  <script type="text/javascript">
    setTimeout(function(){ 
        $('.alert').hide();
        $('.active_table').attr('class', ' ');
    }, 5000);
  </script>
@endif

    <div class="paper mx-3">
        <div class="ribbon {{$factura->estatus()}}"><span>{{$factura->estatus()}}</span></div>
        <!-- Membrete -->
        <div class="row">
            <div class="col-md-4 text-center">
                <img class="img-responsive" src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo)}}" alt="" width="50%">
            </div>
            <div class="col-md-4 text-center padding1">
                <h4>{{Auth::user()->empresa()->nombre}}</h4>
                <p>{{Auth::user()->empresa()->tip_iden('mini')}} {{Auth::user()->empresa()->nit}}  @if(Auth::user()->empresa()->dv != null || Auth::user()->empresa()->dv == 0) - {{Auth::user()->empresa()->dv}} @endif<br> {{Auth::user()->empresa()->email}}</p>
            </div>
            <div class="col-md-4 text-center padding1" >
                <h4><b class="text-primary">No. </b> {{$factura->codigo}}</h4>  @if(isset($factura->nro_remision))<h4><b class="text-primary">No. Remision </b> {{$factura->nro_remision}}</h4> @endif
            </div>
        </div>
        <!--Cliente-->
        
        
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
                    <td>{{$mov->codigo_cuenta}}</td>
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
