@extends('layouts.app')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success">
            {{ Session::get('success') }}
        </div>

        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

    <form method="POST" action="{{ route('siigo.save_impuestos') }}" style="padding: 2% 3%;    " role="form"
        class="forms-sample" novalidate id="form-termino">
        {{ csrf_field() }}

        <div class="card-body" style="background: #f9f9f9; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px;">
            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th class="text-left">Título</th>
                        <th class="text-left">Siigo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($impuestos as $imp)
                        <tr>
                            <td width="30%">{{ $imp->nombre }} - ({{ $imp->porcentaje }}) <input name="imp[]"
                                    type="hidden" value="{{ $imp->id }}"></td>
                            <td width="40%">
                                <select class="form-control selectpicker" data-live-search="true" name="siigo_imp[]">
                                    <option value="0" readonly
                                        {{ $imp->siigo_id == 0 || $imp->siigo_id == null ? 'selected' : '' }}>
                                        Seleccionar Impuesto
                                    </option>
                                    @foreach ($impuestosSiigo as $impSiigo)
                                        <option value="{{ $impSiigo->id }}"
                                            {{ $imp->siigo_id == $impSiigo->id ? 'selected' : '' }}>
                                            {{ $impSiigo->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach

                    @foreach ($retenciones as $ret)
                        <tr>
                            <td width="30%">{{ $ret->nombre }} - ({{ $ret->porcentaje }}) <input name="ret[]"
                                    type="hidden" value="{{ $ret->id }}"></td>
                            <td width="40%">
                                <select class="form-control selectpicker" data-live-search="true" name="siigo_ret[]">
                                    <option value="0" readonly
                                        {{ $imp->siigo_id == 0 || $imp->siigo_id == null ? 'selected' : '' }}>
                                        Seleccionar Retención
                                    </option>
                                    @foreach ($impuestosSiigo as $impSiigo)
                                        <option value="{{ $impSiigo->id }}"
                                            {{ $ret->siigo_id == $impSiigo->id ? 'selected' : '' }}>
                                            {{ $impSiigo->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                    <!-- Repite para otras filas -->
                </tbody>
            </table>
        </div>

        <hr>
        <div class="row">
            <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
                <a href="{{ route('configuracion.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
        </div>
    </form>
@endsection
