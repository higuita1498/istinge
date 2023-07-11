 @extends('layouts.app')
    
@section('content')
    <div class="row card-description">
        <div class="col-sm-6">
            <h4>Tabla <a href="{{route('inventario.index')}}" target="_blanck">Inventario</a></h4>
            <form method="POST" action="{{ route('personalizar_inventario.organizar_store') }}" role="form" class="forms-sample" id="organizar_table">
                {{ csrf_field() }} 
                <ul style="padding: 0; margin: 0;" class="list-group-flush option-disabled">
                    <li class="list-group-item disabled">Referencia</li>
                    <li class="list-group-item disabled">Producto</li>
                    <li class="list-group-item disabled">Precio {{Auth::user()->empresa()->moneda}}</li>
                    <li class="list-group-item disabled">Disp.</li>
                </ul>
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
            <h4>Campos Extras</h4>
            <ul class="list-group list-group-sortable-connected">
                @foreach($campos as $campo)
                    <li class="list-group-item list-group-item-success">{{$campo->nombre}}
                        <input type="hidden" name="table[]" value="{{$campo->id}}">
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="row">
            <div class="col-sm-12" style="text-align: right;  padding-top: 10%;">
              <a href="{{route('personalizar_inventario.index')}}" class="btn btn-outline-secondary">Cancelar</a>
              <button class="btn btn-success" type="button" onclick="confirmar('organizar_table', '¿Estas seguro que deseas guardar esta configuración?');">Guardar</button>
            </div>
        </div>
    </div>

@endsection     
