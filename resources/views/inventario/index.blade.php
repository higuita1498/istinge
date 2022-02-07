@extends('layouts.app')
@section('boton')
    <div class="btn-group" role="group">
        <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-upload"></i> Importar desde Excel
        </button>
        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
            <a class="dropdown-item" href="{{route('inventario.importar')}}">Importar nuevos</a>
            <a class="dropdown-item" href="{{route('inventario.actualizar')}}">Actualización masiva</a>
        </div>
    </div>

    <a href="{{route('inventario.exportar')}}" class="btn btn-secondary btn-sm" ><i class="fas fa-download"></i> Exportar</a>
    <a href="{{route('inventario.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Producto</a>

@endsection
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
        <div class="col-md-12">
            <table class="table table-striped table-hover" id="table-inventario">
                <thead class="thead-dark">
                <tr>
                    <th>Referenciaa <button type="button" class="btn btn-link no-padding orderby "></button></th>
                    <th>Producto <button type="button" class="btn btn-link no-padding orderby "></button></th>
                    <th>Precio <button type="button" class="btn btn-link no-padding orderby "></button></th>
                    <th>Disp. <button type="button" class="btn btn-link no-padding orderby "></button></th>
                    <th>Estatus <br> en la Web  <button type="button" class="btn btn-link no-padding"></button></th>
                    <th>Acciones</th>

                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#table-inventario').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": "{{ url('empresa/inventario/productos')}}",
                "columns": [
                    { data: 'referencia' , orderable: false},
                    { data: 'producto'},
                    { data: 'precio_producto'},
                    { data: 'unidad'},
                    { data: 'web'},
                    { data: 'acciones', orderable: false, searchable: false},
                ],
                "bDestroy": true
            });

            $('#eliminar').click(function(){
               
            });

        });
    </script>
@endsection

