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

    <table class="table-forma1">
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
    <tr>
      <td>
        <div class="">
            <label class="form-check-label">
                <input type="checkbox" class="forma-check" name="contacto[]" value="0" checked="">
                <i class="input-helper"></i>
            </label>
        </div>
      </td>
      <td>1</td>
      <td>Efectivo</td>
      <td>Cartera / Proveedores</td>
      <td>015645465 - Caja general</td>
      <td>Efectivo</td>
      <td>
        <div clas="d-flex">
            <a href=""><i class="fas fa-times"></i></a>
            <a href=""><i class="fas fa-edit"></i></a>
        </div>
    </td>
    </tr>

    <tr>
        <td>
          <div class="">
              <label class="form-check-label">
                  <input type="checkbox" class="forma-check" name="checkForm" id="checkForm" value="0" >
                  <i class="input-helper"></i>
              </label>
          </div>
        </td>
        <td><input type="text" class="form-control form-control-sm" placeholder="codigo" name="codigo" id="codigo"></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="nombre cuenta" name="nombrecuenta" id="nombrecuenta"></td>
        <td>
            <select class="form-control form-control-sm selectpicker p-0" name="relacion" id="relacion" title="Relacionado con" required="">
                <option value="1">Cartera - (Factura de venta - Recibos de caja)</option>
                <option value="2">Proveedores - (Factura de compra - Recibos de pago)</option>
                <option value="3">Cartera / Proveedores</option>
            </select>
        </td>
        <td>
            <select class="form-control form-control-sm selectpicker p-0" name="cuenta" id="cuenta" title="Cuenta contable" required="">
                <option value="1">233258815 - ahorros</option>
                <option value="2">233258815 - ahorros</option>
                <option value="3">233258815 - ahorros</option>
            </select>
        </td>
        <td>
            <select class="form-control form-control-sm selectpicker p-0" name="mediopago" id="mediopago" title="Medio de pago doc. electrónico" required="">
                <option value="1">Efectivo</option>
                <option value="2">Créditro ahorro</option>
                <option value="3">Débito ahorro</option>
            </select>
        </td>
        <td>
            <div clas="d-flex">
                <a href="#" onclick="savePaymentMethod()"><i class="fas fa-save"></i></a>
            </div>
        </td>
      </tr>
  </table>


@endsection

@section('scripts')

  <script>
      function savePaymentMethod(){

        //*Variables
        var checkForm       = $("#checkForm").val();
        var codigo          = $("#codigo").val();
        var nombrecuenta    = $("#nombrecuenta").val();
        var relacion        = $("#relacion").val();
        var cuenta          = $("#cuenta").val();
        var mediopago       = $("#mediopago").val();
        var url             = $("#url").val();

        //*Petición ajax.
        $.ajax({
        url: url +'/empresa/formapago/store',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        method: 'POST',
        data: {
            checkForm:checkForm, 
            codigo:codigo,
            nombrecuenta:nombrecuenta,
            relacion:relacion,
            cuenta:cuenta,
            mediopago:mediopago
        },
        // beforeSend: function(){
        //     cargando(true);
        // },
        success: function(data){

            console.log(data);
            cargando(false);
        },
        error: function(data){
            alert('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
        }
    });

        console.log(checkForm + "-" + codigo + "-" + relacion + "-" + cuenta + "-" + mediopago);
          alert("boton guardar");
      }
  </script>

@endsection
