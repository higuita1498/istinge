@extends('layouts.app')


@section('style')
<style>
    label i.input-helper {
        display: none;
    }
</style>
@endsection

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 body-oscuro">
                <div class="card-body border-0">
                    <h3 class="">Centros de Costos - PUC</h3>
                    <p class="card-text" style="font-size:15px">Estas serán las cuentas bajo las cuales comenzarán todos los conceptos contables que se exporten para cargar al software contable. La cuenta contable de cada persona cambiará de acuerdo a su Centro de Costos. Si deseas cambiar a una persona de centro de costos, deberás editarlo en sus "datos básicos".</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4 mb-5">
        <div class="col-md-12">
            @if(auth()->user()->modo_lecturaNomina())
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <a>Estas en modo lectura, si deseas seguir disfrutando de nuestros servicios adquiere alguno de nuestros planes
                    <a class="text-black" href="{{ route('clientplans.index') }}"> <b>Click Aquí.</b></a></a>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @else
            <button class="btn btn-rounded float-right mb-2 mr-2" style="background-color: #022454; color: white;" data-toggle="modal" data-target="#modalCCStore">
                <i class="fas fa-plus-circle"></i>
                Añadir centro de costos</button>
            @endif
            <div class="table-responsive">
                <table class="table table-light table-striped table-hover w-100" style="border: 1px solid #e9ecef;">
                    <thead class="thead-light">
                        <tr>
                            <th>Centro de costos</th>
                            <th>Prefijo cuenta contable</th>
                            <th>Código cuenta contable</th>
                            <th>N° Personas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody">
                        @foreach($ccostos as $ccosto)
                        <tr @if($ccosto->id==Session::get('ccosto_id')) class="active_table" @endif id="{{ $ccosto->id }}">
                            <td>{{ $ccosto->nombre }}</td>
                            <td>{{ $ccosto->prefijo_contable }}</td>
                            <td>{{ $ccosto->codigo_contable }}</td>
                            <td>{{$ccosto->personas()->count()}}</td>
                            <td>
                                <button class="btn btn-outline-secondary btn-icons editCC" idCC="{{$ccosto->id}}" title="Editar Centro de Costo"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-outline-danger btn-icons destroyCC" idCC="{{$ccosto->id}}" title="Eliminar Centro de Costo"><i class="fas fa-times"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 body-oscuro">
                <div class="card-body border-0">
                    <h3 class="">Cuentas Contables
                    </h3>
                    <p class="card-text" style="font-size:15px">Configura tus conceptos de nómina, los cuales se usarán para generar el archivo para tu software contable y al momento de liquidar tu nómina.</p>
                    <div class="alert alert-info ml-0 text-small text-dark" role="alert">
                        <i class="fas fa-info-circle"></i> Recuerda que el prefijo de cada cuenta será el código cuenta contable de tus Centros de Costos PUC.
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 d-none">
            <div class="card border-0 body-oscuro">
                <div class="card-body border-0">
                    <p class="card-text">¿Cómo deseas configurar las contrapartidas de subsidio de transporte, otros ingresos, horas extras y recargos?
                    </p>

                    <div class="alert alert-info ml-0 text-small text-dark" role="alert">
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1">
                                <label class="form-check-label" for="exampleRadios1">
                                    <p class="card-text">Consolidados con la contrapartida de salario.</p>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="option2">
                                <label class="form-check-label" for="exampleRadios2">
                                    <p class="card-text"> Separados, cada cuenta con su contrapartida.</p>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 body-oscuro">
                <div class="card-body border-0">
                    <div class="accordion" id="accordionExample">
                        @php $cont = 0; @endphp
                        @foreach($cats_general as $cat_general)
                        @php $cont++ @endphp
                        <div class="card">
                            <div class="card-header body-oscuro" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="btn btn-link body-oscuro btn-block text-left collapsed font-weight-bold " style="color: grey;" type="button" data-toggle="collapse" data-target="#collapse{{ $cont }}" aria-expanded="false" aria-controls="collapse{{ $cont }}">
                                        {{ $cat_general->nombre }}
                                    </button>
                                </h2>
                            </div>
                            @if($cat_general->hijos()>0)
                            <div id="collapse{{ $cont }}" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-light table-striped table-hover w-100" style="border: 1px solid #e9ecef;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Concepto</th>
                                                    <th>Cuenta Contable</th>
                                                    <th>Contrapartida</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($cat_general->asociado() as $asociado)
                                                <tr id="{{$asociado->id}}">
                                                    <td>{{ $asociado->nombre }}</td>
                                                    <td>{{ $asociado->codigo }}</td>
                                                    <td></td>
                                                    <td>
                                                        <button class="btn btn-outline-primary btn-icons editCta" idCta="{{$asociado->id}}" title="Editar Cta. Contable"><i class="fas fa-edit"></i></button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="card border-0 body-oscuro">
            <div class="card-body border-0">
                <h3 class="">Instituciones de Seguridad Social</h3>
                <p class="card-text" style="font-size:15px">Aquí podrás modificar la cuenta contable de Salud, Pensión,
                    Riesgos y Caja de Compensación por institución, los cuales se verán reflejados en tu archivo para cargar
                    al software contable.
                </p>

            </div>
        </div>
        <div class="col-12 d-none">
            <div class="card border-0 body-oscuro">
                <div class="card-body border-0">
                    <p class="card-text">¿Cómo deseas causar los conceptos de Seguridad Social?</p>

                    <div class="alert alert-info ml-0 text-small text-dark" role="alert">
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="exampleRadios" id="identificacion-persona" value="">
                                <label class="form-check-label" for="identificacion-persona">
                                    <p class="card-text">Con el número de identificación de la persona</p>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="exampleRadios" id="nit-institucion" value="">
                                <label class="form-check-label" for="nit-institucion">
                                    <p class="card-text">Con el Nit de las institutiones</p>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="exampleRadios" id="nit-centro-costo" value="">
                                <label class="form-check-label" for="nit-centro-costo">
                                    <p class="card-text">Con el Nit de las institutiones y agregado por el centro de costo</p>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 body-oscuro">
                <div class="card-body border-0">
                    <div class="accordion" id="accordionExample2">

                        <div class="card">
                            <div class="card-header body-oscuro" id="headingFour">
                                <h2 class="mb-0">
                                    <button class="btn btn-link body-oscuro btn-block text-left collapsed font-weight-bold " style="color: grey;" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        Eps
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordionExample2">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-light table-striped table-hover w-100" style="border: 1px solid #e9ecef;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Nit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Administrativo</td>
                                                    <td>51</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header body-oscuro" id="headingFive">
                                <h2 class="mb-0">
                                    <button class="btn btn-link body-oscuro btn-block text-left collapsed font-weight-bold " style="color: grey;" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                        Fondos de pensiones
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-parent="#accordionExample2">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-light table-striped table-hover w-100" style="border: 1px solid #e9ecef;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Nit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Administrativo</td>
                                                    <td>51</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header body-oscuro" id="headingSix">
                                <h2 class="mb-0">
                                    <button class="btn btn-link body-oscuro btn-block text-left collapsed font-weight-bold " style="color: grey;" type="button" data-toggle="collapse" data-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                        ARL y caja de compensación
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseSix" class="collapse" aria-labelledby="headingSix" data-parent="#accordionExample2">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-light table-striped table-hover w-100" style="border: 1px solid #e9ecef;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Nit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Administrativo</td>
                                                    <td>51</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCCStore" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                @include('nomina.ccosto.create')
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCCEdit" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                @include('nomina.ccosto.edit')
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCtaEdit" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                @include('nomina.ctacontable.edit')
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script>
    $(document).ready(function() {
        $('.editCC').click(function() {
            var url = '/empresa/nomina/ccosto/' + $(this).attr('idCC') + '/edit';
            var _token = $('meta[name="csrf-token"]').attr('content');
            var i = $(this).attr('idCC');
            $.post(url, {
                idCC: $(this).attr('idCC'),
                _token: _token
            }, function(resul) {
                resul = JSON.parse(resul);
                $('#edit_nombre').val(resul.nombre);
                $('#edit_prefijo').val(resul.prefijo);
                $('#edit_codigo').val(resul.codigo);
                $('#edit_id').val(resul.id);
                $('#modalCCEdit').modal("show");
            });
        });

        $('.destroyCC').click(function() {
            swal({
                title: '¿Está seguro que deseas eliminar el Centro de Costo?',
                text: 'Se borrara de forma permanente',
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#00ce68',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.value) {
                    var url = '/empresa/nomina/ccosto/' + $(this).attr('idCC') + '/destroy';
                    var _token = $('meta[name="csrf-token"]').attr('content');
                    var i = $(this).attr('idCC');
                    $.post(url, {
                        idCC: $(this).attr('idCC'),
                        _token: _token
                    }, function(resul) {
                        resul = JSON.parse(resul);
                        if (resul.status == 'OK') {
                            swal("Registro Eliminado", "El Centro de Costo ha sido eliminado", "success");
                            $("#" + i).remove();
                        } else {
                            swal('ERROR', resul.mensaje, "error");
                        }
                    });
                }
            });
        });

        $('.editCta').click(function() {
            var url = '/empresa/nomina/ctacontable/' + $(this).attr('idCta') + '/edit';
            var _token = $('meta[name="csrf-token"]').attr('content');
            var i = $(this).attr('idCta');
            $.post(url, {
                idCta: $(this).attr('idCta'),
                _token: _token
            }, function(resul) {
                resul = JSON.parse(resul);
                $('#cta_codigo').val(resul.codigo);
                $('#cta_id').val(resul.id);
                $('#modalCtaEdit').modal("show");
            });
        });
    });

    function editTable(id) {
        var url = '/empresa/nomina/ccosto/' + id + '/edit';
        var _token = $('meta[name="csrf-token"]').attr('content');
        var i = id;
        $.post(url, {
            idCC: id,
            _token: _token
        }, function(resul) {
            resul = JSON.parse(resul);
            $('#edit_nombre').val(resul.nombre);
            $('#edit_prefijo').val(resul.prefijo);
            $('#edit_codigo').val(resul.codigo);
            $('#edit_id').val(resul.id);
            $('#modalCCEdit').modal("show");
        });
    }

    function destroyTable(id) {
        swal({
            title: '¿Está seguro que deseas eliminar el Centro de Costo?',
            text: 'Se borrara de forma permanente',
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00ce68',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.value) {
                var url = '/empresa/nomina/ccosto/' + id + '/destroy';
                var _token = $('meta[name="csrf-token"]').attr('content');
                var i = id;
                $.post(url, {
                    idCC: id,
                    _token: _token
                }, function(resul) {
                    resul = JSON.parse(resul);
                    if (resul.status == 'OK') {
                        swal("Registro Eliminado", "El Centro de Costo ha sido eliminado", "success");
                        $("#" + i).remove();
                    } else {
                        swal('ERROR', resul.mensaje, "error");
                    }
                });
            }
        });
    }

    function editTableCta(id) {
        var url = '/empresa/nomina/ctacontable/' + id + '/edit';
        var _token = $('meta[name="csrf-token"]').attr('content');
        var i = id;
        $.post(url, {
            idCta: id,
            _token: _token
        }, function(resul) {
            resul = JSON.parse(resul);
            $('#cta_nombre').val(resul.nombre);
            $('#cta_codigo').val(resul.codigo);
            $('#cta_id').val(resul.id);
            $('#modalCtaEdit').modal("show");
        });
    }
</script>

@endsection
