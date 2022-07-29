@extends('layouts.app')

@section('boton')
    <a href="{{ route('mikrotik.index')}}"  class="btn btn-danger btn-sm" title="Regresar" id="btn_salir"><i class="fas fa-step-backward"></i></i> Regresar</a>
@endsection

@section('style')
<style>
	#tabla-ips > tbody > tr > td:nth-child(1) > span{
		display: none;
	}
</style>
@endsection

@section('content')
    @if(Session::has('danger'))
		<div class="alert alert-danger" >
			{{Session::get('danger')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 10000);
		</script>
	@endif
	
	<div class="row card-description">
    	<div class="col-md-12">
    		<table class="table table-striped table-hover w-100" id="example">
    			<thead class="thead-dark">
    				<tr>
    					<th>ADDRESS</th>
    					<th>MAC-ADDRESS</th>
    					<th>INTERFACE</th>
    					<th>COMPLETE</th>
    					<th>DISABLED</th>
    					<th>COMMENT</th>
    				</tr>
    			</thead>
    			<tbody>
    				@foreach($arrays as $array)
    				    <tr>
                            <td>{{ isset($array['address']) ? $array['address'] : '- - - - -' }}</td>
                            <td>{{ isset($array['mac-address']) ? $array['mac-address'] : '- - - - -' }}</td>
                            <td>{{ isset($array['interface']) ? $array['interface'] : '- - - - -' }}</td>
                            <td>{{ isset($array['complete']) ? $array['complete'] : '- - - - -' }}</td>
                            <td>{{ isset($array['disabled']) ? $array['disabled'] : '- - - - -' }}</td>
                            <td>{{ isset($array['comment']) ? $array['comment'] : '- - - - -' }}</td>
	    				</tr>
    				@endforeach
    			</tbody>
    		</table>
    	</div>
    </div>
@endsection

@section('scripts')
<script>

</script>
@endsection
