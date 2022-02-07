@extends('layouts.app')
@section('boton')
<a href="https://gestordepartes.net/PlanesPagina" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Pago</a>
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
        <table class="table table-striped table-hover" id="example">
            <thead class="thead-dark">
                <tr>
                    <th>Empresa</th>
                    <th>Fecha Pago</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Plan</th>
                    <th>Tipo Pago</th>
                    <th>Referencia</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suscripcionesPagos as $suscripcionPago)
                <tr class="active_table">
                    <td>{{$suscripcionPago->empresa()->nombre }}</td>
                    <td>{{$suscripcionPago->created_at}}</td>
                    <td>{{$suscripcionPago->expiration}}</td>
                    <td>{{$suscripcionPago->plan()}}</td>
                    <td>{{$suscripcionPago->tipoPago()}}</td>
                    <td>{{$suscripcionPago->referencia}}</td>
                    <td>
                        {{$suscripcionPago->estado()}}
                        @if($suscripcionPago->estado == 1)
                            @if($suscripcionPago->valid)
                                <span class="text-success font-weight-bold">
                                    - Vigente
                                </span>
                            @else
                                <span class="text-danger font-weight-bold">
                                    - Vencido
                                </span>
                            @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                @include('suscripciones.modal.nuevoPago')
            </div>
        </div>
    </div>
</div>
@endsection
