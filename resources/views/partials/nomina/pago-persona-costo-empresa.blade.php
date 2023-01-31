<div class="row">
    <input id="actualizar-costo-url" type="hidden"
           value="{{route('nomina.costoPeriodo', ['tipo' => $tipo, 'year' => $year, 'periodo' => $periodo])}}">
    <div class="col-2"></div>
    <div class="col-4">
        <div class="row">
            <div class="col-2">
                <i class="fas fa-users" style="font-size: 23px"></i>
            </div>
            <div class="col-10" style="border-bottom: solid 0.3px gray; border-right:solid 0.3px gray;">
                <div class="row">
                    <div class="col-12">
                        <h5> Pago a {{ $person }}</h5>
                    </div>
                    <div class="col-12">
                            <span style="text-align: right">$<span
                                        id="pago-empleados">{{ $costoPeriodo->pagoEmpleados }}</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="row">
            <div class="col-2">
                <i class="fas fa-building" style="font-size: 23px"></i>
            </div>
            <div class="col-10" style="border-bottom: solid 0.3px gray; border-right:solid 0.3px gray;">
                <div class="row">
                    <div class="col-12">
                        <h5> Costo empresa </h5>
                    </div>
                    <div class="col-12">
                            <span style="text-align: right">$<span
                                        id="costo-empresa">{{ $costoPeriodo->costoEmpresa }}</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-2"></div>
</div>
