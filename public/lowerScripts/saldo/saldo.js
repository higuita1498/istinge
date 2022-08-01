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
            <div class="d-flex justify-content-between d-none" id="divCartera${nro}">
                    <input type="text" class="form-control form-control-sm" readonly>
                    <a class="btn btn-primary-sm not-active-a" onclick="modalShow()" style="
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
    }else{
        $("#spanError").html(""); 
    }

    $('#totalCredito').html(number_format(totalCredito));
    $('#totalDebito').html(number_format(totalDebito));
}

function modalShow(){
    // alert("ok");
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

            console.log(nro);

            if(response == true){
                $("#divCartera"+nro).addClass('d-flex');
                $("#divCartera"+nro).removeClass('d-none');
            }else{
                $("#divCartera"+nro).addClass('d-none');
                $("#divCartera"+nro).removeClass('d-flex');
            }
        },
    });
}