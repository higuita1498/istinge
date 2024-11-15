<style>
    .menu-ing__select {
        .dropdown-menu{
            min-width: fit-content !important;
            overflow-y: auto;
        }
    }
</style>

<div class="modal fade bd-example" id="ingresos-adicionales-1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ingresos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs nav-fill mb-3" id="ex1" role="tablist">
                    <li class="nav-item " role="presentation">
                        <a class="nav-link active" id="ex5-tab-1" data-toggle="tab" href="#ex5-tabs-1" role="tab" aria-controls="ex5-tabs-1" aria-selected="true">Constitutivos de salario</a>
                    </li>
                    <li class="nav-item " role="presentation">
                        <a class="nav-link" id="ex5-tab-2" data-toggle="tab" href="#ex5-tabs-2" role="tab" aria-controls="ex5-tabs-2" aria-selected="false">No Constitutivos de salario</a>
                    </li>
                    <li class="nav-item " role="presentation">
                        <a class="nav-link" id="ex5-tab-3" data-toggle="tab" href="#ex5-tabs-3" role="tab" aria-controls="ex5-tabs-3" aria-selected="false">Aux. Conectividad</a>
                    </li>
                </ul>

                <form method="POST" action="{{ route('adicionales.update') }}" role="form" class="forms-sample p-0" novalidate id="adicionalesUpdate" onsubmit="event.preventDefault();">
                    @csrf
                    <input type="hidden" name="id" id="edit_ingresos_id">
                    <div class="tab-content" id="ex5-content">
                        <div class="tab-pane fade show active" id="ex5-tabs-1" role="tabpanel" aria-labelledby="ex5-tab-1">
                            <div class="row p-3">
                                <div class="col-12">
                                    <p> Agrega aquí todos los ingresos que sumarán para la base de seguridad social y
                                        prestaciones.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <h6 class="text-center">Concepto</h6>
                                </div>
                                <div class="col">
                                    <h6 class="text-center">Valor</h6>
                                </div>
                            </div>
                            <div class="row pb-3 border-bottom" id="div_constitutivos">

                            </div>
                            <div class="row">
                                <div class="col-6">
                                    {{-- <a href="#"><i class="far fa-plus-square"></i> Agregar concepto</a> --}}
                                </div>
                                <div class="col-6">
                                    {{-- <a href="#"><i class="fas fa-external-link-alt"></i> Crear nuevo concepto</a> --}}
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="ex5-tabs-2" role="tabpanel" aria-labelledby="ex5-tab-2">
                            <div class="row p-3">
                                <div class="col-12">
                                    <p> Agrega aquí todos los ingresos que <b>NO</b> sumarán para la base de seguridad
                                        social y prestaciones. </p>
                                </div>
                            </div>
                            <div class="row px-3">
                                <div class="col-6">
                                    <h6 style="text-align: center">Concepto</h6>
                                </div>
                                <div class="col-4">
                                    <h6 style="text-align: center">Valor</h6>
                                </div>
                            </div>

                            <div class="row px-3 pb-3 border-bottom" id="div_no_constitutivos">

                            </div>

                            <div class="row">
                                <div class="col-6">
                                    {{-- <a href="#"><i class="far fa-plus-square"></i> Agregar concepto</a> --}}
                                </div>
                                <div class="col-6">
                                    {{-- <a href="#"><i class="fas fa-external-link-alt"></i> Crear nuevo concepto</a> --}}
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="ex5-tabs-3" role="tabpanel" aria-labelledby="ex5-tab-3">
                            <div class="row p-3">
                                <div class="col-12">
                                    <p> Agrega aquí el número de días que la persona laboró desde su domicilio en el
                                        periodo para que le sean reconocidos como Auxilio de conectividad. Esta es una
                                        medida de contingencia decretada a causa del COVID-19. </p>
                                </div>
                            </div>
                            <div class="row px-3">
                                <div class="col-6">
                                    <h6 style="text-align: center">Concepto</h6>
                                </div>
                                <div class="col-4">
                                    <h6 style="text-align: center">Valor</h6>
                                </div>
                            </div>
                            <div class="row px-3 pb-3 border-bottom">
                                <div class="col-6">
                                    <select class="form-control form-control-sm selectpicker" name="auxiliar_id" id="auxiliar_id" title="Selecciona un concepto" data-live-search="true" data-size="5" required="">
                                        <option value="{{$categorias3->id}}">{{$categorias3->nombre}}</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <input class="form-control" name="auxiliar_ids" type="hidden" id="auxiliar_ids">
                                    <input class="form-control" name="auxiliar_valor" id="auxiliar_valor" type="number" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46" style="width: 100%">
                                </div>
                                <div class="col-2">

                                </div>
                            </div>
                            <br>
                            <div class="alert alert-info d-none d-sm-none d-md-block" role="alert">
                                El auxilio de conectividad es una medida temporal y transitoria del Gobierno en
                                reemplazo del auxilio de transporte para empleados que reciben hasta dos salarios
                                mínimos.
                            </div>
                        </div>
                    </div>
                </form>
                <input type="hidden" id="categorias1" value="{{json_encode($categorias1)}}">
                <input type="hidden" id="categorias2" value="{{json_encode($categorias2)}}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal" id="modal_ingresos">Cerrar
                </button>
                <a href="javascript:adicionalesUpdate()" class="btn btn-success">Guardar</a>
            </div>
        </div>
    </div>
</div>


<script>
    function editAdicionales(id) {
        // cargando(true);
        var url = '/empresa/nomina/liquidar-nomina/' + id + '/edit_adicionales';
        var _token = $('meta[name="csrf-token"]').attr('content');
        var i = id;
        $.post(url, {
            id: id,
            _token: _token
        }, function(resul) {
            cargando(false);
            resul = JSON.parse(resul);
            console.log(resul);
            $("#ex5-tab-1").click();
            $('#edit_ingresos_id').val(resul.id);
            $('#adicionalesUpdate').trigger("reset");
            $("#div_constitutivos,#div_no_constitutivos,#conectividad").html('');

            cat1 = $('#categorias1').val();
            cat1 = JSON.parse(cat1);

            cat2 = $('#categorias2').val();
            cat2 = JSON.parse(cat2);


            // <select class="form-control form-control-sm selectpicker" name="constitutivos_id[]" id="constitutivo_`+resul.constitutivos[i]['id']+`" title="Selecciona un concepto" data-live-search="true" data-size="5">
            // </select>

            //CONSTITUTIVOS
            if (resul.constitutivos.length > 0) {
                for (var i = 0; i < resul.constitutivos.length; i++) {
                    $('#div_constitutivos').append(`
                            <div class="row mt-2 w-100 ml-2" id="` + resul.constitutivos[i]['id'] + `">
                                <div class="col-6">
                                    <select class="form-control form-control-sm selectpicker" name="constitutivos_id[]" id="constitutivo_` + resul.constitutivos[i]['id'] + `" title="Selecciona un concepto" data-live-search="true" data-size="5">
                                    </select>
                                </div>
                                <div class="col-4">
                                    <input class="form-control" name="constitutivos_valor[]"  type="number" style="width: 100%" value="` + resul.constitutivos[i]['valor_categoria'] + `">
                                    <input class="form-control" name="constitutivos_ids[]" type="hidden" value="` + resul.constitutivos[i]['id'] + `">
                                </div>
                                <div class="col-2">
                                        <button class="btn btn-outline-danger btn-icons mt-1" onclick="destroyAdicionales(` + resul.constitutivos[i]['id'] + `)"><i class="fas fa-trash" title="Eliminar"></i></button>
                                </div>
                            </div>`);

                    var $select = $('#constitutivo_' + resul.constitutivos[i]['id']);
                    $.each(cat1, function(key, value) {
                        $select.append('<option value=' + value.id + '>' + value.nombre + '</option>');
                    });

                    $select.selectpicker('refresh');
                    $select.val(resul.constitutivos[i]['fk_categoria']);
                    $select.trigger('change');
                    $('#constitutivo_' + resul.constitutivos[i]['id'] +' option:not(:selected)').attr('disabled', true);
                    $select.selectpicker('refresh');
                }
            }

            $('#div_constitutivos').append(`<div class="row mt-2 w-100 ml-2" id="1"><div class="col-6"><select class="form-control form-control-sm selectpicker" name="constitutivos_id[]" id="constitutivo_1" title="Selecciona un concepto" data-live-search="true" data-size="5"></select></div><div class="col-4"><input class="form-control" name="constitutivos_valor[]" min="0"  onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46" type="number" style="width: 100%"></div><div class="col-2"></div>`);
            var $select = $('#constitutivo_1');
            $.each(cat1, function(key, value) {
                $select.append('<option value=' + value.id + '>' + value.nombre + '</option>');
            });
            $select.selectpicker('refresh');

            //NO CONSTITUTIVOS
            if (resul.no_constitutivos.length > 0) {
                for (var i = 0; i < resul.no_constitutivos.length; i++) {
                    $('#div_no_constitutivos').append(`<div class="row mt-2 w-100 ml-2" id="` + resul.no_constitutivos[i]['id'] + `"><div class="col-6"><select class="form-control form-control-sm selectpicker" name="no_constitutivos_id[]" id="no_constitutivo_` + resul.no_constitutivos[i]['id'] + `" title="Selecciona un concepto" data-live-search="true" data-size="5"></select></div><div class="col-4"><input class="form-control" name="no_constitutivos_valor[]" min="0"  onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46" type="number" style="width: 100%" value="` + resul.no_constitutivos[i]['valor_categoria'] + `"><input class="form-control" name="no_constitutivos_ids[]" type="hidden" value="` + resul.no_constitutivos[i]['id'] + `"></div><div class="col-2"><button class="btn btn-outline-danger btn-icons mt-1" onclick="destroyAdicionales(` + resul.no_constitutivos[i]['id'] + `)"><i class="fas fa-trash" title="Eliminar"></i></button></div>`);
                    var $select = $('#no_constitutivo_' + resul.no_constitutivos[i]['id']);
                    $.each(cat2, function(key, value) {
                        $select.append('<option value=' + value.id + '>' + value.nombre + '</option>');
                    });
                    $select.selectpicker('refresh');
                    $select.val(resul.no_constitutivos[i]['fk_categoria']);
                    $select.trigger('change');
                    $('#no_constitutivo_' + resul.no_constitutivos[i]['id'] + ' option:not(:selected)').attr('disabled', true);
                    $select.selectpicker('refresh');
                }
            }
            $('#div_no_constitutivos').append(`<div class="row mt-2 w-100 ml-2" id="1"><div class="col-6 menu-ing__select"><select class="form-control form-control-sm selectpicker" name="no_constitutivos_id[]" id="no_constitutivo_1" title="Selecciona un concepto" data-live-search="true" data-size="5"></select></div><div class="col-4"><input class="form-control" name="no_constitutivos_valor[]" min="0"  onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46" type="number" style="width: 100%"></div><div class="col-2"></div>`);
            var $select = $('#no_constitutivo_1');
            $.each(cat2, function(key, value) {
                $select.append('<option value=' + value.id + '>' + value.nombre + '</option>');
            });
            $select.selectpicker('refresh');

            //CONECTIVIDAD
            if (resul.conectividad.length > 0) {
                $('#auxiliar_ids').val(resul.conectividad[0]['id']);
                var $select = $('#auxiliar_id');
                $select.val(resul.conectividad[0]['fk_categoria']);
                $select.selectpicker('refresh');
                $('#auxiliar_valor').val(resul.conectividad[0]['valor_categoria']);
            }


            // $('input[name="constitutivos_valor[]"]').mask('000.000.000', {reverse: true});
            // $('input[name="no_constitutivos_valor[]"]').mask('000.000.000', {reverse: true});
            // $('#auxiliar_valor').mask('000.000.000', {reverse: true});

            $('#ingresos-adicionales-1').modal("show");
        });
    }

    function adicionalesUpdate() {
        cargando(true);

        // $('input[name="constitutivos_valor[]"]').each(function () {
        //     $(this).val(this.value.replace('.', ''));
        // });
        //
        // $('input[name="no_constitutivos_valor[]"]').each(function () {
        //     $(this).val(this.value.replace('.', ''));
        // });

        $('#auxiliar_valor').val($('#auxiliar_valor').val().replace('.', ''));

        $.post($("#adicionalesUpdate").attr('action'), $("#adicionalesUpdate").serialize(), function(dato) {
            if (dato['status'] == 'OK') {
                $("#ingresos" + dato['id']).empty().text(number_format(dato['ingresos']));
                $('#modal_ingresos').click();
                $('#adicionalesUpdate').trigger("reset");
                cargando(false);
                formatPago(dato['valor_total'], dato['id']);
                swal("Registro Actualizado", "Actualización de Ingresos adicionales Satisfactoria", "success");
                refrescarCosto();
            } else {
                swal('ERROR', dato['mensaje'], "error");
                cargando(false);
            }
        }, 'json');
    }

    function destroyAdicionales(id) {
        cargando(true);
        var url = '/empresa/nomina/liquidar-nomina/' + id + '/destroy_adicionales';
        var _token = $('meta[name="csrf-token"]').attr('content');
        var i = id;
        $.post(url, {
            id: id,
            _token: _token
        }, function(dato) {
            cargando(false);
            dato = JSON.parse(dato);
            if (dato['status'] == 'OK') {
                $("#" + i).remove();
                $("#ingresos" + dato['id']).empty().text(dato['ingresos']);
            } else {
                cargando(false);
            }
        });
    }
</script>
