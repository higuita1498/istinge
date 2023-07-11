

       @extends('layouts.app') 

@section('content')

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body color " >
                <h4 class="card-title">TOTAL PRODUCTOS</h4>
                <table class="table table-striped table-hover " id="example">
                    <thead class="thead-dark">
                        <tr>
                            <th>Empresa </th>
                            <th>Total Productos </th>
                        </tr>
                    </thead>
                    <tbody >
                     @foreach($productos as $producto)
                     <tr>
                        <td>{{$producto->nombre}}</td>
                        <td>{{$producto->productos}}</td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

    </div>

</div>
<div>
 @endsection