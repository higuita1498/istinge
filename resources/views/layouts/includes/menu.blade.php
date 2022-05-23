@if(Auth::user()->rol==1)
    <li class="nav-item" id="empresas" >
        <a class="nav-link" href="{{url('master/empresas')}}">
            <i class="menu-icon fa fa-building"></i>
            <span class="menu-title">Empresas</span>
        </a>
    </li>
    <li class="nav-item" id="users" >
        <a class="nav-link" href="{{url('master/usuarios')}}">
            <i class="menu-icon fa fa-users"></i>
            <span class="menu-title">Usuarios</span>
        </a>
    </li>
    <li class="nav-item" id="soporte">
        <a  class="nav-link" href="{{route('atencionsoporte.index')}}">
            <i class="menu-icon far fa-life-ring"></i>
            <span class="menu-title">Atención a Soporte</span>
        </a>
    </li>
    <li class="nav-item" id="logout-lateral">
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>
        <a  class="nav-link"  onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="menu-icon fas fa-sign-out-alt"></i>
            <span class="menu-title">Salir</span>
        </a>
    </li>
@elseif(Auth::user()->rol >= 2 )
    @if(isset($_SESSION['permisos']['800']) || isset($_SESSION['permisos']['801']) || isset($_SESSION['permisos']['802']) || isset($_SESSION['permisos']['803']))
        <li class="nav-item" id="ventas-externas">
            <a  class="nav-link" href="{{route('ventas-externas.index')}}">
                <i class="menu-icon fas fa-hand-holding-usd"></i>
                <span class="menu-title">Ventas Externas</span>
            </a>
        </li>
    @endif

    @if(isset($_SESSION['permisos']['1']) || isset($_SESSION['permisos']['2']) || isset($_SESSION['permisos']['3']) || isset($_SESSION['permisos']['4']) || isset($_SESSION['permisos']['5']) || isset($_SESSION['permisos']['6']) || isset($_SESSION['permisos']['7']))
        <li class="nav-item" id="contactos">
            <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                <i class="menu-icon fas fa-users"></i>
                <span class="menu-title">Contactos </span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic">
                <ul class="nav flex-column sub-menu">
                    @if(isset($_SESSION['permisos']['2']) || isset($_SESSION['permisos']['4']) || isset($_SESSION['permisos']['5']) || isset($_SESSION['permisos']['6']) || isset($_SESSION['permisos']['7']))
                        <li class="nav-item" id="clientes">
                            <a class="nav-link" href="{{route('contactos.clientes')}}">Clientes</a>
                        </li>
                    @endif
                    @if(isset($_SESSION['permisos']['3']) || isset($_SESSION['permisos']['4']) || isset($_SESSION['permisos']['5']) || isset($_SESSION['permisos']['6']) || isset($_SESSION['permisos']['7']))
                        <li class="nav-item" id="proveedores">
                            <a class="nav-link" href="{{route('contactos.proveedores')}}">Proveedores</a>
                        </li>
                    @endif
                </ul>
            </div>
        </li>
    @endif

    @if(isset($_SESSION['permisos']['405']) || isset($_SESSION['permisos']['410']) || isset($_SESSION['permisos']['402']) || isset($_SESSION['permisos']['411']))
        <li class="nav-item" id="contratos">
            <a class="nav-link" data-toggle="collapse" href="#ui-contrato" aria-expanded="false" aria-controls="ui-contrato">
                <i class="menu-icon fas fa-file-contract"></i>
                <span class="menu-title">Contratos</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-contrato">
                <ul class="nav flex-column sub-menu">
                    @if(isset($_SESSION['permisos']['405']) || isset($_SESSION['permisos']['411']))
                    <li class="nav-item" id="listado">
                        <a class="nav-link" href="{{route('contratos.index')}}">Listado</a>
                    </li>
                    @endif
                    @if(isset($_SESSION['permisos']['410']) || isset($_SESSION['permisos']['402']))
                        <li class="nav-item" id="asignaciones">
                            <a class="nav-link" href="{{route('asignaciones.index')}}" >Asignaciones</a>
                        </li>
                    @endif
                    <li class="nav-item" id="listado">
                        <a class="nav-link" href="{{route('pings.index')}}">Pings Fallidos&nbsp;&nbsp;<e id="nro_P"></e></a>
                    </li>
                </ul>
            </div>
        </li>
    @endif
    
    @if(isset($_SESSION['permisos']['8']) || isset($_SESSION['permisos']['15']) || isset($_SESSION['permisos']['16'])|| isset($_SESSION['permisos']['21']) || isset($_SESSION['permisos']['29']) || isset($_SESSION['permisos']['34']) || isset($_SESSION['permisos']['412']))
        <li class="nav-item" id="inventario">
            <a class="nav-link" data-toggle="collapse" href="#ui-inventario" aria-expanded="false" aria-controls="ui-inventario">
                <i class="menu-icon fas fa-boxes"></i>
                <span class="menu-title">Inventario</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-inventario">
                <ul class="nav flex-column sub-menu">
                    <?php if(isset($_SESSION['permisos']['8'])){ ?>
                        <li class="nav-item d-none" id="items_venta">
                            <a class="nav-link" href="{{route('inventario.index')}}">Planes</a>
                        </li>
                        <li class="nav-item" id="material">
                            <a class="nav-link" href="{{route('inventario.material')}}">Productos</a>
                        </li>
                        <li class="nav-item" id="planes_tv">
                            <a class="nav-link" href="{{route('inventario.television')}}">Planes de Televisión</a>
                        </li>
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['412'])){ ?>
                    {{-- <li class="nav-item" id="modems">
                        <a class="nav-link" href="{{route('inventario.modems')}}" >Módems</a>
                        </li> --}}
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['15'])){ ?>
                    <li class="nav-item" id="valor_inventario">
                        <a class="nav-link" href="{{route('valorinventario')}}" >Valor Inventario</a>
                    </li>
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['16'])){ ?>
                    <li class="nav-item" id="ajustes_inventario">
                        <a class="nav-link" href="{{route('ajustes.index')}}" >Ajustes del Inventario</a>
                    </li>
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['21'])){ ?>
                    {{-- <li class="nav-item" id="gestion_items">
                        <a class="nav-link" href="{{route('inventario.gestion')}}" >Gestión de Items</a>
                    </li> --}}
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['29'])){ ?>
                    {{-- <li class="nav-item" id="lista_precio">
                        <a class="nav-link" href="{{route('lista_precios.index')}}" >Lista de Precios</a>
                    </li> --}}
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['34'])){ ?>
                    <li class="nav-item" id="bodegas">
                        <a class="nav-link" href="{{route('bodegas.index')}}" >Bodegas</a>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </li>
    @endif
    
    @if(isset($_SESSION['permisos']['40']) || isset($_SESSION['permisos']['45']) || isset($_SESSION['permisos']['50'])|| isset($_SESSION['permisos']['55']) || isset($_SESSION['permisos']['60']) || isset($_SESSION['permisos']['65']))
        <li class="nav-item" id="facturas">
            <a class="nav-link" data-toggle="collapse" href="#ui-factura" aria-expanded="false" aria-controls="ui-factura">
                <i class="menu-icon fas fa-plus"></i>
                <span class="menu-title">Facturación</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-factura">
                <ul class="nav flex-column sub-menu">
                    <?php if(isset($_SESSION['permisos']['40'])){ ?>
                        <li class="nav-item" id="venta">
                            <a class="nav-link" href="{{route('facturas.index')}}" >Facturas Estandar</a>
                        </li>
                        <li class="nav-item" id="venta-electronica">
                            <a class="nav-link" href="{{route('facturas.index-electronica')}}" >Facturas Electrónicas</a>
                        </li>
                    <?php } ?>
                    @if(isset($_SESSION['permisos']['774']))
                    <li class="nav-item" id="promesaspago">
                        <a class="nav-link" href="{{route('promesas-pago.index')}}" >Promesas de Pago</a>
                    </li>
                    @endif
                    <?php if(isset($_SESSION['permisos']['735'])){ ?>
                        <li class="nav-item" id="descuentos">
                            <a class="nav-link" href="{{route('descuentos.index')}}" >Descuentos</a>
                        </li>
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['45'])){ ?>
                        <li class="nav-item" id="ingresos">
                            <a class="nav-link" href="{{route('ingresos.index')}}" >Pagos / Ingresos</a>
                        </li>
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['50'])){ ?>
                        <li class="nav-item" id="credito">
                            <a class="nav-link" href="{{route('notascredito.index')}}" >Notas de Crédito</a>
                        </li>
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['55'])){ ?>
                        <li class="nav-item" id="cotizacion">
                            <a class="nav-link" href="{{route('cotizaciones.index')}}" >Cotizaciones</a>
                        </li>
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['65'])){ ?>
                        <li class="nav-item" id="remisiones">
                            <a class="nav-link" href="{{route('remisiones.index')}}" >Remisiones</a>
                        </li>
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['60'])){ ?>
                        <li class="nav-item" id="ingresosr">
                            <a class="nav-link" href="{{route('ingresosr.index')}}" >Pagos Recibidos R</a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </li>
    @endif
    
    {{--@if(isset($_SESSION['permisos']['420']))
        <li class="nav-item" id="facturaelectronica">
            <a  class="nav-link" href="https://gestoru.com/login" target="_blank">
                <i class="menu-icon fas fa-file-invoice-dollar"></i>
                <span class="menu-title">Fact. Electrónica</span>
            </a>
        </li>
    @endif--}}
    
    @if(isset($_SESSION['permisos']['251']) || isset($_SESSION['permisos']['256']) || isset($_SESSION['permisos']['80'])|| isset($_SESSION['permisos']['85']) || isset($_SESSION['permisos']['90']))
        <li class="nav-item" id="gastos">
            <a class="nav-link" data-toggle="collapse" href="#ui-gastos" aria-expanded="false" aria-controls="ui-gastos">
                <i class="menu-icon fas fa-minus"></i>
                <span class="menu-title">Compras</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-gastos">
                <ul class="nav flex-column sub-menu">
                    <?php if(isset($_SESSION['permisos']['256'])){ ?>
                    <li class="nav-item" id="facturas_proveedores">
                        <a class="nav-link" href="{{route('facturasp.index')}}" >Facturas de Proveedores</a>
                    </li>
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['251'])){ ?>
                    <li class="nav-item" id="pagos">
                        <a class="nav-link" href="{{route('pagos.index')}}" >Pagos / Egresos</a>
                    </li>
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['261'])){ ?>
                    <li class="nav-item" id="pagosrecurrentes">
                        <a class="nav-link" href="{{route('pagosrecurrentes.index')}}" >Pagos Recurrentes</a>
                    </li>
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['85'])){ ?>
                    <li class="nav-item" id="debito">
                        <a class="nav-link" href="{{route('notasdebito.index')}}" >Notas Débito</a>
                    </li>
                    <?php } ?>
                    <?php if(isset($_SESSION['permisos']['90'])){ ?>
                    <li class="nav-item" id="ordenes">
                        <a class="nav-link" href="{{route('ordenes.index')}}" >Órdenes de Compra</a>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </li>
    @endif

    @if(isset($_SESSION['permisos']['744']) || isset($_SESSION['permisos']['745']))
        <li class="nav-item" id="crm">
            <a class="nav-link" data-toggle="collapse" href="#ui-crm" aria-expanded="false" aria-controls="ui-crm">
                <i class="menu-icon fas fa-receipt"></i>
                <span class="menu-title">CRM</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-crm">
                <ul class="nav flex-column sub-menu">
                    @if(isset($_SESSION['permisos']['744']))
                        <li class="nav-item" id="crm_cartera">
                            <a class="nav-link" href="{{route('crm.index')}}">Cartera</a>
                        </li>
                    @endif
                    @if(isset($_SESSION['permisos']['745']))
                        <li class="nav-item" id="crm_informe">
                            <a class="nav-link" href="{{route('crm.informe')}}">Informe</a>
                        </li>
                    @endif
                </ul>
            </div>
        </li>
    @endif
    
    @if(isset($_SESSION['permisos']['427']) || isset($_SESSION['permisos']['200']) || isset($_SESSION['permisos']['300']) || isset($_SESSION['permisos']['425']))
        <li class="nav-item" id="atencion_cliente">
            <a class="nav-link" data-toggle="collapse" href="#ui-atencion_cliente" aria-expanded="false" aria-controls="ui-atencion_cliente">
                <i class="menu-icon fas fa-user-tie"></i>
                <span class="menu-title">Atención al Cliente</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-atencion_cliente">
                <ul class="nav flex-column sub-menu">
                    @if(isset($_SESSION['permisos']['427']))
                    <li class="nav-item" id="wifi">
                        <a class="nav-link" href="{{route('wifi.index')}}">Contraseñas Wifi&nbsp;&nbsp;<e id="nro_W"></e></a>
                    </li>
                    @endif
                    @if(isset($_SESSION['permisos']['200']))
                    <li class="nav-item" id="radicados">
                        <a class="nav-link" href="{{route('radicados.index')}}">Radicados&nbsp;&nbsp;<e id="nro_R"></e></a>
                    </li>
                    @endif
                    @if(isset($_SESSION['permisos']['500']))
                    <li class="nav-item" id="solicitudes">
                        <a class="nav-link" href="{{route('solicitudes.index')}}">Solicitudes de Servicios</a>
                    </li>
                    @endif
                    @if(isset($_SESSION['permisos']['600']))
                    <li class="nav-item" id="pqrs">
                        <a class="nav-link" href="{{route('pqrs.index')}}">PQRS</a>
                    </li>
                    @endif
                </ul>
            </div>
        </li>
    @endif
    
    @if(isset($_SESSION['permisos']['429']) || isset($_SESSION['permisos']['438']))
        <li class="nav-item" id="mikrotik">
            <a class="nav-link" data-toggle="collapse" href="#ui-mikrotik" aria-expanded="false" aria-controls="ui-mikrotik">
                <i class="menu-icon fas fa-server"></i>
                    <span class="menu-title">Servidores</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-mikrotik">
                <ul class="nav flex-column sub-menu">
                    @if(isset($_SESSION['permisos']['429']))
                    <li class="nav-item" id="gestion_mikrotik">
                        <a class="nav-link" href="{{route('mikrotik.index')}}">Mikrotik</a>
                    </li>
                    @endif
                    @if(isset($_SESSION['permisos']['438']))
                    <li class="nav-item" id="gestion_planes">
                        <a class="nav-link" href="{{route('planes-velocidad.index')}}">Planes de Velocidad</a>
                    </li>
                    @endif
                    @if(isset($_SESSION['permisos']['753']))
                    <li class="nav-item" id="gestion_blacklist">
                        <a class="nav-link" href="{{route('monitor-blacklist.index')}}">Monitor Blacklist</a>
                    </li>
                    @endif
                </ul>
            </div>
        </li>
    @endif
    
    @if(isset($_SESSION['permisos']['711']) || isset($_SESSION['permisos']['717']) || isset($_SESSION['permisos']['723']))
        <li class="nav-item" id="zonas">
            <a class="nav-link" data-toggle="collapse" href="#ui-zonas" aria-expanded="false" aria-controls="ui-zonas">
                <i class="menu-icon fas fa-map-marked-alt"></i>
                    <span class="menu-title">Zonas</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-zonas">
                <ul class="nav flex-column sub-menu">
                    @if(isset($_SESSION['permisos']['711']))
                    <li class="nav-item" id="nodos">
                        <a class="nav-link" href="{{route('nodos.index')}}">Nodos</a>
                    </li>
                    @endif
                    @if(isset($_SESSION['permisos']['717']))
                    <li class="nav-item" id="gestion_ap">
                        <a class="nav-link" href="{{route('access-point.index')}}">Access Point</a>
                    </li>
                    @endif
                    @if(isset($_SESSION['permisos']['723']))
                    {{-- <li class="nav-item" id="mapa_red">
                        <a class="nav-link" href="javascript:;">Mapa de Red</a>
                    </li> --}}
                    @endif
                    @if(isset($_SESSION['permisos']['724']))
                    <li class="nav-item" id="grupo_corte">
                        <a class="nav-link" href="{{route('grupos-corte.index')}}">Grupos de Corte</a>
                    </li>
                    @endif
                </ul>
            </div>
        </li>
    @endif
    
    @if(isset($_SESSION['permisos']['700']) || isset($_SESSION['permisos']['710']))
        <li class="nav-item" id="avisos">
            <a class="nav-link" data-toggle="collapse" href="#ui-avisos" aria-expanded="false" aria-controls="ui-avisos">
                <i class="menu-icon fas fa-file-code"></i>
                    <span class="menu-title">Gestión de Avisos</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-avisos">
                <ul class="nav flex-column sub-menu">
                    @if(isset($_SESSION['permisos']['700']))
                    <li class="nav-item" id="plantillas">
                        <a class="nav-link" href="{{route('plantillas.index')}}">Plantillas</a>
                    </li>
                    @endif
                    @if(isset($_SESSION['permisos']['710']))
                    <li class="nav-item" id="envios">
                        <a class="nav-link" href="{{route('avisos.index')}}">Envío de Notificaciones</a>
                    </li>
                    @endif
                </ul>
            </div>
        </li>
    @endif

    <li id="nomina" class="nav-item">
        @if(auth()->user()->empresaObj->nomina && isset($_SESSION['permisos']['782']) || isset($_SESSION['permisos']['783']) || isset($_SESSION['permisos']['784']) || isset($_SESSION['permisos']['785']) || isset($_SESSION['permisos']['786']) || isset($_SESSION['permisos']['787']) || isset($_SESSION['permisos']['788']) || isset($_SESSION['permisos']['789']) || isset($_SESSION['permisos']['790']) || isset($_SESSION['permisos']['791']) || isset($_SESSION['permisos']['792']) || isset($_SESSION['permisos']['793']) || isset($_SESSION['permisos']['794']) || isset($_SESSION['permisos']['795']) || isset($_SESSION['permisos']['796']) || isset($_SESSION['permisos']['797']))
        <a class="nav-link" data-toggle="collapse" href="#ui-nomina" aria-expanded="false" aria-controls="ui-nomina">
            <i class="menu-icon far fa-money-bill-alt"></i>
            <span class="menu-title">Nómina
                <span class="badge badge-info ml-1">Nuevo</span>
            </span>
            <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="ui-nomina">
            <ul class="nav flex-column sub-menu">
                @if(isset($_SESSION['permisos']['782']) || isset($_SESSION['permisos']['783']) || isset($_SESSION['permisos']['784']) || isset($_SESSION['permisos']['785']) || isset($_SESSION['permisos']['786']) || isset($_SESSION['permisos']['787']))
                    <li class="nav-item" id="personas-nomina">
                        <a class="nav-link" href="{{ route('personas.index') }}">Personas</a>
                    </li>
                @endif
                @if(isset($_SESSION['permisos']['788']) || isset($_SESSION['permisos']['789']) || isset($_SESSION['permisos']['790']) || isset($_SESSION['permisos']['791']) || isset($_SESSION['permisos']['792']))
                    <li class="nav-item" id="liquidar-nomina">
                        <a class="nav-link" href="{{ route('nomina.index') }}" id="liquidar-nomina-anchor">Liquidar Nómina</a>
                    </li>
                @endif
                @if(isset($_SESSION['permisos']['793']) || isset($_SESSION['permisos']['794']) || isset($_SESSION['permisos']['795']) || isset($_SESSION['permisos']['796']))
                    <li class="nav-item" id="contabilidad">
                        <a class="nav-link" href="{{ route('contabilidad.index') }}">Contabilidad
                        </a>
                    </li>
                @endif

                @if(isset($_SESSION['permisos']['797']))
                    <li class="nav-item" id="asisente-habilitacion">
                        <a id="nomina_asistente" href="{{ route('nomina-dian.asistente') }}" class="{{auth()->user()->empresaObj->nomina ? 'nav-link' : 'd-none'}}">Asistente de habilitación</a>
                    </li>
                @endif
            </ul>
        </div>
        @endif
    </li>

    @if(isset($_SESSION['permisos']['281']))
        <li class="nav-item" id="bancos">
            <a  class="nav-link" href="{{route('bancos.index')}}">
                <i class="menu-icon fas fa-university"></i>
                <span class="menu-title">Bancos</span>
            </a>
        </li>
    @endif
  
    @if(isset($_SESSION['permisos']['426']))
        <li class="nav-item" id="saldo">
            <a  class="nav-link" href="{{route('recarga.index')}}">
                <i class="menu-icon fas fa-dollar-sign"></i>
                <span class="menu-title">Recarga Saldo</span>
            </a>
        </li>
    @endif
  
    @if(isset($_SESSION['permisos']['415']))
        <li class="nav-item" id="mensajeria">
            <a  class="nav-link" href="{{route('mensajeria.index')}}">
                <i class="menu-icon fas fa-envelope-open-text"></i>
                <span class="menu-title">Mensajería</span>
            </a>
        </li>
    @endif
    
    @if(isset($_SESSION['permisos']['422']))
        <li class="nav-item" id="notificaciones">
            <a  class="nav-link" href="{{route('notificaciones.index')}}">
                <i class="menu-icon fas fa-comment"></i>
                <span class="menu-title">Notificaciones APP</span>
            </a>
        </li>
    @endif
    
    @if(isset($_SESSION['permisos']['291']))
        <li class="nav-item" id="reportes">
            <a  class="nav-link" href="{{route('reportes.index')}}">
                <i class="menu-icon fas fa-chart-line"></i>
                <span class="menu-title">Reportes</span>
            </a>
        </li>
    @endif

    @if(Auth::user()->empresa()->smartOLT && isset($_SESSION['permisos']['760']))
    <li class="nav-item">
        <a  class="nav-link" href="{{Auth::user()->empresa()->smartOLT}}" target="_blank">
            <i class="menu-icon fas fa-server"></i>
            <span class="menu-title">Smart OLT</span>
        </a>
    </li>
    @endif

    @if(Auth::user()->empresa()->adminOLT && isset($_SESSION['permisos']['761']))
    <li class="nav-item">
        <a  class="nav-link" href="{{Auth::user()->empresa()->adminOLT}}" target="_blank">
            <i class="menu-icon fas fa-server"></i>
            <span class="menu-title">Admin OLT</span>
        </a>
    </li>
    @endif
    
    @if(isset($_SESSION['permisos']['111']))
        <li class="nav-item" id="configuracion">
            <a  class="nav-link" href="{{route('configuracion.index')}}">
                <i class="menu-icon fas fa-cogs"></i>
                <span class="menu-title">Configuración</span>
            </a>
        </li>
    @endif
    
    <li class="nav-item" id="logout-lateral">
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>
        <a  class="nav-link"  onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="menu-icon fas fa-sign-out-alt"></i>
            <span class="menu-title">Salir</span>
        </a>
    </li>
    
    <li class="nav-item" id="logout-lateral">
        <a  class="nav-link" href="#">
            <span class="menu-title"></span>
        </a>
    </li>

    <input type="hidden" id="notificacionWifi" value="{{ isset($_SESSION['permisos']['779']) ? 1 : 0 }}">
    <input type="hidden" id="notificacionRadicados" value="{{ isset($_SESSION['permisos']['780']) ? 1 : 0 }}">
    <input type="hidden" id="notificacionPings" value="{{ isset($_SESSION['permisos']['781']) ? 1 : 0 }}">
@endif