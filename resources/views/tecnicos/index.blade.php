@extends('layouts.app')

@section('content')
    @if(Session::has('success'))
        <div class="alert alert-success">
            {{Session::get('success')}}
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

    @if(Session::has('error'))
        <div class="alert alert-danger" >
            {{Session::get('error')}}
        </div>

        <script type="text/javascript">
            setTimeout(function(){
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 8000);
        </script>
    @endif

    @if(Session::has('danger'))
        <div class="alert alert-danger">
            {{Session::get('danger')}}
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

    @if(Session::has('message_denied'))
        <div class="alert alert-danger" role="alert">
            {{Session::get('message_denied')}}
            @if(Session::get('errorReason'))<br> <strong>Razon(es): <br></strong>
            @if(count(Session::get('errorReason')) > 1)
                @php $cont = 0 @endphp
                @foreach(Session::get('errorReason') as $error)
                    @php $cont = $cont + 1; @endphp
                    {{$cont}} - {{$error}} <br>
                @endforeach
            @endif
            @endif
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(Session::has('message_success'))
        <div class="alert alert-success" role="alert">
            {{Session::get('message_success')}}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row card-description">
        <div class="col-md-12">
            <table class="table table-striped table-hover" id="table-general">
                <thead class="thead-dark">
                <tr>
                    <th></th>
                    <th>Nombre de Tecnico</th>
                    <th>Email de Tecnico</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($tecnicos as $tecnico)
                    <tr>
                        <td></td>
                        <td>{{$tecnico->nombres}}</td>
                        <td>{{$tecnico->email}}</td>
                        <td>{{date_format($tecnico->created_at,'d-m-Y')}}</td>
                        <td>

                            <button onclick="mostrarMapa({{ $tecnico->id }}, '{{ $tecnico->location }}')" class="btn btn-outline-primary btn-icons btn-map" title="Ver">
                                <i class="fas fa-map"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para mostrar el mapa -->
    <div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mapModalLabel">Ubicación del Técnico</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="map" style="height: 500px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        let map;
        let marker;

        function initMap(latitude, longitude) {
            const mapOptions = {
                center: { lat: latitude, lng: longitude },
                zoom: 15
            };
            map = new google.maps.Map(document.getElementById("map"), mapOptions);

            marker = new google.maps.Marker({
                position: { lat: latitude, lng: longitude },
                map: map
            });
        }

        function updateUserPosition(tecnicoId){
            $.ajax({
                url: '{{ route('tecnico.getLocation', ':tecnicoId') }}'.replace(':tecnicoId', tecnicoId),
                method: 'GET',
                success: function(response) {
                    if (response.latitude && response.longitude) {
                        const userPosition = {
                            lat: parseFloat(response.latitude),
                            lng: parseFloat(response.longitude)
                        };

                        // Actualizar la posición del marcador en el mapa
                        marker.setPosition(userPosition);

                        // Centrar el mapa en la nueva posición
                        map.setCenter(userPosition);
                    } else {
                        console.log('No se pudo obtener la posición del usuario.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al obtener la posición:', error);
                }
            });
        }

        function mostrarMapa(tecnicoId, location) {
            if(location){

                // Convertir la cadena JSON a un objeto
                const locationData = JSON.parse(location);
                const latitude = parseFloat(locationData.latitude);
                const longitude = parseFloat(locationData.longitude);

                // Abrir el modal
                $('#mapModal').modal('show');

                // Mostrar la ubicación inicial en el mapa
                initMap(latitude, longitude);

                // Actualizar la posición cada minuto
                setInterval(function() {
                    updateUserPosition(tecnicoId);
                }, 60000);  // 60000 ms = 1 minuto
            }else{
                alert("La posición es nula")
            }
        }

    </script>
@endsection
