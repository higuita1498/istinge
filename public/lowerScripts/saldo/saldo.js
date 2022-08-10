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

function crearFilaSaldo(){

    //logica para tener numeros de los tr |importante|
    var nro=$('#table-saldoinicial tbody tr').length +1 ;
    if ($('#saldoini'+nro).length > 0) {
        for (i = 1; i <= nro; i++) {
            if ($('#saldoini'+i).length == 0) {
                nro=i;
                break;
            }
        }
    }

    let contactos = JSON.parse($('#jsonContactos').val());
    let puc = JSON.parse($('#jsonPuc').val());

    $('#table-saldoinicial tbody').append(
        `<tr  id="saldoini${nro}" fila="${nro}">` +
        `
        <td>${nro}</td>
        <td  class="no-padding">
          <select name="puc_cuenta[]" id="puc_cuenta${nro}" onchange="validateDetalleCartera(this.value,${nro})" class="form-control form-control-sm selectpicker p-0" data-live-search="true" data-size="5" required>

          </select>
        </td>
        <td  class="no-padding" id="tdsaldoini${nro}">
           <select name="contacto[]" id="contacto${nro}" class="form-control form-control-sm selectpicker p-0" data-live-search="true" data-size="5" required>

            </select>
        </td>
        <td>
            <div class="d-none justify-content-between" id="divCartera${nro}">
                    <input type="text" class="form-control form-control-sm" 
                    name="detalleComprobante[]"
                    prefijo="" nroComprobante=""  cuota="" fecha="" tipo="" id="divInput${nro}"
                    readonly>
                    <a class="btn btn-primary-sm" onclick="modalComprobante(${nro})" style="
                    padding: 0px;
                    margin-top: 3px;" data-toggle="modal" data-target="#editCartera"><i class="far fa-arrow-alt-circle-down"></i></a>
            </div></td>
        <td>
            <input type="text" class="form-control form-control-sm" name="descripcion[]" id="descripcion${nro}">
        </td>
        <td>
                <input type="number" min="0" name="debito[]" id="debito${nro}"  onkeyup="totalSaldoInicial()" class="form-control form-control-sm" placeholder="Débito" required>
        </td>
        <td>
        <input type="number" min="0" name="credito[]" id="credito${nro}" onkeyup="totalSaldoInicial()" class="form-control form-control-sm" placeholder="Crédito" required>
        </td>
        <td>
            <div clas="d-flex">
                <a href="#" onclick="crearFilaSaldo()"><i class="fas fa-save"></i></a>
                <a href="#" onclick="eliminarSaldo('saldoini${nro}')"><i class="fas fa-trash"></i></a>
            </div>
        </td>
    ` +
        `</tr>`
    );
    

    //Valores iniciales para seleccionar cuenta.
    $('#puc_cuenta'+nro).append($('<option>',
        {
            value: 0,
            text : 'Seleccione una opción',
            selected: true,
            disabled: true
        }
    ));

    $.each( puc, function( key, value ){
        $('#puc_cuenta'+nro).append($('<option>',
            {
                value: value.id,
                text : value.codigo+" - "+ value.nombre+""
            }));
    });

    //Valores iniciales para seleccionar un contacto.
    $('#contacto'+nro).append($('<option>',
        {
            value: 0,
            text : 'Seleccione una opción',
            selected: true,
            disabled: true
        }
    ));

    $.each( contactos, function( key, value ){
        $('#contacto'+nro).append($('<option>',
            {
                value: value.id,
                text : value.nombre
            }));
    });

   

    $('#contacto'+nro).selectpicker('refresh');
    $('#puc_cuenta'+nro).selectpicker('refresh');

}

/* Remueve los tr de una tabla */
function eliminarSaldo(i) {
    $("#" + i).remove();
    totalSaldoInicial();
}

function totalSaldoInicial(nro){
    
    let totalCredito = 0;
    let totalDebito = 0;

    $('#table-saldoinicial tbody tr').each(function() {

        var idFila=$(this).attr('fila');
        idCredito=$("#credito"+idFila);
        idDebito=$("#debito"+idFila);

        if (idCredito.val()) {
            totalCredito+=parseFloat(idCredito.val());
        }

        if (idDebito.val()) {
            totalDebito+=parseFloat(idDebito.val());
        }

    });

    // let totalFactura = document.getElementById('total'); 
    // totalFactura = totalFactura.textContent;
    // totalFactura = parseFloat(totalFactura.replace(/[$.]/g,''));

    // if(total > totalFactura){
    //     swal({
    //         title: 'Error',
    //         html: 'El total de las formas de pago no puede superar el total de la factura.',
    //         type: 'error',
    //         showConfirmButton: true,
    //         confirmButtonColor: '#1A59A1',
    //         confirmButtonText: 'ACEPTAR',
    //     });
    //     $("#precioformapago"+nro).val(0);
    //     return;
    // }

    if(totalCredito != totalDebito){
        $("#spanError").html("El débito y el crédito están disparejos por: $" + Math.abs(totalCredito - totalDebito));
        $("#spanError").attr('value',1);
    }else{
        $("#spanError").html(""); 
        $("#spanError").attr('value',0);
    }

    $('#totalCredito').html(number_format(totalCredito));
    $('#totalDebito').html(number_format(totalDebito));
}

function modalComprobante(nroFila){
    
    //construimos y mostramos el modal con la información.
    $('#editModalComprobante').html('');
    $('#editModalComprobante').append(`
        <div class="modal-dialog modal-lg" role="document" style="width:50%;">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Detalle de cartera / proveedores</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
            <form>
                <span>Crea, modifica o cruza saldos de las obligaciones con terceros</span>
                <div class="row">
                    <div class="form-group col-md-12">
                        <label class="control-label">¿Qué desea hacer? <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-sm-6">
                                <label class="form-radio-label">
                                    <input type="radio" class="form-radio-input" name="saldo_radio" onchange="showDetalleCartera()" value="1" disabled> Cruzar con saldo existente (Próximamente)
                                    <i class="input-helper"></i></label>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-radio-label">
                                    <input type="radio" class="form-radio-input" name="saldo_radio" onchange="showDetalleCartera()" value="2" checked> Crear / Modificar Saldo
                                    <i class="input-helper"></i></label>
                            </div> 
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 detallecartera1 d-none">
                        <input type="text" class="form-control" name="saldo_cruzar" id="saldo_cruzar">
                    </div>
                </div>

                <div class="row detallecartera2">
                        <div class="col-md-3">
                            <select name="prefijo" id="prefijo" class="form-control form-control-sm selectpicker" data-live-search="true" data-size="5" placeholder="Prefijo">
                                <option value="FV">FV - Factura de venta</option>
                                <option value="FC">FC - Factura de compra</option>
                                <option value="RC">RC - Anticipo de clientes</option>
                                <option value="RP">RP - Anticipo de proveedores</option>
                                <option value="CC">CC - Otros</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" placeholder="N* de comprobante" name="nro_comprobante" id="nro_comprobante">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" placeholder="Cuota" name="cuota" id="cuota" value="1">
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="fecha_vencimiento" value="" name="fecha_vencimiento">
                        </div>
                </div>
            </form>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <a type="button" onclick="updateInputModal(${nroFila})" class="btn btn-primary">Actualizar</a>
            </div>
        </div>
        </div>
    `);
    $("#prefijo").selectpicker('refresh');
    $("#editModalComprobante").modal('show');
}

function updateInputModal(nroFila){

    //recuperamos la informacion del modal
    let prefijo = $("#prefijo").val();
    let nroComprobante = $("#nro_comprobante").val();
    let cuota = $("#cuota").val();
    let fecha = $("#fecha_vencimiento").val();

    objInput = {
        prefijo: prefijo,
        nroComprobante: nroComprobante,
        cuota: cuota, 
        fecha: fecha
    }

    if(prefijo == "" || nroComprobante == "" || fecha == "" || cuota== ""){
        alert("Debe diligenciar todos los campos.")
        return;
    }

    //parseamos la posible información que haya en el input delnroFila.
    let input = $("#divInput"+nroFila);
    input.attr('tipo',2);
    input.attr('prefijo',objInput.prefijo);
    input.attr('nroComprobante',objInput.nroComprobante);
    input.attr('cuota',objInput.cuota);
    input.attr('fecha',objInput.fecha);
    input.val(objInput.prefijo+'|'+objInput.nroComprobante+"|"+objInput.cuota+"|"+objInput.fecha);

    $("#editModalComprobante").modal('hide');
}

function showDetalleCartera(){
    
    let opcion = document.querySelector('input[name=saldo_radio]:checked').value

    if(opcion == 1)
    {
    $(".detallecartera1").addClass('d-flex');
    $(".detallecartera1").removeClass('d-none');
    
    $(".detallecartera2").addClass('d-none');
    $(".detallecartera2").removeClass('d-flex');

    }else{
        $(".detallecartera2").addClass('d-flex');
        $(".detallecartera2").removeClass('d-none');
        
        $(".detallecartera1").addClass('d-none');
        $(".detallecartera1").removeClass('d-flex');
    }
}

function validateDetalleCartera(pucId,nro){


    if (window.location.pathname.split("/")[1] === "software") {
        var url='/software/empresa/comprobantes/validatecartera';
    }else{
        var url = '/empresa/comprobantes/validatecartera';
    }

    $.ajax({
        url: url,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        type: "GET",
        datatype: "json",
        data: {
            pucId: pucId,
        },
        success: function (response) {

            if(response == true){
                $("#divCartera"+nro).addClass('d-flex');
                $("#divCartera"+nro).removeClass('d-none');
            }else{
                $("#divInput"+nro).val(0);
                $("#divCartera"+nro).addClass('d-none');
                $("#divCartera"+nro).removeClass('d-flex');
            }
        },
    });
}

function validateComprobante(form){

    let isValid = true;

    $('#table-saldoinicial tbody tr').each(function() {
        var idFila=$(this).attr('fila');

        if(!$("#puc_cuenta"+idFila).val()){
            alert("De escoger una categoría en la fila " + idFila);
            isValid = false;
            return;
        }

        if(!$("#contacto"+idFila).val()){
            alert("De escoger un tercero en la fila " + idFila);
            isValid = false;
            return;
        }
    });

    if($("#spanError").attr('value') == 1){
        alert("El crédito y el débito no son iguales");
        isValid = false;
        return;
    }


    if(isValid){
        $("#"+form).submit();
    }

}   