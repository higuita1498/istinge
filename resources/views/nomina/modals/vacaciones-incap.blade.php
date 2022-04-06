<div class="modal fade bd-example-modal-lg" id="vac-inc-1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Vacaciones, Incapacidades y Licencias</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="vacaciones-incap-nomina-{{$nominaPeriodo->id}}">
                <div class="container-fluid">
                    <div class="row mb-3">
                        <div class="col-12  text-center">
                            <h4 class="font-weight-bold">Salario base vacaciones</h4>
                            <h5>{{ $moneda }} <span id="base_vacations"></span></h5>
                            <!-- <a href="#">Editar</a> -->
                        </div>
                    </div>

                    <ul class="nav nav-tabs nav-fill mb-3 px-0" id="ex1" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="ex3-tab-1" data-toggle="tab" href="#ex3-tabs-1" role="tab" aria-controls="ex3-tabs-1" aria-selected="true">Vacaciones</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="ex3-tab-2" data-toggle="tab" href="#ex3-tabs-2" role="tab" aria-controls="ex3-tabs-2" aria-selected="false">Incapacidades</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="ex3-tab-3" data-toggle="tab" href="#ex3-tabs-3" role="tab" aria-controls="ex3-tabs-3" aria-selected="false">Licencias</a>
                        </li>
                    </ul>

                    <form method="POST" action="{{ route('vacaciones.update') }}" role="form" class="forms-sample p-0" novalidate id="vacacionesUpdate" onsubmit="event.preventDefault();">
                        @csrf
                        <input type="hidden" id="valor-subsidio-empresa" value="{{ $nominaConfiguracionCalculos->where('nro', 1)->first()->valor }}">
                        <input type="hidden" name="id" id="edit_vacaciones_id">
                        <div class="tab-content" id="ex3-content">
                            <div class="tab-pane fade show active" id="ex3-tabs-1" role="tabpanel" aria-labelledby="ex3-tab-1">
                                <div class="row p-3">
                                    <div class="col-12">
                                        <p> Ingresa aquí los días de vacaciones disfrutados o compensados en dinero durante
                                            el periodo. </p>
                                    </div>
                                </div>

                                <div class="row p-3 border-bottom">
                                    @foreach($nominaPeriodo->nominaDetallesUno as $categorias)
                                    @if($categorias->fk_nomina_cuenta == 2 && $categorias->fk_nomina_cuenta_tipo == 4)
                                    <div class="col">
                                        <p>{{$categorias->nombre}}</p>
                                        <input type="hidden" class="form-control" name="v_nombre" value="{{$categorias->nombre}}">
                                        <input type="hidden" class="form-control" name="v_cate" value="{{$categorias->fk_categoria}}">
                                    </div>
                                    <div class="col">
                                        {{$categorias->valor_hora_ordinaria}}
                                        <input type="hidden" class="form-control" name="v_hora" value="{{$categorias->valor_hora_ordinaria}}">
                                    </div>
                                    <div class="col">
                                        <i data-tippy-content="Días que le faltan por disfrutar." class="icono far fa-question-circle"></i>
                                    </div>
                                    @php break; @endphp
                                    @endif
                                    @endforeach
                                </div>

                                <div class="row border-bottom">
                                    <div class="col-12 col-md-4">
                                        <p>Dias disfrutados en el periodo</p>
                                    </div>
                                    <div class="col-12 col-md-8 text-center px-0">
                                        <div id="vacations"></div>
                                        <!-- <span class="badge badge-pill badge-success mt-2">
                                                <i class="far fa-plus-square"></i> Vacaciones
                                                </span> -->
                                        <input type="hidden" id="total-dias-consolidados" name="total_dias_consolidados" value="">
                                    </div>
                                </div>

                                <div class="row p-3 border-bottom d-none">
                                    <div class="col-8 align-self-center">
                                        <p>¿Pago anticipado?</p>
                                    </div>
                                    <div class="col-2">
                                        <div class="form-check form-check-inline" style="display: inline-flex">
                                            <label class="form-check-label pl-4">
                                                <input type="radio" class="form-check-input" name="v_pago" id="pago1" value="1">Si<i class="input-helper"></i><i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline" style="display: inline-flex">
                                            <label class="form-check-label pl-4">
                                                <input type="radio" class="form-check-input" name="v_pago" id="pago0" value="0">No<i class="input-helper"></i><i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <i class="far fa-question-circle"></i>
                                    </div>
                                </div>

                                <div class="row p-3 border-bottom d-none">
                                    <div class="col-8 align-self-center">
                                        <p>Días compensados en dinero y NO disfrutados</p>
                                    </div>
                                    <div class="col-2">
                                        <input type="number" class="form-control" min="0" style="width: 70px" name="v_dias_compensados" id="dias_compensados_dinero" placeholder="0">
                                    </div>
                                    <div class="col-2">
                                        <i class="far fa-question-circle"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="ex3-tabs-2" role="tabpanel" aria-labelledby="ex3-tab-2">
                                <div class="row p-3">
                                    <div class="col-12">
                                        <p> Ingresa aquí los días de incapacidad por enfermedad general o accidente de
                                            trabajo de la persona.</p>
                                    </div>
                                </div>

                                <div class="col-12" id="incapacidades">

                                </div>

                                <input type="hidden" name="total_dias_incap" id="total-dias-incap-general" value="0">
                                <input type="hidden" name="total_dias_incap" id="total-dias-incap-profesional" value="0">
                            </div>


                            <div class="tab-pane fade" id="ex3-tabs-3" role="tabpanel" aria-labelledby="ex3-tab-3">
                                <div class="row p-3">
                                    <div class="col-12">
                                        <p> Ingresa aquí los diferentes días de licencias que tuvo la persona en el
                                            periodo. </p>
                                    </div>
                                </div>

                                <div id="licencias">

                                </div>
                                <input type="hidden" name="total_dias_licencia" id="total-dias-licencia" value="0">
                                <input type="hidden" name="total_dias_licencia_no_remunerado" id="total-dias-licencia-no-remunerado" value="0">
                            </div>
                        </div>


                        <input type="hidden" name="subsidio_transporte" id="subsidio-transporte" value="">
                    </form>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal" id="modal_vacaciones">Cerrar
                </button>
                <a href="javascript:vacacionesUpdate()" class="btn btn-success">Guardar</a>
            </div>
        </div>
    </div>
</div>

<script>
    function vacacionesUpdate() {
        idNomina = $('#edit_vacaciones_id').val();
        cargando(true);
        updateTotalVacaciones(idNomina);
        let data = {
            id: $('#edit_vacaciones_id').val(),
            v_nombre: $('input[name="v_nombre"]').val(),
            v_cate: $('input[name="v_cate"]').val(),
            v_hora: $('input[name="v_hora"]').val(),
            v_id: $('input[name="v_id[]"]').map(function() {
                return $(this).val();
            }).get(),
            v_total_consolidado: $('#total-dias-consolidados').val(),
            v_desde: $('input[name="v_desde[]"]').map(function() {
                return $(this).val();
            }).get(),
            v_hasta: $('input[name="v_hasta[]"]').map(function() {
                return $(this).val();
            }).get(),
            i_id: $('input[name="i_id[]"]').map(function() {
                return $(this).val();
            }).get(),
            i_desde: $('input[name="i_desde[]"]').map(function() {
                return $(this).val();
            }).get(),
            i_hasta: $('input[name="i_hasta[]"]').map(function() {
                return $(this).val();
            }).get(),
            total_dias_incap_g: $('#total-dias-incap-general').val(),
            total_dias_incap_p: $('#total-dias-incap-profesional').val(),
            lr_id: $('.remunerado input[name="l_id[]"]').map(function() {
                return $(this).val();
            }).get(),
            lr_desde: $('.remunerado input[name="l_desde[]"]').map(function() {
                return $(this).val();
            }).get(),
            lr_hasta: $('.remunerado input[name="l_hasta[]"]').map(function() {
                return $(this).val();
            }).get(),
            lnr_id: $('.no-remunerado input[name="l_id[]"]').map(function() {
                return $(this).val();
            }).get(),
            lnr_desde: $('.no-remunerado input[name="l_desde[]"]').map(function() {
                return $(this).val();
            }).get(),
            lnr_hasta: $('.no-remunerado input[name="l_hasta[]"]').map(function() {
                return $(this).val();
            }).get(),
            total_dias_licencia: $('#total-dias-licencia').val(),
            total_dias_licencia_no_remunerado: $('#total-dias-licencia-no-remunerado').val(),
            subsidio_transporte: $('#subsidio-transporte').val(),
            dias_pagos: $('#dias-pagos').val(),
            _token: $('input[name="_token"]').val()
        };

        console.log(data);

        $.post($("#vacacionesUpdate").attr('action'), data, function(dato) {
            if (dato['status'] == 'OK') {
                $("#vacaciones" + dato['id']).empty().text(dato['horas']);
                $('#modal_vacaciones').click();
                $('#vacacionesUpdate').trigger("reset");
                cargando(false);
                formatPago(dato['valor_total'], dato['id']);
                swal("Registro Actualizado", "Actualización de Vacaciones, Incapacidades y Licencias Satisfactoria", "success");
                refrescarCosto();
            } else {
                swal('ERROR', dato['mensaje'], "error");
                cargando(false);
            }
        }, 'json');
    }

    function updateTotalVacaciones(idNomina) {
        let diasV = 0;
        let diasIncapG = 0;
        let diasIncapP = 0;
        let diasLicR = 0;
        let diasLicNoR = 0;

        $('#vacations .row-dates-v').each(function(index) {
            let desde = new Date($(this).find('.desde').val() + 'T00:00:00');
            let hasta = new Date($(this).find('.hasta').val() + 'T00:00:00');
            hasta.setDate(hasta.getDate() + 1);

            var Difference_In_Time = hasta.getTime() - desde.getTime();
            var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);
            if (Number.isInteger(Difference_In_Days)) {
                diasV += Difference_In_Days;
            }
        });


        $('#incapacidades .row-dates-inc.general').each(function(index) {
            let desde = new Date($(this).find('.desde').val() + 'T00:00:00');
            let hasta = new Date($(this).find('.hasta').val() + 'T00:00:00');
            hasta.setDate(hasta.getDate() + 1);
            var Difference_In_Time = hasta.getTime() - desde.getTime();
            var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);

            if (Number.isInteger(Difference_In_Days)) {
                diasIncapG += Difference_In_Days;
            }
        });

        $('#incapacidades .row-dates-inc.profesional').each(function(index) {
            let desde = new Date($(this).find('.desde').val() + 'T00:00:00');
            let hasta = new Date($(this).find('.hasta').val() + 'T00:00:00');
            hasta.setDate(hasta.getDate() + 1);
            var Difference_In_Time = hasta.getTime() - desde.getTime();
            var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);

            if (Number.isInteger(Difference_In_Days)) {
                diasIncapP += Difference_In_Days;
            }
        });


        $('#licencias .row-dates-lic.remunerado').each(function(index) {
            let desde = new Date($(this).find('.desde').val() + 'T00:00:00');
            let hasta = new Date($(this).find('.hasta').val() + 'T00:00:00');
            hasta.setDate(hasta.getDate() + 1);

            var Difference_In_Time = hasta.getTime() - desde.getTime();
            var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);

            if (Number.isInteger(Difference_In_Days)) {
                diasLicR += Difference_In_Days;
            }
        });

        $('#licencias .row-dates-lic.no-remunerado').each(function(index) {
            let desde = new Date($(this).find('.desde').val() + 'T00:00:00');
            let hasta = new Date($(this).find('.hasta').val() + 'T00:00:00');
            hasta.setDate(hasta.getDate() + 1);

            var Difference_In_Time = hasta.getTime() - desde.getTime();
            var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);

            if (Number.isInteger(Difference_In_Days)) {
                diasLicNoR += Difference_In_Days;
            }
        });


        var formulaVac = function(salarioPromedio, dias) {
            return (salarioPromedio * dias) / 30;
        }

        var formulaIncap = function(salarioPromedio, dias, tipo) {
            //el salario promedio, se carga al inicio del modulo y si es inferior al salario minimo legal entonces se le asigna el minimo.
            if (salarioPromedio < $('#salario-minimo-vigente').val()) {
                salarioPromedio = $('#salario-minimo-vigente').val();
            }


            if (tipo == 'general') {
                return (salarioPromedio * dias / 30) * (66.67 / 100);
            } else {
                return (salarioPromedio * dias / 30);
            }
        }

        var formulaLic = function(salarioPromedio, dias) {
            return (salarioPromedio * dias) / 30;
        }

        let salarioPromedio = $('#salario-promedio-nomina-' + idNomina).val();
        let subsidioTrans = subsidioTransporte(idNomina, diasV + diasIncapG + diasIncapP + diasLicR + diasLicNoR);

        $('#total-dias-consolidados').val(formulaVac(salarioPromedio, diasV));
        $('#total-dias-incap-general').val(formulaIncap(salarioPromedio, diasIncapG, 'general'));
        $('#total-dias-incap-profesional').val(formulaIncap(salarioPromedio, diasIncapP, 'profesional'));
        $('#total-dias-licencia').val(formulaLic(salarioPromedio, diasLicR));
        $('#total-dias-licencia-no-remunerado').val(formulaLic(salarioPromedio, diasLicNoR));
        $('#dias-pagos').val(parseInt($('#dias-trabajados-' + idNomina).val()) - (diasIncapG));
        $('#subsidio-transporte').val(subsidioTrans);
    }

    function editVacaciones(id) {
        cargando(true);
        var url = '/empresa/nomina/liquidar-nomina/' + id + '/edit_vacaciones';
        var _token = $('meta[name="csrf-token"]').attr('content');
        var i = id;
        $.post(url, {
            id: id,
            _token: _token
        }, function(resul) {
            cargando(false);
            resul = JSON.parse(resul);
            // console.log(resul);
            $("#ex3-tabs-1").click();
            $('#edit_vacaciones_id').val(resul.id);
            $('#vacacionesUpdate').trigger("reset");
            $("#base_vacations").empty().text(number_format(resul.base));
            $("#vacations,#incapacidades,#licencias").html('');
            var f_ini = resul.limit_inicio;
            var f_fin = resul.limit_final;

            // VACACIONES

            for (var i = 0; i < resul.vacaciones.length; i++) {
                if (resul.vacaciones[i]['fecha_inicio']) {
                    $('#vacations').append(`<div class="row mt-2 row-dates-v" id="` + resul.vacaciones[i]['id'] + `"><input type="hidden" class="form-control" id="v_id_` + i + `" value="` + resul.vacaciones[i]['id'] + `" name="v_id[]"><div class="col-4"><input type="date" class="form-control desde" name="v_desde[]" id="v_desde_` + i + `" onchange="datesV(` + i + `)" value="` + resul.vacaciones[i]['fecha_inicio'] + `" min="` + f_ini + `" max="` + f_fin + `"></div><div class="col-4"><input type="date" class="form-control hasta" name="v_hasta[]" id="v_hasta_` + i + `" onchange="datesV(` + i + `)"value="` + resul.vacaciones[i]['fecha_fin'] + `" min="` + f_ini + `" max="` + f_fin + `"></div><div class="col-4 text-left"><button class="btn btn-outline-danger btn-icons mt-1" onclick="destroyVacaciones(` + resul.vacaciones[i]['id'] + `)"><i class="fas fa-trash" title="Eliminar"></i></button></div></div>`);
                }
            }
            i++;
            $('#vacations').append(`<input type="hidden" class="form-control" value="" name="v_id[]"><div class="row mt-2 row-dates-v"><div class="col-4"><input type="date" class="form-control desde" id="v_desde_` + i + `" onchange="datesV(` + i + `)" name="v_desde[]" min="` + f_ini + `" max="` + f_fin + `"></div><div class="col-4"><input type="date" class="form-control hasta" id="v_hasta_` + i + `" onchange="datesV(` + i + `)" name="v_hasta[]" min="` + f_ini + `" max="` + f_fin + `"></div><div class="col-4 text-left"><i title="Vacaciones disfrutadas = Días disfrutados durante este período * ( Salario base vacaciones / 30 días del mes)" class="icono far fa-question-circle"></i></div></div>`);
            if (resul.vacaciones.length > 0) {
                $('#pago' + resul.vacaciones[0]['pago_anticipado']).attr('checked', true);
                $("#dias_compensados_dinero").val(resul.vacaciones[0]['dias_compensados_dinero']);
            }

            //INCAPACIDADES
            for (var j = 0; j < resul.incapacidades.length; j++) {
                let tipo = '';

                if (resul.incapacidades[j]['nombre'].toLowerCase() == "incapacidad general") {
                    tipo = "general";
                } else {
                    tipo = "profesional";
                }

                $('#incapacidades').append(`
                    <div class="row py-3 row-dates-inc ${tipo}">
                            <div class="col mt-2 align-self-center">
                                <p class="m-0">` + resul.incapacidades[j]['nombre'] + `</p>
                                <input type="hidden" class="form-control" name="i_id[]" value="` + resul.incapacidades[j]['id'] + `">
                            </div>
                            <div class="col mt-2">
                                <input type="date" class="form-control desde" name="i_desde[]" id="i_desde_` + j + `" onchange="datesI(` + j + `)" value="` + resul.incapacidades[j]['fecha_inicio'] + `" min="` + f_ini + `" max="` + f_fin + `">
                            </div>
                            <div class="col mt-2">
                                <input type="date" class="form-control hasta" name="i_hasta[]" id="i_hasta_` + j + `" onchange="datesI(` + j + `)" value="` + resul.incapacidades[j]['fecha_fin'] + `" min="` + f_ini + `" max="` + f_fin + `">
                            </div>
                            <div class="col align-self-center">
                                <button class="btn btn-outline-danger btn-icons mt-2" onclick="destroyVacaciones(` + resul.incapacidades[j]['id'] + `)">
                                    <i class="fas fa-trash" title="Eliminar"></i>
                                </button>
                            </div>
                    </div>`);
            }



            //LICENCIAS
            for (var k = 0; k < resul.licencias.length; k++) {
                $('#licencias').append(`
                    <div class="row py-3 row-dates-lic ${resul.licencias[k]['isRemunerado'] ? 'remunerado' : 'no-remunerado'}">
                        <div class="col mt-2 align-self-center">
                            <p class="m-0 col-12">` + resul.licencias[k]['nombre'] + `</p>
                            <input type="hidden" class="form-control" name="l_id[]" value="` + resul.licencias[k]['id'] + `">
                        </div>
                        <div class="col mt-2">
                            <input type="date" class="form-control desde" name="l_desde[]" id="l_desde_` + k + `" onchange="datesL(` + k + `)" value="` + resul.licencias[k]['fecha_inicio'] + `" min="` + f_ini + `" max="` + f_fin + `">
                        </div>
                        <div class="col mt-2">
                            <input type="date" class="form-control hasta" name="l_hasta[]" id="l_hasta_` + k + `" onchange="datesL(` + k + `)" value="` + resul.licencias[k]['fecha_fin'] + `" min="` + f_ini + `" max="` + f_fin + `">
                        </div>
                        <div class="col align-self-center">
                            <button class="btn btn-outline-danger btn-icons mt-2" onclick="destroyVacaciones(` + resul.licencias[k]['id'] + `)"><i class="fas fa-trash" title="Eliminar"></i></button>
                        </div>
                    </div>`);
            }

            $('#vac-inc-1').modal("show");
        });
    }

    function destroyVacaciones(id) {
        cargando(true);
        var url = '/empresa/nomina/liquidar-nomina/' + id + '/destroy_vacaciones';
        var _token = $('meta[name="csrf-token"]').attr('content');
        var i = id;
        $.post(url, {
            id: id,
            _token: _token
        }, function(dato) {
            cargando(false);
            dato = JSON.parse(dato);
            if (dato['status'] == 'OK') {
                if (dato['tipo'] == 4) {
                    $("#" + i).remove();
                } else {
                    editVacaciones(dato['id']);
                }
                $("#vacaciones" + dato['id']).empty().text(dato['horas']);
            } else {
                cargando(false);
            }
        });
    }

    function subsidioTransporte(idNomina, diasNoTrabajados) {
        let dias_trabajados = $('#dias-trabajados-' + idNomina);
        let valorSubsidio = parseFloat($('#valor-subsidio-empresa').val());

        if (dias_trabajados.length == 0) {
            return 0;
        }

        let dias = parseInt(dias_trabajados.val()) - diasNoTrabajados;
        let subsidio = (valorSubsidio * dias / 30);
        if (subsidio < 0) {
            subsidio = 0;
        }
        return subsidio;
    }
</script>