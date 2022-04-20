@extends('layouts.app')

@section('content')

<style>
    .table-forma1{
        border:none;width:98%;height:auto;
        margin:10px;
    }

    .table-forma1 thead{
        background-color:#ccc;
    }

    .forma-check{
        margin-left: 10px;
    }
</style>

    {{-- url sobre la cual se haran las peticiones --}}
    <input type="hidden" id="url" value="{{url('/')}}">

    <table class="table-forma1" id="table-forma">
    <thead>
        <tr>
        <th width="5%">En uso</th>
        <th width="15%">Código</th>
        <th width="15%">Nombre</th>
        <th width="15%">Inventario</th>
        <th width="15%">Costo</th>
        <th width="15%">Venta</th>
        <th width="15%">Devolución</th>
        <th width="5%"></th>
        </tr>
    </thead>
    @foreach($productos as $producto)
        <tr>
            <td>
                <div class="">
                    <label class="form-check-label">
                        <input type="checkbox" class="forma-check" name="" value="{{$producto->en_uso}}" {{$producto->en_uso == 1 ? 'checked' : ''}} disabled>
                        <i class="input-helper"></i>
                    </label>
                </div>
                </td>
                <td>{{$producto->codigo}}</td>
                <td>{{$producto->nombre}}</td>
                <td>{{$producto->inventario()->nombre}}</td>
                <td>{{$producto->costo()->nombre}}</td>
                <td>{{$producto->venta()->nombre}}</td>
                <td>{{$producto->devolucion()->nombre}}</td>
                <td>
                <div clas="d-flex">
                    <a href="#"  onclick="delete_prodcuto('{{$producto->id}}')"><i class="fas fa-times"></i></a>
                    <a href="#" onclick="edit_producto('{{$producto->id}}')" data-toggle="modal" data-target="#editProducto" class="btn btn-icons"><i class="fas fa-edit"></i></a>
                </div>
            </td>
        </tr>
    @endforeach
    
  </table>

  <table class="table-forma1">
       <thead>
        <tr>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        </tr>
    </thead>
    <tr>
        <td width="5%">
        <div class="">
            <label class="form-check-label">
                <input type="checkbox" class="forma-check" name="checkForm" id="checkForm" value="">
                <i class="input-helper"></i>
            </label>
        </div>
        </td>
        <td width="15%"><input type="text" class="form-control form-control-sm" placeholder="codigo" name="codigo" id="codigo"></td>
        <td width="15%"><input type="text" class="form-control form-control-sm" placeholder="nombre cuenta" name="nombrecuenta" id="nombrecuenta"></td>
        <td width="15%">
            <select class="form-control form-control-sm selectpicker p-0" data-live-search="true" data-size="5" name="inventario_producto" id="inventario_producto" title="Inventario" required="">
                @foreach($categorias as $cat)
                    <option value="{{$cat->id}}">{{$cat->nombre}} - {{$cat->codigo}}</option>
                @endforeach
            </select>
        </td>
        <td width="15%">
            <select class="form-control form-control-sm selectpicker p-0"  data-live-search="true" data-size="5" name="costo" id="costo" title="Costo" required="">
                @foreach($categorias as $cat)
                    <option value="{{$cat->id}}">{{$cat->nombre}} - {{$cat->codigo}}</option>
                @endforeach
            </select>
        </td>
        <td width="15%">
            <select class="form-control form-control-sm selectpicker p-0"  data-live-search="true" data-size="5" name="inventario_producto" id="venta_producto" title="Venta" required="">
                @foreach($categorias as $cat)
                    <option value="{{$cat->id}}">{{$cat->nombre}} - {{$cat->codigo}}</option>
                @endforeach
            </select>
        </td>
        <td width="15%">
            <select class="form-control form-control-sm selectpicker p-0" data-live-search="true" data-size="5" name="devolucion" id="devolucion" title="Devolución" required="">
                @foreach($categorias as $cat)
                    <option value="{{$cat->id}}">{{$cat->nombre}} - {{$cat->codigo}}</option>
                @endforeach
            </select>
        </td>
        <td width="5%">
            <div clas="d-flex">
                <a href="#" onclick="savePaymentMethod()"><i class="fas fa-save"></i></a>
            </div>
        </td>
    </tr>
  </table>

  {{-- Modal editar --}}    
  <div class="modal fade" id="editProducto" tabindex="-1" role="dialog" aria-labelledby="editProducto" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Editar Producto o Servicio</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form>
            <input type="hidden" class="form-control form-control-sm" placeholder="codigo" name="id_edit" id="id_edit">
        
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="message-text" class="col-form-label">Código:</label>
                    <input type="text" class="form-control form-control-sm" placeholder="codigo" name="codigo" id="codigo_edit">
                  </div>

                  <div class="form-group col-md-6">
                    <label for="message-text" class="col-form-label">Nombre cuenta:</label>
                    <input type="text" class="form-control form-control-sm" placeholder="nombre cuenta" name="nombrecuenta_edit" id="nombrecuenta_edit">
                </div>
            </div>
            
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="message-text" class="col-form-label">Inventario:</label>
                    <select class="form-control form-control-sm selectpicker p-0" data-live-search="true" data-size="5" name="inventario_edit" id="inventario_edit" title="Inventario" required="">
                        @foreach($categorias as $cat)
                        <option value="{{$cat->id}}">{{$cat->nombre}} - {{$cat->codigo}}</option>
                        @endforeach
                    </select>
                </div>
    
                <div class="form-group col-md-6">
                    <label for="message-text" class="col-form-label">Costo:</label>
                    <select class="form-control form-control-sm selectpicker p-0" data-live-search="true" data-size="5" name="costo_edit" id="costo_edit" title="Costo" required="">
                        @foreach($categorias as $cat)
                            <option value="{{$cat->id}}">{{$cat->nombre}} - {{$cat->codigo}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            

            <div class="row">
                <div class="form-group col-md-6">
                    <label for="message-text" class="col-form-label">Venta:</label>
                    <select class="form-control form-control-sm selectpicker p-0" data-live-search="true" data-size="5" name="venta_edit" id="venta_edit" title="Venta" required="">
                        @foreach($categorias as $cat)
                            <option value="{{$cat->id}}">{{$cat->nombre}} - {{$cat->codigo}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="message-text" class="col-form-label">Devolución:</label>
                    <select class="form-control form-control-sm selectpicker p-0" data-live-search="true" data-size="5" name="devolucion_edit" id="devolucion_edit" title="Devolución" required="">
                        @foreach($categorias as $cat)
                            <option value="{{$cat->id}}">{{$cat->nombre}} - {{$cat->codigo}}</option>
                        @endforeach
                    </select>

                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-12">
                    <label for="recipient-name" class="col-form-label">¿En uso?:</label>
                    <label class="form-check-label">
                        <input type="checkbox" class="forma-check" name="checkForm_edit" id="checkForm_edit" value="">
                        <i class="input-helper"></i>
                    </label>
                </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <a type="button" onclick="update_producto()" class="btn btn-primary">Actualizar</a>
        </div>
      </div>
    </div>
  </div>
  {{-- End Section modal Editar --}}

@endsection

@section('scripts')

<script>

    function savePaymentMethod(){

        //*Variables
        var checkForm = 0;
        if($("#checkForm").prop('checked')){
            checkForm = 1;
        }

        var validate = validateCampos();

        if(validate){
        var codigo      = $("#codigo").val();
        var nombre      = $("#nombrecuenta").val();
        var inventario  = $("#inventario_producto").val();
        var costo       = $("#costo").val();
        var venta       = $("#venta_producto").val();
        var devolucion  = $("#devolucion").val();
        var url         = $("#url").val();

        var table = $("#table-forma");

        //*Petición ajax.
        $.ajax({
        url: url +'/empresa/productoservicio/store',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        method: 'POST',
        data: {
            checkForm:checkForm, 
            codigo:codigo,
            nombre:nombre,
            inventario:inventario,
            costo:costo,
            venta:venta,
            devolucion:devolucion,
        },
        beforeSend: function(){
            cargando(true);
        },
        success: function(producto){

                if(producto != null){   
                    table.append(`
                    <tr>
                        <td>
                            <div class="">
                                <label class="form-check-label">
                                    <input type="checkbox" class="forma-check" name="contacto[]" value="${producto.en_uso}" ${producto.en_uso == 1 ? 'checked' : ''} disabled>
                                    <i class="input-helper"></i>
                                </label>
                            </div>
                        </td>
                        <td>${producto.codigo}</td>
                        <td>${producto.nombre}</td>
                        <td>${producto.inventario}</td>
                        <td>${producto.costo}</td>
                        <td>${producto.venta}</td>
                        <td>${producto.devolucion}</td>
                        <td>
                            <div clas="d-flex">
                                <a href="#" onclick="delete_producto('${producto.id}')"><i class="fas fa-times"></i></a>
                                <a href="#" onclick="edit_producto('${producto.id}')" data-toggle="modal" data-target="#editProducto" href=""><i class="fas fa-edit"></i></a>
                            </div>
                        </td>
                    </tr>
                    `);
                }

                cargando(false);
            },
            error: function(producto){
                alert('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
            }
        });
        }else{
            alert("Debe llenar todos los campos.");
        }
        
}

function validateCampos(){

    if( 
        $("#codigo").val() == "" || 
        $("#inventario_producto").val() == "" || 
        $("#costo").val() == "" || 
        $("#venta_producto").val() == "" ||
        $("#devolucion").val() == ""
    ){
        return false;
    }else{
        return true;
    }
}

function edit_producto(id) {

    var url = $("#url").val();
    $.ajax({
        url: url +'/empresa/productoservicio/edit',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        type: "GET",
        datatype: "json",
        data: {
            id: id,
        },
        success: function (response) {

            var producto = response.producto;

            //limpiamos los campos
            
            $("#id_edit").val()
            $("#codigo_edit").val();
            $("#nombrecuenta_edit").val();
            $("#inventario_edit").val();
            $("#costo_edit").val();
            $("#venta_edit").val();
            $("#devolucion_edit").val();
            $("#checkForm_edit").prop("checked", false);

            //Agregamos las variables a los campos.
            $("#id_edit").val(producto.id)
            $("#codigo_edit").val(producto.codigo);
            $("#nombrecuenta_edit").val(producto.nombre);
            $("#inventario_edit").selectpicker("val",producto.inventario_id);
            $("#costo_edit").selectpicker("val",producto.costo_id)
            $("#venta_edit").selectpicker("val",producto.venta_id);
            $("#devolucion_edit").selectpicker("val",producto.devolucion_id);
            $("#checkForm_edit").prop("checked", producto.en_uso);

            //refrescamos los selectpicker
            $("#inventario_edit").selectpicker("refresh");
            $("#costo_edit").selectpicker("refresh");
            $("#venta_edit").selectpicker("refresh");
            $("#devolucion_edit").selectpicker("refresh");

        },
    });
}


function update_producto(){
    var url = $("#url").val();

    //obtenemos las Variables
    var id = $("#id_edit").val();
    var codigo = $("#codigo_edit").val();
    var nombre = $("#nombrecuenta_edit").val();
    var inventario = $("#inventario_edit").val();
    var costo = $("#costo_edit").val();
    var venta = $("#venta_edit").val();
    var devolucion = $("#devolucion_edit").val();

    var checkForm = 0;
        if($("#checkForm_edit").prop('checked')){
            checkForm = 1;
    }

    
    $.ajax({
        url: url +'/empresa/productoservicio/update',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        type: "POST",
        datatype: "json",
        data: {
            id : id,
            codigo : codigo,
            nombre : nombre,
            inventario : inventario,
            costo : costo,
            venta : venta,
            devolucion : devolucion,
            checkForm : checkForm,
        },
        beforeSend: function(){
            cargando(true);
        },
        success: function (producto) {
            location.reload();
        },
        error: function(producto){
                alert('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
        }
    });
}

function delete_prodcuto(id){

    var url = $("#url").val();

    Swal.fire({
        title: "¿Eliminar producto?",
        text: "No podrás retroceder esta acción y se puede eliminar mientras no esté en uso en un movimiento contable",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: "Si, eliminar",
    }).then((result) => {
      if (result.value) {
        $.ajax({
        url: url +'/empresa/productoservicio/delete',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        type: "POST",
        datatype: "json",
        data: {
            id : id,
        },
        beforeSend: function(){
            cargando(true);
        },
        success: function (response) {
            if(response.producto){
                location.reload();
            }
            else{
                cargando(false);
                alert('No pudo ser eliminado la forma de pago');
            }
        },
        error: function(response){
                cargando(false);
                alert('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
        }
    });
      }
    })
}
</script>

@endsection
