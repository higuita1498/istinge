@if($status == 1)
<form action="{{ route('wifi.status',$id) }}" method="POST" class="" style="margin: 0;display: inline-block;" id="status{{$id}}">
    @csrf
</form>
<button class="btn btn-outline-success btn-icons" type="submit" title="Solventar Solicitud" onclick="confirmar('status{{$id}}', '¿Está seguro que desea cambiar de status la solicitud?');"><i class="fas fa-check"></i></button>
@endif