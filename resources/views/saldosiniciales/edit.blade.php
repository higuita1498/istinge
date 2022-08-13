@extends('layouts.app')

@section('content')

<style>
    .table-forma1{
        border:none;width:98%;height:auto;
        margin:10px;
    }

    .table-forma1 thead{
        background-color:#ccc;
    }

    .forma-check{
        margin-left: 10px;
    }
</style>

@if(Session::has('success'))
<div class="alert alert-success" >
    {{Session::get('success')}}
</div>

<script type="text/javascript">
    setTimeout(function(){
        $('.alert').hide();
        $('.active_table').attr('class', ' ');
    }, 8000);
</script>
@endif

@if(Session::has('error'))
<div class="alert alert-danger" >
    {{Session::get('error')}}
</div>

<script type="text/javascript">
    setTimeout(function(){
        $('.alert').hide();
        $('.active_table').attr('class', ' ');
    }, 8000);
</script>
@endif

<form method="POST" action="{{ route('saldoinicial.update') }}" style="padding: 2% 3%;" role="form" class="forms-sample" autocomplete="off" novalidate id="form-saldoinicial" >
    {{ csrf_field() }}

    {{-- url sobre la cual se haran las peticiones --}}
    <input type="hidden" id="url" value="{{url('/')}}">
    <input type="hidden" name="nro" id="nro" value="{{$nro}}">
    
    <div class="row">
        <div class="col-md-6">
            <div>
                <label class="form-control-label">Tipo:</label>
                <select class="form-control form-control-sm selectpicker p-0" name="tipo_comprobante" id="tipo_comprobante" data-live-search="true" data-size="5">
                    @foreach($tipos as $tipo)
                    <option value="{{$tipo->id}}" {{$tipo->nro == $movimiento->tipo_comprobante ? 'selected' : ''}}>{{$tipo->nro}} - {{$tipo->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-control-label">Fecha de elaboración:</label>
            <input type="text" class="form-control datepicker" id="fecha" name="fecha" value="{{$movimiento->fecha_elaboracion}}">
        </div>
    </div> 

    <table class="table-forma1 table table-striped table-hover w-100 dtr-inline collapsed" style="margin:10px 0px" id="table-saldoinicial">
    <thead class="thead-dark">
        <tr>
        <th width="3%">#</th>
        <th width="15%">Cuenta contable</th>
        <th width="15%">Tercero</th>
        <th width="20%">Detalle</th>
        <th width="15%">Descripción</th>
        <th width="15%">Débito</th>
        <th width="15%">Crédito</th>
        <th width="3%"></th>
        </tr>
    </thead>
    @php $contt = 1; @endphp
    @foreach($movimientos as $mov)
        <tr id="saldoini{{$contt}}" fila="{{$contt}}">
            <td>{{$contt}}</td> 
            <td>
                <select name="puc_cuenta[]" id="puc_cuenta{{$contt}}" class="form-control form-control-sm selectpicker p-0" onchange="validateDetalleCartera(this.value,{{$contt}})" data-live-search="true" data-size="5" required>
                    <option value="0" selected disabled>Seleccione una opción</option>
                    @foreach($puc as $p)
                    <option value="{{$p->id}}" {{$p->id == $mov->cuenta_id ? 'selected' : ''}}>{{$p->codigo}} - {{$p->nombre}}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="contacto[]" id="contacto{{$contt}}" class="form-control form-control-sm selectpicker p-0" data-live-search="true" data-size="5" required>
                    @foreach($contactos as $contacto)
                        <option value="{{$contacto->id}}" {{$contacto->id == $mov->cliente_id ? 'selected' : ''}}>{{$contacto->nombre}}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <div class="d-none justify-content-between" id="divCartera{{$contt}}">
                    <input type="text" class="form-control form-control-sm" 
                    name="detalleComprobante[]"
                    prefijo="" nroComprobante=""  cuota="" fecha="" tipo="" id="divInput{{$contt}}"
                    readonly>
                    <a class="btn btn-primary-sm" onclick="modalComprobante({{$contt}})" style="
                    padding: 0px;
                    margin-top: 3px;" data-toggle="modal" data-target="#editCartera"><i class="far fa-arrow-alt-circle-down"></i></a>
            </div>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="descripcion[]" id="descripcion1" value="{{$mov->descripcion}}">
            </td>
            <td>
                <input type="number" min="0" name="debito[]" id="debito1" onkeyup="totalSaldoInicial()" class="form-control form-control-sm" placeholder="Débito" required value="{{$mov->debito}}">
            </td>
            <td>
                <input type="number" min="0" name="credito[]" id="credito1" onkeyup="totalSaldoInicial()" class="form-control form-control-sm" placeholder="Crédito" required value="{{$mov->credito}}">
            </td>
            <td>
                <div class="d-flex">
                    <a href="#" onclick="crearFilaSaldo()"><i class="fas fa-save"></i></a>
                    <a href="#" onclick="eliminarSaldo('saldoini'{{$contt}})"><i class="fas fa-trash"></i></a>
                </div>
            </td>
        </tr>
        @php $contt++ @endphp
        @endforeach
        <tfoot class="thead-dark">
            <td colspan="4"></td>
            <th><span>Total:</span></th>
            <th id="totalDebito">{{$movimiento->totalDebito()->total}}</th>
            <th id="totalCredito">{{$movimiento->totalCredito()->total}}</th>
            <th></th>
        </tfoot>
    
    {{-- Totales--}}
  </table>
  <div class="w-100" style="text-align:right;">
    <span id="spanError" class="text-danger" style="font-size: 14px;margin-right: 10px;font-weight: 500;">

    </span>
  </div>


  <div class="row ">
    <div class="col-sm-12 text-right" style="padding-top: 1%;">
      <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
      <a href="{{route('facturas.index')}}" class="btn btn-outline-secondary">Cancelar</a>
    </div>

  </div>

</form>

{{-- Modal Detalle de Comprbantes contables --}}    
<div class="modal fade" id="editModalComprobante" tabindex="-1" role="dialog" aria-labelledby="editModalComprobante" aria-hidden="true">
</div>
{{-- End Section Detalle de Comprbantes contables  --}}

  {{-- COLECCIONES EN JSON --}}
  <input type="hidden" id="jsonContactos" value="{{json_encode($contactos)}}">
  <input type="hidden" id="jsonPuc" value="{{json_encode($puc)}}">

@endsection

@section('scripts')

<script src="{{asset('lowerScripts/saldo/saldo.js')}}"></script>

@endsection
