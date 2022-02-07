@extends('layouts.app')	
@section('content')
	<div class="card-body">
	<p>Para actualizar tus items sigue estos pasos: </p>
	<ul>
    <li>Descarga la(s) plantilla(s) de tu inventario.</li>
    <li>Realiza los cambios en cada archivo y guárdalo en formato Excel (.xlsx).</li>
    <li>Adjunta el archivo y haz clic en "Actualizar inventario".</li>
    <li>Descarga desde este enlace tu plantilla con todos tus productos que desees actualizar.<a href="{{route('inventario.exportar')}}"> Click Aqui</a></li>
  </ul>
 
  <h4>Sigue fielmente estas recomendaciones:</h4>
  <ul>
    <li>Conoce en qué casos no deberías utilizar la actualización masiva.</li>
    <li>El orden y los nombres de las columnas no deben ser modificados.</li>
    <li>Solo podrás modificar cantidad inicial, y costo unitario a productos que sean inventariables. Nota: La cantidad inicial no alterara la cantidad disponible</li>
    <li>Solo se modificara el precio general y el precio unitario en caso de que el producto sea inventariable.</li>
  </ul>
  @if(Session::has('success'))
    <div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
      {{Session::get('success')}}
    </div>
  @endif
  	<form method="POST" action="#" role="form" enctype="multipart/form-data">
  		{{ csrf_field() }}
      @if(Auth::user()->empresa()->carrito==1)
      <div class="row" >
        <div class="col-md-12">
          <div class="form-group row">
          <label for="publico" class="col-md-3 col-form-label">¿Estara el producto público en la web?</label>
            <div class="col-md-4">
              <div class="row">
              <div class="col-sm-4">
              <div class="form-radio">
                <label class="form-check-label">
                <input type="radio" class="form-check-input" name="publico" id="publico1" value="1" > Si
                <i class="input-helper"></i></label>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-radio">
                <label class="form-check-label">
                <input type="radio" class="form-check-input" name="publico" id="publico2" value="0" checked=""  > No
                <i class="input-helper"></i></label>
              </div>
            </div>
            <!--<div class="col-sm-4">
              <div class="form-radio">
                <label class="form-check-label"> 
                <input type="radio" class="form-check-input" name="publico" id="publico3" value="2" checked=""> Ignorar
                <i class="input-helper"></i></label>
              </div>
            </div> -->
            </div>
            </div>        
          </div>
        </div>
      </div>
    @endif
  		<div class="row">
  			<div class="form-group col-md-12">
	  			<label class="control-label">Archivo <span class="text-danger">*</span></label>
				<input type="file" class="form-control" name="archivo" required="">
        <div class="progress">
          <div class="bar">
            <div class="percent">
              0%
            </div>
          </div>
        </div>
				<span class="help-block">
		        	<strong>{{ $errors->first('archivo') }}</strong>
		        </span>
			</div>
		</div>
  		<div class="row">

        <div class="col-md-12">
          @if(count($errors) > 0)
          <div class="alert alert-danger">
            <ul>
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
            </ul>
          </div>
        @endif 
        </div>
  		</div>

  		<hr>
  		<div class="row">
  			<div class="col-md-12 text-right">
				<a href="{{route('inventario.index')}}" class="btn btn-outline-light" >Cancelar</a>
  				<button type="submit" class="btn btn-success">Guardar</button>
  			</div>
  		</div>
  		
  	</form>

    <!-- BARRA DE PORCENTAJE ANIMADA PARA IMPORTACION DE PRODUCTOS -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.js"></script>
<script src="http://malsup.github.com/jquery.form.js"></script>
<script type="text/javascript">

  function validate(formData, jqForm, options) {
    var form = jqForm[0];
    if (!form.archivo.value) {
      alert('File not found');
      return false;
    }
  }

  (function() {

    var bar = $('.bar');
    var percent = $('.percent');
    var status = $('#success');

    $('form').ajaxForm({
      beforeSubmit: validate,
      beforeSend: function() {
        status.empty();
        var percentVal = '0%';
        var posterValue = $('input[name=archivo]').fieldValue();
        bar.width(percentVal)
        percent.html(percentVal);
      },
      uploadProgress: function(event, position, total, percentComplete) {
        var percentVal = percentComplete + '%';
        bar.width(percentVal)
        percent.html(percentVal);
      },
      success: function() {
        var percentVal = 'Wait, Saving';
        bar.width(percentVal)
        percent.html(percentVal);
      },
      complete: function(xhr) {
        status.html(xhr.responseText);
        Swal.fire({
          position: 'top-end',
          type: 'success',
          title: 'Actualizado Correctamente',
          showConfirmButton: false,
          timer: 1500
        })
        window.location.href = "/empresa/inventario/actualizar";
      }
    });

  })();
</script>
<!--/ BARRA DE PORCENTAJE ANIMADA PARA IMPORTACION DE PRODUCTOS -->

  </div>
@endsection