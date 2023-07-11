
   <input type="hidden" name="tipo" value="{{"categoriav"}}">
  <div class="row">
    <div class="col-md-12 form-group">
      <label class="control-label">Nombre <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="nombrecv" name="nombre"  required="" value="{{old('nombre')}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>

  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      
      <button type="submit" id="submitcheck" onclick="nameCategoriav('categoriav', 'modalcategoriav');" value="categoriav" class="btn btn-success">Guardar</button>
    </div>
	</div>

@section('scripts')
{{-- <script type="text/javascript">                                                   
  $(document).ready(function(){
     $("form").bind("submit",function(e){
        nameCategoriav('categoriav', 'modalcategoriav');
        e.preventDefault();
     });
  });
</script>--}}
@endsection