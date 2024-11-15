<div class="modal fade bd-example-modal-lg" id="deducciones-1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Deducciones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs nav-fill mb-3" id="ex4" role="tablist">
                    <li class="nav-item " role="presentation">
                        <a class="nav-link active" id="ex4-tab-1" data-toggle="tab" href="#ex4-tabs-1" role="tab" aria-controls="ex4-tabs-1" aria-selected="true">Deducciones</a>
                    </li>
                    <li class="nav-item " role="presentation">
                        <a class="nav-link" id="ex4-tab-2" data-toggle="tab" href="#ex4-tabs-2" role="tab" aria-controls="ex4-tabs-2" aria-selected="false">Préstamos</a>
                    </li>
                    <li class="nav-item " role="presentation">
                        <a class="nav-link" id="ex4-tab-3" data-toggle="tab" href="#ex4-tabs-3" role="tab" aria-controls="ex4-tabs-3" aria-selected="false">Retefuente</a>
                    </li>
                </ul>

                <form method="POST" action="{{ route('deducciones.update') }}" role="form" class="forms-sample p-0" novalidate id="deduccionesUpdate" onsubmit="event.preventDefault();">
                    @csrf
                    <input type="hidden" name="id" id="edit_deducciones_id">
                    <div class="tab-content" id="ex4-content">
                        <div class="tab-pane fade show active" id="ex4-tabs-1" role="tabpanel" aria-labelledby="ex4-tab-1">
                            <div class="row p-3">
                                <div class="col-12">
                                    <p> Agrega aquí las deducciones adicionales sobre el pago de la persona en el periodo. </p>
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

                            <div class="row px-3 pb-3 border-bottom" id="div_deducciones">

                            </div>

                            <div class="row d-none">
                                <div class="col-6">
                                    <a href="#"><i class="far fa-plus-square"></i> Agregar concepto</a>
                                </div>
                                <div class="col-6">
                                    <a href="#"><i class="fas fa-external-link-alt"></i> Crear nuevo concepto</a>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="ex4-tabs-2" role="tabpanel" aria-labelledby="ex4-tab-2">
                            <div class="row p-3">
                                <div class="col-12">
                                    <p> Agrega aquí el valor a deducir por concepto de préstamos sobre el pago de la persona en el periodo. </p>
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
                            <div class="row px-3 pb-3 border-bottom" id="div_prestamos">

                            </div>
                        </div>

                        <div class="tab-pane fade" id="ex4-tabs-3" role="tabpanel" aria-labelledby="ex4-tab-3">
                            <div class="row p-3">
                                <div class="col-12">
                                    <p> Agrega aquí el valor a deducir por concepto de retención en la fuente por salarios sobre el pago de la persona en el periodo. </p>
                                </div>
                            </div>
                            <div class="row px-3">
                                <div class="col-5">
                                    <h6 style="text-align: center">Concepto</h6>
                                </div>
                                <div class="col-3">
                                    <h6 style="text-align: center">Valor</h6>
                                </div>
                                <div class="col-4"></div>
                            </div>
                            <div class="row px-3 pb-3 border-bottom">
                                <div class="col-5">
                                    <select class="form-control form-control-sm selectpicker" name="retefuente_id" id="retefuente_id" title="Selecciona un concepto" data-live-search="true" data-size="5" required="">
                                        <option value="{{$categorias6->id}}">{{$categorias6->nombre}}</option>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <input class="form-control" type="number" style="width: 100%" name="retefuente_valor" id="retefuente_valor" min="0"  onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46">
                                    <input class="form-control" type="hidden" style="width: 100%" name="retefuente_ids" id="retefuente_ids">
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
                <input type="hidden" id="categorias4" value="{{json_encode($categorias4)}}">
                <input type="hidden" id="categorias5" value="[{{json_encode($categorias5)}}]">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal" id="modal_deducciones">Cerrar</button>
                <a href="javascript:deduccionesUpdate()" class="btn btn-success">Guardar</a>
            </div>
        </div>
    </div>
</div>



<script>

        function editDeducciones(id){
            cargando(true);
            var url = '/empresa/nomina/liquidar-nomina/'+id+'/edit_deducciones';
            var _token = $('meta[name="csrf-token"]').attr('content');
            var i = id;
            $.post(url, {
                id: id,
                _token: _token
            }, function(resul) {
                cargando(false);
                resul = JSON.parse(resul);
                $("#ex4-tab-1").click();
                $('#edit_deducciones_id').val(resul.id);
                $('#deduccionesUpdate').trigger("reset");
                $("#div_deducciones,#div_prestamos").html('');

                //DEDUCCIONES

                    cat4 = $('#categorias4').val();
                    cat4 = JSON.parse(cat4);
                    if (resul.deducciones.length > 0) {
                        for (var i = 0; i < resul.deducciones.length; i++) {
                            $('#div_deducciones').append(`<div class="row mt-2 w-100 ml-2" id="`+resul.deducciones[i]['id']+`"><div class="col-6"><select class="form-control form-control-sm selectpicker" name="deducciones_id[]" id="deduccion_`+resul.deducciones[i]['id']+`" title="Selecciona un concepto" data-live-search="true" data-size="5"></select></div><div class="col-4"><input class="form-control" name="deducciones_valor[]" min="0"  onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46" type="number" style="width: 100%" value="`+resul.deducciones[i]['valor_categoria']+`"><input class="form-control" name="deducciones_ids[]" type="hidden" value="`+resul.deducciones[i]['id']+`"></div><div class="col-2"><button class="btn btn-outline-danger btn-icons mt-1" onclick="destroyDeducciones(`+resul.deducciones[i]['id']+`)"><i class="fas fa-trash" title="Eliminar"></i></button></div>`);
                            var $select = $('#deduccion_'+resul.deducciones[i]['id']);
                            $.each(cat4, function (key, value) {
                                $select.append('<option value=' + value.id + '>' + value.nombre + '</option>');
                            });
                            $select.selectpicker('refresh');
                            $select.val(resul.deducciones[i]['fk_categoria']);
                            $select.trigger('change');
                            $('#deduccion_'+resul.deducciones[i]['id'] +' option:not(:selected)').attr('disabled', true);
                            $select.selectpicker('refresh');
                        }
                    }

                    $('#div_deducciones').append(`<div class="row mt-2 w-100 ml-2" id="1"><div class="col-6"><select class="form-control form-control-sm selectpicker" name="deducciones_id[]" id="deduccion_1" title="Selecciona un concepto" data-live-search="true" data-size="5"></select></div><div class="col-4"><input class="form-control" name="deducciones_valor[]" min="0"  onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46" type="number" style="width: 100%"></div><div class="col-2"></div>`);
                    var $select = $('#deduccion_1');
                    $.each(cat4, function (key, value) {
                        $select.append('<option value=' + value.id + '>' + value.nombre + '</option>');
                    });
                    $select.selectpicker('refresh');

                //PRESTAMOS

                    cat5 = $('#categorias5').val();
                    cat5 = JSON.parse(cat5);
                    if (resul.prestamos.length > 0) {
                        for (var i = 0; i < resul.prestamos.length; i++) {
                            $('#div_prestamos').append(`<div class="row mt-2 w-100 ml-2" id="`+resul.prestamos[i]['id']+`"><div class="col-6"><select class="form-control form-control-sm selectpicker" name="prestamos_id[]" id="prestamos_`+resul.prestamos[i]['id']+`" title="Selecciona un concepto" data-live-search="true" data-size="5"></select></div>
                            <div class="col-4"><input class="form-control" name="prestamos_valor[]" min="0"  onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46" type="number" style="width: 100%" value="`+resul.prestamos[i]['valor_categoria']+`"><input class="form-control" name="prestamos_ids[]" type="hidden" value="`+resul.prestamos[i]['id']+`"></div><div class="col-2"><button class="btn btn-outline-danger btn-icons mt-1" onclick="destroyDeducciones(`+resul.prestamos[i]['id']+`)"><i class="fas fa-trash" title="Eliminar"></i></button></div>`);
                            var $select = $('#prestamos_'+resul.prestamos[i]['id']);
                            $.each(cat5, function (key, value) {
                                $select.append('<option value=' + value.id + '>' + value.nombre + '</option>');
                            });
                            $select.selectpicker('refresh');
                            $select.val(resul.prestamos[i]['fk_categoria']);
                            $select.trigger('change');
                            $('#prestamos_'+resul.prestamos[i]['id'] +' option:not(:selected)').attr('disabled', true);
                            $select.selectpicker('refresh');
                        }
                    }
                    $('#div_prestamos').append(`<div class="row mt-2 w-100 ml-2" id="1"><div class="col-6"><select class="form-control form-control-sm selectpicker" name="prestamos_id[]" id="prestamos_1" title="Selecciona un concepto" data-live-search="true" data-size="5"></select></div><div class="col-4"><input class="form-control" name="prestamos_valor[]" min="0"  onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46" type="number" style="width: 100%"></div><div class="col-2"></div>`);
                    var $select = $('#prestamos_1');
                    $.each(cat5, function (key, value) {
                        $select.append('<option value=' + value.id + '>' + value.nombre + '</option>');
                    });
                    $select.selectpicker('refresh');

                //RETEFUENTE
                    if (resul.retefuente.length > 0) {
                        $('#retefuente_ids').val(resul.retefuente[0]['id']);
                        var $select = $('#retefuente_id');
                        $select.val(resul.retefuente[0]['fk_categoria']);
                        $select.selectpicker('refresh');
                        $('#retefuente_valor').val(resul.retefuente[0]['valor_categoria']);
                    }


                $('input[name="deducciones_valor[]"]').mask('000.000.000', {reverse: true});
                $('input[name="prestamos_valor[]"]').mask('000.000.000', {reverse: true});
                //$('#retefuente_valor').mask('000.000.000', {reverse: true});
                $('#deducciones-1').modal("show");
            });
        }

        function deduccionesUpdate(){
            cargando(true);

            $('input[name="deducciones_valor[]"]').each(function(){
                $(this).val(this.value.replace('.', ''));
            });

            $('input[name="prestamos_valor[]"]').each(function(){
                $(this).val(this.value.replace('.', ''));
            });

            $('#retefuente_valor').val($('#retefuente_valor').val().replace('.', ''));

            $.post($("#deduccionesUpdate").attr('action'), $("#deduccionesUpdate").serialize(), function(dato) {
                if (dato['status'] == 'OK') {
                    $("#deducciones"+dato['id']).empty().text(number_format(dato['deducciones']));
                    $('#modal_deducciones').click();
                    $('#deduccionesUpdate').trigger("reset");
                    cargando(false);
                    formatPago(dato['valor_total'], dato['id']);
                    swal("Registro Actualizado", "Actualización de Deducc, prést y ReteFuen Satisfactoria", "success");
                    refrescarCosto();
                } else {
                    swal('ERROR', dato['mensaje'], "error");
                    cargando(false);
                }
            }, 'json');
        }

        function destroyDeducciones(id){
            cargando(true);
            var url = '/empresa/nomina/liquidar-nomina/'+id+'/destroy_deducciones';
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
                    $("#deducciones"+dato['id']).empty().text(dato['deducciones']);
                } else {
                    cargando(false);
                }
            });
        }

</script>
