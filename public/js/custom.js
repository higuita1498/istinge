Dropzone.autoDiscover = false;

function vencimiento() {
    var vencimiento = $('#vencimiento').datepicker();
    $('#vencimiento').datepicker({
        locale: 'es-es',
        uiLibrary: 'bootstrap4',
        format: 'dd-mm-yyyy',
        minDate: $('#fecha').val(),
        select: function(e) {

            $("#plazo").val('n');
        }
    });

}

function plazo() {
    var dias = $('#plazo option:selected').attr('dias');
    //var dias=5;
    if ($.isNumeric(dias)) {
        fecha = moment($('#fecha').val(), "DD-MM-YYYY").add('days', dias);
        $('#vencimiento').val(fecha.format('DD-MM-YYYY'));
    }
}

function table(id, value = null, order = [0, "desc"]) {
    var pageLength = $("#pageLength").val();

    var person_dataTable = $('#' + id).DataTable({
        responsive: true,
        "pageLength": pageLength,
        "lengthMenu": [25, 50, 100],
        "language": {
            "zeroRecords": "Disculpe, No existen registros",
            "info": "Mostrando páginas _PAGE_ de _PAGES_",
            "infoEmpty": " ",
            "infoFiltered": "(Filtrado de _MAX_ total entradas)",
            "infoPostFix": "",
            "decimal": ",",
            "thousands": ".",
            "lengthMenu": "Mostrar _MENU_ Registros por página",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin resultados encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        columnDefs: value,
        "order": [order]
    });

    $('#' + id).DataTable();

    ocultar_mostrar(person_dataTable);
}

function notabletable(id) {

    var pageLength = $("#pageLength").val();

    $('#' + id).DataTable({
        responsive: true,
        "bPaginate": false,
        "pageLength": pageLength,
        "searching": false, // Search Box will Be Disabled
        "ordering": false, // Ordering (Sorting on Each Column)will Be Disabled
        "info": true, // Will show "1 to n of n entries" Text at bottom
        "bInfo": false,
        "lengthChange": false, // Will Disabled Record number per page
        "language": {
            "zeroRecords": "Disculpe, No existen registros",
            "info": "Mostrando páginas _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(Filtrado de _MAX_ total entradas)",
            "infoPostFix": "",
            "decimal": ",",
            "thousands": ".",
            "lengthMenu": "Mostrar _MENU_ Registros por página",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin resultados encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        }
    });
}

$(document).ready(function() {
    notificacionRadicado();
    notificacionPing();
    notificacionWifi();
    notificacionTecnico();
    $('[data-toggle="tooltip"]').tooltip();
    $('.precio').mask('000.000.000.000.000', { reverse: true });
    if ($('#table-general').length > 0) {
        table('table-general');
    }
    if ($('#notable-general').length > 0) {
        notabletable('notable-general');
    }

    if ($('#table-facturas').length > 0) {
        notabletable('table-facturas');
    }
    if ($('#table-remisiones').length > 0) {
        notabletable('table-remisiones');
    }
    if ($('#table-compras1').length > 0) {
        notabletable('table-compras1');
    }
    if ($('#table-contactos').length > 0) {
        notabletable('table-contactos');
    }
    if ($('#table-inventario').length > 0) {
        notabletable('table-inventario');
    }
    if ($('#example').length > 0) {
        table('example', null, [0, "desc"]);
    }
    if ($('#example0').length > 0) {
        table('example0', null, [0, "desc"]);
    }
    if ($('#example00').length > 0) {
        table('example00');
    }
    if ($('#table-reporte').length > 0) {
        notabletable('table-facturas');
    }

    if ($('#table-bodega').length > 0) {
        var pageLength = $("#pageLength").val();
        $('#table-bodega').DataTable({
            responsive: true,
            "pageLength": pageLength,
            "language": {
                "zeroRecords": "Disculpe, No existen registros",
                "info": "Mostrando páginas _PAGE_ de _PAGES_",
                "infoEmpty": "No hay registros disponibles",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "decimal": ",",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ Registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            columnDefs: [
                { "width": "20%" },
                { "width": "20%" }
            ]
        });
    }
    if ($('#table-cotizacion').length > 0) {
        notabletable('table-cotizacion');
    }

    if ($('#table-fatcutas').length > 0) {
        table('table-fatcutas', null, [0, "desc"]);
    }

    if ($('#table-ingresos').length > 0) {
        notabletable('table-ingresos');
    }

    $('.orderby').on('click', function() {
        campo = $(this).attr('campo');
        order = $(this).attr('order');
        orderby(campo, order);
    });

    $('#valor_bodega').change(function() {
        $('#form-table-valorinv').submit();
    });

    $('#fecha_corte').change(function() {
        if ($('#fecha_corte').val() == 50) {
            $("#fecha_suspension").val('');
            $("#fecha_suspension").selectpicker('refresh');
            $("#fecha_suspension option[value=0]").attr('selected', 'selected');
            $("#fecha_suspension").selectpicker('refresh');
        } else {
            $("#fecha_suspension").val('');
            $("#fecha_suspension").selectpicker('refresh');
            $("#fecha_suspension option[value=5]").attr('selected', 'selected');
            $("#fecha_suspension").selectpicker('refresh');
        }
    });

    if ($('#imprimir').length > 0) {
        var url = $('#imprimir').val();
        window.open(url, "nombre de la ventana", "width=" + $(window).width + ", height=" + $(window).height + "");
    }

    if ($('#accordion').length > 0) {
        $('.colapsea').on('click', function() {
            $(this).toggleClass('expanded');
        });
    }

    $('.selectpicker').selectpicker();
    $('.datepicker').datepicker({
        locale: 'es-es',
        uiLibrary: 'bootstrap4',
        format: 'dd-mm-yyyy',
    });
    $('.datepickerinput').datepicker({
        locale: 'es-es',
        uiLibrary: 'bootstrap4',
        format: 'dd-mm-yyyy',
    });


    /* VALIDAR FORMULARIOS  */
    if ($('#form-empresa').length > 0) {
        $('#telefono').mask('000000000000');
        $('#username').mask('AAAAAAAAAAAAAAAAAAAA');
        $("#form-empresa").validate({
            language: 'es',
            rules: {
                inputPasswordConfirm: { equalTo: "#inputPassword" }
            },
            messages: {
                'inputPasswordConfirm': {
                    equalTo: "Las contraseñas no coinciden"
                }
            }
        });
        $('#changepass').change(function() {
            changepass();
        });

    }
    if ($('#form-contrato').length > 0) {
        $('#mac_address').mask('AA:AA:AA:AA:AA:AA', {
            'translation': { A: { pattern: /[0-9a-fA-F]/ } },
        });
        $('.mac_address').mask('AA:AA:AA:AA:AA:AA', {
            'translation': { A: { pattern: /[0-9a-fA-F]/ } },
        });
        $('#ip').mask('099.099.099.099');
        $('#local_address').mask('099.099.099.099/99');
        $('#telefono1').mask('000000000000');
        $('#telefono2').mask('000000000000');
        $('#fax').mask('000000000000');
        $('#celular').mask('000000000000');
        $('#username').mask('AAAAAAAAAAAAAAAAAAAA');
        $("#form-contacto").validate({ language: 'es' });
    }
    if ($('#form-contacto').length > 0) {
        $('#telefono1').mask('000000000000');
        $('#telefono2').mask('000000000000');
        $('#fax').mask('000000000000');
        $('#celular').mask('000000000000');
        $('#username').mask('AAAAAAAAAAAAAAAAAAAA');
        $("#form-contacto").validate({
            language: 'es'
        });

    }
    if ($('#form-inventario').length > 0) {
        $('.dropify').dropify({
            messages: {
                'default': 'Arrastra y suelta un archivo aquí o haz click',
                'replace': 'Arrastra y suelta o haz clic para reemplazar',
                'remove': 'Remover',
                'error': 'Vaya, sucedió algo malo.'
            },
            error: {
                'fileSize': 'El tamaño del archivo es demasiado grande ({{ value }} max).',
                'minWidth': 'The image width is too small ({{ value }}}px min).',
                'maxWidth': 'The image width is too big ({{ value }}}px max).',
                'minHeight': 'The image height is too small ({{ value }}}px min).',
                'maxHeight': 'The image height is too big ({{ value }}px max).',
                'imageFormat': 'The image format is not allowed ({{ value }} only).'
            }

        });
        $('.precio').mask('000.000.000.000.000', { reverse: true });
        $('.nro').mask('00000');
        form_inventario();
        $('#precio').change(function() {
            change_precio_listas();
        });

        $('#precio_unid').change(function() {
            precio = $('#precio_unid').val();
            //$('#precio').val(precio).trigger('change');
        });
        $('input[type=radio][name=tipo_producto]').change(function() {
            if (this.value == 1) {
                $('#inventariable').show();
                $('#precio').removeAttr('required');
                $('#precio').attr('disabled', '');
                $('#precio_unid').attr('required', '');
                $('#unidad').attr('required', '');
                $('#nro_unid').attr('required', '');
                agregarbodega_inventario();
                $("#form-inventario").validate('destroy');
                form_inventario();
            } else {
                $('#inventariable').hide();
                $("#precio_unid").val('');
                $("#nro_unid").val('');
                $('#unidad').val('').trigger('change');
                $('#precio').attr('required');
                $('#precio').removeAttr('disabled');
                $('#unidad').removeAttr('required');
                $('#nro_unid').removeAttr('required');
                eliminarbodega_inventario();
                $("#form-inventario").validate('destroy');
                form_inventario();

            }
        });

        if ($('#camposextra').length > 0) {
            camposextra = $('#camposextra').val();
            camposextra = JSON.parse(camposextra);
            $.each(camposextra, function(key, value) {
                autocomplete(value);
            });

        }
        $('#button_show_div_img').on('click', function() {
            if ($("#div_imagen").is(":visible")) {
                hidediv('div_imagen');
            } else {
                showdiv('div_imagen');
            }
        });


    }
    if ($('#form-listaprecios').length > 0) {
        $("#form-listaprecios").validate({ language: 'es' });
        $('input[type=radio][name=tipo]').change(function() {
            if (this.value == 1) {
                showdiv('div_porcentaje');
            } else {
                hidediv('div_porcentaje');
                $('#porcentaje').val('');

            }
        })
    }

    if ($('#form-general').length > 0) {
        $("#form-general").validate({ language: 'es' });
    }



    if ($('#form-banco').length > 0) {
        /*$('#saldo').mask('0000000000.00', {reverse: true});*/
        $("#form-banco").validate({ language: 'es' });
    }

    if ($('#form-termino').length > 0) {
        $('#dias').mask('000', { reverse: true });
        $("#form-termino").validate({ language: 'es' });
    }

    if ($('#form-transferencia').length > 0) {
        $("#form-transferencia").validate({
            language: 'es',
            submitHandler: function(form) {
                $("#fecha").removeAttr("disabled");
                form.submit();
            }
        });
        $('#bodega_origen').change(function() {
            diferentes_bodegas('bodega_origen');
        });
        $('#bodega_destino').change(function() {
            diferentes_bodegas('bodega_destino');
        });

    }
    if ($('#form-ajuste').length > 0) {
        $("#form-ajuste").validate({
            language: 'es',
            submitHandler: function(form) {
                $("#fecha").removeAttr("disabled");
                form.submit();
            }
        });
        $('#bodega').change(function() {
            traer_inventario();
        });

        $('#item').change(function() {
            traer_item();
        });

        $('#cant').change(function() {
            cantidadFinal();
        });
        $('input[type=radio][name=ajuste]').change(function() {
            cantidadFinal();
        });

    }

    if ($('#form-factura').length > 0) {
        $('.precio').mask('000.000.000.000.000', { reverse: true });
        $('.nro').mask('000');
        $("#form-factura").validate({
            language: 'es',
            submitHandler: function(form) {
                $("#fecha").removeAttr("disabled");
                $("#vencimiento").removeAttr("disabled");
                nro = 0;
                $('#table-form  tbody tr').each(function() {
                    nro++;
                });

                if (nro == 0) {
                    $('#error-items').html('Debe estar registrado un item');
                    $('#error-items').show();
                    return false;
                }

                if ($('#devoluciones-dinero').length > 0) {
                    $('#devoluciones-dinero tbody tr').each(function() {
                        var id_fact = $(this).attr('id').split("_")[1];
                        $('#fecha_dev' + id_fact).removeAttr("disabled");

                    });

                }
                if ($('#table-retencion').length > 0) {
                    $('#table-retencion  tbody tr').each(function() {
                        var id_reten = $(this).attr('id');
                        id_reten = id_reten.substr(5, 3);
                        retencion = $('#retencion' + id_reten).val();
                        if (retencion) {
                            $("#precio_reten" + id_reten).removeAttr("disabled");
                        }
                    });
                }
                form.submit();
            }
        });
        $('#fecha').change(function() {
            if ($('#vencimiento').length > 0) {
                vencimiento();
                plazo();
            }

        });
        if ($('#plazo').length > 0) { plazo(); }
        $('#plazo').change(function() {
            plazo();
        });
        if ($('#vencimiento').length > 0) { vencimiento(); }
        if ($('#cliente').val()) {
            contacto($('#cliente').val(), true);
        }
        $('#pago').change(function() {
            Chequeado('pago', 'new');
        });
        $('#new').change(function() {
            Chequeado('new', 'pago');
        });
        $('#total_value').change(function() {
            function_totales_facturas();
        });
        $('#lista_precios').change(function() {
            cambiar_precios();
        });
        $('#bodega').change(function() {
            cambiar_bodega();
        });

    }

    if ($('#form-ingreso').length > 0) {
        if ($('input:radio[name=tipo]:checked').val() == 1) {
            if ($('#es_gastos').length == 0) {
                if ($('#ingreso').length > 0) { factura_pendiente($('#ingreso').val()); } else { factura_pendiente(); }
            } else {
                if ($('#ingreso').length > 0) { factura_proveedor_pendiente($('#ingreso').val()); } else { factura_proveedor_pendiente(); }

            }
            showdiv('si');


        } else if ($('#ingreso').length > 0) {
            factura_pendiente($('#ingreso').val());
        } else if ($('#cliente').val()) {
            factura_pendiente();
        }

        $('.precio').mask('000.000.000.000.000', { reverse: true });
        $('.nro').mask('000.000');
        $("#form-ingreso").validate({
            language: 'es',
            errorElement: 'div',
            errorLabelContainer: '.errorTxt',
            submitHandler: function(form) {
                $('#table-retencion  tbody tr').each(function() {
                    var id_reten = $(this).attr('id');
                    id_reten = id_reten.substr(5, 3);
                    retencion = $('#retencion' + id_reten).val();
                    if (retencion) {
                        $("#precio_reten" + id_reten).removeAttr("disabled");
                    }
                });

                $('#table-facturas  tbody tr').each(function() {
                    id = $(this).attr('id');
                    $('#retenciones_factura_' + id + ' div').each(function() {
                        var id_reten = $(this).attr('id');
                        if (id_reten) {
                            id_reten = id_reten.split('_')[2];
                            retencion = $('#fact' + id + '_retencion' + id_reten).val();
                            if (retencion) {
                                $('#fact' + id + '_precio_reten' + id_reten).removeAttr("disabled");
                            }
                        }
                    });
                });
                $("#fecha").removeAttr("disabled");
                form.submit();
            }
        });

    }



    if ($('#form-numeracion').length > 0) {
        $('.nro').mask('00000');
        $("#form-numeracion").validate({ language: 'es' });
        var today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
        $('#desde').datepicker({
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            locale: 'es-es',
            uiLibrary: 'bootstrap4',
            format: 'dd-mm-yyyy',
            maxDate: function() {
                return $('#hasta').val();
            }
        });
        $('#hasta').datepicker({
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            locale: 'es-es',
            uiLibrary: 'bootstrap4',
            format: 'dd-mm-yyyy',
            minDate: function() {
                return $('#desde').val();
            }
        });
    }

    if ($('#form-vendedores').length > 0) {
        $("#form-vendedores").validate({ language: 'es' });
    }

    if ($('#form-impuesto').length > 0) {
        /*$('#porcentaje').mask('000.00', {reverse: true});*/
        $('#campo').mask('AAAAAAAAAAAAAAAAAAAA');
        $("#form-impuesto").validate({ language: 'es' });
    }

    if ($('#form-retencion').length > 0) {
        $("#form-retencion").validate({ language: 'es' });
    }

    if ($('#form-usuarios').length > 0) {
        $("#form-usuarios").validate({
            language: 'es',
            rules: {
                password: {
                    required: true,
                    minlength: 6
                },
                inputPasswordConfirm: {
                    minlength: 6,
                    equalTo: "#password"
                }
            },
            messages: {
                'inputPasswordConfirm': {
                    equalTo: "Las contraseñas no coinciden"
                }
            }
        });
        $('#changepass').change(function() {
            changepass('form-usuarios');
        });
    }

    if ($('#form-myusuario').length > 0) {
        $('#changepass').change(function() {
            changepass('form-myusuario');
        });
    }


    if ($('#form-reporte').length > 0) {
        if ($('#valuefecha').val()) {
            $("#fechas").val($('#valuefecha').val());
        } else {
            $("#fechas").val('1');
        }
        $("#fechas").selectpicker('refresh');
        $("#form-reporte").validate({ language: 'es' });
        $('#desde').datepicker({
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            locale: 'es-es',
            uiLibrary: 'bootstrap4',
            format: 'dd-mm-yyyy',
            maxDate: function() {
                return $('#hasta').val();
            }
        });
        $('#hasta').datepicker({
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            locale: 'es-es',
            uiLibrary: 'bootstrap4',
            format: 'dd-mm-yyyy',
            minDate: function() {
                return $('#desde').val();
            }
        });

        $('#fechas').change(function() {
            cambiar_fecha();
        });

        $('#desde').change(function() {
            $("#fechas").val('7');
            $("#fechas").selectpicker('refresh');
        });
        $('#hasta').change(function() {
            $("#fechas").val('7');
            $("#fechas").selectpicker('refresh');
        });


        $('#generar').on('click', function() {
            $('#form-reporte').attr('action', $("#urlgenerar").val());
            $('#form-reporte').submit();
        });
        $('#exportar').on('click', function() {
            $('#form-reporte').attr('action', $("#urlexportar").val());
            $('#form-reporte').submit();
        });




    }



    if ($('.collapsibleList').length > 0) {
        collapsibleListGo('.collapsibleList');

        // global var used to disable click event
        var clickDisabled = 0;

        function collapsibleListGo(elementSelector) {

            // search the dom for existance of .collapsibleList
            if ($(elementSelector).length > 0) {

                // iterate through all elementSelector li's
                $(elementSelector + ' li').each(function() {

                    // test for children
                    if ($(this).children().length > 0) {
                        // console.log('this has children');

                        // add class closed
                        $(this).addClass('collapsibleListClosed');

                        // hide all children
                        $(this).children().slideUp();

                        $(this).click(function() {
                            if (clickDisabled == 0) {

                                $(this).toggleClass('collapsibleListClosed').toggleClass('collapsibleListOpen');
                                $(this).children().slideToggle();

                                // 100 ms delay to prevent parent item from closing
                                resetClick(100);
                            };
                        });

                    };
                });
            };
        }; // end collapsibleListGo

        function resetClick(timeoutTime) {
            clickDisabled = 1;

            var resetClickTimeout = setTimeout(function() {
                clickDisabled = 0;
            }, timeoutTime);
        }
    }








    $(function() {
        $('.list-group-sortable-connected').sortable({
            placeholderClass: 'list-group-item',
            connectWith: '.connected'
        });
    });













    /*GRAFICOS*/
    if ($('#grafic-gastos-categorias').length > 0) {

        Morris.Donut({
            element: 'grafic-gastos-categorias',
            data: [
                { value: 70, label: 'Gastos generales' },
                { value: 15, label: 'Gastos de administración' },
                { value: 10, label: 'Costos de ventas y operación' },
                { value: 5, label: 'Seguros generales' }
            ],
            backgroundColor: '#fff',
            labelColor: '#333',
            colors: [
                '#F25E09',
                '#FC860A',
                '#E59914',
                '#FBBC0A'
            ],
            formatter: function(x) { return x + "%" }
        });


        Morris.Donut({
            element: 'grafic-gastos-proveedores',
            data: [
                { value: 65, label: 'Santiago Roca Rodriguez' },
                { value: 25, label: 'Jan Martin Cruz' },
                { value: 10, label: 'Lucas Cano Santana' }
            ],
            backgroundColor: '#fff',
            labelColor: '#333',
            colors: [
                '#F25E09',
                '#FC860A',
                '#E59914'
            ],
            formatter: function(x) { return x + "%" }
        });


        //hidediv('grafic-gastos-proveedores');

        Morris.Bar({
            element: 'grafic-gastos-ingresos',
            data: [
                { x: 'Junio', y: 78, z: 70 },
                { x: 'Julio', y: 80, z: 70 },
                { x: 'Agosto', y: 90, z: 50 },
                { x: 'Septiembre', y: 100, z: 90 },
                { x: 'Octubre', y: 130, z: 100 },
                { x: 'Noviembre', y: 240, z: 130 }
            ],
            xkey: 'x',
            ykeys: ['y', 'z'],
            labels: ['Ingresos', 'Gastos'],
            barColors: function(row, series, type) {
                if (series.key == 'y') {

                    return '#A1BF34';
                } else {
                    return '#08497F';
                }
            }
        });


        Morris.Donut({
            element: 'grafic-ventas-clientes',
            data: [
                { value: 30, label: 'Biel Santos Gonzalez' },
                { value: 10, label: 'Nil Romero Cabrera' },
                { value: 10, label: 'Rubén Muñoz Alonso' },
                { value: 20, label: 'Antonio Gomez Iglesias' },
                { value: 15, label: 'Martín Marin Martinez' },
                { value: 15, label: 'Marti Hidalgo Cano' }
            ],
            backgroundColor: '#fff',
            labelColor: '#333',
            colors: [
                '#000026',
                '#03A596',
                '#F0AA21',
                '#F38626',
                '#F0412C',
                '#47FFAF'
            ],
            formatter: function(x) { return x + "%" }
        });

        Morris.Donut({
            element: 'grafic-ventas-productos',
            data: [
                { value: 16, label: 'Producto #1' },
                { value: 28, label: 'Producto #2' },
                { value: 12, label: 'Producto #3' },
                { value: 19, label: 'Producto #4' },
                { value: 15, label: 'Producto #5' },
                { value: 10, label: 'Producto #6' }
            ],
            backgroundColor: '#fff',
            labelColor: '#333',
            colors: [
                '#000026',
                '#03A596',
                '#F0AA21',
                '#F38626',
                '#F0412C',
                '#47FFAF'
            ],
            formatter: function(x) { return x + "%" }
        });


        Morris.Bar({
            element: 'grafic-cuentas',
            data: [

                { x: 'Junio', y: 78, z: 70, a: 12 },
                { x: 'Julio', y: 80, z: 70, a: 3 },
                { x: 'Agosto', y: 90, z: 50, a: 3 },
                { x: 'Septiembre', y: 100, z: 90, a: 3 },
                { x: 'Octubre', y: 130, z: 100, a: 3 },
                { x: 'Noviembre', y: 240, z: 130, a: 3 }
            ],
            xkey: 'x',
            ykeys: ['y', 'z', 'a'],
            labels: ['30 días o menos (incluye no vencidas)', '31 días a 60 días', 'más de 61 días'],
            stacked: true,
            barColors: function(row, series, type) {
                if (series.key == 'y') {

                    return '#6BBAB5';
                } else if (series.key == 'a') {
                    return '#112F3B';
                } else {
                    return '#2B7379';
                }
            }
        });

    }



    hidediv('grafic-ventas-productos');


});

function addrevclass(button, remove = false) {
    if (remove) {
        $("#" + button).removeClass("active");
    } else {
        $("#" + button).addClass("active");
    }
}

function changepass(form = 'form-empresa') {
    var validator = $("#" + form).validate();
    if ($('#cambiar').val() == 0) {
        $("#changepass ").attr('checked', true);
        $('#cambiar').val(1);
        $('#pass').show();
        if ($('#pass_actual').length > 0) {
            $('#pass_actual').attr('required', '');
        }
        validator.destroy();
        $("#" + form).validate({
            language: 'es',
            rules: {
                password: {
                    required: true,
                    minlength: 6
                },
                inputPasswordConfirm: {
                    required: true,
                    minlength: 6,
                    //            equalTo: "#password"
                }
            },
            messages: {
                'inputPasswordConfirm': {
                    equalTo: "Las contraseñas no coinciden"
                }



            }
        });
    } else {
        $("#changepass ").attr('checked', false);
        $('#cambiar').val(0);
        $('#pass').hide();
        $('#inputPassword').removeAttr('required');
        $('#inputPasswordConfirm').removeAttr('required');
        if ($('#pass_actual').length > 0) {
            $('#pass_actual').removeAttr('required');
            $('#pass_actual').val('');
        }
        $('#inputPassword').val('');
        $('#inputPasswordConfirm').val('');
        validator.destroy();
        $("#" + form).validate({ language: 'es' });


    }
}

function confirmar(form, mensaje = "¿Estas seguro que deseas desactivar la empresa?", submensaje = null, confirmar = 'Aceptar') {
    swal({
        title: mensaje,
        text: submensaje,
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#00ce68',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmar,
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.value) {
            cargando('true');
            document.getElementById(form).submit();
        }
    })
}

function add_tr() {
    // Find a <table> element with id="myTable":
    var table = document.getElementById("table-form");

    // Create an empty <tr> element and add it to the 1st position of the table:
    var row = table.insertRow(0);

    // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:
    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);

    // Add some text to the new cells:
    cell1.innerHTML = "NEW CELL1";
    cell2.innerHTML = "NEW CELL2";
}

function addPerson(e) {
    e.preventDefault();
    const row = createRow({
        name: $('#name').val(),
        lastname: $('#lastname').val()
    });
    $('table tbody').append(row);
    clean();
}




















function change_profile_pic() {
    $('#change_profile_pic').show();
    $('.profile-userpic').hide();
    if ($('.canvas--helper').length == 0) {
        var p = new profilePicture('.profile', null, {
            imageHelper: true,
            onRemove: function(type) {},
            onError: function(type) {
                console.log('Error type: ' + type);
            }
        });
        $('#save_profile_pic').on('click', function() {
            $('#imagenperfil').val(p.getAsDataURL());
            $('.profile-userpic>img').attr('src', p.getAsDataURL());
            cancel_profile_pic();

            document.getElementById('form-usuario').submit();


        });
    }

}

function cancel_profile_pic() {
    $('#change_profile_pic').hide();
    $('.profile-userpic').show();
}

function delete_img(img) {
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    var datos = { "img": img, "tipo": 'delete', _token: CSRF_TOKEN };
    $.ajax({
        type: $('#frmFileUpload').attr('method'),
        url: $('#frmFileUpload').attr('action'),
        data: datos,
        complete: function(data) {},
        success: function(response) {

            $('.img-obj-' + img).remove();
        },
        error: function(error) {
            console.log(error);
        }
    });
}

$(function() {
    if ($('#aniimated-thumbnials').length > 0) {
        $('#aniimated-thumbnials').lightGallery({
            thumbnail: true,
            selector: 'a.img-view'
        });
        var myDropzone = new Dropzone("#frmFileUpload", {
            maxFilesize: 5,
            paramName: "file",
            acceptedFiles: ".png,.jpg,.gif,.bmp,.jpeg",
            maxFiles: 10,
            addRemoveLinks: true,
            dictFileTooBig: '5000',
            successmultiple: function(file) {
                setTimeout(function() {
                    //location.reload();
                }, 3000);
            },
            success: function(file) {
                setTimeout(function() {
                    // location.reload();
                }, 3000);
            }
        });

    }
})

$(document).ready(function() {

    if ($('#table-show-movimientos').length > 0) {
        var pageLength = $("#pageLength").val();
        var dataTable = $('#table-show-movimientos').DataTable({
            responsive: true,
            "pageLength": pageLength,
            "language": {
                "zeroRecords": "Disculpe, No existen registros",
                "info": "Mostrando páginas _PAGE_ de _PAGES_",
                "infoEmpty": " ",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "decimal": ",",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ Registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [1],
                "orderable": false
            }],

            "processing": true,
            "serverSide": true,
            "ajax": {
                url: $('#url-show-movimientos').val(), // json datasource
            },
            error: function() { // error handling
                $("#table-show-movimientos").append('<tbody><tr><td colspan="5" class="table_info">El contacto no tiene transacciones registradas</td></tbody>');
            }
        });

    }

    if ($('#table-show-promesas').length > 0) {
        var pageLength = $("#pageLength").val();
        var url = window.location.pathname;
        var idContacto = "/empresa/contactos/" + $('#idContacto').val();
        var server = url == idContacto ? true : false;
        var dataTable = $('#table-show-promesas').DataTable({
            responsive: true,
            "pageLength": pageLength,
            "language": {
                "zeroRecords": "Disculpe, No existen registros",
                "info": "Mostrando páginas _PAGE_ de _PAGES_",
                "infoEmpty": " ",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "decimal": ",",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ Registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [
                [0, "desc"]
            ],

            "processing": true,
            "serverSide": server,
            "ajax": {
                url: $('#url-show-promesas').val(), // json datasource
            },
            error: function() { // error handling
                $("#table-show-promesas").append('<tbody><tr><td colspan="9" class="table_info">El Cliente No Tiene Promesas de Pagos Asociadas</td></tbody>');
            }
        });

    }

    if ($('#table-show-facturas').length > 0) {
        var pageLength = $("#pageLength").val();
        var url = window.location.pathname;
        var idContacto = "/empresa/contactos/" + $('#idContacto').val();
        var server = url == idContacto ? true : false;
        var dataTable = $('#table-show-facturas').DataTable({
            responsive: true,
            "pageLength": pageLength,
            "language": {
                "zeroRecords": "Disculpe, No existen registros",
                "info": "Mostrando páginas _PAGE_ de _PAGES_",
                "infoEmpty": " ",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "decimal": ",",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ Registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [
                [1, "desc"]
            ],
            "columnDefs": [{
                "targets": [8],
                "orderable": false
            }],

            "processing": true,
            "serverSide": server,
            "ajax": {
                url: $('#url-show-facturas').val(), // json datasource
            },
            error: function() { // error handling
                $("#table-show-facturas").append('<tbody><tr><td colspan="9" class="table_info">El ítem no tiene facturas de venta registradas</td></tbody>');
            }
        });

    }

    if ($('#table-show-radicados').length > 0) {
        var pageLength = $("#pageLength").val();
        var dataTable = $('#table-show-radicados').DataTable({
            responsive: true,
            "pageLength": pageLength,
            "language": {
                "zeroRecords": "Disculpe, No existen registros",
                "info": "Mostrando páginas _PAGE_ de _PAGES_",
                "infoEmpty": " ",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "decimal": ",",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ Registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "EL CLIENTE NO TIENE RADICADOS ASOCIADOS",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [
                [1, "asc"]
            ],
            "columnDefs": [{
                "targets": [1],
                "orderable": false
            }],

            "processing": true,
            "serverSide": true,
            "ajax": {
                url: $('#url-show-radicados').val(), // json datasource
            },
            error: function() { // error handling
                $("#table-show-radicados").append('<tbody><tr><td colspan="5" class="table_info">El cliente no tiene radicados asociados</td></tbody>');
            }
        });

    }


    if ($('#table-show-crm-history').length > 0) {
        var pageLength = $("#pageLength").val();
        var dataTable = $('#table-show-crm-history').DataTable({
            responsive: true,
            "pageLength": pageLength,
            "language": {
                "zeroRecords": "Disculpe, No existen registros",
                "info": "Mostrando páginas _PAGE_ de _PAGES_",
                "infoEmpty": " ",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "decimal": ",",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ Registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "EL CLIENTE NO TIENE RADICADOS ASOCIADOS",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [
                [1, "asc"]
            ],
            "columnDefs": [{
                "targets": [1],
                "orderable": false
            }],

            "processing": true,
            "serverSide": true,
            "ajax": {
                url: $('#url-show-crm-history').val(), // json datasource
            },
            error: function() { // error handling
                $("#table-show-crm-history").append('<tbody><tr><td colspan="5" class="table_info">El cliente no tiene radicados asociados</td></tbody>');
            }
        });

    }


    if ($('#table-show-facturas-compras').length > 0) {
        var pageLength = $("#pageLength").val();
        var dataTable = $('#table-show-facturas-compras').DataTable({
            responsive: true,
            "pageLength": pageLength,
            "language": {
                "zeroRecords": "Disculpe, No existen registros",
                "info": "Mostrando páginas _PAGE_ de _PAGES_",
                "infoEmpty": " ",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "decimal": ",",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ Registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [
                [6, "desc"]
            ],
            "columnDefs": [{
                "targets": [1, 7],
                "orderable": false
            }],

            "processing": true,
            "serverSide": true,
            "ajax": {
                url: $('#url-show-facturas-compras').val(), // json datasource
            },
            error: function() { // error handling
                $("#table-show-facturas-compras").append('<tbody><tr><td colspan="9" class="table_info">El ítem no tiene facturas de venta registradas</td></tbody>');
            }
        });

    }

    if ($('#table-show-notascredito').length > 0) {
        var pageLength = $("#pageLength").val();
        var dataTable = $('#table-show-notascredito').DataTable({
            responsive: true,
            "pageLength": pageLength,
            "language": {
                "zeroRecords": "Disculpe, No existen registros",
                "info": "Mostrando páginas _PAGE_ de _PAGES_",
                "infoEmpty": " ",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "decimal": ",",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ Registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [5],
                "orderable": false
            }],

            "processing": true,
            "serverSide": true,
            "ajax": {
                url: $('#url-show-notascredito').val(), // json datasource
            },
            error: function() { // error handling
                $("#table-show-notascredito").append('<tbody><tr><td colspan="9" class="table_info">El ítem no tiene facturas de venta registradas</td></tbody>');
            }
        });

    }

    if ($('#table-show-notasdebito').length > 0) {
        var pageLength = $("#pageLength").val();
        var dataTable = $('#table-show-notasdebito').DataTable({
            responsive: true,
            "pageLength": pageLength,
            "language": {
                "zeroRecords": "Disculpe, No existen registros",
                "info": "Mostrando páginas _PAGE_ de _PAGES_",
                "infoEmpty": " ",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "decimal": ",",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ Registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [5],
                "orderable": false
            }],
            error: function() { // error handling
                $("#table-show-notasdebito").append('<tbody><tr><td colspan="9" class="table_info">El ítem no tiene facturas de venta registradas</td></tbody>');
            }
        });

    }

    if ($('#table-show-cotizaciones').length > 0) {
        var pageLength = $("#pageLength").val();
        var dataTable = $('#table-show-cotizaciones').DataTable({
            responsive: true,
            "pageLength": pageLength,
            "language": {
                "zeroRecords": "Disculpe, No existen registros",
                "info": "Mostrando páginas _PAGE_ de _PAGES_",
                "infoEmpty": " ",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "decimal": ",",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ Registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [5],
                "orderable": false
            }],

            "processing": true,
            "serverSide": true,
            "ajax": {
                url: $('#url-show-cotizaciones').val(), // json datasource
            },
            error: function() { // error handling
                $("#table-show-cotizaciones").append('<tbody><tr><td colspan="9" class="table_info">El ítem no tiene cotizaciones registradas</td></tbody>');
            }
        });

    }

    if ($('#table-show-remisiones').length > 0) {
        var pageLength = $("#pageLength").val();
        var dataTable = $('#table-show-remisiones').DataTable({
            responsive: true,
            "pageLength": pageLength,
            "language": {
                "zeroRecords": "Disculpe, No existen registros",
                "info": "Mostrando páginas _PAGE_ de _PAGES_",
                "infoEmpty": " ",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "decimal": ",",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ Registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [8],
                "orderable": false
            }],

            "processing": true,
            "serverSide": true,
            "ajax": {
                url: $('#url-show-remisiones').val(), // json datasource
            },
            error: function() { // error handling
                $("#table-show-remisiones").append('<tbody><tr><td colspan="9" class="table_info">El ítem no tiene remisiones registradas</td></tbody>');
            }
        });

    }

    if ($('#table-show-ordenes').length > 0) {
        var pageLength = $("#pageLength").val();
        var dataTable = $('#table-show-ordenes').DataTable({
            responsive: true,
            "pageLength": pageLength,
            "language": {
                "zeroRecords": "Disculpe, No existen registros",
                "info": "Mostrando páginas _PAGE_ de _PAGES_",
                "infoEmpty": " ",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "decimal": ",",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ Registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [6],
                "orderable": false
            }],

            "processing": true,
            "serverSide": true,
            "ajax": {
                url: $('#url-show-ordenes').val(), // json datasource
            },
            error: function() { // error handling
                $("#table-show-ordenes").append('<tbody><tr><td colspan="9" class="table_info">El ítem no tiene órdenes de compra registradas</td></tbody>');
            }
        });

    }

    if ($('#table-show-transaccion').length > 0) {
        var pageLength = $("#pageLength").val();
        var dataTable = $('#table-show-transaccion').DataTable({
            responsive: true,
            "pageLength": pageLength,
            "language": {
                "zeroRecords": "Disculpe, No existen registros",
                "info": "Mostrando páginas _PAGE_ de _PAGES_",
                "infoEmpty": " ",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "decimal": ",",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ Registros por página",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [2, 3, 7],
                "orderable": false
            }],

            "processing": true,
            "serverSide": true,
            "ajax": {
                url: $('#url-show-transaccion').val(), // json datasource
            },
            error: function() { // error handling
                $("#table-show-transaccion").append('<tbody><tr><td colspan="9" class="table_info">El ítem no tiene órdenes de compra registradas</td></tbody>');
            }
        });

    }

    $('#table-valor-actual').DataTable({
        "language": {
            "zeroRecords": "Disculpe, No existen registros",
            "info": "",
            "infoEmpty": " ",
            "infoFiltered": "(Filtrado de _MAX_ total entradas)",
            "infoPostFix": "",
            "decimal": ",",
            "thousands": ".",
            "lengthMenu": "Mostrar _MENU_ Registros por página",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin resultados encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "order": [
            [7, "desc"]
        ],

    });

    $('#table-ingresos-categoria').DataTable({
        "language": {
            "zeroRecords": "Disculpe, No existen registros",
            "info": "",
            "infoEmpty": " ",
            "infoFiltered": "(Filtrado de _MAX_ total entradas)",
            "infoPostFix": "",
            "decimal": ",",
            "thousands": ".",
            "lengthMenu": "",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin resultados encontrados",
        },
        "paging": false,
        "searching": false,
        "order": [
            [0, "asc"]
        ],

    });

    $('#table-egresos-categoria').DataTable({
        "language": {
            "zeroRecords": "Disculpe, No existen registros",
            "info": "",
            "infoEmpty": " ",
            "infoFiltered": "(Filtrado de _MAX_ total entradas)",
            "infoPostFix": "",
            "decimal": ",",
            "thousands": ".",
            "lengthMenu": "",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin resultados encontrados",
        },
        "paging": false,
        "searching": false,
        "order": [
            [0, "asc"]
        ],

    });

    table('table-ventas-item', null, [2, 'desc']);
    table('table-ventas-cliente', null, [1, 'desc']);
    table('table-cuentas-remisiones', null, [0, 'desc']);
    table('table-rentabilidad', null, [5, 'desc']);
    table('table-pagos-recurrentes', null, [0, 'desc']);
    table('table-reporte-categorias', null, [0, 'desc']);

    $("#fecha").datepicker({
        uiLibrary: 'bootstrap4',
        locale: 'es-es',
        uiLibrary: 'bootstrap4',
        format: 'dd-mm-yyyy',
    });
});

function ocultar_mostrar(person_dataTable) {
    $(".boton_ocultar_mostrar").on('click', function() {
        var indice = $(this).index(".boton_ocultar_mostrar");
        $(".boton_ocultar_mostrar").eq(indice).toggleClass("btn-danger");
        var columna = person_dataTable.column(indice);
        columna.visible(!columna.visible());
    });
}

function filterOptions() {
    var sw = $("#buttonfilter").val();

    if (sw == 0) {
        $("#columnOptions").show();
        $("#buttonfilter").val("1");
    } else {
        $("#columnOptions").hide();
        $("#buttonfilter").val("0");
    }
}
