<table class="table table-striped table-hover " id="table-ingresos-categoria">
    <thead class="thead-dark">
    <tr>
        <th>Categoría</th>
        <th>Total</th>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td><a href="#" target="_blanck">Total Ingresos</a> </td>
            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($ingresos)}}</td>
        </tr>
    </tbody>

</table>
<div class="card text-right">
    <div class="card-body">
        <h5 class="card-text">
            <strong>Ingresos:</strong> <br>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($ingresos)}}
        </h5>

    </div>
</div>
