@extends('layouts.app')
@section('content')
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <form id="form-reporte">


        <div class="row card-description">
            <div class="form-group col-md-2">
                <label></label>
                <select class="form-control selectpicker" name="fechas" id="fechas">
                    <optgroup label="Presente">
                        <option value="0">Hoy</option>
                        <option value="1">Este Mes</option>
                        <option value="2">Este Año</option>
                    </optgroup>
                    <optgroup label="Anterior">
                        <option value="3">Ayer</option>
                        <option value="4">Semana Pasada</option>
                        <option value="5">Mes Anterior</option>
                        <option value="6">Año Anterior</option>
                    </optgroup>
                    <optgroup label="Manual">
                        <option value="7">Manual</option>
                    </optgroup>
                </select>
            </div>
            <div class="form-group col-md-4">
                <div class="row">
                    <div class="col-md-6">
                        <label>Desde <span class="text-danger">*</span></label>
                        <input type="text" class="form-control"  id="desde" value="{{$request->fecha}}" name="fecha" required="" >
                    </div>
                    <div class="col-md-6">
                        <label >Hasta <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="hasta" value="{{$request->hasta}}" name="hasta" required="">
                    </div>

                </div>
            </div>
            <div class="form-group col-md-4" style="    padding-top: 2%;">
                <button type="button" id="generar" class="btn btn-outline-secondary">Generar Reporte</button>
                <button type="button" id="exportar" class="btn btn-outline-secondary">Exportar a Excel</button>
            </div>
        </div>
        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table table-striped table-hover " id="example">
                    <thead class="thead-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Identificacion</th>
                        <th>Telefono</th>
                        <th>Tipo de Contacto</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($contactos as $contacto)
                        <tr>
                            <td><a href="{{route('contactos.show', $contacto['id'])}}" target="_blanck">{{$contacto['nombre']}}</a> </td>
                            <td><spam title="{{$contacto->tip_iden()}}">({{$contacto->tip_iden('mini')}})</spam> {{$contacto->nit}}</td>
                            <td>{{$contacto->telefono1}}</td>
                            <td>{{$contacto->tipo_contacto()}}</td>
                            <td></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="text-right">
                   {{-- {{$contactos->links()}}--}}

                </div>
            </div>
        </div>
    </form>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.contactos')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.contactos')}}">
@endsection
