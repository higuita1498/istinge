@extends('layouts.app')

@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" >
			{{Session::get('success')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){ 
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>


	@endif
	<div class="row card-description">

		<div class="col-md-12">

			<form method="post" action="#">
				{{ csrf_field() }}
							<div class="busqueda-especial-container">
								<h5>Busqueda de proveedores por tipo</h5>
	  <div class="row">
	  <div class="col-md-3 form-group">
      <label class="control-label">Tipo </label>
      <select class="form-control selectpicker" id="tipo" name="tipo" title="Seleccione el tipo"  data-size="5" data-live-search="true" onchange="tipoMV(this.value)" required="">
        
        <option value="{{2}}">{{"Maquinaria"}}</option>
        <option value="{{1}}">{{"Vehicular"}}</option>
      
    </select>

      <span class="help-block error">
        <strong>{{ $errors->first('categoriam') }}</strong>
      </span>
    </div>

				  <div class="col-md-3 form-group">
      <label class="control-label">Marca </label>
      <select class="form-control selectpicker" id="marca" name="marca" title="Seleccione la marca"  data-size="5" data-live-search="true">
       
      
    </select>

      <span class="help-block error">
        <strong>{{ $errors->first('categoriam') }}</strong>
      </span>
    </div>

    		  <div class="col-md-3 form-group">
      <label class="control-label">Linea </label>
      <select class="form-control selectpicker" id="linea" name="linea" title="Seleccione la linea"  data-size="5" data-live-search="true">
      
      
    </select>

      <span class="help-block error">
        <strong>{{ $errors->first('categoriam') }}</strong>
      </span>
    </div>

    		  <div class="col-md-3 form-group">
      <label class="control-label">Categoria </label>
      <select class="form-control selectpicker" id="categoria" name="categoria" title="Seleccione la categoria"  data-size="5" data-live-search="true">

      
    </select>

      <span class="help-block error">
        <strong>{{ $errors->first('categoriam') }}</strong>
      </span>
    </div>

</div>

<div class="row">
		  <div class="col-md-3 form-group">
      <label class="control-label">Fabricante </label>
      <select class="form-control selectpicker" id="fabricante" name="fabricante" title="Seleccione el fabricante"  data-size="5" data-live-search="true">
        
      
    </select>

      <span class="help-block error">
        <strong>{{ $errors->first('categoriam') }}</strong>
      </span>
    </div>

      <div class="col-md-4 form-group" style="margin-top:25px;">
					<button type="button" id="submitcheck"  class="btn btn-success">Buscar</button>
					<button type="button" id="reset1" class="btn btn-danger">Reset</button>
				</div>
</div>	

<hr>
<h5>Busqueda de proveedores por producto</h5>
<div class="row">
	<div class="col-md-6 form-group">
	 <label class="control-label">Producto </label>
      <select class="form-control selectpicker" id="producto" name="producto" title="Seleccione el producto"  data-size="5" data-live-search="true">
       @foreach($productos as $producto)
      <option value="{{$producto->id}}">{{$producto->producto}} - ({{$producto->ref}})</option>
      @endforeach
    </select>	
	</div>

	<div class="col-md-6" style="margin-top:25px;">
		<button type="button" id="submitProduct" class="btn btn-success">Buscar</button>
	</div>
	
</div>	 
		</div>
			</form>

			<table class="table table-striped table-hover" id="tbproveedor">
			<thead class="thead-dark">
				<tr>
				  <th>id</th>
	              <th>Nombre</th>
	              <th>Teléfono</th>
	              <th>Tipo Empresa</th>
	              <th>Tipo venta</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			{{--<tbody>
				@foreach($proveedores as $proveedor)
					<tr> 
						<td><a href="{{route('contactos.show',$proveedor->id)}}">{{$proveedor->nombre}}</a></td>
						<td>{{$proveedor->telefono}} </td>
						<td>{{$proveedor->tipo_empresa()}}</td>
						<td>{{$proveedor->tipo_venta()}}</td>
						<td>
							<a href="{{route('configuracion.agregarmarcaprov',$proveedor->id)}}" class="btn btn-outline-info btn-icons" title="Asociar campos"><i class="fas fa-info-circle"></i></a>
							<a href="{{route('contactos.show',$proveedor->id)}}" class="btn btn-outline-info btn-icons"><i class="far fa-eye"></i></i></a>
							
						</td>
					</tr>
				@endforeach
			</tbody>--}}
		</table>
		</div>
	</div>

	<script type="text/javascript">
		  
</script>
@endsection


{{--<script type="text/javascript">
	$(document).ready(function(){
	var checkboxm = document.getElementById('maquinaria2');
		checkboxm.addEventListener("change", validaCheckbox, false);

		function validaCheckbox(){
		  var checked = checkboxm.checked;
		  if(checked){
		    alert('checkbox esta seleccionado ' + checkboxm.value);
		  }
		}

	});
</script>--}}

@section('scripts')
<script type="text/javascript">
	  $(document).ready( function () {
        {{--$('#tbproveedor').DataTable({
            Processing: true,
            ServerSide: true,
            aaSorting: [[3, 'asc']],
            "ajax":"{{url('api/tbproveedor')}}",
            "columns":[
            {data: 'nombre'},
            {data: 'telefono1'},
            {data: 'tipo_empresa'},
            {data: 'tipo_contacto'},
            {data: 'btn'},
            ]
        });--}}

        filtroproveedores();

        $("#submitcheck").click(function(){
	  	var tipo = $("#tipo").val();
    	var marca = $("#marca").val();
    	var linea = $("#linea").val();
    	var categoria = $("#categoria").val();
    	var fabricante = $("#fabricante").val();
    	
    	if (tipo != '') {
    		$("#tbproveedor").DataTable().destroy();
    		filtroproveedores(marca,linea,categoria,fabricante,tipo);
    	}
    	else
    	{
    		alert("Elija un tipo")
    	}
	  });


        $("#submitProduct").click(function(){
        	var idproducto = $("#producto").val();

        		$("#tbproveedor").DataTable().destroy();
        		proveedoresxproducto(idproducto);
        	
        });

    });
    
    $("#reset1").click(function (){

          $("#tipo").val('default');
          $("#tipo").selectpicker("refresh");

          $("#marca").val('default');
          $("#marca").selectpicker("refresh");

          $("#linea").val('default');
          $("#linea").selectpicker("refresh");

          $("#categoria").val('default');
          $("#categoria").selectpicker("refresh");

          $("#fabricante").val('default');
          $("#fabricante").selectpicker("refresh");
          
          $("#tbproveedor").DataTable().destroy();
          filtroproveedores();
        });

	  
</script>
@endsection

