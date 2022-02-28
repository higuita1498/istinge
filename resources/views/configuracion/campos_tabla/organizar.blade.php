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

    <div class="row card-description">
        <div class="col-sm-6">
            <h4>Campos Visibles</h4>
            <form method="POST" action="{{ route('campos.organizar_store') }}" role="form" class="forms-sample" id="organizar_table">
                {{ csrf_field() }}
                <input type="hidden" value="{{ $id }}" name="id">
                <ul class="list-group list-group-sortable-connected list-group-flush" style="min-height: 50px;">
                    @foreach($tabla as $campo)
                        <li class="list-group-item list-group-item-info">{{$campo->nombre}}
                            <input type="hidden" name="table[]" value="{{$campo->id}}">
                        </li>
                    @endforeach
                </ul>

                <ul style="padding: 0; margin: 0;"  class="list-group-flush option-disabled">
                    <li class="list-group-item disabled">Acciones</li>
                </ul>

            </form>
        </div>
        <div class="col-sm-6">
            <h4>Campos No Visibles</h4>
            <ul class="list-group list-group-sortable-connected">
                @foreach($campos as $campo)
                    <li class="list-group-item list-group-item-success">{{$campo->nombre}}
                        <input type="hidden" name="table[]" value="{{$campo->id}}">
                    </li>
                @endforeach
                <ul style="padding: 0; margin: 0;"  class="list-group-flush option-disabled">
                    <li class="list-group-item disabled text-danger">Los campos que mueva a ésta lista no serán visibles</li>
                </ul>
            </ul>
        </div>
        <div class="col-sm-12" style="text-align: right;">
            <hr>
            <a href="{{route('configuracion.index')}}" class="btn btn-outline-secondary">Cancelar</a>
            <button class="btn btn-success" type="button" onclick="confirmar('organizar_table', '¿Está seguro que desea guardar esta configuración?');">Guardar</button>
        </div>
    </div>

@endsection     
