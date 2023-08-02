
$('#tipo_autoretencion2').change(function (e) {
    if ($('#tipo_autoretencion2').val() == 2) {
        $('.cls-autoretencion').removeClass('d-none');
    } else {
        $('.cls-autoretencion').addClass('d-none');
    }
});


$('#tipo_autoretencion1').change(function (e) {
    if ($('#tipo_autoretencion1').val() == 1) {
        $('.cls-autoretencion').addClass('d-none');
    } else {
        $('.cls-autoretencion').removeClass('d-none');
    }
});
