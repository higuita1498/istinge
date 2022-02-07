<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-top justify-content-center">
        <a class="navbar-brand brand-logo" href="{{route('home')}}">
            @if(Auth::user()->rol != 1)
            <img class="img-contenida" src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo)}}" alt="logo" onerror="this.src='{{asset('../images/logo1.png')}}'" style="height: 50px;"/>
            @else
            <img class="img-contenida" src="{{asset('images/Empresas/Empresa1/logo.png')}}" alt="logo" style="height: 50px;"/>
            @endif
        </a>
        <a class="navbar-brand brand-logo-mini" href="{{route('home')}}">
            <img class="img-contenida" src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/favicon.png')}}" alt="logo" style="width: 75px !important;"/>
        </a>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-center">
        <ul class="navbar-nav navbar-nav-right d-block">
            @if(Auth::user()->rol > 1 && auth()->user()->rol == 8)
            <li class="nav-item dropdown d-none d-inline-block">
                <span id="div_ganancia" class="d-none d-lg-inline-flex font-weight-bold text-white small saldo" idUser="{{auth()->user()->id}}"" style="background: @if(Auth::user()->ganancia == 0) #fc2919 @else #55de4c @endif;padding: 10px 20px;border-radius: 15px;">GANANCIA: {{Auth::user()->empresa()->moneda}}{{ App\Funcion::Parsear(Auth::user()->ganancia) }}</span>
                <span id="div_saldo" class="d-none d-lg-inline-flex font-weight-bold text-white small" style="background: @if(Auth::user()->saldo == 0) #fc2919 @else #55de4c @endif;padding: 10px 20px;border-radius: 15px;">SALDO: {{Auth::user()->empresa()->moneda}}{{ App\Funcion::Parsear(Auth::user()->saldo) }}</span>
            </li>
            @endif
            <li class="nav-item dropdown d-none d-xl-inline-block">
                <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-toggle="dropdown" aria-expanded="false">
                    <span class="profile-text" style="text-transform:capitalize;">{{Auth::user()->nombres}}</span>
                    @if(Auth::user()->image)
                        <img src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/usuarios/'.Auth::user()->image)}}" onerror="this.src='{{asset('images/no-user-image.png')}}'" alt="profile image" class="img-xs rounded-circle">
                    @else
                        <img src="{{asset('images/no-user-image.png')}}" class="img-xs rounded-circle" alt="profile image">
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>
                    <a class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Cerrar sesión
                    </a>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>

    <div class="modal fade" id="modal"  tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="modal-title">INTERCAMBIO</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body" id="modal-body">
				    
				</div>
			</div>
		</div>
	</div>
	
@section('scripts')
<script>
	$(document).ready(function () {
        $('.saldo').click(function(){
            cargando('true');
            $('#form-ganancia').trigger("reset");
            var url = '/software/empresa/configuracion/gananciaUsuario';
            var _token =   $('meta[name="csrf-token"]').attr('content');
            $("#modal-title").html($(this).attr('title'));
            $.post(url,{ id : $(this).attr('idUser'), _token : _token },function(resul){
                $("#modal-body").html(resul);
                $('.loader').removeAttr('style').attr('style','display:none');
            });
            $('#modal').modal("show");
        });
    });
</script>
@endsection