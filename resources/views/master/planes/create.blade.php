@extends('layouts.app')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{route('p_personalizados.store')}}" role="form" class="forms-sample" novalidate id="form-general" >
                {{ csrf_field() }}
                <div class="card bg-white shadow-lg">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{old('nombre')}}" maxlength="200">
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('nombre') }}</strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label">Nro. Facturas <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="facturas"  required="" value="{{old('facturas')}}"  min="1">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Ingresos <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="ingresos"  required="" value="{{old('ingresos')}}"  min="1">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Precio <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="precio"  required="" value="{{old('precio')}}"  min="1">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Meses <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="meses"  required="" value="{{old('meses')}}"  min="1">
                                </div>
                            </div>
                        </div>
                        <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
                        <br>
                        <br>
                        <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="card-link btn btn-success float-right">
                            Guardar
                        </button>
                        <a href="{{route('p_personalizados.index')}}" class="card-link btn btn-secondary float-right mr-3">
                            Cancelar
                        </a>

                    </div>
                </div>

            </form>
        </div>
    </div>


@endsection

