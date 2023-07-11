@extends('layouts.app')
@section('content')
	<style>
    .readonly{ border: 0 !important; }
  </style>

    @if(Session::has('danger'))
		<div class="alert alert-danger" >
			{{Session::get('danger')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>
	@endif

  <form method="POST" action="{{ route('radicados.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-radicado" >
   {{ csrf_field() }}
  <div class="row">
    <div class="col-md-4 form-group">
      <label class="control-label">Nombre del Cliente</label>

      <div class="input-group">
        <input type="text" class="form-control" id="clienteApi" name="clienteApi" value="{{old('clienteApi')}}" maxlength="30">
        <div class="input-group-append" >
          <span class="input-group-text nopadding">
            <a class="btn btn-outline-secondary btn-icons" title="Buscar" onclick="search_contacts();" style="margin-left: 8%;"><i class="fas fa-search"></i></a>
          </span>
        </div>
      </div>
      <div class="input-group" id="selectApi" style="display: none;">
        <select class="form-control selectpicker" name="cliente" id="cliente" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="search_contact(this.value);">
        </select>
      </div>
      <span class="help-block error">
        <strong>{{ $errors->first('cliente') }}</strong>
      </span>
    </div>

  <div id="content" style="display: none;">
    <input type="hidden" class="form-control" id="id_cliente" name="id_cliente" readonly="" maxlength="20">

    <div class="col-md-4 form-group">
      <label class="control-label">Identificación</label>
      <input type="text" class="form-control" id="ident" name="ident" readonly="" value="{{old('identificacion')}}" maxlength="20">
      <span class="help-block error">
        <strong>{{ $errors->first('identificacion') }}</strong>
      </span>
    </div>

    <div class="col-md-4 form-group">
      <label class="control-label">N° Telefónico</label>
      <input type="text" class="form-control"  id="telefono" name="telefono" value="{{old('telefono')}}" maxlength="30">
      <span class="help-block error">
        <strong>{{ $errors->first('telefono') }}</strong>
      </span>
    </div>

    <div class="col-md-4 form-group">
      <label class="control-label">Correo Electrónico</label>
      <input type="text" class="form-control"  id="correo" name="correo" value="{{old('correo')}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('correo') }}</strong>
      </span>
    </div>

    <div class="col-md-4 form-group">
      <label class="control-label">N° Contrato</label>
      <input type="text" class="form-control"  id="contrato" name="contrato" readonly="" value="{{old('contrato')}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('contrato') }}</strong>
      </span>
    </div>

    <div class="col-md-4 form-group">
      <label class="control-label">Plan Contratado</label>
      <input type="text" class="form-control" id="plan" name="plan" readonly="" value="{{old('identificacion')}}" maxlength="20">
      <span class="help-block error">
        <strong>{{ $errors->first('plan') }}</strong>
      </span>
    </div>

    <div class="col-md-12 form-group">
      <label class="control-label">Dirección</label>
      <input type="text" class="form-control" id="direccion" name="direccion" readonly="" value="{{old('direccion')}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('direccion') }}</strong>
      </span>
    </div>

    <div class="col-md-3 form-group">
      <label class="control-label">Fecha</label>
      <input type="text" class="form-control datepicker"  id="fecha" name="fecha" required="" value="{{date('d-m-Y')}}" >
      <span class="help-block error">
        <strong>{{ $errors->first('fecha') }}</strong>
      </span>
    </div>

    <div class="col-md-3 form-group">
      <label class="control-label">Tipo de Servicio <span class="text-danger">*</span></label>
      <select class="form-control selectpicker" name="servicio" id="servicio" required="" title="Seleccione">
        @foreach($servicios as $servicio)
          <option {{old('servicio')==$servicio->id?'selected':''}} value="{{$servicio->id}}">{{$servicio->nombre}}</option>
        @endforeach
      </select>
      <span class="help-block error">
        <strong>{{ $errors->first('servicio') }}</strong>
      </span>
    </div>

    <div class="col-md-3 form-group">
      <label class="control-label">¿Escalar Caso? <span class="text-danger">*</span></label>
      <select class="form-control selectpicker" name="estatus" id="estatus" required="" title="Seleccione" onchange="searchDV(this.value)">
          <option value="0" selected>No</option>
          <option value="2">Si</option>
      </select>
    </div>

    <div class="col-md-3 form-group" id="div_tecnico" style="display:none;">
      <label class="control-label">Técnico Asociado <span class="text-danger">*</span></label>
      <select class="form-control selectpicker" name="tecnico" id="tecnico" required="" title="Seleccione">
        @foreach($tecnicos as $tecnico)
          <option {{old('tecnico')==$tecnico->id?'selected':''}} value="{{$tecnico->id}}">{{$tecnico->nombres}}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-12 form-group">
      <label class="control-label">Observaciones</label>
      <textarea  class="form-control form-control-sm min_max_100" id="desconocido" required="" name="desconocido"></textarea>
      <span class="help-block error">
        <strong>{{ $errors->first('desconocido') }}</strong>
      </span>
    </div>
  </div>
  </div>
  <small>Los campos marcados con son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('radicados.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
    </div>
	</div>
</form>


{{-- Modal contacto nuevo --}}
    <div class="modal fade" id="contactoModal" role="dialog">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" id="modal-titlec"></h4>
          </div>
          <div class="modal-body" id="modal-bodyc">
            @include('contactos.modal.modal')
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
@endsection

@section('scripts')
    <script>

        function search_contacts(){
          if ($("#clienteApi").val() == '') {
            $("#clienteApi").focus();
            return false;
          }
          if (window.location.pathname.split("/")[1] === "software") {
            var url = '/software/empresa/clientesApi/'+$("#clienteApi").val();
          }else{
            var url = '/empresa/clientesApi/'+$("#clienteApi").val();
          }
          cargando(true);

          $.get(url,function(data){
            cargando(false);
            data = JSON.parse(data);
            var $select = $('#cliente');
            $('#ident,#telefono,#correo,#direccion,#contrato,#plan').val('').selectpicker('refresh');

            $select.find('option').remove();

            for(let i = 0; i < data.data.length; i++) {
              $select.append('<option value=' + data.data[i].id + '>' + data.data[i].name +' - '+data.data[i].national_identification_number+'' +'</option>');
            }
            $select.selectpicker('refresh');
            $("#selectApi").removeAttr('style');
          });
        }

        function search_contact(selected, modificar=false){
          if (window.location.pathname.split("/")[1] === "software") {
            var url = '/software/empresa/clienteApi/'+selected;
          }else{
            var url = '/empresa/clienteApi/'+selected;
          }

          $.ajax({
              url: url,
              beforeSend: function(){
                  cargando(true);
              },
              success: function(data){
                data=JSON.parse(data);

                $('#id_cliente').val('').val(data.data.id);
                $('#clienteApi').val('').val(data.data.name);
                $('#ident').val('').val(data.data.national_identification_number);
                $('#telefono').val('').val(data.data.phone_mobile);
                $('#correo').val('').val(data.data.email);
                $('#direccion').val('').val(data.data.address);
                $('#contrato').val('').val(data.data.contrato);
                $('#plan').val('').val(data.data.plan).selectpicker('refresh');

                //BUSCAR CONTRATO

                if (window.location.pathname.split("/")[1] === "software") {
                  var urlContrato = '/software/empresa/contratoApi/'+data.data.id;
                }else{
                  var urlContrato = '/empresa/contratoApi/'+data.data.id;
                }

                $.get(urlContrato,function(data){
                  dataC = JSON.parse(data);
                  var $select = $('#cliente');console.log(dataC);console.log(dataC.data[0]);
                  if(!dataC.data[0]){
                      document.getElementById("content").style.display = "contents";
                      $('#servicio').val(4).selectpicker('refresh').attr('readonly',true);
                      cargando(false);
                      return false;
                  }
                  $('#contrato').val('').val(dataC.data[0].public_id);

                  //BUSCAR PLAN

                  if (window.location.pathname.split("/")[1] === "software") {
                    var urlPlan = '/software/empresa/planApi/'+dataC.data[0].plan_id;
                  }else{
                    var urlPlan = '/empresa/planApi/'+dataC.data[0].plan_id;
                  }

                  $.get(urlPlan,function(data){
                    dataP = JSON.parse(data);
                    var $select = $('#cliente');
                    $('#plan').val('').val(dataP.data.name);
                    document.getElementById("content").style.display = "contents";
                    cargando(false);
                  });
                });
              },
              error: function(data){
                Swal.fire({
                  position: 'top-center',
                  type: 'error',
                  title: 'Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde',
                  showConfirmButton: false,
                  timer: 2500
                });
                cargando(false);
              }
          });
        }

        function searchDV(id){
          option = id;
          if (option == 2) {
            document.getElementById("div_tecnico").style.display = "block";
          }else{
            document.getElementById("div_tecnico").style.display = "none";
            $("#tecnico").val('').selectpicker('refresh');
          }
        }

    </script>
@endsection

