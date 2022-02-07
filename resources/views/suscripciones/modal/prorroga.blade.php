@extends('layouts.app')


@section('content')
<form method="POST" action="{{ route('suscripciones.agregarProrroga',$suscripcion->id)}}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-banco" >
    {{ csrf_field() }}
    <div class="row">
        <div class="col-md-12 form-group">
            <label class="control-label">Dias Prorroga <span class="text-danger">*</span></label>
            <input type="hidden" value="{{$suscripcion->id}}"name="id_suscripcion">
            <input type="text" class="form-control"  id="prorroga" name="prorroga"  required="" value="{{old('prorroga')}}" maxlength="200">
            <span class="help-block error">
        <strong>{{ $errors->first('prorroga') }}</strong>
      </span>
        </div>
    </div>
    <button type="submit" class="btn btn-success">Guardar</button>
</form>
@endsection