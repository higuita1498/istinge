@extends('layouts.app')
@section('content')
@if(Session::has('success'))
<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
  {{Session::get('success')}}
</div>
@endif

<script type="text/javascript">
  setTimeout(function(){ 
    $('.alert').hide();
    $('.active_table').attr('class', ' ');
  }, 5000);
</script>
	<form method="POST" action="{{ route('BusquedaProveedor.asociarproveedor') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-proveedores" >
   {{ csrf_field() }}
   <input type="hidden" name="idproveedor" value="{{$proveedor->id}}">
  <div class="row">
    <div class="col-md-4 form-group">
      <label class="control-label">Nombredel proveedor <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="nombreproveedor" name="nombreproveedor"  required="" value="{{$proveedor->nombre}}" maxlength="200" disabled="">
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>
    {{--<div class="form-group col-md-3">
          <label class="control-label">Tipo<span class="text-danger">*</span></label>
        <div class="form-check form-check-flat">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input" name="contacto[]" value="0"> Maquinaria
                    <i class="input-helper"></i></label>
                  </div>
                  <div class="form-check form-check-flat">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input" name="contacto[]" value="1" > Vehicular
                    <i class="input-helper"></i></label>
                  </div>
                  <span class="help-block error">
          <strong>{{ $errors->first('contacto') }}</strong>
        </span>
      </div>--}}
  </div>
  <hr>
  

  <h5>Asociar Maquinarias</h5>
  <div class="row">
   <div class="col-md-4 form-group">
      <label class="control-label">Marcas <span class="text-danger">*</span></label>
      <select class="form-control selectpicker" id="marcam" name="marca[]" title="Selecciona la(s) marca(s)"  data-size="5" data-live-search="true" multiple>
        @foreach($marcasm as $marcam)
        <option value="{{$marcam->id}}" @foreach($marcascheck as $mcheck)
          {{$mcheck->id_marca == $marcam->id ? 'selected' : ''}}   
          @endforeach>{{$marcam->nombre}}</option>
        @endforeach
      
    </select>
  
      <span class="help-block error">
        <strong>{{ $errors->first('marcam') }}</strong>
      </span>

      {{--<div class="row">
      <div class="col-md-12">
        <div class="list-group" id="show-list-placa">
          <a  onclick='agregarMarcaTag(this.value, Isuzu)' class='list-group-item list-group-item-action border-1' value='{[1]}'>Isuzu</a>
          <a  onclick='' class='list-group-item list-group-item-action border-1' value='".$vehiculo->id."'>cat</a>
          <a  onclick='' class='list-group-item list-group-item-action border-1' value='".$vehiculo->id."'>kia</a>
        </div>
      </div>
    </div>--}}

    <div class="row">
      <div class="col-md-12" style="display: inline-flex;">

      </div>
    </div>

    </div>
    <div class="col-md-4 form-group">
      <label class="control-label">Lineas </label>
         <select class="form-control selectpicker" id="lineam" name="linea[]" title="Selecciona la(s) linea(s)"  data-size="5" data-live-search="true" multiple>
        @foreach($lineasm as $lineam)
        <option value="{{$lineam->id}}" @foreach($lineascheck as $lcheck)
          {{$lcheck->id_linea == $lineam->id ? 'selected' : ''}}   
          @endforeach>{{$lineam->nombre}}</option>
        @endforeach
      
    </select>
    <p class="text-left nomargin">
                    <a href="" data-toggle="modal" data-target="#modallineam" class="modalTr" tr="1">
                        <i class="fas fa-plus"></i> Nueva linea
                    </a>
                  </p>
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
      <div class="col-md-12">
        {{--<div class="tag-container">
          <div class="tag">
            <span>Javascript</span>
            <i class="fas fa-times-circle"></i>
          </div>

        </div>--}}

        {{--<div class="row">
      <div class="col-md-12">
        <div class="list-group" id="show-list-linea">
          
        </div>
      </div>
    </div>--}}

      </div>


    </div>
    <div class="col-md-4 form-group">
      <label class="control-label">Categorias </label>
      <select class="form-control selectpicker" id="categoriam" name="categoria[]" title="Selecciona la(s) categoria(s)"  data-size="5" data-live-search="true" multiple>

        @foreach($categoriasm as $categoriam)
        <option value="{{$categoriam->id}}" @foreach($categoriascheck as $ccheck)
          {{$ccheck->id_categoria == $categoriam->id ? 'selected' : ''}}   
          @endforeach>{{$categoriam->nombre}}</option>
        @endforeach
      
    </select>
    <p class="text-left nomargin">
                    <a href="" data-toggle="modal" data-target="#modalcategoriam" class="modalTr" tr="1">
                        <i class="fas fa-plus"></i> Nueva categoria
                    </a>
                  </p>
      <span class="help-block error">
        <strong>{{ $errors->first('categoriam') }}</strong>
      </span>
    </div>
  </div>


  <div class="row">
    <div class="col-md-4 form-group">
      <label class="control-label">Fabricantes <span class="text-danger">*</span></label>
         <select class="form-control selectpicker" id="fabricantem" name="fabricante[]" title="Selecciona los fabricante(s)"  data-size="5" data-live-search="true" multiple>
        @foreach($fabricantesm as $fabricantem)
        <option value="{{$fabricantem->id}}" @foreach($fabricantescheck as $fcheck)
          {{$fcheck->id_fabricante == $fabricantem->id ? 'selected' : ''}}   
          @endforeach>{{$fabricantem->nombre}}</option>
        @endforeach
      
    </select>
      <p class="text-left nomargin">
                    <a href="" data-toggle="modal" data-target="#modalfabricantem" class="modalTr" tr="1">
                        <i class="fas fa-plus"></i> Nuevo fabricante
                    </a>
                  </p>
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>
  </div>
<hr>

<h5>Asociar vehiculares</h5>
  <div class="row">
   <div class="col-md-4 form-group">
      <label class="control-label">Marcas <span class="text-danger">*</span></label>
      <select class="form-control selectpicker" id="marcav" name="marca[]" title="Selecciona la(s) marca(s)"  data-size="5" data-live-search="true" multiple>
        @foreach($marcasv as $marcav)
        <option value="{{$marcav->id}}" @foreach($marcascheck as $mcheck)
          {{$mcheck->id_marca == $marcav->id ? 'selected' : ''}}   
          @endforeach > {{$marcav->nombre}}</option>
          
        @endforeach
      
    </select>
      <span class="help-block error">
        <strong>{{ $errors->first('marcav') }}</strong>
      </span>

      {{--<div class="row">
      <div class="col-md-12">
        <div class="list-group" id="show-list-placa">
          <a  onclick='agregarMarcaTag(this.value, Isuzu)' class='list-group-item list-group-item-action border-1' value='{{1}}'>Isuzu</a>
          <a  onclick='' class='list-group-item list-group-item-action border-1' value='".$vehiculo->id."'>cat</a>
          <a  onclick='' class='list-group-item list-group-item-action border-1' value='".$vehiculo->id."'>kia</a>
        </div>
      </div>
    </div>--}}

    <div class="row">
      <div class="col-md-12" style="display: inline-flex;">

      </div>
    </div>

    </div>
    <div class="col-md-4 form-group">
      <label class="control-label">Lineas </label>
      <select class="form-control selectpicker" id="lineav" name="linea[]" title="Selecciona la(s) línea(s)"  data-size="5" data-live-search="true" multiple>
        @foreach($lineasv as $lineav)
        <option value="{{$lineav->id}}" @foreach($lineascheck as $lcheck)
          {{$lcheck->id_linea == $lineav->id ? 'selected' : ''}}   
          @endforeach>{{$lineav->nombre}}</option>
        @endforeach
      
    </select>
     <p class="text-left nomargin">
                    <a href="" data-toggle="modal" data-target="#modallineav" class="modalTr" tr="1">
                        <i class="fas fa-plus"></i> Nueva linea
                    </a>
                  </p>
      <span class="help-block error">
        <strong>{{ $errors->first('lineav') }}</strong>
      </span>
      <div class="col-md-12">
        {{--<div class="tag-container">
          <div class="tag">
            <span>Javascript</span>
            <i class="fas fa-times-circle"></i>
          </div>

        </div>--}}

        {{--<div class="row">
      <div class="col-md-12">
        <div class="list-group" id="show-list-linea">
          
        </div>
      </div>
    </div>--}}

      </div>


    </div>
    <div class="col-md-4 form-group">
      <label class="control-label">Categorias </label>
      <select class="form-control selectpicker" id="categoriav" name="categoria[]" title="Selecciona la(s) categoria(s)"  data-size="5" data-live-search="true" multiple>
        @foreach($categoriasv as $categoriav)
        <option value="{{$categoriav->id}}" @foreach($categoriascheck as $ccheck)
          {{$ccheck->id_categoria == $categoriav->id ? 'selected' : ''}}   
          @endforeach>{{$categoriav->nombre}}</option>
        @endforeach
      
    </select>
    <p class="text-left nomargin">
                    <a href="" data-toggle="modal" data-target="#modalcategoriav" class="modalTr" tr="1">
                        <i class="fas fa-plus"></i> Nueva categoria
                    </a>
                  </p>
      <span class="help-block error">
        <strong>{{ $errors->first('categoriav') }}</strong>
      </span>
    </div> 
  </div>

  <div class="row">
    <div class="col-md-4 form-group">
      <label class="control-label">Fabricantes <span class="text-danger">*</span></label>
        <select class="form-control selectpicker" id="fabricantev" name="fabricante[]" title="Selecciona los fabricante(s)"  data-size="5" data-live-search="true" multiple>
        @foreach($fabricantesv as $fabricantev)
        <option value="{{$fabricantev->id}}" @foreach($fabricantescheck as $fcheck)
          {{$fcheck->id_fabricante == $fabricantev->id ? 'selected' : ''}}   
          @endforeach>{{$fabricantev->nombre}}</option>
        @endforeach
      
    </select>
      <p class="text-left nomargin">
                    <a href="" data-toggle="modal" data-target="#modalfabricantev" class="modalTr" tr="1">
                        <i class="fas fa-plus"></i> Nuevo fabricante
                    </a>
                  </p>
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>
  </div>

    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
  <div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('bancos.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
    </div>
  </div>

</form>

{{--  ******************MODALES******************  --}}
{{-- Modal Nueva linea vehicular  --}}
  <div class="modal fade" id="modallineav" role="dialog">
      <div class="modal-dialog modal-sm">
          <input type="hidden" id="trFila" value="0">
          <div class="modal-content">
              <div class="modal-header">
                  <h4 class="modal-title">Nueva Linea</h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body">
                  @include('busquedas.modales.lineav')
              </div>

          </div>
      </div>
  </div>
  {{--/Modal Nueva linea vehicular  --}}

  {{-- Modal Nueva linea Maquinaria  --}}
  <div class="modal fade" id="modallineam" role="dialog">
      <div class="modal-dialog modal-sm">
          <input type="hidden" id="trFila" value="0">
          <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Nueva Linea</h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  
              </div>
              <div class="modal-body">
                  @include('busquedas.modales.lineam')
              </div>

          </div>
      </div>
  </div>
  {{--/Modal Nueva linea Maquinaria  --}}

    {{-- Modal Nueva categoria vehicular  --}}
  <div class="modal fade" id="modalcategoriav" role="dialog">
      <div class="modal-dialog modal-sm">
          <input type="hidden" id="trFila" value="0">
          <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Nueva Categoria</h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  
              </div>
              <div class="modal-body">
                  @include('busquedas.modales.categoriav')
              </div>

          </div>
      </div>
  </div>
  {{--/Modal Nueva categoria vehicular  --}}

      {{-- Modal Nueva categoria maquinaria  --}}
  <div class="modal fade" id="modalcategoriam" role="dialog">
      <div class="modal-dialog modal-sm">
          <input type="hidden" id="trFila" value="0">
          <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Nueva Categoria</h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  
              </div>
              <div class="modal-body">
                  @include('busquedas.modales.categoriam')
              </div>

          </div>
      </div>
  </div>
  {{--/Modal Nueva categoria maquinaria  --}}

  {{-- Modal Nueva fabricante vehicular  --}}
  <div class="modal fade" id="modalfabricantev" role="dialog">
      <div class="modal-dialog modal-sm">
          <input type="hidden" id="trFila" value="0">
          <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Nuevo Fabricante</h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  
              </div>
              <div class="modal-body">
                  @include('busquedas.modales.fabricantev')
              </div>

          </div>
      </div>
  </div>
  {{--/Modal Nueva fabricante vehicular  --}}

   {{-- Modal Nueva fabricante Maquinaria  --}}
  <div class="modal fade" id="modalfabricantem" role="dialog">
      <div class="modal-dialog modal-sm">
          <input type="hidden" id="trFila" value="0">
          <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Nuevo Fabricante</h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body">
                  @include('busquedas.modales.fabricantem')
              </div>

          </div>
      </div>
  </div>
  {{--/Modal Nueva fabricante Maquinaria  --}}
{{--  ******************MODALES******************  --}}



@endsection

@section('scripts')
<script type="text/javascript">
  

    {{--$(document).ready(function(){--}}

      /*........................................
          autocompletar campos x linea
      ..........................................*/

      {{--$("#linea").keyup(function(){
        var searchLinea = $(this).val();

        if(searchLinea != ''){
          $.ajax({
            url: '/autocomplete_linea',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            method: 'POST',
            data:   {query:searchLinea},
            success: function(response){
              console.log(response);
              $("#show-list-linea").html(response);
            }
          });
        }else{
          $("#show-list-placa").html('');
        }
      });
      $(document).on('click','#show-list-linea a',function(){
        $("#placa").val($(this).text());
        $("#show-list-placa").html('');
      });



      $("html").click(function() {
        document.getElementById("ocultnamebyclickout").style.display="none";
        document.getElementById("ocultdocumentobyclickout").style.display="none";
        document.getElementById("ocultplacaobyclickout").style.display="none";
      });

      $('#linea').click(function (e) {
          e.stopPropagation();
      });

    });--}}
  </script>
@endsection