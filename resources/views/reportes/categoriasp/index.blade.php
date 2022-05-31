@extends('layouts.app')
@section('content')
    <input type="hidden" id="primera" value="{{$request->date ? $request->date['primera'] : ''}}">
    <input type="hidden" id="ultima" value="{{$request->date ? $request->date['ultima'] : ''}}">
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <form id="form-reporte">

        <div class="row card-description">
            <p></p>
        </div>
        <div class="row card-description">
            <div class="form-group col-md-2">
                <label></label>
                <select class="form-control selectpicker" name="categoria" title="Seleccione" data-live-search="true" data-size="5">
                @foreach($categorias as $categoria)
                  		@if($categoria->estatus==1)
                            <option {{$request->categoria==$categoria->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria->id?'selected':'')}} value="{{$categoria->id}}">{{$categoria->nombre}}</option>
                        @endif
                        @foreach($categoria->hijos(true) as $categoria1)
                  			@if($categoria1->estatus==1)
	                  			<option {{$request->categoria==$categoria1->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria1->id?'selected':'')}} value="{{$categoria1->id}}">{{$categoria1->nombre}} - {{$categoria1->codigo}}</option>
	                  		@endif
	                  		@foreach($categoria1->hijos(true) as $categoria2)
	                  			@if($categoria2->estatus==1)
		                  			<option {{$request->categoria==$categoria2->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria2->id?'selected':'')}} value="{{$categoria2->id}}">{{$categoria2->nombre}} - {{$categoria2->codigo}}</option>
		                  		@endif
		                  		@foreach($categoria2->hijos(true) as $categoria3)
		                  			@if($categoria3->estatus==1)
			                  			<option {{$request->categoria==$categoria3->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria3->id?'selected':'')}} value="{{$categoria3->id}}">{{$categoria3->nombre}} - {{$categoria3->codigo}}</option>
			                  		@endif
			                  		@foreach($categoria3->hijos(true) as $categoria4)
			                  			@if($categoria4->estatus==1)
				                  			<option {{$request->categoria==$categoria4->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria4->id?'selected':'')}} value="{{$categoria4->id}}">{{$categoria4->nombre}} - {{$categoria1->codigo}}</option>
				                  		@endif
			                  		@endforeach
		                  		@endforeach
	                  		@endforeach
                  		@endforeach
	  				@endforeach
                </select>   
            </div>
            <div class="form-group col-md-2">
		    <label></label>
		    <select class="form-control selectpicker" name="fechas" id="fechas">
		    	<optgroup label="Presente">
				    <option value="0">Hoy</option>
				    <option value="1">Este Mes</option>
				    <option value="2">Este Año</option>
			  	</optgroup>
		    	<optgroup label="Anterior">
				    <option value="3">Ayer</option>
				    <option value="4">Semana Pasada</option>
				    <option value="5">Mes Anterior</option>
				    <option value="6">Año Anterior</option>
			  	</optgroup>
		    	<optgroup label="Manual">
				    <option value="7">Manual</option>
			  	</optgroup>
                <optgroup label="Todas">
                    <option value="8">Todas</option>
                </optgroup>
		    </select>
	  	</div>
	  	<div class="form-group col-md-4">
			<div class="row">
				<div class="col-md-6">
					<label>Desde <span class="text-danger">*</span></label>
					<input type="text" class="form-control"  id="desde" value="{{$request->fecha}}" name="fecha" required="" >
				</div>
				<div class="col-md-6">
					<label >Hasta <span class="text-danger">*</span></label>
	  				<input type="text" class="form-control" id="hasta" value="{{$request->hasta}}" name="hasta" required="">
				</div>

			</div>
	  	</div>
            <div class="form-group col-md-4" style="    padding-top: 2%;">
                <button type="button" id="generar" class="btn btn-outline-secondary">Generar Reporte</button>
                <button type="button" id="exportar" class="btn btn-outline-secondary">Exportar a Excel</button>
            </div>
        </div>

        <!-- BANNER DE VALORES -->
        <style type="text/css"> .card{ background: #f9f1ed !important;}</style>
        <div class="card-body">
            <p><b>Descripción: </b>' {{$categoriadata->descripcion}} '</p>
            <div class="row" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:2% !important;">
                <div class="offset-md-1 offset-xl-1 offset-lg-1 col-xl-2 col-lg-2 col-md-2 col-sm-12  stretch-card">
                    <div class="card card-statistics" style="background-color: #fff !important;">
                        <div class="clearfix">
                            <div class="float-center">
                                <p class="mb-0 text-center">Código</p>
                                <div class="fluid-container">
                                    <h4 class="font-weight-medium text-center mb-0">
                                        {{$codigo}}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card">
                    <div class="card card-statistics" style="background-color: #fff !important;">
                        <div class="clearfix">
                            <div class="float-center">
                                <p class="mb-0 text-center">Nombre</p>
                                <div class="fluid-container">
                                    <h4 class="font-weight-medium text-center mb-0">
                                        {{$categoriadata->nombre}}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card">
                    <div class="card card-statistics" style="background-color: #fff !important;">
                        <div class="clearfix">
                            <div class="float-center">
                                <p class="mb-0 text-center"># Pagos</p>
                                <div class="fluid-container">
                                    <h4 class="font-weight-medium text-center mb-0">
                                        {{$cantidadPagos}}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card">
                    <div class="card card-statistics" style="background-color: #fff !important;">
                        <div class="clearfix">
                            <div class="float-center">
                                <p class="mb-0 text-center">Ingresos</p>
                                <div class="fluid-container">
                                    <h4 class="font-weight-medium text-center mb-0 text-success">
                                        {{Auth::user()->empresa()->moneda}}
                                        {{App\Funcion::Parsear($ingresos[$request->categoria]['total'])}}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card">
                    <div class="card card-statistics" style="background-color: #fff !important;">
                        <div class="clearfix">
                            <div class="float-center">
                                <p class="mb-0 text-center">Egresos</p>
                                <div class="fluid-container">
                                    <h4 class="font-weight-medium text-center mb-0 text-danger">
                                        {{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($egresos[$request->categoria]['total'])}}
                                        </td>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- FIN BANNER DE VALORES -->
        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table table-striped table-hover " id="table-reporte-categorias">
                    <thead class="thead-dark">
                    <tr>
                        <th>Referencia</th>
                        <th>Cliente</th>
                        <th>Detalle</th>
                        <th>Fecha</th>
                        <th>Valor</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($pagos as $pago)
                            <tr>
                                <td><div class="elipsis-short"><a href="{{route('pagos.show',$pago->nro)}}">{{$pago->nro}}</a></div></td>
                                <td>@if($pago->beneficiario())<a href="{{route('contactos.show',$pago->beneficiario()->id)}}" target="_blanck">{{$pago->beneficiario()->nombre}}@endif</a></td>
                                <td><div class="elipsis-short">{{$pago->producto}}</div></td>
                                <td>{{$pago->fecha}}</td>
                                <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($pago->valor)}}</td>

                            </tr>
                        @endforeach

                    </tbody>
                </table>    
                <div class="text-right">
                </div>

            </div>
        </div>
    </form>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.categoriasp')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.categorias')}}">
@endsection
