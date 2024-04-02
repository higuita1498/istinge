@extends('layouts.app')
@section('content')
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <input type="hidden" id="primera" value="{{$request->date ? $request->date['primera'] : ''}}">
    <input type="hidden" id="ultima" value="{{$request->date ? $request->date['ultima'] : ''}}">
    <form id="form-reporte">
        <div class="row card-description">
            <div class="form-group col-md-4">
                <div class="row">
                    <label>Seleccione el Año<span class="text-danger">*</span></label>
                    <select class="form-control" id="anio" name="anio" required="">
                        @php
                            $anioActual = date('Y');
                            $cantidadAnios = 20; // Cantidad de años hacia atrás desde el año actual
                        @endphp
                        @for ($i = $anioActual; $i >= $anioActual - $cantidadAnios; $i--)
                            <option value="{{$i}}">{{$i}}</option>
                        @endfor
                    </select>

                </div>
            </div>
            <div class="form-group col-md-4 ml-5">
                <div class="row">
                    <label>Seleccione el Trimestre <span class="text-danger">*</span></label>
                    <select class="form-control" id="trimestre" name="trimestre" required="">
                        <option value="1">Trimestre 1</option>
                        <option value="2">Trimestre 2</option>
                        <option value="3">Trimestre 3</option>
                        <option value="4">Trimestre 4</option>
                    </select>

                </div>
            </div>
            <div class="form-group col-md-4">

            </div>
            <div class="form-group col-md-4 text-center offset-md-4">
                <center><button type="button" id="generar" class="btn btn-outline-primary">Generar Reporte</button>
                <button type="button" id="exportar" class="btn btn-outline-success">Exportar a Excel</button></center>
            </div>
        </div>
        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table" id="table-facturas">
                <thead class="thead-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Trimestre</th>
                    <th>Id municipio</th>
                    <th>Id segemento planes</th>
                    <th>Cantidad de suscriptores</th>
                    <th>Nombre del plan</th>
                    <th>Valor plan iva</th>
                    <th>Valor plan</th>
                    <th>Id modalidad plan</th>
                    <th>Fecha inicio</th>
                    <th>Fecha fin</th>
                    <th>Id tipo plan</th>
                    <th>Tiene telefonia fija</th>
                    <th>Tarifa telefonia fija</th>
                    <th>Cantidad minutos</th>
                    <th>Valor minuto inlcuido telefonia</th>
                    <th>Valor minuto adicional telefonia</th>
                    <th>Tiene internet fijo</th>
                    <th>Nombre Plan Int FI</th>
                    <th>Tarifa Mensual Internet</th>
                    <th>Velocidad Ofrecida Bajada</th>
                    <th>Velocidad Ofrecida Subida</th>
                    <th>Id Tecnologia</th>
                    <th>Canales Premium TV</th>
                    <th>Canales HD TV</th>
                    <th>Video Demanda</th>
                    <th>Costo Deco Adición</th>
                    <th>Otras Caracteristicas</th>
                </tr>
                </thead>
                <tbody>

                @foreach($movimientos as $movimiento)
                    <tr>
                        <td><a href="{{$movimiento->show_url()}}">{{date('d-m-Y', strtotime($movimiento->fecha))}}</a></td>
                        <td>
                            <a href="{{$movimiento->show_url()}}">
                                {{$movimiento->id}}
                            </a>
                        </td>
                        <td>
                            {{$movimiento->banco()->nombre}}
                        </td>
                        <td>
                            {{$movimiento->categoria()}}
                        </td>
                        <td>
                            <spam class="text-{{$movimiento->estatus(true)}}">
                                {{$movimiento->estatus()}}
                            </spam>
                        </td>
                        <td>
                            {{$movimiento->tipo==1?Auth::user()->empresa()->moneda.\App\Funcion::Parsear($movimiento->saldo):''}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="thead-dark">
                    <td colspan="5"></td>
                </tfoot>
                </table>
                <div class="text-right">
                    {{$movimientos->links()}}
                </div>

            </div>
        </div>
    </form>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.instalacion')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.cajas')}}">

   {{-- Agregando el script para poder enviar fecha y trimestre  --}}
    <script>
        document.getElementById('generar').addEventListener('click', function() {
            var anio = document.getElementById('anio').value;
            var trimestre = document.getElementById('trimestre').value;
            var url = document.getElementById('urlgenerar').value;
            var csrfToken = '{{ csrf_token() }}';

            // Enviar datos al controlador utilizando AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-CSRF-Token', csrfToken);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    // Procesar la respuesta si es necesario
                    console.log(xhr.responseText);
                }
            };
            xhr.send(JSON.stringify({anio: anio, trimestre: trimestre}));
        });
    </script>
@endsection
