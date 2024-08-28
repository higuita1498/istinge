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

@if(Session::has('error'))
  <div class="alert alert-danger" >
    {{Session::get('error')}}
  </div>

  <script type="text/javascript">
    setTimeout(function(){
        $('.alert').hide();
        $('.active_table').attr('class', ' ');
    }, 5000);
  </script>
@endif

@if(Session::has('success-newcontact'))
<div class="alert alert-success" style="text-align: center;">
  {{Session::get('success-newcontact')}}
</div>

<script type="text/javascript">
  setTimeout(function(){
    $('.alert').hide();
    $('.active_table').attr('class', ' ');
  }, 5000);
</script>
@endif

<style>
    #titulo{
        display:none;
    }
</style>

<div class="paper">
  <!-- Membrete -->
	<div class="row">
    <div class="col-md-4 text-center align-self-center">
      <img class="img-responsive" src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo)}}" alt="" width="50%">
    </div>
    <div class="col-md-4 text-center align-self-center">
      <h4>{{Auth::user()->empresa()->nombre}}</h4>
      <p>{{Auth::user()->empresa()->tip_iden('mini')}} {{Auth::user()->empresa()->nit}} @if(Auth::user()->empresa()->dv != null || Auth::user()->empresa()->dv == 0) - {{Auth::user()->empresa()->dv}} @endif<br> {{Auth::user()->empresa()->email}}</p>
    </div>
    <div class="col-md-4 text-center align-self-center" >
      {{-- <h4><b class="text-primary">No. </b> {{$nro->prefijo}}{{$nro->inicio}}</h4> --}}
    </div>
	</div>
	<hr>
  <!--Formulario Facturas-->
	<form method="POST" action="{{ route('asignacionmaterial.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" >
		{{ csrf_field() }}

        <input type="hidden" value="1" name="referencia" id="referencia">
		<div class="row text-right">
			<div class="col-md-5">
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Tecnico <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <select class="form-control selectpicker" name="id_tecnico" id="id_tecnico" required="" title="Seleccione" data-live-search="true" data-size="5" >
                                @foreach($tecnicos as $tecnico)
                                  <option value="{{$tecnico->id}}">{{$tecnico->nombres}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
  		        </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Fecha <span class="text-danger">*</span> <a><i data-tippy-content="Fecha en la que se realiza la factura de venta" class="icono far fa-question-circle"></i></a></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control"  id="fecha" value="{{$fecha}}" name="fecha" disabled=""  >
                    </div>
                </div>
		    </div>
        </div>
        <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>
        <hr>
        <div class="fact-table">
		    <div class="row">
			    <div class="col-md-12">
                    <table class="table table-striped table-sm" id="table-form" width="100%">
                        <thead class="thead-dark">
                            <tr>
                                <th width="24%">Material</th>
                                <th width="10%">Referencia - Material</th>
                                <th width="13%">Descripción</th>
                                <th width="7%">Cantidad</th>
                                <th width="2%"></th>
                            </tr>
                        </thead>
                        <tbody  id="dynamic-table">
                            <tr id="1">
                                <td  class="no-padding" style="padding-top: 2% !important;">
                                  <select class="form-control selectpicker items_inv"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item1" onchange="setReference(1, this.value);" required="">
                                   @foreach($inventario as $item)
                                    <option value="{{$item->id}}">{{$item->producto}} - ({{$item->ref}})</option>
                                   @endforeach
                                  </select>
                                </td>
                            <td>
                                <div class="resp-refer">
                                    <input type="text" class="form-control form-control-sm" id="ref1" name="ref[]" placeholder="Referencia" required="">
                                </div>
                                <td  style="padding-top: 1% !important;">
                                    <div class="resp-descripcion">
                                        <textarea  class="form-control form-control-sm" id="descripcion1" name="descripcion[]" placeholder="Descripción" ></textarea>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" id="cant1" name="cant[]" placeholder="Cantidad" min="1" required="" onblur="checkStock(1)">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-secondary btn-icons" onclick="removeRow(1);">X</button>
                                </td>
                            </tr>
                      </tbody>
                    </table>
                    <div class="alert alert-danger" style="display: none;" id="error-items"></div>
                </div>
		    </div>
            <button class="btn btn-outline-primary" id="add-row" type="button" style="margin-top: 2%">Agregar línea</button>
            <div class="row" style="margin-top: 5%; padding: 3%; min-height: 180px;">
              <div class="col-md-12 form-group">
                <label class="form-label">Notas <a><i data-tippy-content="" class="icono far fa-question-circle"></i></a>
                </label>
                <textarea  class="form-control form-control-sm min_max_100" name="notas"></textarea>
              </div>
            </div>
            <div class="col-md-12"><small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small></div>
  	        <hr>

            <div class="row ">
                <div class="col-sm-12 text-right" style="padding-top: 1%;">
                    <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
                    <a href="{{route('asignacionmaterial.index')}}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
    <script>
        const items = @json($inventario);
        function setReference(rowId, itemId){
            const item = items.find(i => i.id == itemId);

            // Rellenar la referencia
            document.getElementById('ref' + rowId).value = item.ref;
            if(parseInt(item.nro) < 0){
                Swal.fire({
                    position: 'top-center',
                    type: 'info',
                    title: 'Cantidad negativa.',
                    text: 'El material cuenta con cantidad en stock negativa',
                    showConfirmButton: true,
                });
            }
        }
        function checkStock(rowId) {
            const itemId = document.getElementById('item' + rowId).value;
            const item = items.find(i => i.id == itemId);
            const cantidadIngresada = parseInt(document.getElementById('cant' + rowId).value);

            if (item && cantidadIngresada > parseFloat(item.nro)) {
                const availableStock = Math.floor(parseFloat(item.nro));
                Swal.fire({
                    position: 'top-center',
                    icon: 'info',
                    title: 'Stock insuficiente',
                    text: `La cantidad ingresada excede la cantidad disponible en stock (${availableStock}).`,
                    showConfirmButton: true,
                });
            }
        }
        let rowCount = 1;

        // Function to add a new row
        document.getElementById('add-row').addEventListener('click', function() {
            rowCount++;
            let table = document.getElementById('dynamic-table');
            let newRow = document.createElement('tr');
            newRow.id = rowCount;
            newRow.innerHTML = `
            <td>
              <select class="form-control selectpicker items_inv" title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item${rowCount}" onchange="setReference(${rowCount}, this.value);" required>
               @foreach($inventario as $item)
            <option value="{{$item->id}}">{{$item->producto}} - ({{$item->ref}})</option>
               @endforeach
            </select>
          </td>
          <td>
              <input type="text" class="form-control form-control-sm" id="ref${rowCount}" name="ref[]" placeholder="Referencia" required>
            </td>
            <td>
                <textarea class="form-control form-control-sm" id="descripcion${rowCount}" name="descripcion[]" placeholder="Descripción"></textarea>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" id="cant${rowCount}" name="cant[]" placeholder="Cantidad" min="1" required onblur="checkStock(${rowCount});">
            </td>
            <td>
                <button type="button" class="btn btn-outline-secondary btn-icons" onclick="removeRow(${rowCount});">X</button>
            </td>
        `;
            table.appendChild(newRow);

            // Refresh selectpicker
            $('.selectpicker').selectpicker('refresh');
        });

        // Function to remove a row
        function removeRow(id) {
            document.getElementById(id).remove();
        }
    </script>
@endsection
