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
        <th>En uso</th>
        <th>Código</th>
        <th>Nombre</th>
        <th>Relacionado con</th>
        <th>cuenta contable</th>
        <th>Medio de pago<th>
        </tr>
    </thead>
    @foreach($formasPago as $forma)
        <tr>
            <td>
                <div class="">
                    <label class="form-check-label">
                        <input type="checkbox" class="forma-check" name="" value="{{$forma->en_uso}}" {{$forma->en_uso == 1 ? 'checked' : ''}} disabled>
                        <i class="input-helper"></i>
                    </label>
                </div>
                </td>
                <td>{{$forma->codigo}}</td>
                <td>{{$forma->nombre}}</td>
                <td>{{$forma->relacion()}}</td>
                <td>{{$forma->categoria->nombre}}</td>
                <td>{{$forma->formaPagoMedio->nombre}}</td>
                <td>
                <div clas="d-flex">
                    <a href="#"  onclick="delete_forma('{{$forma->id}}')"><i class="fas fa-times"></i></a>
                    <a href="#" onclick="edit_forma('{{$forma->id}}')" data-toggle="modal" data-target="#editForma" class="btn btn-icons"><i class="fas fa-edit"></i></a>
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
        <th><th>
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
        <td width="10%"><input type="text" class="form-control form-control-sm" placeholder="codigo" name="codigo" id="codigo"></td>
        <td width="15%"><input type="text" class="form-control form-control-sm" placeholder="nombre cuenta" name="nombrecuenta" id="nombrecuenta"></td>
        <td width="20%">
            <select class="form-control form-control-sm selectpicker p-0" name="relacion" id="relacion" title="Relacionado con" required="">
                <option value="1">Cartera - (Factura de venta - Recibos de caja)</option>
                <option value="2">Proveedores - (Factura de compra - Recibos de pago)</option>
                <option value="3">Cartera / Proveedores</option>
            </select>
        </td>
        <td width="25%">
            <select class="form-control form-control-sm selectpicker p-0" name="cuenta" id="cuenta" title="Cuenta contable" required="">
                @foreach($categorias as $cat)
                    <option value="{{$cat->id}}">{{$cat->nombre}}</option>
                @endforeach
            </select>
        </td>
        <td width="25%">
            <select class="form-control form-control-sm selectpicker p-0" name="mediopago" id="mediopago" title="Medio de pago doc. electrónico" required="">
                @foreach($mediosPago as $medio)
                    <option value="{{$medio->id}}">{{$medio->nombre}}</option>
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
  <div class="modal fade" id="editForma" tabindex="-1" role="dialog" aria-labelledby="editForma" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Editar Forma de Pago</h5>
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
                    <label for="message-text" class="col-form-label">Relacionado con:</label>
                    <select class="form-control form-control-sm selectpicker p-0" name="relacion_edit" id="relacion_edit" title="Relacionado con" required="">
                        <option value="1">Cartera - (Factura de venta - Recibos de caja)</option>
                        <option value="2">Proveedores - (Factura de compra - Recibos de pago)</option>
                        <option value="3">Cartera / Proveedores</option>
                    </select>
                </div>
    
                <div class="form-group col-md-6">
                    <label for="message-text" class="col-form-label">Cuenta contable:</label>
                    <select class="form-control form-control-sm selectpicker p-0" name="cuenta_edit" id="cuenta_edit" title="Cuenta contable" required="">
                        @foreach($categorias as $cat)
                            <option value="{{$cat->id}}">{{$cat->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            

            <div class="row">
                <div class="form-group col-md-6">
                    <label for="message-text" class="col-form-label">Medio de pago doc. electrónico:</label>
                    <select class="form-control form-control-sm selectpicker p-0" name="mediopago_edit" id="mediopago_edit" title="Medio de pago doc. electrónico" required="">
                        @foreach($mediosPago as $medio)
                            <option value="{{$medio->id}}">{{$medio->nombre}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6">
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
          <a type="button" onclick="update_forma()" class="btn btn-primary">Actualizar</a>
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

        var codigo    = $("#codigo").val();
        var nombre    = $("#nombrecuenta").val();
        var relacion  = $("#relacion").val();
        var cuenta_id = $("#cuenta").val();
        var medio_pago_id = $("#mediopago").val();
        var url = $("#url").val();

        var table = $("#table-forma");

        //*Petición ajax.
        $.ajax({
        url: url +'/empresa/formapago/store',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        method: 'POST',
        data: {
            checkForm:checkForm, 
            codigo:codigo,
            nombre:nombre,
            relacion:relacion,
            cuenta_id:cuenta_id,
            medio_pago_id:medio_pago_id
        },
        beforeSend: function(){
            cargando(true);
        },
        success: function(pago){

                if(pago != null){   
                    table.append(`
                    <tr>
                        <td>
                            <div class="">
                                <label class="form-check-label">
                                    <input type="checkbox" class="forma-check" name="contacto[]" value="${pago.en_uso}" checked="">
                                    <i class="input-helper"></i>
                                </label>
                            </div>
                        </td>
                        <td>${pago.codigo}</td>
                        <td>${pago.nombre}</td>
                        <td>${pago.relacion}</td>
                        <td>${pago.cuenta}</td>
                        <td>${pago.medioPago}</td>
                        <td>
                            <div clas="d-flex">
                                <a href="#" onclick="delete_forma('${$pago.id}')"><i class="fas fa-times"></i></a>
                                <a href="#" onclick="editForma('${pago.id}')" data-toggle="modal" data-target="#editForma" href=""><i class="fas fa-edit"></i></a>
                            </div>
                        </td>
                    </tr>
                    `);
                }

                cargando(false);
            },
            error: function(pago){
                alert('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
            }
        });
}

function edit_forma(id) {

    var url = $("#url").val();
    $.ajax({
        url: url +'/empresa/formapago/edit',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        type: "GET",
        datatype: "json",
        data: {
            id: id,
        },
        success: function (response) {

            var forma = response.forma;

            //limpiamos los campos
            
            $("#id_edit").val()
            $("#codigo_edit").val();
            $("#nombrecuenta_edit").val();
            $("#relacion_edit").val();
            $("#cuenta_edit").val();
            $("#mediopago_edit").val();
            $("#checkForm_edit").prop("checked", false);

            //Agregamos las variables a los campos.
            $("#id_edit").val(forma.id)
            $("#codigo_edit").val(forma.codigo);
            $("#nombrecuenta_edit").val(forma.nombre);
            $("#relacion_edit").selectpicker("val",forma.relacion);
            $("#cuenta_edit").selectpicker("val",forma.cuenta_id)
            $("#mediopago_edit").selectpicker("val",forma.medio_pago_id);
            $("#checkForm_edit").prop("checked", forma.en_uso);

            //refrescamos los selectpicker
            $("#relacion_edit").selectpicker("refresh");
            $("#cuenta_edit").selectpicker("refresh");
            $("#mediopago_edit").selectpicker("refresh");

        },
    });
}


function update_forma(){
    var url = $("#url").val();

    //obtenemos las Variables
    var id = $("#id_edit").val();
    var codigo = $("#codigo_edit").val();
    var nombre = $("#nombrecuenta_edit").val();
    var relacion = $("#relacion_edit").val();
    var cuenta_id = $("#cuenta_edit").val();
    var medio_pago_id = $("#mediopago_edit").val();

    var checkForm = 0;
        if($("#checkForm_edit").prop('checked')){
            checkForm = 1;
    }

    
    $.ajax({
        url: url +'/empresa/formapago/update',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        type: "POST",
        datatype: "json",
        data: {
            id : id,
            codigo : codigo,
            nombre : nombre,
            relacion : relacion,
            cuenta_id : cuenta_id,
            medio_pago_id : medio_pago_id,
            checkForm : checkForm,
        },
        beforeSend: function(){
            cargando(true);
        },
        success: function (pago) {
            location.reload();
        },
        error: function(pago){
                alert('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
        }
    });
}

function delete_forma(id){

    var url = $("#url").val();

    Swal.fire({
        title: "Eliminar Forma de Pago",
        text: "No podrás retroceder esta acción",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: "Si, eliminar",
    }).then((result) => {
      if (result.value) {
        $.ajax({
        url: url +'/empresa/formapago/delete',
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
            if(response.forma){
                location.reload();
            }
            else{
                cargando(false);
                alert('No pudo ser eliminado la forma de pago');
            }
        },
        error: function(response){
                alert('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
                cargando(false);
        }
    });
      }
    })
}
</script>

@endsection
