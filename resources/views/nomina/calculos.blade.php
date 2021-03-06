@extends('layouts.app')

@section('content')

<style>
    .card-persona {
        border: 0px;
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        border-radius: 15px;
        padding: 15px 0 20px 0;
    }

    .card-header {
        border-bottom: 0px;
        background: #fff;
        font-weight: bold;
    }

    .list-group-item {
        border: 0px;
    }

    .list-group-item:hover {
        background-color: #fff;
    }

    .list-group-item i {
        font-size: 1.1em;
        font-style: normal;
    }

    .list-group-item span {
        font-size: 1.5em;
        font-weight: bold;
    }

    .list-group-flush:last-child .list-group-item:last-child {
        border-bottom: 0;
        border-radius: 15px;
    }

    /*.table td, .table th {
			padding: 13px 0px 13px 50px !important;
			width: 50%;
		}
		.table > tbody > tr > td{
			padding-left: 0px !important;
			font-weight: bold;
		}*/
    .card-header a {
        font-size: 13px;
    }

    .text-gestor {
        color: #d08f50;
    }

    .table-responsive {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .15) !important;
        border-radius: 5px;
    }

    .nav-tabs .nav-link {
        font-size: 1em;
    }

    .nav-tabs .nav-link.active,
    .nav-tabs .nav-item.show .nav-link {
        color: #d08f50;
        border-color: #ffffff #ffffff #d08f50;
    }

    .nav-tabs .nav-link {
        border: 2px solid transparent;
    }

    th {
        font-weight: bold !important;
    }
</style>

<div class="row card-description">
    <div class="col-md-4">
        <div class="card-persona">
            <div class="card-header text-center">

            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item text-center"><span>Empresa</span><br><i>{{$persona->empresa->nombre}}</i><br><br><br></li>
            </ul>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-persona">
            <div class="card-header text-center">

            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item text-center"><span>Persona</span><br> <i>{{$persona->nombre()}} <br> {{$persona->tipo_documento()}}: {{$persona->nro_documento}} <br> Cargo: {{$persona->cargo()->nombre}}</i></li>
            </ul>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-persona">
            <div class="card-header text-center">

            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item text-center"><span>Periodo de pago</span><br> <i>{{$mensajePeriodo}}</i> <br> D??as trabajados: {{$totalidad['diasTrabajados']['total']}}</i><br><br></li>
            </ul>
        </div>
    </div>
</div>

<div class="row card-description mb-0">
    <div class="col-md-12 mb-0">
        <ul class="nav nav-tabs mb-3" id="ex1" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="ex3-tab-1" data-toggle="tab" href="#ex3-tabs-1" role="tab" aria-controls="ex3-tabs-1" aria-selected="true">Detalles de la colilla</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="ex3-tab-2" data-toggle="tab" href="#ex3-tabs-2" role="tab" aria-controls="ex3-tabs-2" aria-selected="false">Seguridad social y parafiscales</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="ex3-tab-3" data-toggle="tab" href="#ex3-tabs-3" role="tab" aria-controls="ex3-tabs-3" aria-selected="false">Provisi??n prestaciones sociales</a>
            </li>
        </ul>

        <div class="tab-content" id="ex3-content">
            <div class="tab-pane fade show active" id="ex3-tabs-1" role="tabpanel" aria-labelledby="ex3-tab-1">
                <div class="row p-3">
                    <div class="col-12 border-bottom">
                        <h4>Detalles de la colilla</h4>
                    </div>

                    <div class="col-12 mt-5">
                        <h4>Resumen del pago</h4>
                        <div class="table-responsive mt-3">
                            <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Salario </td>
                                        <td>{{$moneda}} {{$nomina::parsear($totalidad['pago']['salario'])}}</td>
                                    </tr>
                                    <tr>
                                        <td>Subsidio de transporte </td>
                                        <td>{{$moneda}} {{$nomina::parsear($totalidad['pago']['subsidioDeTransporte'])}}</td>
                                    </tr>
                                    <tr>
                                        <td>Horas extras, ordinarias y recargos</td>
                                        <td>{{$moneda}} {{$nomina::parsear($totalidad['pago']['extrasOrdinariasRecargos'])}}</td>
                                    </tr>
                                    <tr>
                                        <td>Vacaciones, Incapacidades y Licencias</td>
                                        @if(isset($totalidad['pago']['licencias']))
                                        <td>{{$moneda}} {{$nomina::parsear($totalidad['pago']['vacaciones'] + $totalidad['pago']['licencias'])}}</td>
                                        @else
                                        <td>{{$moneda}} {{$nomina::parsear($totalidad['pago']['vacaciones'])}}</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <td>Ingresos adicionales</td>
                                        <td>{{$moneda}} {{$nomina::parsear($totalidad['pago']['ingresosAdicionales'])}}</td>
                                    </tr>
                                    <tr>
                                        <td>Retenciones y deducciones </td>
                                        <td>{{$moneda}} - {{$nomina::parsear($totalidad['pago']['retencionesDeducciones'])}}</td>
                                    </tr>
                                    <tr style="background: #E0E0E0; font-weight: bold;">
                                        <td>Total neto a pagar al empleado </td>
                                        <td>{{$moneda}} {{$nomina::parsear($totalidad['pago']['total'])}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 mt-5">
                        <h4>D??as trabajados</h4>
                        <div class="table-responsive mt-3">
                            <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Concepto</th>
                                        <th>D??as</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>D??as del per??odo</td>
                                        <td> &nbsp; {{ $totalidad['diasTrabajados']['diasPeriodo'] }}</td>
                                    </tr>
                                    @foreach($totalidad['diasTrabajados']['ausencia'] as $key => $value)
                                    <tr>
                                        <td>{{ strtolower($key) }}</td>
                                        <td> - {{ $value }}</td>
                                    </tr>
                                    @endforeach
                                    <tr style="background: #E0E0E0; font-weight: bold;">
                                        <td>Total d??as trabajados</td>
                                        <td> &nbsp;&nbsp; {{ $totalidad['diasTrabajados']['total'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 mt-5">
                        <h4>Salario base y subsidio</h4>
                        <div class="table-responsive mt-3">
                            <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Valor/Mensual</th>
                                        <th>D??as trabajados</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Salario</td>
                                        <td>{{$nomina::parsear($totalidad['salarioSubsidio']['salarioCompleto'])}}</td>
                                        <td>{{ $nomina::parsear($totalidad['diasTrabajados']['total']) }}</td>
                                        <td>{{ $nomina::parsear($totalidad['salarioSubsidio']['valorDia'] * $totalidad['diasTrabajados']['total']) }}</td>
                                    </tr>
                                    <tr style="background: #E0E0E0; font-weight: bold;">
                                        <td>Subsidio de transporte</td>
                                        <td>$ 106.454</td>
                                        <td>{{ $nomina::parsear($totalidad['diasTrabajados']['total']) }}</td>
                                        <td>$ {{ $nomina::parsear($totalidad['salarioSubsidio']['subsidioTransporte']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 mb-0">- F??rmula = Valor mensual * D??as trabajados / 30 D??as<br>- Tienes configurado el pago de subsidio de transporte quincenal</p>
                    </div>

                    <div class="col-12 mt-5">
                        <h4>IBC Seguridad Social</h4>
                        <div class="table-responsive mt-3">
                            <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Valor/Mensual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Salario</td>
                                        <td>$ {{ $nomina::parsear($totalidad['ibcSeguridadSocial']['salario']) }}</td< /tr>
                                    <tr>
                                        <td>Horas extras, ordinarias, recargos y adicionales</td>
                                        <td>$ {{ $nomina::parsear($totalidad['ibcSeguridadSocial']['ingresosyExtras']) }}</td< /tr>
                                    <tr>
                                        <td>Vacaciones, Incapacidades y Licencias</td>
                                        <td>$ {{ $nomina::parsear($totalidad['ibcSeguridadSocial']['vacaciones']) }}</td< /tr>
                                    <tr style="background: #E0E0E0; font-weight: bold;">
                                        <td>IBC Seguridad Social</td>
                                        <td>$ {{ $nomina::parsear($totalidad['ibcSeguridadSocial']['total']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 mb-0 text-justify">- Para el c??lculo del IBC Seguridad Social no se tiene en cuenta el subsidio de transporte, valor de vacaciones compensadas ni dem??s ingresos no constitutivos de salario<br>- La base de cotizaci??n a seguridad social no puede ser inferior a 1 SMMLV, por esta raz??n, se toma como IBC $ 454.263</p>
                    </div>

                    <div class="col-12 mt-5">
                        <h4>Retenciones</h4>
                        <div class="table-responsive mt-3">
                            <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Concepto</th>
                                        <th>%</th>
                                        <th>IBC Seguridad Social</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Salud</td>
                                        <td>{{$totalidad['retenciones']['porcentajeSalud']}}</td>
                                        <td>$ {{ $nomina::parsear($totalidad['ibcSeguridadSocial']['total']) }}</td>
                                        <td>$ {{$nomina::parsear($totalidad['retenciones']['salud'])}}</td>
                                    </tr>
                                    <tr>
                                        <td>Pensi??n</td>
                                        <td>{{$totalidad['retenciones']['porcentajePension']}}</td>
                                        <td>$ {{ $nomina::parsear($totalidad['ibcSeguridadSocial']['total']) }}</td>
                                        <td>$ {{$nomina::parsear($totalidad['retenciones']['pension'])}}</td>
                                    </tr>
                                    <tr style="background: #E0E0E0; font-weight: bold;">
                                        <td colspan="3">Total Retenciones</td>
                                        <td>$ {{$nomina::parsear($totalidad['retenciones']['total'])}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 mb-0">- F??rmula = % * IBC Seguridad Social</p>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="ex3-tabs-2" role="tabpanel" aria-labelledby="ex3-tab-2">
                <div class="row p-3">
                    <div class="col-12 border-bottom">
                        <h4>Seguridad social y parafiscales</h4>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <h4>C??lculos seguridad social</h4>
                    <div class="table-responsive mt-3">
                        <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                            <thead class="thead-light">
                                <tr>
                                    <th>Concepto</th>
                                    <th>IBC</th>
                                    <th>%Empresa</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Pensi??n</td>
                                    <td>$ {{ $nomina::parsear($totalidad['ibcSeguridadSocial']['total']) }}</td>
                                    <td>12%</td>
                                    <td>$ {{$nomina::parsear($totalidad['seguridadSocial']['pension'])}}</td>
                                </tr>
                                <tr>
                                    <td>Riesgo 1 - M??nimo</td>
                                    <td>$ {{$nomina::parsear($totalidad['ibcSeguridadSocial']['salario']) }}</td>
                                    <td>0.522%</td>
                                    <td>$ {{$nomina::parsear($totalidad['seguridadSocial']['riesgo1'])}}</td>
                                </tr>
                                <tr style="background: #E0E0E0; font-weight: bold;">
                                    <td>Seguridad Social</td>
                                    <td></td>
                                    <td>12.52%</td>
                                    <td>$ {{$nomina::parsear($totalidad['seguridadSocial']['total'])}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-2 mb-0">- F??rmula = % Empresa * IBC<br>- La empresa est?? exenta de pago de Aportes a Salud, ya que tienes configurado que aplica para Ley 1607 de 2012.</p>
                </div>

                <div class="col-12 mt-5">
                    <h4>C??lculos parafiscales</h4>
                    <div class="table-responsive mt-3">
                        <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                            <thead class="thead-light">
                                <tr>
                                    <th>Concepto</th>
                                    <th>IBC</th>
                                    <th>%Empresa</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Caja de Compensaci??n</td>
                                    <td>$ {{ $nomina::parsear($totalidad['ibcSeguridadSocial']['total']) }}</td>
                                    <td>4%</td>
                                    <td>$ {{ $nomina::parsear($totalidad['parafiscales']['cajaCompensacion']) }}</td>
                                </tr>
                                <tr style="background: #E0E0E0; font-weight: bold;">
                                    <td>Parafiscales</td>
                                    <td></td>
                                    <td>4%</td>
                                    <td>$ {{ $nomina::parsear($totalidad['parafiscales']['total']) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-2 mb-0">- F??rmula = % Empresa * IBC<br>- La empresa est?? exenta de pago de Parafiscales, ya que tienes configurado que aplica para Ley 1607 de 2012.</p>
                </div>

                <div class="d-none">
                    <div class="col-12 mt-5">
                        <h4>C??lculo IBC pensi??n</h4>
                        <div class="table-responsive mt-3">
                            <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Salario</td>
                                        <td>$ 450.000</td>
                                    </tr>
                                    <tr style="background: #E0E0E0; font-weight: bold;">
                                        <td>IBC Pensi??n</td>
                                        <td>$ 450.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 mb-0">- La base de cotizaci??n a seguridad social no puede ser inferior a 1 SMMLV, por esta raz??n, se toma como IBC $ 454.263</p>
                    </div>

                    <div class="col-12 mt-5">
                        <h4>C??lculo IBC riesgos</h4>
                        <div class="table-responsive mt-3">
                            <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Salario</td>
                                        <td>$ 450.000</td>
                                    </tr>
                                    <tr style="background: #E0E0E0; font-weight: bold;">
                                        <td>IBC Riesgos</td>
                                        <td>$ 450.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 mb-0">- Para el c??lculo del IBC Seguridad Social no se tiene en cuenta subsidio de transporte, valor de vacaciones compensadas ni dem??s ingresos no constitutivos de salario<br>- La base de cotizaci??n a riesgos no puede ser inferior a 1 SMMLV, por esta raz??n, se toma como IBC $ 454.263</p>
                    </div>

                    <div class="col-12 mt-5 mb-4">
                        <h4>C??lculo IBC parafiscales</h4>
                        <div class="table-responsive mt-3">
                            <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Salario</td>
                                        <td>$ 450.000</td>
                                    </tr>
                                    <tr style="background: #E0E0E0; font-weight: bold;">
                                        <td>IBC Parafiscales</td>
                                        <td>$ 450.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <div class="tab-pane fade" id="ex3-tabs-3" role="tabpanel" aria-labelledby="ex3-tab-3">
                <div class="row p-3">
                    <div class="col-12 border-bottom">
                        <h4>Provisi??n prestaciones sociales</h4>
                    </div>
                </div>

                <div class="col-12 mt-4 mb-3">
                    <h4>Resumen c??lculos provisi??n prestaciones sociales</h4>
                    <div class="table-responsive mt-3">
                        <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                            <thead class="thead-light">
                                <tr>
                                    <th>Concepto</th>
                                    <th>IBC</th>
                                    <th>%Empresa</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Cesantias</td>
                                    <td>$ {{ $nomina::parsear($totalidad['salarioSubsidio']['total']) }}</td>
                                    <td>8.33%</td>
                                    <td>$ {{ $nomina::parsear($totalidad['provisionPrestacion']['cesantias']) }}</td>
                                </tr>
                                <tr>
                                    <td>Intereses a las Cesantias</td>
                                    <td>$ {{ $nomina::parsear($totalidad['provisionPrestacion']['cesantias']) }}</td>
                                    <td>12%</td>
                                    <td>$ {{$nomina::parsear($totalidad['provisionPrestacion']['interesesCesantias'])}}</td>
                                </tr>
                                <tr>
                                    <td>Prima de Servicios</td>
                                    <td>$ {{ $nomina::parsear($totalidad['salarioSubsidio']['total']) }}</td>
                                    <td>8.33%</td>
                                    <td>$ {{$nomina::parsear($totalidad['provisionPrestacion']['primaServicios'])}}</td>
                                </tr>
                                <tr>
                                    <td>Vacaciones, Incapacidades y Licencias</td>
                                    <td>$ {{$nomina::parsear($totalidad['ibcSeguridadSocial']['total'])}}</td>
                                    <td>4.17%</td>
                                    <td>$ {{$nomina::parsear($totalidad['provisionPrestacion']['vacaciones'])}}</td>
                                </tr>
                                <tr style="background: #E0E0E0; font-weight: bold;">
                                    <td>Total provisiones</td>
                                    <td></td>
                                    <td></td>
                                    <td>$ {{$nomina::parsear($totalidad['provisionPrestacion']['total'])}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-none">
                    <div class="col-12 mt-5">
                        <h4>C??lculo base cesant??as e intereses a las cesant??as</h4>
                        <div class="table-responsive mt-3">
                            <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Salario</td>
                                        <td>$ 450.000</td>
                                    </tr>
                                    <tr>
                                        <td>Subsidio de transporte</td>
                                        <td>$ 53.227</td>
                                    </tr>
                                    <tr style="background: #E0E0E0; font-weight: bold;">
                                        <td>Base Cesant??as</td>
                                        <td>$ 503.227</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 mb-0">- Se incluye el Subsidio de transporte en la Base para Cesant??as ya que es inferior a 2 SMMLV y la persona tiene configurado este auxilio</p>
                    </div>

                    <div class="col-12 mt-5">
                        <h4>C??lculo base prima</h4>
                        <div class="table-responsive mt-3">
                            <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Salario</td>
                                        <td>$ 450.000</td>
                                    </tr>
                                    <tr>
                                        <td>Subsidio de transporte</td>
                                        <td>$ 53.227</td>
                                    </tr>
                                    <tr style="background: #E0E0E0; font-weight: bold;">
                                        <td>Base Prima</td>
                                        <td>$ 503.227</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 mb-0">- Las licencias no remuneradas o suspensiones no afectan el pago de la Prima, solo afectan las Vacaciones y Cesant??as<br>- Se incluye el Subsidio de transporte en la Base para Prima ya que es inferior a 2 SMMLV y la persona tiene configurado este auxilio</p>
                    </div>

                    <div class="col-12 mt-5">
                        <h4>C??lculo base vacaciones</h4>
                        <div class="table-responsive mt-3">
                            <table class="table table-light table-striped" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Salario</td>
                                        <td>$ 450.000</td>
                                    </tr>
                                    <tr style="background: #E0E0E0; font-weight: bold;">
                                        <td>Base Vacaciones</td>
                                        <td>$ 450.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 mb-4">- De Horas Extras y Recargos,s??lo se incluyen los Recargos Nocturnos en la Base para Vacaciones</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection