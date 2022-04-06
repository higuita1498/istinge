@extends('layouts.app')

@section('style')
@endsection

@section('content')


@section('inputs-tips')



@endsection

<div class="row">
    <div class="col-12">
        <div class="card border-0">
            <div class="card-body border-0">
                <p class="card-text" style="font-size:15px">Podrás visualizar el historial de las nóminas que has liquidado en los diferentes periodos.</p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row mt-4">
        <div class="col-md-12">
            <button class="btn btn-rounded float-right mb-2 mr-2" style="background-color: #D08F50; color: white;" data-toggle="modal" data-target="#historial-pedidos">
                <i class="fas fa-file-alt"></i>
                Reporte de pagos</button>
            <div class="table-responsive">
                <table class="table table-light table-striped table-hover w-100" style="border: 1px solid #e9ecef;">
                    <thead class="thead-light">
                        <tr>
                            <th id="elemet-message">Periodo</th>
                            <th>N°</th>
                            <th>Salarios</th>
                            <th>Otros pagos</th>
                            <th>Prestaciones</th>
                            <th id="elemet-message2">Costo total</th>
                            <th>Deducción & Retención</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historial as $historia)
                        <tr>
                            <td>{{date("d",strtotime($historia->fecha_desde))}} - {{date("d",strtotime($historia->fecha_hasta))}} {{date("M",strtotime($historia->fecha_desde)) }} {{date("Y",strtotime($historia->fecha_desde)) }}</td>  
                            {{-- <td>{{date("d",strtotime($historia->fecha_desde)) - date("d",strtotime($historia->fecha_hasta)) date("M",strtotime($historia->fecha_desde))}}</td>   --}}
                            <td>{{$historia->numeroNominas}}</td>  
                            <td>{{Auth::user()->empresaObj->moneda}}{{App\Funcion::Parsear($historia->pagoEmpleado)}}</td>  
                            <td>{{Auth::user()->empresaObj->moneda}}{{App\Funcion::Parsear($historia->otrosPagos)}}</td>  
                            <td>{{Auth::user()->empresaObj->moneda}}{{App\Funcion::Parsear($historia->prestacionValor)}}</td>  
                            <td>{{Auth::user()->empresaObj->moneda}}{{App\Funcion::Parsear($historia->costo_total)}}</td>  
                            <td>{{Auth::user()->empresaObj->moneda}}{{App\Funcion::Parsear($historia->deduccPrestRet)}}</td>  
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="historial-pedidos" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
            <form id="form-reporte">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title font-weight-bold" id="exampleModalLabel">Reporte consolidado por períodos</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <p>Con esta opción puedes descargar resúmenes de nómina de varios <br> periodos juntos o de forma individual.</p>
                            <p><strong>Recuerda: </strong>Los contratista y empleados liquidados aparecen en hojas diferentes dentro del reporte</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="text-small" for="fecha_inicial">Día inicial del reporte</label>
                                <select name="fecha_inicial" id="fecha_inicial" class="form-control selectpicker">
                                    @foreach($rangos as $rango)
                                    <option value="{{$rango}}">{{$rango}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="fecha_final" class="text-small">Día final del reporte</label>
                                <select name="fecha_final" id="fecha_final" class="form-control selectpicker">
                                    @foreach($rangos as $rango)
                                    <option value="{{$rango}}">{{$rango}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded" data-dismiss="modal">Cancelar</button>
                    <button  id="generar" type="button" class="btn text-white rounded" style="background-color:#D08F50;">Generar Reporte</button>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>

<input type="hidden" id="urlgenerar" value="{{route('historial.periodos')}}">

@endsection

@section('scripts')

@endsection