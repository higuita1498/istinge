@extends('layouts.app')
{{--
@section('boton')
    <a href="" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo</a>
@endsection
--}}

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
                    <th>Plan</th>
                    <th>F. Inicio</th>
                    <th>F. Vencimiento</th>
                    <th>Prorroga</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>


                @foreach($suscripciones as $suscripcion)
                    <tr>
                        <td>{{$suscripcion->empresa()->nombre}}</td>
                        <td>{{$suscripcion->empresa()->plan}}</td>
                        <td>{{date('d-m-Y', strtotime($suscripcion->fec_inicio))}}</td>
                        <td>{{date('d-m-Y', strtotime($suscripcion->fec_vencimiento))}}</td>
                        <td>{{$suscripcion->prorroga}}</td>
                        <td>
                            <a href="{{route('suscripciones.prorroga',$suscripcion->id)}}" class="btn btn-outline-primary btn-icons" ><i class="fas fa-edit"></i></a>
                            <a href="{{route('suscripciones.ilimitado',$suscripcion->id)}}" class="btn btn-outline-info " >{{$suscripcion->ilimitado()}}</a>

                        </td>
                    </tr>
                @endforeach


                {{--@foreach($bancos as $banco)
                    <tr @if($banco->id==Session::get('banco_id')) class="active_table" @endif>
                        <td><a href="{{route('bancos.show',$banco->nro)}}">{{$banco->nombre}}</a></td>
                        <td>{{$banco->nro_cta}}</td>
                        <td>{{$banco->descripcion}} </td>
                        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($banco->saldo())}}</td>
                        <td>
                            <a href="{{route('bancos.show',$banco->nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>

                            <a href="{{route('bancos.edit',$banco->nro)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
                            @if(!$banco->uso())
                                <form action="{{ route('bancos.destroy',$banco->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-banco">
                                    {{ csrf_field() }}
                                    <input name="_method" type="hidden" value="DELETE">
                                </form>
                                <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-banco', '¿Estas seguro que deseas eliminar el banco?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
                            @endif


                        </td>
                    </tr>
                @endforeach--}}
                </tbody>
            </table>
        </div>
    </div>


    {{-- Modal contacto nuevo --}}
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Agregar Dias de Prorroga</h4>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>
@endsection