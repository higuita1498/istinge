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

    <form method="POST" action="{{ route('siigo.save_vendedores') }}" style="padding: 2% 3%;    " role="form"
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
                    @foreach ($vendedores as $vendedor)
                        <tr>
                            <td width="30%">{{ $vendedor->nombre }}<input name="vendedores[]"
                                    type="hidden" value="{{ $vendedor->id }}"></td>
                            <td width="40%">
                                <select class="form-control selectpicker" data-live-search="true" name="siigo_vendedores[]">
                                    <option value="0" readonly
                                        {{ $vendedor->siigo_id == 0 || $vendedor->siigo_id == null ? 'selected' : '' }}>
                                        Seleccionar Vendedor
                                    </option>
                                    @foreach ($vendedoresSiigo as $venSiigo)
                                        <option value="{{ $venSiigo['id'] }}"
                                            {{ $vendedor->siigo_id == $venSiigo['id'] ? 'selected' : '' }}>
                                            {{ $venSiigo['username'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
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
