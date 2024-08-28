@extends('layouts.app')
@section('content')
    @if(Session::has('success'))
        <div class="alert alert-success" >
            {{Session::get('success')}}
        </div>

        <script type="text/javascript">
            setTimeout(function(){
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
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
            }, 5000);
        </script>
    @endif

    @if(Session::has('success-newcontact'))
        <div class="alert alert-success" style="text-align: center;">
            {{Session::get('success-newcontact')}}
        </div>

        <script type="text/javascript">
            setTimeout(function(){
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

    <style>
        #titulo{
            display:none;
        }
    </style>

    <div class="paper">
        <!-- Membrete -->
        <div class="row">
            <div class="col-md-4 text-center align-self-center">
                <img class="img-responsive" src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo)}}" alt="" width="50%">
            </div>
            <div class="col-md-4 text-center align-self-center">
                <h4>{{Auth::user()->empresa()->nombre}}</h4>
                <p>{{Auth::user()->empresa()->tip_iden('mini')}} {{Auth::user()->empresa()->nit}} @if(Auth::user()->empresa()->dv != null || Auth::user()->empresa()->dv == 0) - {{Auth::user()->empresa()->dv}} @endif<br> {{Auth::user()->empresa()->email}}</p>
            </div>
            <div class="col-md-4 text-center align-self-center" >
                {{-- <h4><b class="text-primary">No. </b> {{$nro->prefijo}}{{$nro->inicio}}</h4> --}}
            </div>
        </div>
        <hr>
        <div class="row text-right">
            <div class="col-md-5">
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Tecnico <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <select class="form-control selectpicker" name="id_tecnico" id="id_tecnico" required="" title="Seleccione" data-live-search="true" data-size="5" >
                                <option value="{{$material_asignado->tecnico->id}}" >{{$material_asignado->tecnico->nombres}}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Fecha <span class="text-danger">*</span> <a><i data-tippy-content="Fecha en la que se realiza la factura de venta" class="icono far fa-question-circle"></i></a></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control"  id="fecha" value="{{$material_asignado->fecha}}" name="fecha" disabled=""  >
                    </div>
                </div>
            </div>
        </div>
        <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
        </div>
        <hr>
        <div class="fact-table">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-striped table-sm" id="table-form" width="100%">
                        <thead class="thead-dark">
                        <tr>
                            <th hidden=""></th>
                            <th width="24%">Material</th>
                            <th width="10%">Referencia - Material</th>
                            <th width="13%">Descripción</th>
                            <th width="7%">Cantidad</th>
                        </tr>
                        </thead>
                        <tbody  id="dynamic-table">
                        @foreach($material_asignado->items as $key => $item)
                            @php
                                $material = \App\Model\Inventario\ProductosBodega::where("producto", $item->id_material)->first();
                            @endphp
                            <tr id="{{ $key + 1 }}">
                                <td hidden="">
                                    <input type="text"  id="itemId{{$key + 1}}" name="itemId[]" value="{{ $item->id }}" hidden="">
                                </td>
                                <td  class="no-padding" style="padding-top: 2% !important;">
                                    <select class="form-control selectpicker items_inv"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item{{$key + 1}}" onchange="setReference('{{$key + 1}}', this.value);" required="">
                                        <option value="{{$material->id}}" selected>{{$material->producto->}} - ({{$material->ref}})</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="resp-refer">
                                        <input type="text" class="form-control form-control-sm"  value="{{ $material->ref }}">
                                    </div>
                                <td  style="padding-top: 1% !important;">
                                    <div class="resp-descripcion">
                                        <textarea  class="form-control" ></textarea>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control"  value="{{ $item->cantidad }}">
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="alert alert-danger" style="display: none;" id="error-items"></div>
                </div>
            </div>
            <div class="row" style="margin-top: 5%; padding: 3%; min-height: 180px;">
                <div class="col-md-12 form-group">
                    <label class="form-label">Notas <a><i data-tippy-content="" class="icono far fa-question-circle"></i></a>
                    </label>
                    <textarea  class="form-control form-control-sm min_max_100" name="notas"></textarea>
                </div>
            </div>
            <div class="col-md-12"><small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small></div>
            <hr>

            <div class="row ">
                <div class="col-sm-12 text-right" style="padding-top: 1%;">
                    <a href="{{route('asignacionmaterial.index')}}" class="btn btn-outline-secondary">Regresar</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
