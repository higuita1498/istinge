<form method="POST" action="{{ route('inventario.storeback') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form" enctype="multipart/form-data">
	{{ csrf_field() }}
	<div class="row">
		<div class="form-group col-md-4">
			<label class="control-label">Nombre del Producto <span class="text-danger">*</span></label>
			<input type="text" class="form-control" name="producto" id="producto" required="" maxlength="200" value="{{old('producto')}}">
			<span class="help-block error">
		        	<strong>{{ $errors->first('producto') }}</strong>
		        </span>
			</div>
			<div class="form-group col-md-4 modal-contact">
	  			<label class="control-label">Referencia <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="ref" id="ref" maxlength="200" value="{{old('ref')}}">
				<span class="help-block error">
		        	<strong>{{ $errors->first('ref') }}</strong>
		        </span>
		</div>
		<div class="form-group col-md-4">
			<label class="control-label">Categoria <span class="text-danger">*</span><a><i data-tippy-content="Selecciona la categoría en la que se registrarán los valores por venta del ítem" class="icono far fa-question-circle"></i></a></label>
			<select class="form-control selectpicker" name="categoria" id="categoria" required="" title="Seleccione" data-live-search="true" data-size="5">
				@foreach($categorias as $categoria)
					@if($categoria->estatus==1)
						<option {{old('categoria')==$categoria->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria->id?'selected':'')}} {{$categoria->nombre == 'Activos' ? 'selected' : ''}} value="{{$categoria->id}}">{{$categoria->nombre}}</option>
					@endif
					@foreach($categoria->hijos(true) as $categoria1)
						@if($categoria1->estatus==1)
							<option {{old('categoria')==$categoria1->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria1->id?'selected':'')}} value="{{$categoria1->id}}">{{$categoria1->nombre}}</option>
						@endif
						@foreach($categoria1->hijos(true) as $categoria2)
							@if($categoria2->estatus==1)
								<option {{old('categoria')==$categoria2->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria2->id?'selected':'')}} value="{{$categoria2->id}}">{{$categoria2->nombre}}</option>
							@endif
							@foreach($categoria2->hijos(true) as $categoria3)
								@if($categoria3->estatus==1)
									<option {{old('categoria')==$categoria3->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria3->id?'selected':'')}} value="{{$categoria3->id}}">{{$categoria3->nombre}}</option>
								@endif
								@foreach($categoria3->hijos(true) as $categoria4)
									@if($categoria4->estatus==1)
										<option {{old('categoria')==$categoria4->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria4->id?'selected':'')}} value="{{$categoria4->id}}">{{$categoria4->nombre}}</option>
									@endif

								@endforeach

							@endforeach

						@endforeach

					@endforeach


				@endforeach
			</select>
			<span class="help-block error">
		        	<strong>{{ $errors->first('categoria') }}</strong>
		        </span>
		</div>
	</div>
	<div class="row">
		<div class="form-group col-md-5">
			<label class="control-label">Impuesto <span class="text-danger">*</span></label>
			<select class="form-control selectpicker" name="impuesto" id="impuesto" required="" title="Seleccione">
				@foreach($impuestos as $impuesto)
					<option {{old('impuesto')==$impuesto->id?'selected':''}} value="{{$impuesto->id}}">{{$impuesto->nombre}} - {{$impuesto->porcentaje}} %</option>
				@endforeach
			</select>
			<span class="help-block error">
					<strong>{{ $errors->first('impuesto') }}</strong>
				</span>
		</div>
		<div class="form-group col-md-7 ">
			<div class="row">
				<div class="col-md-6 monetario">
					<label class="control-label">Precio de Venta <span class="text-danger">*</span></label>
					<input type="number" class="form-control " id="precio" name="precio" required="" maxlength="24" value="{{old('precio')}}" placeholder="{{Auth::user()->empresa()->moneda}}" min="0" >
					<span class="help-block error">
				        	<strong>{{ $errors->first('precio') }}</strong>
				        </span>
					<span class="litte">Use el punto (.) para colocar decimales</span>
				</div>
				<div class="col-md-6" style="padding-top: 6%;padding-left: 0;"><button type="button" class="btn btn-link " style="padding-left: 0;" onclick="agregarlista_precios();" @if(json_encode($listas)=='[]') title="Usted no tiene lista de precios registrada" @endif><i class="fas fa-plus"></i> Agregar otra lista de precio</button></div>
			</div>
			<div class="row" id="lista_precios_inventario">
				<div class="col-md-12">
					<table id="table_lista_precios">
						<tbody>
						</tbody>
					</table>
				</div>
			</div>

		</div>
	</div>
	<div class="row">
		<div class="form-group col-md-8">
			<label class="control-label" for="email">Descripción</label>
			<textarea class="form-control" name="descripcion" id="descripcion" rows="4" value="{{old('descripcion')}}"></textarea>
			{{--<input type="text" class="form-control" id="email" name="descripcion" maxlength="255"  value="{{old('descripcion')}}">--}}
			<div class="help-block error with-errors"></div>
			<span class="help-block error">
					<strong>{{ $errors->first('descripcion') }}</strong>
				</span>
		</div>

		<div class="form-group col-md-4">
			<label class="control-label"><button type="button" class="btn btn-link btn-fw" id="button_show_div_img">Imagen (Opcional)</button></label>
			<div style="display: none;" id="div_imagen">
				<input type="file" class="dropify" name="imagen" />
				<span class="help-block error">
			        	<strong>{{ $errors->first('imagen') }}</strong>
			        </span>
			</div>
		</div>


	</div>
	@if(Auth::user()->empresa()->carrito==1)
		<div class="row" >
			<div class="col-md-12">
				<div class="form-group row">
					<label for="publico" class="col-md-3 col-form-label">¿Estara el producto público en la web? <a><i data-tippy-content="Si eliges 'si' automaticamente al presionar guardar el producto irá a tu tienda online" class="icono far fa-question-circle"></i></a></label>
					<div class="col-md-2">
						<div class="row">
							<div class="col-sm-6">
								<div class="form-radio">
									<label class="form-check-label">
										<input type="radio" class="form-check-input" name="publico" id="publico1" value="1" > Si
										<i class="input-helper"></i></label>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-radio">
									<label class="form-check-label">
										<input type="radio" class="form-check-input" name="publico" id="publico" value="0" checked=""> No
										<i class="input-helper"></i></label>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12 form-group">
				Imagenes extras
				<input type="file" class="form-control" name="imagenes_extra[]" multiple/>
			</div>
		</div>
	@endif

	<small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	<hr>
	<div class="row" style="text-align: right;">
		<div class="col-md-12">
			<button type="button" class="btn btn-outline-light" data-dismiss="modal" id="cancelar">Cancelar</button>
			<button type="button" class="btn btn-success" id="guardar">Guardar</button>
		</div>
	</div>
	<input type="hidden" name="tipo_producto" value="2">

</form>
<input type="hidden" id="json_precios" value="{{json_encode($listas)}}">
<input type="hidden" id="json_bodegas" value="{{json_encode($bodegas)}}">

@section('scripts')
<script>

    $('#contacto').click( function () {

        var url = '/empresa/contactos/contactosModal';
        var _token =   $('meta[name="csrf-token"]').attr('content');

        $("#modal-titlec").html($(this).attr('title'));

        $.post(url,{  _token : _token },function(resul){

            $("#modal-bodyc").html(resul);
            $('.selectpicker').selectpicker();
        });
        $('#contactoModal').modal("show");

    });


    $("#form").submit(function () {
        return false;
    });

    $(document).ready(function () {

        $('#inventariable').hide();
        $('#table-form').on('click','.modalTr',function () {
            $('#trFila').val($(this).attr('tr'));
        });

        $("#guardar").click(function (form) {
            $.post($("#form").attr('action'), $("#form").serialize(), function (dato) {
                if(dato['status']=='OK'){
                    var trFila = $('#trFila').val();
                    var select = $('#item'+trFila);
                    select.append('<option value=' + dato['id'] + ' selected>' + dato['producto'] + '</option>');
                    select.selectpicker('refresh');
                    $('#ref'+trFila).val('');
                    $('#precio'+trFila).val('');
                    $('#desc'+trFila).val('');
                    $('#descripcion'+trFila).val('');
                    $('#cant'+trFila).val('');
                    $('#total'+trFila).val('');
                    $("#item"+trFila).trigger('change');
                    $('#trFila').val('0');
                    $('#cancelar').click();

                    $('#form').trigger("reset");

                    //swal("Registro Guardado", "Nuevo Producto Agregado!!!", "success");
                    swal({
                        title: "Registro Guardado",
                        text: "Nuevo Producto Agregado!!!",
                        type: "success"
                    }).then(function () {
                        $('#cancelar').click();
                    });
                    return false;
                } else {
                    swal('Info!!', dato['mensaje'], "error");
                    //alert(dato['mensaje']);
                }
            }, 'json');

        });
    });

    setTimeout(function () {
        $('#inventariable').show();
        $('#precio').removeAttr('required');
        $('#precio_unid').attr('required', '');
        $('#unidad').attr('required', '');
        $('#nro_unid').attr('required', '');
        agregarbodega_inventario();
        $("#form-inventario").validate('destroy');
        form_inventario();
        clearTimeout(this);
    }, 2000);

    $('input[type=radio][name=tipo_producto]').change(function() {
        if (this.value == 1) {
            $('#inventariable').show();
            $('#precio').removeAttr('required');
            $('#precio').attr('disabled', '');
            $('#precio_unid').attr('required', '');
            $('#unidad').attr('required', '');
            $('#nro_unid').attr('required', '');
            agregarbodega_inventario();
            $("#form-inventario").validate('destroy');
            form_inventario();
        }
        else  {
            $('#inventariable').hide();
            $("#precio_unid").val('');
            $("#nro_unid").val('');
            $('#unidad').val('').trigger('change');
            $('#precio').attr('required');
            $('#precio').removeAttr('disabled');
            $('#unidad').removeAttr('required');
            $('#nro_unid').removeAttr('required');
            eliminarbodega_inventario();
            $("#form-inventario").validate('destroy');
            form_inventario();

        }
    });

</script>
@endsection

