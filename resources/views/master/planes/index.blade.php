@extends('layouts.app')
@section('content')
    <hr>
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
    <div class="row">
        <div class="col-sm-9"></div>
        <div class="col-sm-2">
            <a href="{{route('p_personalizados.create')}}" class="btn btn-block btn-primary mb-2">
                Crear
            </a>
        </div>
        <div class="col-sm-1"></div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-rep-plugin">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered dt-responsive nowrap " id="table-general" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Facturas</th>
                                    <th>Ingresos</th>
                                    <th>Precio</th>
                                    <th>Meses</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($planes as $plan)
                                        <tr>
                                            <td>
                                                {{$plan->nombre}}
                                            </td>
                                            <td>
                                                {{$plan->facturas}}
                                            </td>
                                            <td>
                                                {{$plan->ingresos}}
                                            </td>
                                            <td>
                                                {{$plan->precio}}
                                            </td>
                                            <td>
                                                {{$plan->meses}}
                                            </td>
                                            <td>
                                                <a href="{{route('p_personalizados.edit', $plan->id)}}" class="btn btn-info btn-icons">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                @if($used->contains('id', $plan->id))
                                                    <form action="{{route('p_personalizados.destroy', $plan->id)}}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-plan{{$plan->id}}">
                                                        {{ csrf_field() }}
                                                    </form>
                                                    <button class="btn btn-danger  btn-icons " type="submit" title="Eliminar" onclick="confirmar('eliminar-plan{{$plan->id}}', '¿Estas seguro que deseas eliminar el plan?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

