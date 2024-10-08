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
                    <th width='3%'>PON Type</th>
                    <th width='3%'>Board</th>
                    <th width='3%'>Port</th>
                    <th width='1%'>Pon Description</th>
                    <th width='3%'>SN</th>
                    <th width='3%'>Type</th>
                    <th width='3%'>Estatus</th>
                    <th width='3%'>Action</th>
                </tr>
                </thead>
                <tbody>
                @for ($i=0; $i < count($onus); $i++)
                    <tr>
                        <td>{{ $onus[$i]['pon_type'] }}</td>
                        <td>{{ $onus[$i]['board'] }}</td>
                        <td>{{ $onus[$i]['port'] }}</td>
                        <td>{{ $onus[$i]['pon_description'] }}</td>
                        <td>{{ isset($onus[$i]['sn']) ? $onus[$i]['sn'] : '' }}</td>
                        <td>{{ $onus[$i]['onu_type_name'] }}</td>
                        <td>{{ $onus[$i]['is_disabled'] == 1 ? 'Innactivo' : 'Activo' }}</td>
                        <td>
                            @if($onus[$i]['is_disabled'] == 0)
                            <a href="" onclick="authorizeOnu()">Authorize</a>
                            @endif
                        </td>
                    </tr>
                @endfor
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para mostrar el mapa -->
    {{-- <div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true">
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
    </div> --}}

@endsection
@section('scripts')
    <script>
        function authorizeOnu(){
            alert("okok");
        }
    </script>
@endsection
