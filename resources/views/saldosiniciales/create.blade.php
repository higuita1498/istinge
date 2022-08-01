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

    .not-active {
        cursor: not-allowed;
    }

    .not-active-a{
        pointer-events: none; 
            cursor: default; 
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

<div class="row">
    <div class="col-md-8"></div>
    <div class="col-md-4 text-center align-self-center">
        <h4 style="position:absolute;bottom: 1.25em;left:12em;"><b class="text-primary">No. </b> {{$proximoNumero}}</h4>
    </div>
</div>

<form method="POST" action="{{ route('saldoinicial.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" autocomplete="off" novalidate id="form-saldoinicial" >
    {{ csrf_field() }}

    {{-- url sobre la cual se haran las peticiones --}}
    <input type="hidden" id="url" value="{{url('/')}}">

    <div class="row">
        <div class="col-md-6">
            <div>
                <label class="form-control-label">Tipo:</label>
                <select class="form-control form-control-sm selectpicker p-0" name="tipo_comprobante" id="tipo_comprobante" data-live-search="true" data-size="5">
                    @foreach($tipos as $tipo)
                    <option value="{{$tipo->id}}">{{$tipo->nro}} - {{$tipo->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-control-label">Fecha de elaboración:</label>
            <input type="text" class="form-control datepicker" id="fecha" name="fecha" value="{{Carbon\Carbon::now()->parse()->format('d-m-Y')}}">
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
    {{-- @foreach($productos as $producto) --}}
        <tr id="saldoini1" fila="1">
            <td>1</td> 
            <td>
                <select name="puc_cuenta[]" id="puc_cuenta1" class="form-control form-control-sm selectpicker p-0" onchange="validateDetalleCartera(this.value,1)" data-live-search="true" data-size="5" required>
                    <option value="0" selected disabled>Seleccione una opción</option>
                    @foreach($puc as $p)
                    <option value="{{$p->id}}">{{$p->codigo}} - {{$p->nombre}}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="contacto[]" id="contacto1" class="form-control form-control-sm selectpicker p-0" data-live-search="true" data-size="5" required>
                    @foreach($contactos as $contacto)
                        <option value="{{$contacto->id}}">{{$contacto->nombre}}</option>
                    @endforeach
                </select>
            </td>
            <td class="not-active">
                {{-- <div class="d-flex justify-content-between" id="divCartera1">
                    <input type="text" class="form-control form-control-sm" readonly>
                    <a class="btn btn-primary-sm not-active-a" onclick="modalShow()" style="
                    padding: 0px;
                    margin-top: 3px;" data-toggle="modal" data-target="#editCartera"><i class="far fa-arrow-alt-circle-down"></i></a>
                </div> --}}
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="descripcion[]" id="descripcion1">
            </td>
            <td>
                <input type="number" min="0" name="debito[]" id="debito1" onkeyup="totalSaldoInicial()" class="form-control form-control-sm" placeholder="Débito" required>
            </td>
            <td>
                <input type="number" min="0" name="credito[]" id="credito1" onkeyup="totalSaldoInicial()" class="form-control form-control-sm" placeholder="Crédito" required>
            </td>
            <td>
                <div clas="d-flex">
                    <a href="#" onclick="crearFilaSaldo()"><i class="fas fa-save"></i></a>
                    {{-- <a href="#" onclick="eliminarSaldo('saldoini1')"><i class="fas fa-trash"></i></a> --}}
                </div>
            </td>
        </tr>
        <tfoot class="thead-dark">
            <td colspan="4"></td>
            <th><span>Total:</span></th>
            <th id="totalDebito"></th>
            <th id="totalCredito"></th>
            <th></th>
        </tfoot>
    {{-- @endforeach --}}
    
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

  {{-- Modal Detalle de cartera --}}    
  <div class="modal fade" id="editCartera" tabindex="-1" role="dialog" aria-labelledby="editCartera" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Detalle de cartera / proveedores</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form>
            <span>Crea, modifica o cruza saldos de las obligaciones con terceros</span>
            <div class="row">
                <div class="form-group col-md-12">
                    <label class="control-label">¿Qué desea hacer? <span class="text-danger">*</span></label>
                    <div class="row">
                        <div class="col-sm-6">
                            <label class="form-radio-label">
                                <input type="radio" class="form-radio-input" name="saldo_radio" onchange="showDetalleCartera()" value="1" checked> Cruzar con saldo existente
                                <i class="input-helper"></i></label>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-radio-label">
                                <input type="radio" class="form-radio-input" name="saldo_radio" onchange="showDetalleCartera()" value="2"> Crear / Modificar Saldo
                                <i class="input-helper"></i></label>
                        </div> 
                    </div>
                    <span class="help-block error">
                        <strong>{{ $errors->first('saldo_radio') }}</strong>
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 detallecartera1">
                    <input type="text" class="form-control" name="saldo_cruzar" id="saldo_cruzar">
                </div>
            </div>

            <div class="row detallecartera2 d-none">
                    <div class="col-md-3">
                        <select class="form-control form-control-sm selectpicker" data-live-search="true" data-size="5" placeholder="Prefijo">
                            <option>FV - Factura de venta</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="N* de comprobante" name="nro_comprobante" id="nro_comprobante">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Cuota" name="cuota" id="cuota">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control datepicker" id="fecha_vencimiento" value="{{Carbon\Carbon::now()->parse()->format('d-m-Y')}}" name="fecha_vencimiento" disabled="">
                    </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <a type="button" onclick="update_forma()" class="btn btn-primary">Actualizar</a>
        </div>
      </div>
    </div>
  </div>
  {{-- End Section Detalle de cartera  --}}

  {{-- COLECCIONES EN JSON --}}
  <input type="hidden" id="jsonContactos" value="{{json_encode($contactos)}}">
  <input type="hidden" id="jsonPuc" value="{{json_encode($puc)}}">

@endsection

@section('scripts')

<script src="{{asset('lowerScripts/saldo/saldo.js')}}"></script>

@endsection
