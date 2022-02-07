


<div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reporte diario de ventas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card bg-transparent">
                    <div-card class="card-body text-center">
                        <h6 >Seleccione fecha a consultar</h6>
                        <form action="{{route('exportar.reporteDiario')}}" method="post">
                            @csrf
                            <input type="text" class="form-control datepicker"  id="date" value="{{date('d-m-Y')}}" name="date" required="" >
                            <hr>
                            <button type="submit" class="btn btn-sm btn-primary">Generar reporte</button>
                        </form>

                    </div-card>
                </div>
            </div>

        </div>
    </div>
</div>
