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
    					<th>NETWORK</th>
    					<th>INTERFACE</th>
    					<th>ACTUAL-INTERFACE</th>
    					<th>INVALID</th>
    					<th>DYNAMIC</th>
    					<th>DISABLED</th>
    					<th>COMMENT</th>
    				</tr>
    			</thead>
    			<tbody>
    				@foreach($arrays as $array)
    				    <tr>
                            <td>{{ $array['address'] }}</td>
                            <td>{{ $array['network'] }}</td>
                            <td>{{ $array['interface'] }}</td>
                            <td>{{ $array['actual-interface'] }}</td>
                            <td>{{ $array['invalid'] }}</td>
                            <td>{{ $array['dynamic'] }}</td>
                            <td>{{ $array['disabled'] }}</td>
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
