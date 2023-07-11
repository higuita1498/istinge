<div class="modal-content">
	<div class="modal-header">

		<h5 class="modal-title" id="exampleModalLabel">Beneficiario: &nbsp 
			@if($gasto->beneficiario()){{$gasto->beneficiario()->nombre}}@endif</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			@if ($gasto->observaciones)
			<center><h5>Observacion:</h5></center>
			<center><p>{{$gasto->observaciones}}</p></center>
			@else
			<center><h5 class="text-danger">No hay ninguna observacion</h5></center>
			@endif

		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	</div>