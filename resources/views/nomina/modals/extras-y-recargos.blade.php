<div class="modal fade bd-example-modal-lg" id="extras-recargo-{{$nominaPeriodo->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Extras y recargos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="extras-recargo-nomina-{{$nominaPeriodo->id}}">
                <div class="container-fluid">
                    <ul class="nav nav-tabs nav-fill mb-3" id="ex1-{{$nominaPeriodo->id}}" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="ex2-tab-1-n{{$nominaPeriodo->id}}" data-toggle="tab" href="#ex2-tabs-1-n{{$nominaPeriodo->id}}" role="tab" aria-controls="ex2-tabs-1-n{{$nominaPeriodo->id}}" aria-selected="true">Horas extras</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="ex2-tab-2-n{{$nominaPeriodo->id}}" data-toggle="tab" href="#ex2-tabs-2-n{{$nominaPeriodo->id}}" role="tab" aria-controls="ex2-tabs-2-n{{$nominaPeriodo->id}}" aria-selected="false">Recargos</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="ex2-tab-3-n{{$nominaPeriodo->id}}" data-toggle="tab" href="#ex2-tabs-3-n{{$nominaPeriodo->id}}" role="tab" aria-controls="ex2-tabs-3-n{{$nominaPeriodo->id}}" aria-selected="false">Otros conceptos</a>
                        </li>
                    </ul>

                    <form method="POST" action="{{ route('extras.update') }}" role="form" class="forms-sample p-0" novalidate id="extraUpdate-{{$nominaPeriodo->id}}" onsubmit="event.preventDefault();">
                        @csrf
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="valor_calculado_extras" id="valor_calculado_extras">
                        <div class="tab-content" id="ex2-content">
                            <div class="tab-pane fade show active" id="ex2-tabs-1-n{{$nominaPeriodo->id}}" role="tabpanel" aria-labelledby="ex2-tab-1-n{{$nominaPeriodo->id}}">
                                <div class="row p-3">
                                    <div class="col-12">
                                        <p> Ingresa aquí el número de horas extras o recargos </p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-8"><span class="text-right text-small font-weight-bold">Valor sobre hora ordinaria</span></div>
                                    <div class="col-4"><span class="text-right text-small font-weight-bold"># de horas</span></div>
                                </div>
                                @foreach($nominaPeriodo->nominaDetallesUno as $categorias)
                                @if($categorias->fk_nomina_cuenta == 1 && $categorias->fk_nomina_cuenta_tipo == 1)
                                <div class="row py-1 row-extra">
                                    <div class="col">
                                        <p>{{$categorias->nombre}}</p>
                                    </div>

                                    <div class="col v-hora-ordinaria">
                                        {{$categorias->valor_hora_ordinaria}}
                                    </div>

                                    <div class="col">
                                        <input class="form-control c-hora" type="number" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46" id="{{$categorias->id}}" name="horas_extras[]" style="width: 70px">
                                        <input class="form-control" type="hidden" value="{{$categorias->nombre}}" name="id_extras[]" style="width: 70px">
                                        <input class="c-value" type="hidden" id="val-{{$categorias->id}}" name="horas_extras_value[]">
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </div>
                            <div class="tab-pane fade" id="ex2-tabs-2-n{{$nominaPeriodo->id}}" role="tabpanel" aria-labelledby="ex2-tab-2-n{{$nominaPeriodo->id}}">
                                <div class="row p-3">
                                    <div class="col-12">
                                        <p> Ingresa aquí el número de horas extras o recargos </p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-8"><span class="text-right text-small font-weight-bold">Valor sobre hora ordinaria</span></div>
                                    <div class="col-4"><span class="text-right text-small font-weight-bold"># de horas</span></div>
                                </div>

                                @foreach($nominaPeriodo->nominaDetallesUno as $categorias)
                                @if($categorias->fk_nomina_cuenta == 1 && $categorias->fk_nomina_cuenta_tipo == 2)
                                <div class="row py-1 px-3 row-recargo">
                                    <div class="col">
                                        <p>{{$categorias->nombre}}</p>
                                    </div>

                                    <div class="col v-hora-ordinaria">
                                        {{$categorias->valor_hora_ordinaria}}
                                    </div>

                                    <div class="col">
                                        <input class="form-control c-hora" type="number" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46" id="{{$categorias->id}}" name="horas_extras_ordinaria[]" style="width: 70px">
                                        <input class="form-control" type="hidden" value="{{$categorias->nombre}}" name="id_extras_ordinaria[]" style="width: 70px">
                                        <input class="c-value" type="hidden" id="val-{{$categorias->id}}" name="horas_extras_ordinaria_value[]">
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </div>
                            <div class="tab-pane fade" id="ex2-tabs-3-n{{$nominaPeriodo->id}}" " role=" tabpanel" aria-labelledby="ex2-tab-3-n{{$nominaPeriodo->id}}">
                                <div class="row p-3">
                                    <div class="col-12">
                                        <p> Si deseas crear una nueva hora extra o recargo con un valor sobre la hora ordinaria
                                            diferentes a los estipulados por la ley, aquí puedes crear nuevos conceptos y
                                            definir el valor que desees. Si tienes inquietudes ingresa a este </p>
                                    </div>
                                </div>

                                 <div class="row">
                                    <div class="col-8"><span class="text-right text-small font-weight-bold">Valor sobre hora ordinaria</span></div>
                                    <div class="col-4"><span class="text-right text-small font-weight-bold"># de horas</span></div>
                                </div>

                                @php $count = 8; @endphp
                                @foreach($nominaPeriodo->nominaDetallesUno as $categorias)
                                @if($categorias->fk_nomina_cuenta == 1 && $categorias->fk_nomina_cuenta_tipo == 3)
                                <div class="row py-1 px-3 row-concepto">
                                    <div class="col">
                                        <p>{{$categorias->nombre}}</p>
                                    </div>

                                    <div class="col v-hora-ordinaria">
                                        {{$categorias->valor_hora_ordinaria}}
                                    </div>

                                    <div class="col">
                                        <input class="form-control c-hora" type="number" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46" id="{{$categorias->id}}" name="otros_horas[]" style="width: 70px" value="0">
                                        <input class="form-control" type="hidden" value="{{$categorias->nombre}}" name="otros_id[]" style="width: 70px">
                                        <input class="c-value" type="hidden" id="val-{{$categorias->id}}" name="otros_horas_value[]">
                                    </div>
                                </div>
                                @php $count++; @endphp
                                @endif
                                @endforeach
                                <br>
                                <div class="row">
                                    <div class="col">
                                        <input type="text" class="form-control bg-light bordered" placeholder="Nueva Hora Extra" name="otros_new_nombres[]">
                                    </div>
                                    <div class="col">
                                        <input type="number" class="form-control bg-light bordered" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46" name="otros_new_valor[]">
                                    </div>

                                    <div class="col">
                                        <input class="form-control bg-light bordered c-value" type="number" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46" name="otros_new_horas[]">
                                    </div>
                                </div>

                                <br>

                                {{-- <span class="badge badge-pill badge-secondary"><i class="far fa-plus-square"></i> Agregar una nueva hora o recargo</span> --}}
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal" id="cancelarModal">Cerrar</button>
                <a href="javascript:extrasUpdate()" class="btn btn-success">Guardar</a>
            </div>
        </div>
    </div>
</div>

@if($loop->iteration == 1)
<script>
    var idNomina = 0;

    function extrasUpdate() {
        cargando(true);
        editId = idNomina;
        resultTotal = updateTotal(editId);
        $('#valor_calculado_extras').val(resultTotal);
        console.log($("#extraUpdate-" + editId).attr('action'))
        $.post($("#extraUpdate-" + editId).attr('action'), $("#extraUpdate-" + editId).serialize(), function(dato) {
            if (dato['status'] == 'OK') {
                $("#extras" + dato['id']).empty().text(dato['horas']);
                $('#extras-recargo-' + editId + ' #cancelarModal').click();
                $("#extraUpdate-" + editId).trigger("reset");
                cargando(false);
                swal("Registro Actualizado", "Actualización de Extras y recargos Satisfactoria", "success");
                if (dato['refresh'] == 'OK') {
                    window.location.reload();
                }
                $('#valor_calculado_extras').val(dato['valor_total']);
                formatPagoExtras(dato['valor_total'], dato['id']);
                refrescarCosto();
            } else {
                swal('ERROR', dato['mensaje'], "error");
                cargando(false);
            }
        }, 'json');
    }

    function updateTotal(idNomina) {
        var extras = $(`#extras-recargo-nomina-${idNomina} .row-extra`);
        var recargos = $(`#extras-recargo-nomina-${idNomina} .row-recargo`);
        var conceptos = $(`#extras-recargo-nomina-${idNomina} .row-concepto`);
        var salarioBase = parseFloat($('#base-periodo-nomina-' + idNomina).val());


        var formula1 = function(salarioBase, valorHora, nHora) {
            salarioBase = parseFloat(salarioBase);
            valorHora = parseFloat(valorHora);
            nHora = parseInt(nHora);
            if (isNaN(salarioBase)) {
                salarioBase = 0;
            }
            if (isNaN(valorHora)) {
                valorHora = 0;
            }
            if (isNaN(nHora)) {
                nHora = 0;
            }

            return ((salarioBase * valorHora * nHora) / (30 * 8));
        };
        var result = 0;

        extras.each(function(i) {
            let row = $(this);
            let value = formula1(salarioBase, row.find('.v-hora-ordinaria').text(), row.find('.c-hora').val());
            row.find('.c-value').val(value);
            result += value;
        });

        recargos.each(function(i) {
            let row = $(this);
            let value = formula1(salarioBase, row.find('.v-hora-ordinaria').text(), row.find('.c-hora').val());
            row.find('.c-value').val(value);
            result += value;
        });

        conceptos.each(function(i) {
            let row = $(this);
            let value = formula1(salarioBase, row.find('.v-hora-ordinaria').text(), row.find('.c-hora').val());
            row.find('.c-value').val(value);
            result += value;
        });

        pagoFormat = formatPagoExtras(result + salarioBase, idNomina);
        return (result + salarioBase);
    }

    function formatPagoExtras(value, idNomina, formated = null) {

        let pagoFormat = '';

        if (!formated) {
            pagoFormat = (value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,').slice(0, -3);
            (parseFloat(value)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,').slice(0, -3);
        } else {
            pagoFormat = formated;
        }

        $('#pago-nomina-format-' + idNomina).text('$' + pagoFormat);
        return pagoFormat;
    }

    function editHoras(id) {
        if (window.location.pathname.split("/")[1] === "software") {
					var url='/software/empresa';
		}else{
					var url = '/empresa';
		}
        cargando(true);
        var url = url + '/nomina/liquidar-nomina/' + id + '/edit';
        var _token = $('meta[name="csrf-token"]').attr('content');
        var i = id;
        $.post(url, {
            id: id,
            _token: _token
        }, function(resul) {
            cargando(false);
            resul = JSON.parse(resul);
            $('#extras-recargo-nomina-' + resul.id + ' #ex2-tab-1').click();
            $('#extras-recargo-nomina-' + resul.id + ' #edit_id').val(resul.id);
            idNomina = resul.id;
            $('#extraUpdate-' + resul.id).trigger("reset");
            var a = 1;
            for (var i = 0; i < resul.nomina.length; i++) {
                if (resul.nomina[i]['numero_horas']) {
                    horas = resul.nomina[i]['numero_horas'];
                } else {
                    horas = 0;
                }
                $('#extras-recargo-nomina-' + resul.id + ' input[id=' + (resul.nomina[i]['id']) + ']').val(horas);
                a++;
            }
            $('#extras-recargo-' + resul.id).modal("show");
        });
    }
</script>
@endif