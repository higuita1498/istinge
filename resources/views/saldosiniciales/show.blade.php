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
       
    
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
            <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
        </div>
    @else
        @if(auth()->user()->rol <> 8)
          
        @endif
    @endif
    

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
                <h4><b class="text-primary">Saldo inicial No.  </b> {{$nro}}</h4>
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
