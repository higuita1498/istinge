<table class="table table-striped table-hover " id="table-egresos-categoria">
    <thead class="thead-dark">
    <tr>
        <th>Categoría</th>
        <th>Total</th>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td><a href="#" target="_blanck">Total Egresos</a> </td>
            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($gastos)}}</td>
        </tr>
    </tbody>
</table>
<div class="card text-right">
    <div class="card-body">
        <h5 class="card-text">
            <strong>Egresos:</strong> <br>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($gastos)}}
        </h5>
    </div>
</div>
