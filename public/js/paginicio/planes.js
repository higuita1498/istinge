$(document).ready(function(){

});

/*
* Boton de payu segun datos de usuario
*/

function CrearBotonPayu(valor)
{

    $.ajax({
        dataType: "json",
        method: "GET",
        url: "/pagos/obtenerinformacionpago/"+valor
    }).done(function( infopago ){

        $('#idPayuButtonContainer').empty();

        console.log("AFIRMATIVO");
        var html_button = "<form method='post' action='https://checkout.payulatam.com/ppp-web-gateway-payu/' id='formpayupago'>\
        <input name='merchantId' id='merchantId'  type='hidden'  value='"+infopago.merchantId+"'   >\
        <input name='accountId' id='accountId'  type='hidden'  value='"+infopago.accountId+"' >\
        <input name='description' id='description'  type='hidden'  value='"+infopago.description+"'  >\
        <input name='referenceCode' id='referenceCode' type='hidden'  value='"+infopago.referenceCode+"' >\
        <input name='amount' id='amount'      type='hidden'  value='"+infopago.amount+"'   >\
        <input name='tax' id='tax' type='hidden'  value='"+infopago.tax+"'  >\
        <input name='taxReturnBase' id='taxReturnBase' type='hidden'  value='"+infopago.taxReturnBase+"' >\
        <input name='currency' id='currency' type='hidden'  value='"+infopago.currency+"' >\
        <input name='signature' id='signature' type='hidden'  value='"+infopago.signature+"'  >\
        <input name='test'  id='test'  type='hidden'  value='"+infopago.test+"' >\
        <input name='buyerEmail'id='buyerEmail' type='hidden'  value='"+infopago.buyerEmail+"' >\
        <input name='responseUrl' id='responseUrl'  type='hidden'  value='"+infopago.responseUrl+"' >\
        <input name='confirmationUrl' id='confirmationUrl' type='hidden' value='"+infopago.confirmationUrl+"' >\
        <a name='Submit' id='clickpayu' onclick='preguardarpago()' class='btn-obtener-pln btn-alarged' type='submit' value='Pagar' >PAGAR</a>\
        </form>";

        $('#idPayuButtonContainer').append(html_button);

    });
}

function preguardarpago()
{
    var radiobuton = document.getElementsByName("optradio");
    for(i=0; i<radiobuton.length; i++){
        if(radiobuton[i].checked){
            var meses=radiobuton[i].id;
            //console.log(meses);
        }
    }
    
    var route = "/PreGuardarPago";

      //console.log($('#referenceCode').val());
      $.ajax({
        type: 'GET',
        url: route,
        dataType: 'json',
        data:{
            'meses': meses,
            'plan': $('#plan').val(),
            'personalPlan': $('#personalPlan').val(),
            'pMeses': $('#p_meses').val(),
            'referenceCode':$('#referenceCode').val(),
            'merchantId':$('#merchantId').val(),
            'accountId':$('#accountId').val(),
            'description':$('#description').val(),
            'amount': $('#amount').val(),
            'tax':$('#tax').val(),
            'taxReturnBase':$('#taxReturnBase').val(),
            'currency':$('#currency').val(),
            'signature':$('#signature').val(),
            'test':$('#test').val(),
            'buyerEmail':$('#buyerEmail').val(),
            'responseUrl':$('#responseUrl').val(),
            'confirmationUrl':$('#confirmationUrl').val(),
        }, 

        complete:function(data){
          $("#formpayupago").submit();
      },

      error: function(data){

        console.log('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
    }
});
  }
  
    function consultaestadotransaccion()
  {
    var route = "/ConsultaEstadoTransaccion";

    $.ajax({
        type: 'GET',
        url: route,
        dataType: 'json',
        data:{}, 

        success:function(data){
            //console.log(data);
    if(data.length > 0)
    {
        console.log("entr®Æ");
            $.each( data, function( key, value ){
                if (value.TransactionState == 4) {
                    Swal.fire({
                        type: 'success',
                        title: 'Pago del plan hecho correctamente!',
                        html: 'Monto Pagado: '+value.monto+"<br>"+
                        'Referencia del pago: '+value.referencia_pago+"<br>"+
                        'Estado Transacci√≥n: '+value.EstadoTransaccion,
                        footer: '<a href="#">Ver mis pagos</a>'
                    })
                }else if(value.TransactionState == 6 || value.TransactionState == 104)
                {
                    Swal.fire({
                        type: 'error',
                        title: 'Pago del plan rechazado por Payuu!',
                        html: 'Monto a Pagar: '+value.monto+"<br>"+
                        'Referencia del pago: '+value.referencia_pago+"<br>"+
                        'Estado Transacci®Æn: '+value.EstadoTransaccion,
                        footer: '<a href="#">Ver mis pagos</a>'
                    })
                }
            });
    }
        },

        error: function(data){

            console.log('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde 2222');
        }
    });
        }
        
                 function datosfaltantestransaccion()
  {
    var route = "/DatosFaltantesTransaccion";

    $.ajax({
        type: 'GET',
        url: route,
        dataType: 'json',
        data:{}, 

        success:function(data){
            console.log("hecho");
        },

        error: function(data){

            console.log('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde 3333');
        }
    });
        }