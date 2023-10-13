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

@if(Session::has('error'))
  <div class="alert alert-danger" >
    {{Session::get('error')}}
  </div>

  <script type="text/javascript">
    setTimeout(function(){
        $('.alert').hide();
        $('.active_table').attr('class', ' ');
    }, 5000);
  </script>
@endif

@if(Session::has('success-newcontact'))
<div class="alert alert-success" style="text-align: center;">
  {{Session::get('success-newcontact')}}
</div>

<script type="text/javascript">
  setTimeout(function(){
    $('.alert').hide();
    $('.active_table').attr('class', ' ');
  }, 5000);
</script>
@endif

<style>
    #titulo{
        display:none;
    }
</style>

<div class="paper">
  <!-- Membrete -->
	<div class="row">
    <div class="col-md-4 text-center align-self-center">
      <img class="img-responsive" src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo)}}" alt="" width="50%">
    </div>
    <div class="col-md-4 text-center align-self-center">
      <h4>{{Auth::user()->empresa()->nombre}}</h4>
      <p>{{Auth::user()->empresa()->tip_iden('mini')}} {{Auth::user()->empresa()->nit}} @if(Auth::user()->empresa()->dv != null || Auth::user()->empresa()->dv == 0) - {{Auth::user()->empresa()->dv}} @endif<br> {{Auth::user()->empresa()->email}}</p>
    </div>
    <div class="col-md-4 text-center align-self-center" >
      {{-- <h4><b class="text-primary">No. </b> {{$nro->prefijo}}{{$nro->inicio}}</h4> --}}
    </div>
	</div>
	<hr>
  <!--Formulario Facturas-->
	<form method="POST" action="{{ route('facturas.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" >
		{{ csrf_field() }}

    @include('facturas.includes.comment-descuento', ['comentario2' => null ])

        <input type="hidden" value="1" name="fact_vent" id="fact_vent">
		<div class="row text-right">
			<div class="col-md-5">
  			<div class="form-group row">
  				<label class="col-sm-4 col-form-label">Tecnico <span class="text-danger">*</span></label>
	  			<div class="col-sm-8">
            <div class="input-group">
              <select class="form-control selectpicker" name="cliente" id="cliente" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="identificacion(this.value)">
                @foreach($clientes as $client)
                  <option {{old('cliente')==$client->id?'selected':''}} {{$cliente==$client->id?'selected':''}}  value="{{$client->id}}">{{$client->nombres}}</option>
                @endforeach
              </select>
              <div class="input-group-append" >
                <span class="input-group-text nopadding">
                  <a class="btn btn-outline-secondary btn-icons" title="Actualizar" onclick="contactos('{{route('contactos.clientes.json')}}', 'cliente');" style="margin-left: 8%;"><i class="fas fa-sync"></i></a>
                </span>
              </div>
            </div>
            {{-- <p class="text-left nomargin">
              <a href="#" id="contacto">
                <i class="fas fa-plus"></i> Nuevo Contacto
              </a>
            </p> --}}
	  			</div>
          <span class="help-block error">
          	<strong>{{ $errors->first('cliente') }}</strong>
          </span>
  		  </div>

	  		<div class="form-group row" id="miDiv" style="display: none">
  				<label class="col-sm-4 col-form-label">Correo electronico</label>
	  			<div class="col-sm-8">
	  				<input type="text" class="form-control" readonly="" id="ident" value="">
	  			</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Fecha <span class="text-danger">*</span> <a><i data-tippy-content="Fecha en la que se realiza la factura de venta" class="icono far fa-question-circle"></i></a></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control"  id="fecha" value="{{$fecha}}" name="fecha" disabled=""  >
                </div>
            </div>
  			{{-- <div class="form-group row">
  				<label class="col-sm-4 col-form-label">Teléfono</label>
	  			<div class="col-sm-8">
	  				<input type="text" class="form-control" readonly="" id="telefono" value="">
	  			</div>
  			</div> --}}
            {{-- <div class="form-group row">
                <label class="col-sm-4 col-form-label">Vendedor <a><i data-tippy-content="Vendedor asociado a la factura de venta, puedes agregar nuevos vendedores haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a></label>
                <div class="col-sm-8">
                    <select name="vendedor" id="vendedor" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required>
                        @foreach($vendedores as $vendedor)
                            <option value="{{$vendedor->id}}" selected="">{{$vendedor->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div> --}}
            {{-- <div class="form-group row">
              <label class="col-sm-4 col-form-label">Periodo a cobrar</label>
              <div class="col-sm-8">
                  <select name="periodo_cobrado" id="periodo_cobrado" class="form-control selectpicker " title="Seleccione" data-live-search="false" data-size="5" required>
                      <option value="1" {{$empresa->periodo_cobrado==1 ? 'selected' : ''}}>Mes anticipado</option>
                      <option value="2" {{$empresa->periodo_cobrado==2 ? 'selected' : ''}}>Mes vencido</option>
                  </select>
              </div>
          </div> --}}
            {{-- <div class="form-group row">
              <label class="col-sm-4 col-form-label">Forma de Pago <a><i data-tippy-content="Elige a que cuenta ira enlazado el movimiento contable" class="icono far fa-question-circle"></i></a></label>
              <div class="col-sm-8">
                  <select name="relacion" id="relacion" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5">
                      @foreach($relaciones as $relacion)
                          <option value="{{$relacion->id}}">{{$relacion->codigo}} - {{$relacion->nombre}}</option>
                      @endforeach
                  </select>
              </div>
          </div> --}}

        <div class="form-group row">
          {{-- <p class="col-sm-4 " style="background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 20px;color: #fff;padding: 1%;text-align: center;"><a onclick="toggediv('masopciones');">Más opciones</a></p> --}}
        </div>
		  </div>

		  <div class="col-md-5 offset-md-2">

                {{-- <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Plazo <a><i data-tippy-content="Tiempo maximo para realizar el pago, puedes agregar nuevos plazos haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a></label>
                    <div class="col-sm-8">
                        <select name="plazo" id="plazo" class="form-control " title="Seleccione">
                @foreach($terminos as $termino)
                    <option value="{{$termino->id}}" dias="{{$termino->dias}}">{{$termino->nombre}}</option>
                @endforeach
                        </select>
                    </div>
                </div> --}}
  			{{-- <div class="form-group row">
  				<label class="col-sm-4 col-form-label">Vencimiento <span class="text-danger">*</span><a><i data-tippy-content="Fecha de vencimiento de la factura, se calcula automaticamente si se define el plazo" class="icono far fa-question-circle"></i></a></label>
	  			<div class="col-sm-8">
	  				<input type="text" class="form-control datepicker" id="vencimiento" value="{{$fecha}}" name="vencimiento" disabled="">
	  			</div>
  			</div> --}}
        {{-- <div class="form-group row">
          <label class="col-sm-4 col-form-label">Bodega <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select name="bodega" id="bodega" class="form-control"  required="">
              @foreach($bodegas as $bodega)
                <option value="{{$bodega->id}}">{{$bodega->bodega}}</option>
              @endforeach
            </select>
          </div>
        </div> --}}
        {{-- <div class="form-group row">
              <label class="col-sm-4 col-form-label">Tipo de operación <span class="text-danger">*</span></label>
              <div class="col-sm-8">
                  <select name="tipo_operacion" id="tipo_operacion" class="form-control selectpicker " data-live-search="true" data-size="5" required="" onchange="operacion(this.value)">
                      <option value="1" @if(isset($tipo_operacion->tipo)){{$tipo_operacion->tipo==1?'selected':'selected'}}@endif>Estandar</option>
                      <option value="2" @if(isset($tipo_operacion->tipo)){{$tipo_operacion->tipo==2?'selected':''}}@endif>Factura de servicios AIU</option>
                  </select>
              </div>
          </div>
          <div class="form-group row">
              <label class="col-sm-4 col-form-label">Tipo de documento <span class="text-danger">*</span><a><i data-tippy-content="Elige el nombre que desees dar a tus documentos" class="icono far fa-question-circle"></i></a></label>
              <div class="col-sm-8">
                  <select name="documento" id="documento" class="form-control selectpicker " data-live-search="true" data-size="5" required="">
                      <option value="1" @if(isset($tipo_documento->tipo)){{$tipo_documento->tipo==1?'selected':''}}@endif>Factura de Venta</option>
                      <option value="3" @if(isset($tipo_documento->tipo)){{$tipo_documento->tipo==4?'selected':''}}@endif>Cuenta de Cobro</option>
                  </select>
              </div>
          </div> --}}

        @if(auth()->user()->empresa()->estado_dian == 1)
            <div class="form-group row">
              <label class="col-sm-4 col-form-label">Orden de compra<a><i data-tippy-content="Número de orden de compra o servicio (dejar vacio si no tiene número)" class="icono far fa-question-circle"></i></a></label>
              <div class="col-sm-8">
                {{-- <input type="text" class="form-control" name="ordencompra" id="ordencompra" value=""> --}}
              </div>
            </div>
        @endif
  		</div>
    </div>
    <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>
		<div class="row text-right" style="display: none;" id="masopciones">
			<div class="col-md-5">
				<div class="form-group row">
				  {{-- <label class="col-sm-4  col-form-label">Observaciones <br> <small>No visible en la factura de venta</small></label> --}}
    			<div class="col-sm-8">
    				{{-- <textarea  class="form-control form-control-sm min_max_100" name="observaciones"></textarea> --}}
    			</div>
		    </div>
			</div>
      <div class="col-md-4 offset-md-3">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Lista de Precios <a><i data-tippy-content="Lista de precios asociada a la factura de venta, puedes agregar nuevas listas de precio haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <select name="lista_precios" id="lista_precios" class="form-control selectpicker">
              @foreach($listas as $lista)
                <option value="{{$lista->id}}">{{$lista->nombre()}} </option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
		</div>
    <hr>
    <div id="notasaui"></div>
    <!-- Desgloce -->
        <div id="noMore">
            @if($producto)
                @if($producto->inventario()==0)
                    @php
                        $alert = '<div class="alert alert-warning alert-dismissible fade show" id="alertInventario" role="alert">
            <strong>¡Atención!</strong> Usted esta intentando facturar un producto que no tiene unidades en inventario.
             ¿Desea continuar? <button type="button" class="close" data-dismiss="alert" aria-label="Close">
             <span aria-hidden="true">&times;</span> </button></div>';
                        echo $alert;
                    @endphp
                    <script>
                        setTimeout(function(){
                            $('#alertInventario').remove();
                        }, 5000);
                    </script>
                @endif
            @endif
        </div>
        <div class="fact-table">
		<div class="row">
			<div class="col-md-12">
        <table class="table table-striped table-sm" id="table-form" width="100%">
        	<thead class="thead-dark">
        		<tr>
              <th width="5%"></th>
        			<th width="24%">Ítem/Referencia</th>
              <th width="10%">Referencia</th>
              {{-- <th width="12%">Precio</th> --}}
        			{{-- <th width="5%">Desc %</th> --}}
        			{{-- <th width="12%">Impuesto</th> --}}
        			<th width="13%">Descripción</th>
        			<th width="7%">Cantidad</th>
        			{{-- <th width="10%">Total</th> --}}
              <th width="2%"></th>
        		</tr>
        	</thead>
            <tbody>
              <tr id="1">
                <td class="no-padding">
                  <a class="btn btn-outline-secondary btn-icons" title="Actualizar" onclick="inventario('1');"><i class="fas fa-sync"></i></a>
                </td>
                <td  class="no-padding" style="padding-top: 2% !important;">
                  @if($producto) <input type="hidden" id="producto_inv" value="true">   @endif
                  <select class="form-control selectpicker items_inv"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item1" onchange="rellenar(1, this.value);" required="">

                   @foreach($inventario as $item)
                   <option value="{{$item->id}}" @if($producto) {{$producto->id==$item->id?'selected':''}}  @endif>{{$item->producto}} - ({{$item->ref}})</option>
                   @endforeach
                  </select>
                  <p class="text-left nomargin">
                    <a href="" data-toggle="modal" data-target="#modalproduct" class="modalTr d-none" tr="1">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </a>&nbsp;
                  </p>
                </td>
                <td>
                    <div class="resp-refer">
                  <input type="text" class="form-control form-control-sm" id="ref1" name="ref[]" placeholder="Referencia" required="">
                  </div>
                </td>
                <td class="monetario">
                    <div class="resp-precio">
                  {{-- <input type="number" class="form-control form-control-sm" id="precio1" name="precio[]" placeholder="Precio Unitario" onkeyup="total(1)" required="" maxlength="24" min="0"> --}}
                    </div>
                </td>
                <td>
                  {{-- <input type="text" class="form-control form-control-sm" id="desc1" name="desc[]" placeholder="%" onkeyup="total(1)" > --}}
                </td>
                <td>
                <select class="form-control form-control-sm selectpicker" name="impuesto[]" id="impuesto1" title="Impuesto" onchange="totalall();" required="">
                  {{-- @foreach($impuestos as $impuesto)
                    <option value="{{$impuesto->id}}" porc="{{$impuesto->porcentaje}}">{{$impuesto->nombre}} - {{$impuesto->porcentaje}}%</option>
                  @endforeach --}}
                </select>
              </td>
              <td  style="padding-top: 1% !important;">
                  <div class="resp-descripcion">
                <textarea  class="form-control form-control-sm" id="descripcion1" name="descripcion[]" placeholder="Descripción" ></textarea>
                </div>
              </td>
              <td>
                <input type="number" class="form-control form-control-sm" id="cant1" name="cant[]" placeholder="Cantidad" onchange="total(1);" min="1" required="">
                <p class="text-danger nomargin" id="pcant1"></p>
              </td>
              <td>
                  <div class="resp-total">
                {{-- <input type="text" class="form-control form-control-sm text-right" id="total1" value="0" disabled=""> --}}
                </div>
              </td>
              <td>
                <button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar(1);">X</button>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="alert alert-danger" style="display: none;" id="error-items"></div>
      </div>
		</div>
    <button class="btn btn-outline-primary" onclick="createRow();" type="button" style="margin-top: 2%">Agregar línea</button>
    {{-- <div class="row"  style="margin-top: 5%; margin-left:0px;">
          <div class="col-md-5 no-padding">
        <h5>RETENCIONES</h5>
            <table class="table table-striped table-sm" id="table-retencion">
              <thead class="thead-dark">
                <th width="60%">Tipo de Retención</th>
                <th width="35%">Valor</th>
                <th width="5%"></th>
              </thead>
              <tbody>
              </tbody>
            </table>
            <button class="btn btn-outline-primary" onclick="CrearFilaRetencion();" type="button" style="margin-top: 2%;">Agregar Retención</button><a><i data-tippy-content="Agrega nuevas retenciones haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a>
          </div>
          <div class="col-md-7"> --}}
            {{-- <h5>FORMAS DE PAGO <a><i data-tippy-content="Elige a que cuenta ira enlazado el movimiento contable" class="icono far fa-question-circle"></i></a></h5>
                <table class="table table-striped table-sm" id="table-formaspago">
                  <thead class="thead-dark">
                    <th width="50%">Cuenta</th>
                    <th width="25%">Cruce</th>
                    <th width="20%" class="no-padding">Valor</th>
                    <th width="5%"></th>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
                <div class="row">
                  <div class="col-md-6">
                    <button class="btn btn-outline-primary" onclick="CrearFilaFormaPago();" type="button" style="margin-top: 2%;">Agregar forma de pago</button><a><i data-tippy-content="Agrega nuevas formas de pago haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a>
                  </div>
                  <div class="col-md-6 d-flex justify-content-between pt-3">
                    <h5>Total:</h5>
                    <span>$</span><span id="anticipototal">0</span>
                  </div>
                  <div class="col-md-12">
                    <span class="text-danger" style="font-size:12px"><strong>El total de las formas de pago debe coincidir con el total neto</strong></span>
                  </div>
                </div>
              </div> --}}
    {{-- </div> --}}
    <!-- Totales -->
        <div class="row" style="margin-top: 10%;">
            <div class="col-md-4 offset-md-8">
                <table class="text-right widthtotal" >
                    {{-- <tr>
                        <td width="40%">Subtotal</td>
                        <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal">0</span></td>
                        <input type="hidden" id="subtotal_categoria_js" value="0">
                    </tr>
                    <tr>
                        <td>Descuento</td><td id="descuento">{{Auth::user()->empresa()->moneda}} 0</td>
                    </tr>
                </table>
                <table class="text-right widthtotal"  style="width: 100%" id="totales">
                    <tr style="display: none">
                        <td width="40%">Subtotal</td>
                        <td >{{Auth::user()->empresa()->moneda}} <span id="subsub">0</span></td>
                    </tr>
                    <tr>
                        <td width="40%">Subtotal</td>
                        <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal2">0</span></td>

                    </tr> --}}
                </table>

                <table  class="text-right widthtotal"  id="totalesreten" style="width: 100%">
                    <tbody></tbody>
                </table>


                <hr>
                <table class="text-right widthtotal" style="font-size: 24px !important;">
                    <tr>
                        {{-- <td width="40%">TOTAL A PAGAR</td>
                        <td>{{Auth::user()->empresa()->moneda}} <span id="total">0</span></td> --}}
                    </tr>
                </table>
            </div>
            </div>
        </div>
    <!-- Terminos y Condiciones -->
    <div class="row" style="margin-top: 5%; padding: 3%; min-height: 180px;">
      {{-- <div class="col-md-8 form-group">
        <label class="form-label">Términos y Condiciones <a><i data-tippy-content="Agrega los términos y condiciones para tus clientes sobre las ventas generadas" class="icono far fa-question-circle"></i></a></label>
        <textarea  class="form-control min_max_100" name="term_cond">{{Auth::user()->empresa()->terminos_cond}}</textarea>
      </div> --}}
      <div class="col-md-12 form-group">
        <label class="form-label">Notas <a><i data-tippy-content="Agrega la información importante que verán tus cintes en la factura de venta" class="icono far fa-question-circle"></i></a>
        </label>
        <textarea  class="form-control form-control-sm min_max_100" name="notas">{{Auth::user()->empresa()->notas_fact}}</textarea>
      </div>
    </div>
      <div class="col-md-12"><small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small></div>
  	<hr>
    <!--Botones Finales -->
    {{-- <div class="row">
    </div>
		<div class="row d-none" >
			<div class="col-md-2">
        <div class="form-check form-check-flat">
          <label class="form-check-label">
            <input type="checkbox" class="form-check-input" name="pago" id="pago" value="1"> Agregar Pago
          <i class="input-helper"></i></label>
        </div>
			</div>
      <div class="col-md-2">
        <div class="form-check form-check-flat">
          <label class="form-check-label">
            <input type="checkbox" class="form-check-input" name="new" id="new" value="1"> Crear una nueva
          <i class="input-helper"></i></label>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-check form-check-flat">
          <label class="form-check-label">
            <input type="checkbox" class="form-check-input" name="print" value="1">Imprimir
          <i class="input-helper"></i></label>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-check form-check-flat">
          <label class="form-check-label">
            <input type="checkbox" class="form-check-input" name="send" value="1">Enviar por correo
          <i class="input-helper"></i></label>
        </div>
      </div>
      </div> --}}


      <div class="row ">
        <div class="col-sm-12 text-right" style="padding-top: 1%;">
          <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
          <a href="{{route('facturas.index')}}" class="btn btn-outline-secondary">Cancelar</a>
        </div>

      </div>

  </form>
    <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
    @foreach ($impuestos as $impuesto)
      <input type="hidden" id="hddn_imp_{{$impuesto->id}}" value="{{$impuesto->tipo}}">
    @endforeach
    <input type="hidden" id="allproductos" value="{{json_encode($inventario)}}">
    <input type="hidden" id="url" value="{{url('/')}}">
    <input type="hidden" id="jsonproduc" value="{{route('inventario.all')}}">
    <input type="hidden" id="simbolo" value="{{Auth::user()->empresa()->moneda}}">
    <input type="hidden" id="retenciones" value="{{json_encode($retenciones)}}">
    <input type="hidden" id="formaspago" value="{{json_encode($relaciones)}}">
    {{-- VARIABLE DE SALDO A FAVOR DEL CLIENTE --}}
    <input type="hidden" id="saldofavorcliente" name="saldofavorcliente">
</div>

  {{-- Modal contacto nuevo --}}
    <div class="modal fade" id="contactoModal" role="dialog">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" id="modal-titlec"></h4>
          </div>
          <div class="modal-body" id="modal-bodyc">
            {{--@include('contactos.modal.modal')--}}
          </div>
         {{-- <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>--}}
        </div>
      </div>
    </div>

       {{-- Modal Editar Direccion Contacto--}}
    <div class="modal fade" id="modaleditDirection" role="dialog"  data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Editar información Básica.</h4>
          </div>
          <div class="modal-body">
        {{-- <form method="POST" action="" style="padding: 2% 3%;" role="form"
        class="forms-sample border-btm marginb" novalidate id="form-editDirection"> --}}
        <div class="container">
          <div id="conte-modalesedit"></div>
        </div>

      {{-- </form> --}}

    </div>
    <div class="modal-footer">
      {{-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> --}}
    </div>
  </div>
</div>
</div>
{{-- /Modal Editar --}}

{{-- Modal Nuevo producto  --}}
  <div class="modal fade" id="modalproduct" role="dialog">
      <div class="modal-dialog modal-lg">
          <input type="hidden" id="trFila" value="0">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h4 class="modal-title"></h4>
              </div>
              <div class="modal-body">
                  @include('inventario.modal.create')
              </div>

          </div>
      </div>
  </div>
  {{--/Modal Nuevo producto  --}}
@endsection

@section('scripts')

        <script>
        function identificacion(id) {
            console.log(id);
            var div = document.getElementById('miDiv');
                if (div.style.display === 'none' || div.style.display === '') {
                    div.style.display = 'block'; // Mostrar el div
                } else {
                    div.style.display = 'none'; // Ocultar el div
                    var inputElement = document.getElementById('iden');
                    // Asigna un valor al input
                    inputElement.value = id;
                }
        }

        $(document).ready(function() {
       /*
            $.ajax({
                url: '{{url('empresa/facturas/productos')}}',
                type: 'get',
                dataType: 'json',
                success: function(data){
                    $(data).each(function(i, v){ // indice, valor
                        $('.items_inv').append('<option value="' + v.id + '">' + v.id + '</option>');
                    })

                }

        });*/
        });
    </script>
@endsection
