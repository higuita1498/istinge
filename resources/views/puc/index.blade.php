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

	 @if($default) 
		<form action="{{ route('categorias.quitar') }}" method="POST">

			<h5 style="margin-left: 2%;">{{$default}} <button class="btn btn-outline-danger  btn-icons" type="submit" title="Quitar" ><i class="fas fa-ban"></i></h5>
			{{ csrf_field() }}
			
		</form>
		 
	@endif
	<!--
<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
				<thead class="thead-dark">
				<tr>
					<th>#</th>
					<th>Nombre</th>
					<th>Codigo</th>
					<th>Acciones</th>
				</tr>
				</thead>
				<tbody>
				<?php $c=0; ?>
				@foreach($categorias as $categoria)
					<tr @if($categoria->id==Session::get('id')) class="active_table " @endif>
						<td class="">{{$c=$c+1}}</td>
						<td class=""><a href="">c - {{$categoria->nombre}}</a></td>
						<td>{{$categoria->codigo}}</td>
						<td>
							<a href="#" onclick="modal_show('{{route('puc.create_id',$categoria->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Agregar sub-categoría"><i class="fas fa-plus"></i></a>
							<a href="#" onclick="modal_show('{{route('categorias.edit',$categoria->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Modificar"><i class="far fa-edit"></i></a>
							<form action="{{ route('categorias.act_desc',$categoria->nro) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$categoria->nro}}">
								{{ csrf_field() }}
							</form>

							@if($categoria->estatus==1)
								<button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-{{$categoria->nro}}', '¿Estas seguro que deseas desactivar esta categoría?', 'No aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>

								@if($categoria->id!=Auth::user()->empresa()->categoria_default)
									<form action="{{ route('categorias.default',$categoria->nro) }}" method="POST" style="display: none;" id="default-{{$categoria->nro}}">
										{{ csrf_field() }}
									</form>
									<button class="btn btn-outline-success  btn-icons" type="button" title="Por Defecto" onclick="confirmar('default-{{$categoria->nro}}', '¿Estas seguro que deseas colocar esta categoría por defecto?', 'Se marcara en categoría al crear un item');"><i class="fas fa-asterisk"></i></button>
								@endif

							@else
								<button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$categoria->nro}}', '¿Estas seguro que deseas activar esta categoría?', 'Aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
							@endif
						</td>
					</tr>
					@if($categoria->hijos()>0)
						@foreach($categoria->hijos(true) as $categoria1)
							<tr @if($categoria1->id==Session::get('id')) class="active_table " @endif>
								<td class="">{{$c=$c+1}}</td>
								<td class=""><a href="">c1 - {{$categoria1->nombre}}</a></td>
								<td>{{$categoria1->codigo}}</td>
								<td>
									<a href="#" onclick="modal_show('{{route('puc.create_id',$categoria1->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Agregar sub-categoría"><i class="fas fa-plus"></i></a>
									<a href="#" onclick="modal_show('{{route('categorias.edit',$categoria1->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Modificar"><i class="far fa-edit"></i></a>
									<form action="{{ route('categorias.act_desc',$categoria1->nro) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$categoria1->nro}}">
										{{ csrf_field() }}
									</form>

									@if($categoria1->estatus==1)
										<button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-{{$categoria1->nro}}', '¿Estas seguro que deseas desactivar esta categoría?', 'No aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>

										@if($categoria1->id!=Auth::user()->empresa()->categoria_default)
											<form action="{{ route('categorias.default',$categoria1->nro) }}" method="POST" style="display: none;" id="default-{{$categoria1->nro}}">
												{{ csrf_field() }}
											</form>
											<button class="btn btn-outline-success  btn-icons" type="button" title="Por Defecto" onclick="confirmar('default-{{$categoria1->nro}}', '¿Estas seguro que deseas colocar esta categoría por defecto?', 'Se marcara en categoría al crear un item');"><i class="fas fa-asterisk"></i></button>
										@endif

									@else
										<button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$categoria1->nro}}', '¿Estas seguro que deseas activar esta categoría?', 'Aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
									@endif
									@if($categoria1->usado()==0)
										<form action="{{ route('categorias.destroy',$categoria1->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$categoria1->nro}}">
											{{ csrf_field() }}
											<input name="_method" type="hidden" value="DELETE">
										</form>
										<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$categoria1->nro}}', '¿Estas seguro que deseas eliminar la categoría?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
									@endif
								</td>
							</tr>
							@if($categoria1->hijos()>0)
								@foreach($categoria1->hijos(true) as $categoria2)
									<tr @if($categoria2->id==Session::get('id')) class="active_table " @endif>
										<td class="">{{$c=$c+1}}</td>
										<td class=""><a href="">c2 - {{$categoria2->nombre}}</a></td>
										<td>{{$categoria2->codigo}}</td>
										<td>
											<a href="#" onclick="modal_show('{{route('puc.create_id',$categoria2->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Agregar sub-categoría"><i class="fas fa-plus"></i></a>
											<a href="#" onclick="modal_show('{{route('categorias.edit',$categoria2->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Modificar"><i class="far fa-edit"></i></a>
											<form action="{{ route('categorias.act_desc',$categoria2->nro) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$categoria2->nro}}">
												{{ csrf_field() }}
											</form>

											@if($categoria2->estatus==1)
												<button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-{{$categoria2->nro}}', '¿Estas seguro que deseas desactivar esta categoría?', 'No aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>

												@if($categoria2->id!=Auth::user()->empresa()->categoria_default)
													<form action="{{ route('categorias.default',$categoria2->nro) }}" method="POST" style="display: none;" id="default-{{$categoria2->nro}}">
														{{ csrf_field() }}
													</form>
													<button class="btn btn-outline-success  btn-icons" type="button" title="Por Defecto" onclick="confirmar('default-{{$categoria2->nro}}', '¿Estas seguro que deseas colocar esta categoría por defecto?', 'Se marcara en categoría al crear un item');"><i class="fas fa-asterisk"></i></button>
												@endif
											@else
												<button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$categoria2->nro}}', '¿Estas seguro que deseas activar esta categoría?', 'Aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
											@endif

											@if($categoria2->usado()==0)
												<form action="{{ route('categorias.destroy',$categoria2->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$categoria2->nro}}">
													{{ csrf_field() }}
													<input name="_method" type="hidden" value="DELETE">
												</form>
												<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$categoria2->nro}}', '¿Estas seguro que deseas eliminar la categoría?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
											@endif
										</td>
									</tr>
									@if($categoria2->hijos()>0)
										@foreach($categoria2->hijos(true) as $categoria3)
											<tr @if($categoria3->id==Session::get('id')) class="active_table " @endif>
												<td class="">{{$c=$c+1}}</td>
												<td class=""><a href="">c3 - {{$categoria3->nombre}}</a></td>
												<td>{{$categoria3->codigo}}</td>
												<td>
													<a href="#" onclick="modal_show('{{route('puc.create_id',$categoria3->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Agregar sub-categoría"><i class="fas fa-plus"></i></a>
													<a href="#" onclick="modal_show('{{route('categorias.edit',$categoria3->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Modificar"><i class="far fa-edit"></i></a>
													<form action="{{ route('categorias.act_desc',$categoria3->nro) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$categoria3->nro}}">
														{{ csrf_field() }}
													</form>

													@if($categoria3->estatus==1)
														<button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-{{$categoria3->nro}}', '¿Estas seguro que deseas desactivar esta categoría?', 'No aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
														@if($categoria3->id!=Auth::user()->empresa()->categoria_default)
															<form action="{{ route('categorias.default',$categoria3->nro) }}" method="POST" style="display: none;" id="default-{{$categoria3->nro}}">
																{{ csrf_field() }}
															</form>
															<button class="btn btn-outline-success  btn-icons" type="button" title="Por Defecto" onclick="confirmar('default-{{$categoria3->nro}}', '¿Estas seguro que deseas colocar esta categoría por defecto?', 'Se marcara en categoría al crear un item');"><i class="fas fa-asterisk"></i></button>
														@endif
													@else
														<button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$categoria3->nro}}', '¿Estas seguro que deseas activar esta categoría?', 'Aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
													@endif

													@if($categoria3->usado()==0)
														<form action="{{ route('categorias.destroy',$categoria3->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$categoria3->nro}}">
															{{ csrf_field() }}
															<input name="_method" type="hidden" value="DELETE">
														</form>
														<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$categoria3->nro}}', '¿Estas seguro que deseas eliminar la categoría?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
													@endif
												</td>
											</tr>
											@if($categoria3->hijos()>0)
												@foreach($categoria3->hijos(true) as $categoria4)
													<tr @if($categoria4->id==Session::get('id')) class="active_table " @endif>
														<td class="">{{$c=$c+1}}</td>
														<td class=""><a href="">c4 - {{$categoria4->nombre}}</a></td>
														<td>{{$categoria4->codigo}}</td>
														<td>
															<a href="#" onclick="modal_show('{{route('puc.create_id',$categoria4->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Agregar sub-categoría"><i class="fas fa-plus"></i></a>
															<a href="#" onclick="modal_show('{{route('categorias.edit',$categoria4->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Modificar"><i class="far fa-edit"></i></a>
															<form action="{{ route('categorias.act_desc',$categoria4->nro) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$categoria4->nro}}">
																{{ csrf_field() }}
															</form>

															@if($categoria4->estatus==1)
																<button class="btn btn-outline-secondary  btn-icons" type="button" title="Desactivar" onclick="confirmar('act_desc-{{$categoria4->nro}}', '¿Estas seguro que deseas desactivar esta categoría?', 'No aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
																@if($categoria4->id!=Auth::user()->empresa()->categoria_default)
																	<form action="{{ route('categorias.default',$categoria4->nro) }}" method="POST" style="display: none;" id="default-{{$categoria4->nro}}">
																		{{ csrf_field() }}
																	</form>
																	<button class="btn btn-outline-success  btn-icons" type="button" title="Por Defecto" onclick="confirmar('default-{{$categoria4->nro}}', '¿Estas seguro que deseas colocar esta categoría por defecto?', 'Se marcara en categoría al crear un item');"><i class="fas fa-asterisk"></i></button>
																@endif

															@else
																<button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$categoria4->nro}}', '¿Estas seguro que deseas activar esta categoría?', 'Aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
															@endif

															@if($categoria4->usado()==0)
																<form action="{{ route('categorias.destroy',$categoria4->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$categoria4->nro}}">
																	{{ csrf_field() }}
																	<input name="_method" type="hidden" value="DELETE">
																</form>
																<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$categoria4->nro}}', '¿Estas seguro que deseas eliminar la categoría?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
															@endif
														</td>
													</tr>
												@endforeach
											@endif
										@endforeach
									@endif
								@endforeach
							@endif
						@endforeach
					@endif
				@endforeach
				</tbody>
			</table>
		</div>
		{{$categorias->links()}}
	</div>-->
<div id="accordion">
	<!-- Nivel 1 -->
	@foreach($categorias as $categoria)
	<div class="card">
	    <div class="card-header" id="heading{{$categoria->nro}}">
	      	<h5 class="mb-0">
	      		<div class="row">
		      		<div class="col-sm-5">
		      			@if($categoria->hijos()>0)
		      				<a class="btn btn-link colapsea" data-toggle="collapse" data-target="#collapse{{$categoria->nro}}" aria-expanded="true" aria-controls="{{$categoria->nro}}">
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

		                   @if($categoria->id!=Auth::user()->empresa()->categoria_default)
	                   			<form action="{{ route('categorias.default',$categoria->nro) }}" method="POST" style="display: none;" id="default-{{$categoria->nro}}">
			                    {{ csrf_field() }}
			                	</form>
			                  <button class="btn btn-outline-success  btn-icons" type="button" title="Por Defecto" onclick="confirmar('default-{{$categoria->nro}}', '¿Estas seguro que deseas colocar esta categoría por defecto?', 'Se marcara en categoría al crear un item');"><i class="fas fa-asterisk"></i></button>
		                  	@endif

		                @else
		                  <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$categoria->nro}}', '¿Estas seguro que deseas activar esta categoría?', 'Aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
		                @endif



					</div> 
	      		</div>
	      	</h5>
	    </div>
	    @if($categoria->hijos()>0)
			<div id="collapse{{$categoria->nro}}" class="collapse" aria-labelledby="heading{{$categoria->nro}}" data-parent="#accordion{{$categoria->nro}}">
	       	
			<!-- Nivel 2 -->
	        @foreach($categoria->hijos(true) as $categoria1)
			    <div class="card-header" id="heading{{$categoria1->nro}}">
					<h5 class="mx-3">
						<div class="row">
					      	<div class="col-sm-5">
				      			@if($categoria1->hijos()>0)
				      				<a class="btn btn-link colapsea" data-toggle="collapse" data-target="#collapse{{$categoria1->nro}}" aria-expanded="true" aria-controls="{{$categoria1->nro}}">
					          		{{$categoria1->nombre}} 
					        	</a>

				      			@else
				      				<p class='btn nopadding' style="padding-left: 15%;">{{$categoria1->nombre}}</p> 
								@endif
					    	</div>
					      	<div class="col-sm-4">{{$categoria1->codigo}}</div>
				      		<div class="col-sm-3">
				      			<a href="#" onclick="modal_show('{{route('puc.create_id',$categoria1->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Agregar sub-categoría"><i class="fas fa-plus"></i></a>
		      					<a href="#" onclick="modal_show('{{route('categorias.edit',$categoria1->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Modificar"><i class="far fa-edit"></i></a>
		      					<form action="{{ route('categorias.act_desc',$categoria1->nro) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$categoria1->nro}}">
				                    {{ csrf_field() }}
				                </form>

				                @if($categoria1->estatus==1)
				                  <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-{{$categoria1->nro}}', '¿Estas seguro que deseas desactivar esta categoría?', 'No aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>

				                   @if($categoria1->id!=Auth::user()->empresa()->categoria_default)
			                   			<form action="{{ route('categorias.default',$categoria1->nro) }}" method="POST" style="display: none;" id="default-{{$categoria1->nro}}">
					                    {{ csrf_field() }}
					                	</form>
					                  <button class="btn btn-outline-success  btn-icons" type="button" title="Por Defecto" onclick="confirmar('default-{{$categoria1->nro}}', '¿Estas seguro que deseas colocar esta categoría por defecto?', 'Se marcara en categoría al crear un item');"><i class="fas fa-asterisk"></i></button>
				                  	@endif

				                @else
				                  <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$categoria1->nro}}', '¿Estas seguro que deseas activar esta categoría?', 'Aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
				                @endif
				                @if($categoria1->usado()==0)
					                <form action="{{ route('categorias.destroy',$categoria1->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$categoria1->nro}}">
					                    {{ csrf_field() }}
					                <input name="_method" type="hidden" value="DELETE">
					                </form>
					                <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$categoria1->nro}}', '¿Estas seguro que deseas eliminar la categoría?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
					              @endif

							</div>

				      	</div>
			      	</h5>
			    </div>
			    @if($categoria1->hijos()>0)
					<div id="collapse{{$categoria1->nro}}" class="collapse" aria-labelledby="heading{{$categoria1->nro}}" data-parent="#accordion{{$categoria1->nro}}">
						<!-- Nivel 3 -->
				        @foreach($categoria1->hijos(true) as $categoria2)
					    	<div class="card-header" id="heading{{$categoria2->nro}}">
						      <h5 class="mx-5">
							      	<div class="row">
							      		<div class="col-sm-5">
							      			@if($categoria2->hijos()>0)
							      				<a class="btn btn-link colapsea" data-toggle="collapse" data-target="#collapse{{$categoria2->nro}}" aria-expanded="true" aria-controls="{{$categoria2->nro}}">
								          		{{$categoria2->nombre}} 
								        	</a>

							      			@else
							      				<p class='btn nopadding' style="padding-left: 25%;">{{$categoria2->nombre}}</p>
											@endif
								    	</div>
								      	<div class="col-sm-4">{{$categoria2->codigo}}</div>
							      		<div class="col-sm-3">
							      			<a href="#" onclick="modal_show('{{route('puc.create_id',$categoria2->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Agregar sub-categoría"><i class="fas fa-plus"></i></a>
							      			<a href="#" onclick="modal_show('{{route('categorias.edit',$categoria2->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Modificar"><i class="far fa-edit"></i></a>
							      			<form action="{{ route('categorias.act_desc',$categoria2->nro) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$categoria2->nro}}">
							                    {{ csrf_field() }}
							                </form>

							                @if($categoria2->estatus==1)
							                  <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-{{$categoria2->nro}}', '¿Estas seguro que deseas desactivar esta categoría?', 'No aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>

							                  @if($categoria2->id!=Auth::user()->empresa()->categoria_default)
					                   			<form action="{{ route('categorias.default',$categoria2->nro) }}" method="POST" style="display: none;" id="default-{{$categoria2->nro}}">
							                    {{ csrf_field() }}
							                	</form>
							                  <button class="btn btn-outline-success  btn-icons" type="button" title="Por Defecto" onclick="confirmar('default-{{$categoria2->nro}}', '¿Estas seguro que deseas colocar esta categoría por defecto?', 'Se marcara en categoría al crear un item');"><i class="fas fa-asterisk"></i></button>
							                  @endif
							                @else
							                  <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$categoria2->nro}}', '¿Estas seguro que deseas activar esta categoría?', 'Aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
							                @endif

							                @if($categoria2->usado()==0)
								                <form action="{{ route('categorias.destroy',$categoria2->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$categoria2->nro}}">
								                    {{ csrf_field() }}
								                <input name="_method" type="hidden" value="DELETE">
								                </form>
								                <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$categoria2->nro}}', '¿Estas seguro que deseas eliminar la categoría?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
								              @endif

										</div>
							      	</div>
						      </h5>
						    </div>
						        @if($categoria2->hijos()>0)
						    	<div id="collapse{{$categoria2->nro}}" class="collapse" aria-labelledby="heading{{$categoria2->nro}}" data-parent="#accordion{{$categoria2->nro}}">
									<!-- Nivel 4 -->
							        @foreach($categoria2->hijos(true) as $categoria3)
							        	<div class="card-header" id="heading{{$categoria3->nro}}">
									      <h5 class="mx-6">
										      	<div class="row">
										      		<div class="col-sm-5">
												        
										      			@if($categoria3->hijos()>0)
										      				<a class="btn btn-link colapsea" data-toggle="collapse" data-target="#collapse{{$categoria3->nro}}" aria-expanded="true" aria-controls="{{$categoria3->nro}}">
											          		{{$categoria3->nombre}} 
											        	</a>

										      			@else
										      				<p class="btn " style=" padding-left: 25%;">{{$categoria3->nombre}}</p>
														@endif
											    	</div>
											      	<div class="col-sm-4">{{$categoria3->codigo}}</div>
										      		<div class="col-sm-3">
										      			<a href="#" onclick="modal_show('{{route('puc.create_id',$categoria3->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Agregar sub-categoría"><i class="fas fa-plus"></i></a>
										      			<a href="#" onclick="modal_show('{{route('categorias.edit',$categoria3->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Modificar"><i class="far fa-edit"></i></a>
										      			<form action="{{ route('categorias.act_desc',$categoria3->nro) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$categoria3->nro}}">
										                    {{ csrf_field() }}
										                </form>

										                @if($categoria3->estatus==1)
										                  <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-{{$categoria3->nro}}', '¿Estas seguro que deseas desactivar esta categoría?', 'No aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
										                  @if($categoria3->id!=Auth::user()->empresa()->categoria_default)
								                   			<form action="{{ route('categorias.default',$categoria3->nro) }}" method="POST" style="display: none;" id="default-{{$categoria3->nro}}">
										                    {{ csrf_field() }}
										                	</form>
										                  <button class="btn btn-outline-success  btn-icons" type="button" title="Por Defecto" onclick="confirmar('default-{{$categoria3->nro}}', '¿Estas seguro que deseas colocar esta categoría por defecto?', 'Se marcara en categoría al crear un item');"><i class="fas fa-asterisk"></i></button>
										                  @endif
										                @else
										                  <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$categoria3->nro}}', '¿Estas seguro que deseas activar esta categoría?', 'Aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
										                @endif

										                @if($categoria3->usado()==0)
											                <form action="{{ route('categorias.destroy',$categoria3->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$categoria3->nro}}">
											                    {{ csrf_field() }}
											                <input name="_method" type="hidden" value="DELETE">
											                </form>
											                <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$categoria3->nro}}', '¿Estas seguro que deseas eliminar la categoría?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
											              @endif

													</div>
										      	</div>
									      </h5>
									    </div>
									    @if($categoria3->hijos()>0)
										<div id="collapse{{$categoria3->nro}}" class="collapse" aria-labelledby="heading{{$categoria3->nro}}" data-parent="#accordion{{$categoria3->nro}}">
											<!-- Nivel 5 -->
											@foreach($categoria3->hijos(true) as $categoria4)
									        	<div class="card-header" id="heading{{$categoria4->nro}}">
											      <h5 class="mb-0">
												      	<div class="row">
												      		<div class="col-sm-5">
										      				<p class='btn nopadding' style="padding-left: 10%;">{{$categoria4->nombre}}</p>
													    	</div>
													      	<div class="col-sm-5">{{$categoria4->codigo}}</div>
												      		<div class="col-sm-2">
							      								<a href="#" onclick="modal_show('{{route('categorias.edit',$categoria4->nro)}}', 'small');" class="btn btn-outline-primary btn-icons" title="Modificar"><i class="far fa-edit"></i></a>
							      								<form action="{{ route('categorias.act_desc',$categoria4->nro) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$categoria4->nro}}">
												                    {{ csrf_field() }}
												                </form>

												                @if($categoria4->estatus==1)
												                  <button class="btn btn-outline-secondary  btn-icons" type="button" title="Desactivar" onclick="confirmar('act_desc-{{$categoria4->nro}}', '¿Estas seguro que deseas desactivar esta categoría?', 'No aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
											                   	@if($categoria4->id!=Auth::user()->empresa()->categoria_default)
										                   		<form action="{{ route('categorias.default',$categoria4->nro) }}" method="POST" style="display: none;" id="default-{{$categoria4->nro}}">
												                    {{ csrf_field() }}
												                </form>
												                  <button class="btn btn-outline-success  btn-icons" type="button" title="Por Defecto" onclick="confirmar('default-{{$categoria4->nro}}', '¿Estas seguro que deseas colocar esta categoría por defecto?', 'Se marcara en categoría al crear un item');"><i class="fas fa-asterisk"></i></button>
												                  @endif

												                @else
												                  <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$categoria4->nro}}', '¿Estas seguro que deseas activar esta categoría?', 'Aparecera para seleccionar en el inventario');"><i class="fas fa-power-off"></i></button>
												                @endif

												                @if($categoria4->usado()==0)
													                <form action="{{ route('categorias.destroy',$categoria4->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$categoria4->nro}}">
													                    {{ csrf_field() }}
													                <input name="_method" type="hidden" value="DELETE">
													                </form>
													                <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$categoria4->nro}}', '¿Estas seguro que deseas eliminar la categoría?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
													              @endif


															</div>
												      	</div>
											      </h5>
											    </div>
											@endforeach

										</div>	
										@endif
									@endforeach

					    		</div>	
								@endif
						@endforeach
				    </div>
				@endif
			@endforeach
	    	</div>
		@endif    
  	</div>
	@endforeach

  </div>
@endsection	