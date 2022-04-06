<div class="modal fade" id="modal-select-persona" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog " role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Seleccionar persona</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
            <select class="form-control selectpicker" id="persona-liquidar" required="" title="Seleccione"  data-live-search="true" data-size="100" data-size="5">
				@foreach($personas as $persona)
				<option value="{{ $request->fullUrl().'&persona='.$persona->id }}">{{ $persona->nombre.' '.$persona->apellido}}</option>
				@endforeach
			</select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-success" onclick="confirmarPersona()">Confirmar</button>
        </div>
      </div>
    </div>
  </div>


<script>

function confirmarPersona(){

    let urlPersona = $('#persona-liquidar').val();

    window.location.replace(urlPersona);

}

</script>
