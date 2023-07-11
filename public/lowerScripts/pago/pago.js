function showAnti(){
    let opcionShow = $("#realizar").val();
    
    if(opcionShow == 2){
        $(".cls-realizar").addClass('d-block');
        $(".cls-realizar").removeClass('d-none');

        $(".cls-realizar-inv").addClass('d-none');
        $(".cls-realizar-inv").removeClass('d-block');
    }else{
        $(".cls-realizar").removeClass('d-block');
        $(".cls-realizar").addClass('d-none');

        $(".cls-realizar-inv").removeClass('d-none');
        $(".cls-realizar-inv").addClass('d-block');
    }
}

function saldoContacto(id){
    var url= 'contacto/'+id;
    var _token =   $('meta[name="csrf-token"]').attr('content');
   
    $.get(url,{_token:_token},function(data){
        $('#total_saldo').val(data);
    });
}