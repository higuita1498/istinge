@extends('layouts.app')

@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" >
			{{Session::get('success')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){ 
			    $('.alert').hide();
			}, 5000);
		</script>
	@endif

    @if(Session::has('info'))
		<div class="alert alert-info" >
			{{Session::get('info')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			}, 10000);
		</script>
	@endif

<div id="accordion">
	<!-- Nivel 1 -->
	@foreach($categorias as $categoria)
	<div class="card">
	    <div class="card-header" id="heading{{$categoria->nro}}">
	      	<h5 class="mb-0">
	      		<div class="row">
		      		<div class="col-sm-5">
		      			@if($categoria->hijos()>0)
		      				<a class="btn btn-link colapsea" data-toggle="collapse" data-target="#collapse{{$categoria->nro}}" 
                                aria-expanded="true" aria-controls="{{$categoria->nro}}" 
                                onclick="showCategory({{$categoria->codigo}})"
                            >
			          		{{$categoria->nombre}} 
			        	</a>

		      			@else
		      				<p style="padding-left: 15%;">{{$categoria->nombre}}</p> 
						@endif
			        	
		    		</div>
	      			<div class="col-sm-5"><p>{{$categoria->codigo}}</p></div>
		      		<div class="col-sm-2">
		      			<a href="#" onclick="modal_show('{{route('puc.create_id',$categoria->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Agregar sub-categoría"><i class="fas fa-plus"></i></a>
		      			<a href="#" onclick="modal_show('{{route('categorias.edit',$categoria->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Modificar"><i class="far fa-edit"></i></a>
		      			<form action="{{ route('categorias.act_desc',$categoria->nro) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$categoria->nro}}">
		                    {{ csrf_field() }}
		                </form>

		                @if($categoria->estatus==1)
		                  <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-{{$categoria->nro}}', '¿Estas seguro que deseas desactivar esta categoría?', 'No aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>

		                @else
		                  <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$categoria->nro}}', '¿Estas seguro que deseas activar esta categoría?', 'Aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
		                @endif



					</div> 
	      		</div>
	      	</h5>
	    </div>
        <div id="collapse{{$categoria->nro}}" class="collapse" aria-labelledby="heading{{$categoria->nro}}" 
            data-parent="#accordion{{$categoria->nro}}" estado="0"
        >
        </div>
  	</div>
	@endforeach

  </div>
@endsection	

@section('scripts')
  <script>
      function showCategory(codigo){

        if (window.location.pathname.split("/")[1] === "software") {
        var url='/software/empresa';
        }else{
            var url = '/empresa';
        }

        $.ajax({
            url: url+'/puc/'+codigo+'/show',
            beforeSend: function(){
                // cargando(true);
            },
            success: function(data){

                    let html = ``;
                    let pos = 0;
                    let categories = data.categories;
                    let idCollapse = document.getElementById("collapse"+codigo);
                    let estadoCollapse = $("#collapse"+codigo).attr('estado');

                    
                    //Construcción del acrodeón

                    if(categories.length > 0){
                        categories.forEach( cat => {
                        html+=`
                        <div class="card-header" id="heading${cat.nro}">
                            <h5 class="mx-${cat.nivel}">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <a class="btn btn-link" data-toggle="collapse" 
                                        data-target="#collapse${cat.nro}" 
                                        aria-expanded="true" aria-controls="${cat.nro}"
                                            onclick="showCategory(${cat.codigo})"
                                        >
                                        <i class="fas fa-plus" id="iplus${cat.nro}"></i>
                                        <i class="fas fa-minus" id="iminus${cat.nro}" style="display:none;"></i>
                                        </a>
                                        <p class='btn nopadding' style="padding-left: ${cat.nivel*5}% !important;">${cat.nombre}</p>				
                                    </div>
                                    <div class="col-sm-4">${cat.codigo}</div>
                                        <div class="col-sm-3">
                                            <a href="#" onclick="modal_show('${url}/puc/create/${cat.codigo}', 'small');" class="btn btn-outline-primary btn-icons" title="Agregar sub-categoría"><i class="fas fa-plus"></i></a>
                                            <a href="#" onclick="modal_show('${url}/puc/${cat.codigo}/edit', 'small');" class="btn btn-outline-primary btn-icons" title="Modificar"><i class="far fa-edit"></i></a>
                                            <form action="${url}/puc/create/${cat.codigo}/act_desc" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-${cat.nro}">
                                                @csrf
                                            </form>

                                            <form action="${url}/puc/${cat.codigo}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-${cat.nro}">
                                                @csrf
                                            <input name="_method" type="hidden" value="DELETE">
                                            </form>
                                            <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-${cat.nro}', '¿Estas seguro que deseas eliminar la categoría?', 'Se borrara de forma permanente');">
                                                <i class="fas fa-times"></i>
                                            </button>
                                    </div>

                                </div>
                            </h5>
                            </div>
                            
                            <div id="collapse${cat.nro}" class="collapse" 
                                aria-labelledby="heading${cat.nro}" 
                                data-parent="#accordion${cat.nro}"
                                estado="0"
                            >
                            </div>

                        `;
                        });

                         //implementación del acordeon dentro del dom
                        if(estadoCollapse == 0){
                            $("#collapse"+codigo).append(html);
                            $("#collapse"+codigo).collapse();
                            $("#collapse"+codigo).collapse('show');
                            $("#collapse"+codigo).attr('estado',1);

                            $("#iplus"+codigo).attr('style','display:none;');
                            $("#iminus"+codigo).attr('style','display:block;');

                        }else{
                            if(idCollapse.classList.contains('show')){
                                $("#collapse"+codigo).collapse('show');

                                $("#iplus"+codigo).attr('style','display:none;');
                                $("#iminus"+codigo).attr('style','display:block;');
                            }else{
                                $("#collapse"+codigo).collapse('hide');

                                $("#iplus"+codigo).attr('style','display:block;');
                                $("#iminus"+codigo).attr('style','display:none;');
                            }
                        }
                    }else{
                        alert("no tiene cuentas hijas");
                    }
                },
                error: function(data){
                alert('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
                cargando(false);
                }
                // cargando(false);
        });

      }
  </script>
@endsection