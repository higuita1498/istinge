@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
            <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
        </div>
    @else
    <a id="crear-persona-empleado" href="{{route('personas.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nueva Persona</a>
    @endif
@endsection

@section('content')
@if(Session::has('success'))
<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
    {{Session::get('success')}}
</div>
<!-- <script type="text/javascript">
            setTimeout(function () {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script> -->
@endif

@if(Session::has('danger'))
<div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
    {{Session::get('danger')}}
</div>
<script type="text/javascript">
    setTimeout(function() {
        $('.alert').hide();
        $('.active_table').attr('class', ' ');
    }, 5000);
</script>
@endif

@if(Session::has('error'))
<div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
    {{Session::get('error')}}
</div>
<script type="text/javascript">
    setTimeout(function() {
        $('.alert').hide();
        $('.active_table').attr('class', ' ');
    }, 5000);
</script>
@endif

@if(Session::has('preferencia'))
<div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
    DISCULPE, DEBE SELECCIONAR UNA PREFERENCIA DE PAGO. PARA ELLO, INGRESE AL SIGUIENTE LINK <a href="{{route('nomina.preferecia-pago')}}" class="alert-link">PREFERENCIA DE PAGO</a>
</div>
@endif

<span class="text-center ml-5">
    <strong>Total de personas: {{ $personas->count() }}</strong>
</span>

{{-- @include('nomina.tips.serie-base', ['pasos' => \collect([5])->diff($guiasVistas->keyBy('nro_tip')->keys())->all()]) --}}

<div class="row card-description">
    <div class="col-md-12 table-responsive">
        <form id="form-table-facturas">
            <input type="hidden" name="orderby" id="order_by" value="1">
            <input type="hidden" name="order" id="order" value="0">
            <input type="hidden" id="form" value="form-table-facturas">
            <div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif>
                <table class="table filtro thresp">
                    <tr class="form-group">
                        <th>
                            <input type="text" class="form-control form-control-sm" name="name_1" placeholder="Nombre" value="{{$request->name_1}}">
                        </th>
                        <th>
                            <input type="text" class="form-control form-control-sm" name="name_2" placeholder="Apellido" value="{{$request->name_2}}">
                        </th>
                        <th>
                            <input type="text" class="form-control form-control-sm" name="name_3" placeholder="N° de Documento" value="{{$request->name_3}}">
                        </th>
                    </tr>
                    <tr class="form-group">
                        <th>
                            <select name="name_4" class="form-control form-control-sm selectpicker" title="Sedes" data-width="100%">
                                @foreach($sedes as $sede)
                                <option {{$request->name_4==$sede->id?'selected':''}} value="{{$sede->id}}">{{$sede->nombre}}</option>
                                @endforeach
                            </select>
                        </th>
                        <th>
                            <select name="name_5" class="form-control form-control-sm selectpicker" title="Cargos" data-width="100%">
                                @foreach($cargos as $cargo)
                                <option {{$request->name_5==$cargo->id?'selected':''}} value="{{$cargo->id}}">{{$cargo->nombre}}</option>
                                @endforeach
                            </select>
                        </th>
                        <th>
                            <select name="name_6" class="form-control form-control-sm selectpicker" title="Términos Contrato" data-width="100%">
                                @foreach($termino_contratos as $termino_contrato)
                                <option {{$request->name_6==$termino_contrato->id?'selected':''}} value="{{$termino_contrato->id}}">{{$termino_contrato->nombre}}</option>
                                @endforeach
                            </select>
                        </th>
                        <th class="d-none">
                            <select name="name_7" class="form-control form-control-sm selectpicker" title="Salario Base" data-width="100%">
                                @foreach($salario_bases as $salario_base)
                                <option {{$request->name_7==$salario_base->id?'selected':''}} value="{{$salario_base->id}}">{{$salario_base->nombre}}</option>
                                @endforeach
                            </select>
                        </th>
                    </tr>
                </table>
                <center>
                    <button class="btn btn-outline-success btn-sm">Filtrar</button>
                    @if(!$busqueda)
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="hidediv('filtro_tabla'); showdiv('boto_filtrar'); vaciar_fitro();">Cerrar
                    </button>
                    @else
                    <a href="{{route('personas.index')}}" class="btn btn-outline-danger btn-sm">Cerrar</a>
                    @endif
                </center>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <button type="button" @if($busqueda) style="display: none;" @endif class="btn btn-sm btn-outline-success float-right" id="boto_filtrar" onclick="showdiv('filtro_tabla'); hidediv('boto_filtrar');"><i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>

        <table class="table table-striped table-hover w-100" id="table-facturas">
            <thead class="thead-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Identificación</th>
                    <th>Cargo</th>
                    <th>Sede</th>
                    <th>Contrato</th>
                    <th>Salario Base</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($personas as $persona)
                <tr @if($persona->id==Session::get('persona_id')) class="active_table" @endif>
                    <td>{{ $persona->nombre() }}</td>
                    <td>({{ $persona->tipo_documento() }}) - {{ $persona->nro_documento }}</td>
                    <td>{{ $persona->cargo()->nombre }}</td>
                    <td>{{ $persona->sede()->nombre }}</td>
                    <td>{{ $persona->terminoContrato->nombre }}</td>
                    <td>{{ $persona->salario_base()->nombre }}
                        - {{Auth::user()->empresaObj->moneda}} {{App\Funcion::Parsear($persona->valor)}}</td>
                    <td>
                        @if ($persona->is_liquidado)
                        <span class="text-danger">Liquidado</span>
                        @else
                        <span class="text-{{$persona->status(true)}}">{{$persona->status()}}</span>
                        @endif
                    </td>
                    <td>
                    
                        <a href="{{route('personas.show', $persona->id)}}" class="btn btn-outline-info btn-icons" title="Ver Detalles"><i class="far fa-eye"></i>
                        </a>
                        @if($modoLectura->success)
                        @else
                       
                        <a href="{{route('personas.edit', $persona->id)}}" class="btn btn-outline-primary btn-icons" title="Editar Persona"><i class="fas fa-edit"></i></a>
                      
                        @if(!$persona->uso())
                        <form action="{{ route('personas.destroy',$persona->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-persona-{{$persona->id}}">
                            @csrf
                            <input name="_method" type="hidden" value="DELETE">
                        </form>
                        <button class="btn btn-outline-danger btn-icons" title="Eliminar" onclick="confirmar('eliminar-persona-{{$persona->id}}', '¿Está seguro que deseas eliminar a la persona?', 'Se borrara de forma permanente');">
                            <i class="fas fa-times"></i></button>
                        @endif
                        
                        @endif
                        @if(!$persona->is_liquidado)
                        <form action="{{ route('personas.act_des',$persona->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$persona->id}}">
                            @csrf
                        </form>
                        @if($persona->status==1)
                        <button type="button" class="btn btn-outline-danger negative_paging btn-icons" type="submit" title="Deshabilitar" onclick="confirmar('act_desc-{{$persona->id}}', '¿Estas seguro que deseas deshabilitar esta persona?', 'No aparecera para seleccionar en la creación de nomina');">
                            <i class="fas fa-power-off"></i></button>
                        @else
                        <button type="button" class="btn btn-outline-success negative_paging btn-icons" type="submit" title="Habilitar" onclick="confirmar('act_desc-{{$persona->id}}', '¿Estas seguro que deseas habilitar esta persona?', 'Aparecera para seleccionar en la creación de nomina');">
                            <i class="fas fa-power-off"></i></button>
                        @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-right">
            {!!$personas->render()!!}
            @if($personas->lastPage() != 1)
            @include('layouts.includes.goTo')
            @endif
        </div>
    </div>
</div>

@section('scripts')

<script>
    $(document).ready(function() {

        // firstTip = $('.tour-tips').first().attr('nro_tip');

        // if (firstTip) {
        //     nuevoTip(firstTip);
        // }

    });
</script>

@endsection

@endsection