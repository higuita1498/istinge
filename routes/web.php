<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. conThese
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use Illuminate\Support\Facades\Artisan;

Route::get('phpinfo', function(){phpinfo();});

Route::get('clear', function () {
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('route:cache');
    $exitCode = Artisan::call('view:clear');

    return redirect()->back();
});



/* IMPORTAR API*/
Route::post('/import_puc','PucController@import_puc')->name('puc.import_puc');

Route::get('/import_plans','Controller@import_plans')->name('import_plans');
Route::get('/import_clients','Controller@import_clients')->name('import_clients');
Route::get('/import_contracts','Controller@import_contracts')->name('import_contracts');
Route::get('/import_bmus','Controller@import_bmus')->name('import_bmus');
Route::get('/import_mikrotiks','Controller@import_mikrotiks')->name('import_mikrotiks');
Route::get('/generarfactura','CronController@CrearFactura')->name('CrearFactura');
Route::get('/cortarfacturas','CronController@CortarFacturas')->name('CortarFacturas');
Route::get('/enviarsms','CronController@EnviarSMS')->name('EnviarSMS');
Route::get('/migrarCRM','CronController@migrarCRM')->name('migrarCRM');

/*PAYU*/

Route::get('/respuestapayu', 'Controller@respuestapayu')->name('respuestapayu');
Route::get('/pagopayu', 'Controller@pagopayu')->name('pagopayu');

Route::get('getGraph', 'Controller@getGraph');

Route::get('/change_pass/{nombre}/{identificacion}', 'Controller@change_pass');
Route::get('/show_contract/{id}', 'Controller@show_contract');
Route::get('/radicados/{codigo}/{identificacion}', 'Controller@consultar')->name('radicados.consulta');
Route::get('/factura/{identificacion}', 'Controller@consultar_invoice')->name('invoice.show');

/*DATATABLE ORACLE*/
Route::get('contratos/{nodo?}', 'ContratosController@contratos');
Route::get('nodos', 'NodosController@nodos');
Route::get('facturas', 'FacturasController@facturas');
Route::get('facturas-electronicas','FacturasController@facturas_electronica');
Route::get('ingresos', 'IngresosController@ingresos');
//Route::get('contactos', 'ContactosController@contactos');
Route::get('contactos/{tipo_usuario?}', 'ContactosController@contactos');
Route::get('solicitudes', 'WifiController@solicitudes');
Route::get('pqrs', 'PqrsController@pqrs');
Route::get('aps', 'APController@ap');
Route::get('logs/{contrato}', 'ContratosController@logs');
Route::get('logsMK/{mikrotik}', 'Controller@logsMK');
Route::get('pings', 'PingsController@pings');
Route::get('grupos', 'GruposCorteController@grupos');
Route::get('radicados/{tipo}', 'RadicadosController@radicados');
Route::get('descuentos', 'DescuentosController@descuentos');
Route::get('tipos-gastos', 'TiposGastosController@tipos_gastos');
Route::get('cartera/{tipo}', 'CRMController@cartera');
Route::get('reporte', 'CRMController@reporte');
Route::get('puertos', 'PuertosController@puertos');
/*DATATABLE ORACLE*/

Route::get('/clear', function() {
   $exitCode = Artisan::call('config:cache');
    $exitCode = Artisan::call('cache:clear');
   // $exitCode = Artisan::call('route:cache');
    $exitCode = Artisan::call('view:clear');

     return redirect()->back();
});

Route::get('pdfmariano', 'ContactosController@pdfmariano')->name('contactos.pdfmariano');

Route::get('/testerroute54','Controller@tester54');

Route::get('qrcode', function () {
     return QrCode::generate('Make me into a QrCode!');
 });

Route::get('guiaenvio/contacto/{id}/{cliente}','ContactosController@modalGuiaEnvio');
Route::post('factura/guiaenvio/asociar','FacturasController@asociarGuiaEnvio')->name('factura.guia_envio');

// Rutas referentes a la Dian
Route::post('/validatedian/invoice', 'FacturasController@validate_dian');
Route::post('/validatetechnicalkeydian', 'FacturasController@validate_technicalkey_dian');

//Route::get('', 'HomeController@inicio')->name('Inicio');
Route::get('', 'Auth\LoginController@showLoginForm')->name('login');
Route::get('/home', 'HomeController@home')->name('home');
Route::get('/carrito/{empresa}', 'HomeController@carrito')->name('carrito');
Route::get('/terminosycondiciones', 'HomeController@terminoscondiciones')->name('terminoscondiciones');

//Close all Sessions
Route::get('/closeallsession','HomeController@peticionCloseAllSesions')->name('home.closeallsession');


//Rutas Planes
Route::resource('PlanesPagina', 'PlanesController');
Route::get('PlanesSIC/plan/personalizado', 'PlanesController@indexPersonalizado')->name('planes.personalizado');
Route::get('PlanesSIC/{pago}', 'PlanesController@pagos')->name('planes.pagos');
Route::get('PlanesSIC/{pago}/{id}', 'PlanesController@pagos')->name('planes.p_pagos');
Route::post('PagarSIC', 'PlanesController@pagar')->name('planes.pagar');
Route::get('Mercadopago/{plan}', 'PlanesController@mercadopago')->name('planes.mercadopago');
Route::get('PlanesPagina/verificar/{cuenta}', 'PlanesController@verificarLimites')->name('planes.verificar');
Route::get('/respuestapago', 'PlanesController@respuestapago')->name('planes.respuestapago');
Route::get('/PagoHecho', 'PlanesController@pagohecho')->name('planes.pagohecho');
Route::get('/pagos/obtenerinformacionpago/{valor}', 'PlanesController@getObtenerInformacionPago');
Route::get('/PreGuardarPago','PlanesController@PreGuardarPago');
Route::get('/ConsultaEstadoTransaccion','PlanesController@consultaestado');
Route::get('/DatosFaltantesTransaccion','PlanesController@datosfaltantes');
//fin rutas planes
Route::post('searchMunicipality', 'ContactosController@searchMunicipality');
Route::get('/getDataClient/{id}','ContactosController@getDataClient');
Route::post('/updatedirection/client','ContactosController@updatedirection');

/*.......................................................................
Rutas busqueda de proveedores por marca, linea categoria y fabricante
.........................................................................*/
Route::get('busquedaproveedor', 'BusquedaProveedorController@listabusquedaproveedor')->name('Configuracion.listaproveedor');
Route::get('busquedaproveedores/getdata','BusquedaProveedorController@index');
Route::get('busquedaproveedores/getdataproduct','BusquedaProveedorController@getproveedoresxproducto');
Route::get('agregarmarcaprov/{id}', 'BusquedaProveedorController@agregarmarcaprov')->name('configuracion.agregarmarcaprov');
Route::post('/asociarproveedor/guardar', 'BusquedaProveedorController@asociarproveedor')->name('BusquedaProveedor.asociarproveedor');
Route::post('/asociarproveedor/campos','BusquedaProveedorController@guardarcampop')->name('configuracion.guardarcampop');
Route::get('/llenarbusquedaproveedor','BusquedaProveedorController@llenarbusquedaproveedor');
/*.......................................................................
/Rutas busqueda de proveedores por marca, linea categoria y fabricante
.........................................................................*/

//PAGINA INICIO SIC
/*Route::get("Inicio",function(){
	return view("PaginaInicio.index");
})->name("Inicio");*/
Route::get('/Inicio', 'HomeController@inicio')->name('Inicio');
Route::get('/Contactanos', 'HomeController@contactanos')->name('contactanos.index');
Route::get('/Modulos', 'HomeController@modulos')->name('sic.modulos');
Route::get('/Planes', 'HomeController@planes')->name('sic.planes');
Route::get('/Registrarse', 'HomeController@registrarse')->name('sic.registrarse');

Route::get('/Servicios', 'PaginaInicioController@servicios')->name('sic.servicios');
//FIN PAGINA INICIO SIC

//Rutas de  mi Pagina web
Route::get('/PaginaWeb','PaginaWebController@index')->name('PaginaWeb.index');
Route::get('/PaginaWeb/pedidos','PaginaWebController@pedidos')->name('PaginaWeb.pedidos');
Route::get('/PaginaWeb/pedidos/{pedido}', 'PaginaWebController@detallepedido')->name('PaginaWeb.detallepedido');
Route::get('/PaginaWeb/Comentarios','PaginaWebController@comentarios')->name('PaginaWeb.comentarios');
Route::get('/PaginaWeb/Comentarios/{comentario}', 'PaginaWebController@detallecomentarios')->name('PaginaWeb.detallecomentarios');
Route::get('/PaginaWeb/Personas', 'PaginaWebController@personas')->name('PaginaWeb.personas');

 //Rutas de SocialAuthController:
 Route::get('login/{provider}','SocialAuthController@redirectToProvider')->name('google.logueo');
 Route::get('login/{provider}/callback','SocialAuthController@handlerProviderCallback');
 Route::post('registernormally','SocialAuthController@registronormal')->name('social.registronormal');

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login')->name('login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');

//Rutas para la obtenci��n de datos
Route::get('getAllDatacb4ccecb55sdfh93195d6a785829', 'Controller@getAllData');

//generador_facturas_recurrentes
Route::get('4bb4ccecbb7823c435b493195a785829', 'RecurrentesController@generar_factura')->name('generar_factura');
Route::get('3bd8497e1b07070fc7e1927d29669055', 'PagosRecurrentesController@generar_pagos')->name('generar_pagos');


Route::get('olvido_pass', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@enviar')->name('password.email');
Route::get('change_pass/{token}', 'Auth\ResetPasswordController@recuperar_pass')->name('pass.change');
Route::post('save_pass', 'Auth\ResetPasswordController@cambiar_pass')->name('pass.save');

Route::group(['prefix' => 'master', 'middleware' => ['auth', 'master']], function() {
	Route::get('/', 'HomeController@index')->name('master');

	Route::get('/reportefacturasMaster','MasterReportesController@reportefactura')->name('master.reportefactura');
	Route::get('/reporteRemisionesMaster','MasterReportesController@reporteremisiones')->name('master.reporteremisiones');
	Route::get('/reportePagosMaster','MasterReportesController@reportepagos')->name('master.reportepagos');
	Route::get('/reporteProductosMaster','MasterReportesController@reporteproductos')->name('master.reporteproductos');

	Route::group(['prefix' => 'planes'], function (){
        Route::get('/personalizados','PlanesController@personalizados_index')->name('p_personalizados.index');
        Route::get('/personalizados/crear','PlanesController@personalizados_create')->name('p_personalizados.create');
        Route::post('/personalizados/crear','PlanesController@personalizados_store')->name('p_personalizados.store');
        Route::get('/personalizados/{id}/editar','PlanesController@personalizados_edit')->name('p_personalizados.edit');
        Route::post('/personalizados/{id}/editar','PlanesController@personalizados_update')->name('p_personalizados.update');
        Route::post('/personalizados/{id}/borrar','PlanesController@personalizados_destroy')->name('p_personalizados.destroy');
    });


    Route::group(['prefix' => 'suscripcion'], function() {
        Route::get('listado', 'SuscripcionController@index')->name('listado.index');
        Route::get('pagos', 'SuscripcionController@indexPagos')->name('listadoPagos.index');


        Route::post('guardar', 'SuscripcionController@store')->name('suscripciones.store');
        Route::post('aprobarPago/{id}', 'SuscripcionController@aprobarPago')->name('suscripciones.aprobar');
        Route::get('prorroga/{id}', 'SuscripcionController@prorrogaForm')->name('suscripciones.prorroga');
        Route::post('agregarProrroga/{id}', 'SuscripcionController@prorrogaUpdate')->name('suscripciones.agregarProrroga');
        Route::get('ilimitado/{id}', 'SuscripcionController@ilimitado')->name('suscripciones.ilimitado');

        Route::get('anular/{id}', 'SuscripcionController@anular')->name('suscripciones.anular');
        Route::get('activar/{id}', 'SuscripcionController@activar')->name('suscripciones.activar');
    });


    Route::group(['prefix' => 'empresas'], function() {
		Route::post('desactivar/{id}', 'EmpresasController@desactivar')->name('empresas.desactivar');
        Route::post('activar/{id}', 'EmpresasController@activar')->name('empresas.activar');
        Route::get('inactivas', 'EmpresasController@inactivas')->name('empresas.inactivas');
        Route::get('ingresar/{email}','EmpresasController@ingresar')->name('empresas.ingresar');
	});
	Route::resource('empresas', 'EmpresasController');
	Route::get('edit', 'UsuariosController@my_edit')->name('user.editar');
	Route::post('edit', 'UsuariosController@my_update')->name('user.editar');
	Route::resource('atencionsoporte', 'SoporteController');
	
	Route::group(['prefix' => 'usuarios'], function() {
        Route::get('ingresar/{email}','UsuariosController@ingresar')->name('usuario.ingresar');
	});
	Route::resource('usuarios', 'UsuariosController');
});

Route::group(['prefix' => 'empresa', 'middleware' => ['auth']], function() {
	Route::get('/', 'HomeController@index')->name('empresa');
	Route::group(['prefix' => 'contactos'], function() {
		Route::get('clientes', 'ContactosController@clientes')->name('contactos.clientes');
		Route::get('proveedores', 'ContactosController@proveedores')->name('contactos.proveedores');
		Route::get('clientes/json/{type?}', 'ContactosController@json')->name('contactos.clientes.json');
		Route::get('{id}/json', 'ContactosController@json')->name('contactos.json');
		Route::get('exportar/{tipo?}', 'ContactosController@exportar')->name('contactos.exportar');
		Route::get('importar', 'ContactosController@importar')->name('contactos.importar');
		Route::get('ejemplo', 'ContactosController@ejemplo')->name('contactos.ejemplo');
		Route::post('importar', 'ContactosController@cargando')->name('contactos.importar');
		Route::get('/create/modal', 'ContactosController@create_modal')->name('contactos.create.modal');
        Route::get('contactosModal', 'ContactosController@contactoModal')->name('contactos.contactoModal');
        Route::post('{id}/desasociar', 'ContactosController@desasociar')->name('contactos.desasociar');
        Route::get('createp', 'ContactosController@createp')->name('contactos.createp');
	});
	Route::resource('contactos', 'ContactosController');

	//Ruta especial para alamacenar y retornar contacto recien creado
	Route::post('contactosBack', 'ContactosController@storeBack')->name('contactos.storeBack');


	//Suscripciones
    Route::group(['prefix' => 'suscripcion'], function() {
    Route::get('pagos', 'SuscripcionController@indexPagos')->name('listadoPagos.index');
    Route::post('guardar', 'SuscripcionController@store')->name('suscripciones.store');
    });

	Route::group(['prefix' => 'inventario'], function() {
		Route::post('/{id}/act_desc', 'InventarioController@act_desc')->name('inventario.act_desc');
		Route::get('{id}/json', 'InventarioController@json')->name('inventario.json');
		Route::get('json', 'InventarioController@json')->name('inventario.all');
		Route::get('importar', 'InventarioController@importar')->name('inventario.importar');
		Route::post('importar', 'InventarioController@cargando')->name('inventario.importar');
		Route::get('actualizar', 'InventarioController@actualizar')->name('inventario.actualizar');
		Route::post('actualizar', 'InventarioController@actualizando')->name('inventario.actualizar');
		Route::get('exportar', 'InventarioController@exportar')->name('inventario.exportar');
		Route::get('ejemplo', 'InventarioController@ejemplo')->name('inventario.ejemplo');
		Route::post('{id}/imagenes', 'InventarioController@imagenes')->name('inventario.imagenes');
		Route::get('/valor', 'ExtraInventarioController@valorinventario')->name('valorinventario');
		Route::get('/gestion', 'ExtraInventarioController@gestion')->name('inventario.gestion');
		Route::get('/precios', 'ExtraInventarioController@lista_precio')->name('inventario.precios');
		Route::post('/publicar/{id}', 'InventarioController@publicar')->name('inventario.publicar');
		Route::get('/modems', 'InventarioController@modems')->name('inventario.modems');
		Route::get('/material', 'InventarioController@material')->name('inventario.material');

		Route::post('/diaiva', 'InventarioController@diaIva');



        //Ruta datatable
        Route::get('/productos', 'InventarioController@getDataTable');


		Route::get('items','InventarioController@repararLinea')->name('inventario.items');

		//Ruta para rellenar las tablas inferiores de informacion del porducto
        Route::get('/{id}/facturaVenta', 'FacturasController@datatable_producto')->name('rellenarFV');
        Route::get('/{id}/facturaCompra', 'FacturaspController@datatable_producto')->name('rellenarFC');
        Route::get('/{id}/notaCredito', 'NotascreditoController@datatable_producto')->name('rellenarNC');
        Route::get('/{id}/cotizaciones', 'CotizacionesController@datatable_producto')->name('rellenarC');
        Route::get('/{id}/notaDebito', 'NotasdebitoController@datatable_producto')->name('rellenarND');
        Route::get('/{id}/remisiones', 'RemisionesController@datatable_producto')->name('rellenarR');
        Route::get('/{id}/ordenesCompra', 'OrdenesController@datatable_producto')->name('rellenarOD');

		Route::group(['prefix' => 'bodegas'], function() {
			Route::get('{id}/json', 'BodegasController@json')->name('bodegas.json');
			Route::post('/{id}/act_desc', 'BodegasController@act_desc')->name('bodegas.act_desc');
			Route::get('transferencia/{id}/imprimir', 'TransferenciaController@imprimir')->name('transferencia.imprimir');
			Route::resource('transferencia', 'TransferenciaController');
		});

		Route::post('/lista_precios/{id}/act_desc', 'ListaPreciosController@act_desc')->name('lista_precios.act_desc');
		Route::resource('bodegas', 'BodegasController');
		Route::resource('lista_precios', 'ListaPreciosController');
		Route::resource('ajustes', 'AjusteInventarioController');

	});

	Route::resource('inventario', 'InventarioController');

	//Ruta especial para crear producto en la ventana modal de fasturasp
	Route::post('inventario/modalStore', 'InventarioController@storeBack')->name('inventario.storeback');

	Route::resource('logistica', 'LogisticaController');

	//Facturas de Venta

	Route::group(['prefix' => 'facturas'], function() {
	    /* PASAR INFORMACION DE REMISION A FACTURA*/
	    Route::get('remision/{id}', 'FacturasController@remisionAfactura')->name('factura.remision');
	    /**/

	    Route::get('xml/{id}','FacturasController@xmlFacturaVenta')->name('xml.factura');
	    Route::get('xmlexport','FacturasController@exportData');
	    Route::get('xmlcorreo/{id}','FacturasController@xmlFacturaVentabyCorreo')->name('xml.correo');

		Route::get('/cliente/{id}', 'FacturasController@cliente_factura_json')->name('facturas.cliente.json');
		Route::get('/{id}/json', 'FacturasController@factura_json')->name('facturas.json');
		Route::get('/{id}/clientejson', 'FacturasController@cliente_factura_json_all')->name('facturas.clientejson');
		Route::get('/{id}/facturajson', 'FacturasController@items_factura_json')->name('facturas.facturajson');
		Route::get('/{id}/copia', 'FacturasController@copia')->name('facturas.copia');
		Route::get('/{id}/pdf', 'FacturasController@pdf')->name('facturas.pdf');
		Route::get('/{id}/copia', 'FacturasController@copia')->name('facturas.copia');
		Route::get('pdf/{id}/{name}', 'FacturasController@ImprimirElec')->name('facturas.imprimir');
		Route::get('tirilla/{id}/{name}', 'FacturasController@imprimirTirilla')->name('facturas.tirilla');

		Route::get('/{id}/xml','FacturasController@xml')->name('facturas.xml');
		
		//Route::get('pdfele/{id}/{name}', 'FacturasController@ImprimirElec')->name('facturas.imprimir');


		/**/
        Route::get('/{id}/imprimirTirilla.pdf', 'FacturasController@imprimirTirilla')->name('facturas.imprimirT');
		/**/
		Route::get('/{id}/imprimircopia', 'FacturasController@Imprimircopia')->name('facturas.imprimircopia');
		Route::get('/{id}/enviar', 'FacturasController@enviar')->name('facturas.enviar');
		Route::get('/{id}/enviarcopia', 'FacturasController@enviarcopia')->name('facturas.enviarcopia');
		Route::get('/create_cliente/{cliente}', 'FacturasController@create_cliente')->name('factura.create.cliente');
		Route::get('/create_item/{producto}', 'FacturasController@create_item')->name('factura.create_item');
		Route::get('/datatable/producto/{producto}', 'FacturasController@datatable_producto')->name('factura.datatable.producto');
		Route::get('/datatable/cliente/{producto}', 'FacturasController@datatable_cliente')->name('factura.datatable.cliente');
		Route::post('{id}/anular', 'FacturasController@anular')->name('factura.anular');
		Route::post('{id}/cerrar', 'FacturasController@cerrar')->name('factura.cerrar');
		Route::get('/{id}/mensaje', 'FacturasController@mensaje')->name('facturas.mensaje');

		Route::get('/productos', 'FacturasController@getItemsSelect');

        Route::get('{id}/aceptarFe', 'FacturasController@aceptarFe')->name('factura.aceptarfe');

        Route::post('/validatetime/emicion','FacturasController@validateTimeEmicion')->middleware(['auth']);
        
        Route::get('/{id}/promesa_pago', 'FacturasController@promesa_pago')->name('factura.promesa_pago');
        Route::post('/store_promesa', 'FacturasController@store_promesa')->name('factura.store_promesa');

		Route::get('facturas_electronica', 'FacturasController@index_electronica')->name('facturas.index-electronica');
		Route::get('facturas_electronica/create', 'FacturasController@create_electronica')->name('facturas.create-electronica');
		Route::get('/{tipo}/listado', 'FacturasController@indexNew')->name('facturas.tipo');

	});
	Route::resource('facturas', 'FacturasController');

	//Cotizaciones

	Route::group(['prefix' => 'cotizaciones'], function() {
		Route::get('/{id}/imprimir', 'CotizacionesController@Imprimir')->name('cotizaciones.imprimir');
		Route::get('pdf/{id}/{name}', 'CotizacionesController@Imprimir')->name('cotizaciones.imprimir.nombre');
		Route::get('/{id}/enviar', 'CotizacionesController@enviar')->name('cotizaciones.enviar');
		Route::get('/{id}/facturar', 'CotizacionesController@facturar')->name('cotizaciones.facturar');
		Route::get('/{id}/remision', 'RemisionesController@cotizacionARemision')->name('cotizaciones.remision');
		Route::get('/datatable/producto/{producto}', 'CotizacionesController@datatable_producto')->name('cotizaciones.datatable.producto');
		Route::get('/datatable/cliente/{cliente}', 'CotizacionesController@datatable_cliente')->name('cotizaciones.datatable.cliente');
	});
	Route::resource('cotizaciones', 'CotizacionesController');
	Route::group(['prefix' => 'ingresos'], function() {
		Route::get('/create/{cliente}/{factura}', 'IngresosController@create')->name('ingresos.create_id');
		Route::get('/create_cuenta/{cliente}/{factura}/{banco}', 'IngresosController@create')->name('ingresos.create_cuenta');


        Route::get('contacto/{id}', 'IngresosController@saldoContacto')->name('notascredito.saldoCliente');
		Route::get('/pendiente/{cliente}/{id?}', 'IngresosController@pendiente')->name('ingresos.pendiente');
		Route::get('/ingpendiente/{cliente}/{id?}', 'IngresosController@ingpendiente')->name('ingpendiente.pendiente');
		Route::get('/{id}/imprimir', 'IngresosController@Imprimir')->name('ingresos.imprimir');
		Route::get('pdf/{id}/{name}', 'IngresosController@Imprimir')->name('ingresos.imprimir.nombre');
		Route::get('/{id}/enviar', 'IngresosController@enviar')->name('ingresos.enviar');
		Route::post('{id}/anular', 'IngresosController@anular')->name('ingresos.anular');
	});

	Route::resource('recurrentes', 'RecurrentesController');
	Route::resource('ingresos', 'IngresosController');
	Route::resource('ingresosr', 'IngresosRController');
	Route::group(['prefix' => 'ingresosr'], function() {
		Route::get('/create/{cliente}/{remision}', 'IngresosRController@create')->name('ingresosr.create_id');
		Route::get('/pendiente/{cliente}/{id?}', 'IngresosRController@pendiente')->name('ingresosr.pendiente');
		Route::get('/ingpendiente/{cliente}/{id?}', 'IngresosRController@ingpendiente')->name('ingresosr.ingpendiente');
		Route::get('/{id}/imprimir', 'IngresosRController@Imprimir')->name('ingresosr.imprimir');
		Route::get('pdf/{id}/{name}', 'IngresosRController@Imprimir')->name('ingresosr.imprimir.nombre');
		Route::get('/{id}/enviar', 'IngresosRController@enviar')->name('ingresosr.enviar');
		Route::post('{id}/anular', 'IngresosRController@anular')->name('ingresosr.anular');
	});

	Route::group(['prefix' => 'notascredito'], function() {

		Route::get('xml/{id}','NotascreditoController@xmlNotaCredito')->name('xml.notacredito');
		Route::get('descargar/{nro}', 'NotascreditoController@xml')->name('notascredito.xml');

		Route::get('/{id}/imprimir', 'NotascreditoController@Imprimir')->name('notascredito.imprimir');
		Route::get('pdf/{id}/{name}', 'NotascreditoController@Imprimir')->name('notascredito.imprimir.nombre');
		Route::get('/{id}/enviar', 'NotascreditoController@enviar')->name('notascredito.enviar');
        Route::get('items/{id}', 'NotascreditoController@items_fact')->name('notascredito.itemsfact');
        Route::get('reteitems/{id}', 'NotascreditoController@facturas_retenciones')->name('notascredito.reteitems');
		Route::get('/datatable/producto/{producto}', 'NotascreditoController@datatable_producto')->name('notascredito.datatable.producto');
		Route::get('/datatable/cliente/{cliente}', 'NotascreditoController@datatable_cliente')->name('notascredito.datatable.cliente');

		Route::post('/validatetime/emicion', 'NotascreditoController@validateTimeEmicion')->middleware(['auth']);


	});
	Route::resource('notascredito', 'NotascreditoController');

	Route::group(['prefix' => 'remisiones'], function() {
		Route::get('/{id}/imprimir', 'RemisionesController@Imprimir')->name('remisiones.imprimir');
		Route::get('pdf/{id}/{name}', 'RemisionesController@Imprimir')->name('remisiones.imprimir');
		Route::get('/{id}/enviar', 'RemisionesController@enviar')->name('remisiones.enviar');
		Route::get('/datatable/producto/{producto}', 'RemisionesController@datatable_producto')->name('remisiones.datatable.producto');
		Route::get('/datatable/cliente/{cliente}', 'RemisionesController@datatable_cliente')->name('remisiones.datatable.cliente');
		Route::post('{id}/anular', 'RemisionesController@anular')->name('remisiones.anular');
	});

	Route::resource('remisiones', 'RemisionesController');
	Route::group(['prefix' => 'pagos'], function() {
		Route::get('/create/{cliente}/{factura}', 'PagosController@create')->name('pagos.create_id');
		Route::get('/create_cuenta/{cuenta}', 'PagosController@create')->name('pagos.create_cuenta');

		Route::get('/pendiente/{cliente}/{id?}', 'PagosController@pendiente')->name('pagos.pendiente');
		Route::get('/ingpendiente/{cliente}/{id?}', 'PagosController@ingpendiente')->name('pagos.ingpendiente');
		Route::get('/{id}/imprimir', 'PagosController@Imprimir')->name('pagos.imprimir');
		Route::get('pdf/{id}/{name}', 'PagosController@Imprimir')->name('pagos.imprimir.nombre');
		Route::get('/{id}/enviar', 'PagosController@enviar')->name('pagos.enviar');
		Route::post('{id}/anular', 'PagosController@anular')->name('pagos.anular');
	});



	Route::resource('pagos', 'PagosController');

	Route::group(['prefix' => 'facturasp'], function() {
		Route::get('/proveedor/{id}', 'FacturaspController@proveedor_factura_json')->name('facturasp.proveedor.json');
		Route::get('/{id}/json', 'FacturaspController@facturap_json')->name('facturasp.json');

        Route::get('/{id}/pdf', 'FacturaspController@pdf')->name('facturasp.pdf');
        Route::get('/{id}/copia', 'FacturaspController@copia')->name('facturasp.copia');
        Route::get('/{id}/imprimir', 'FacturaspController@Imprimir')->name('facturasp.imprimir');
        Route::get('pdf/{id}/{name}', 'FacturaspController@Imprimir')->name('facturasp.imprimir.nombre');
        Route::get('/{id}/imprimircopia', 'FacturaspController@Imprimircopia')->name('facturasp.imprimircopia');



		Route::get('/create_proveedor/{proveedor}', 'FacturaspController@create')->name('facturasp.create.proveedor');
		Route::get('/create_item/{producto}', 'FacturaspController@create_item')->name('facturasp.create_item');
		Route::get('/datatable/producto/{producto}', 'FacturaspController@datatable_producto')->name('facturasp.datatable.producto');
		Route::get('/datatable/cliente/{producto}', 'FacturaspController@datatable_cliente')->name('facturap.datatable.cliente');
	});


	Route::resource('facturasp', 'FacturaspController');
	Route::get('facturaspid/{id}', 'FacturaspController@showId')->name('facturasp.showid');

    Route::get('/{id}/pdf', 'FacturaspController@pdf')->name('facturasp.pdf');
    Route::get('/{id}/copia', 'FacturaspController@copia')->name('facturasp.copia');
    Route::get('/{id}/imprimir', 'FacturaspController@Imprimir')->name('facturasp.imprimir');
    Route::get('/{id}/imprimircopia', 'FacturaspController@Imprimircopia')->name('facturasp.imprimircopia');

	Route::group(['prefix' => 'notasdebito'], function() {

        Route::get('xml/{id}','NotasdebitoController@xmlNotaDebito')->name('xml.notadebito');

        Route::get('/{id}/imprimir', 'NotasdebitoController@Imprimir')->name('notasdebito.imprimir');
        Route::get('pdf/{id}/{name}', 'NotasdebitoController@Imprimir')->name('notasdebito.imprimir.nombre');
        Route::get('items/{id}', 'NotasdebitoController@items_fact')->name('notasdebito.itemsfact');
		Route::get('/datatable/producto/{producto}', 'NotasdebitoController@datatable_producto')->name('notasdebito.datatable.producto');
		Route::get('/datatable/cliente/{cliente}', 'NotasdebitoController@datatable_cliente')->name('notasdebito.datatable.cliente');
	});

	Route::resource('notasdebito', 'NotasdebitoController');


	//ÓRDENES

	Route::group(['prefix' => 'ordenes'], function() {
		Route::get('{id}/imprimir', 'OrdenesController@Imprimir')->name('ordenes.imprimir');
		Route::get('pdf/{id}/{name}', 'OrdenesController@Imprimir')->name('ordenes.imprimir.nombre');
		Route::post('{id}/anular', 'OrdenesController@anular')->name('ordenes.anular');
		Route::post('{id}/facturar', 'OrdenesController@facturar')->name('ordenes.facturar');
		Route::get('{id}/enviar', 'OrdenesController@enviar')->name('ordenes.enviar');
		Route::get('/datatable/producto/{producto}', 'OrdenesController@datatable_producto')->name('ordenes.datatable.producto');
		Route::get('/datatable/cliente/{cliente}', 'OrdenesController@datatable_cliente')->name('ordenes.datatable.cliente');
	});
	Route::resource('ordenes', 'OrdenesController');

	//BANCOS

	Route::get('/bancos/datatable/{id}', 'BancosController@datatable_movimientos')->name('bancos.movimientos.cuenta');
	Route::get('/bancos/datatable/cliente/{cliente}', 'BancosController@datatable_movimientos_cliente')->name('bancos.cliente.movimientos.cuenta');
	Route::get('/bancos/transferencia/{id}', 'BancosController@create_transferencia')->name('bancos.transferencia');
	Route::post('/bancos/transferencia/{id}', 'BancosController@store_transferencia')->name('bancos.transferencia');
	Route::post('/bancos/act_desac/{id}', 'CategoriasController@default')->name('bancos.act_desac');
	Route::resource('bancos', 'BancosController');

    //PAGOS RECURRENTES

	Route::resource('pagosrecurrentes', 'PagosRecurrentesController');
	Route::get('pagosrecurrentes/pagar/{gasto}', 'PagosRecurrentesController@ingreso')->name('pagosR.ingreso');
	Route::post('pagosrecurrentes/ingreso/', 'PagosRecurrentesController@pagar')->name('pagosRecu.pagar');
    Route::get('pagosrecurrentes/imprimir/{gasto}', 'PagosRecurrentesController@imprimir')->name('pagosR.imprimir');
    Route::post('pagosrecurrentes/anular/{gasto}', 'PagosRecurrentesController@anular')->name('pagosR.anular');
    Route::post('pagosrecurrentes/destroy_p/{gasto}', 'PagosRecurrentesController@destroy_pago')->name('pagosR.destroyP');
	Route::post('pagosrecurrentes/ingreso/', 'PagosRecurrentesController@pagar')->name('pagosRecu.pagar');
	Route::get('pagosrecurrentes/{id}/act_des', 'PagosRecurrentesController@act_des')->name('pagosrecurrentes.act_des');

	Route::group(['prefix' => 'categorias'], function() {
		Route::get('/create/{id}', 'CategoriasController@create')->name('categorias.create_id');
		Route::post('/create/{id}/act_desc', 'CategoriasController@act_desc')->name('categorias.act_desc');
		Route::post('/default/{id}', 'CategoriasController@default')->name('categorias.default');
		Route::post('/quitar', 'CategoriasController@quitar')->name('categorias.quitar');


	});

	Route::resource('categorias', 'CategoriasController');
	Route::resource('puc', 'PucController');
	Route::group(['prefix' => 'configuracion'], function() {
		Route::get('/terminos', 'ConfiguracionController@terminos')->name('configuracion.terminos');
		Route::get('/termino/create', 'ConfiguracionController@terminos_create')->name('termino.create');
		Route::post('/termino/store', 'ConfiguracionController@terminos_store')->name('termino.store');
		Route::get('/termino/{id}/edit', 'ConfiguracionController@terminos_edit')->name('termino.edit');
		Route::post('/termino/{id}/edit', 'ConfiguracionController@terminos_update')->name('termino.update');
		Route::delete('/termino/{id}', 'ConfiguracionController@terminos_destroy')->name('termino.destroy');
		Route::get('/numeraciones', 'ConfiguracionController@numeraciones')->name('configuracion.numeraciones');
		Route::post('/numeraciones', 'ConfiguracionController@numeraciones_store')->name('configuracion.numeraciones');
		Route::get('/numeraciones/create', 'ConfiguracionController@numeraciones_create')->name('numeraciones.create');
		Route::post('/numeraciones/store', 'ConfiguracionController@numeraciones_store')->name('numeraciones.store');
		Route::get('/numeracion/{id}/edit', 'ConfiguracionController@numeraciones_edit')->name('numeraciones.edit');
		Route::post('/numeracion/{id}/edit', 'ConfiguracionController@numeraciones_update')->name('numeraciones.update');
		Route::get('/numeraciones/dian', 'ConfiguracionController@numeraciones_dian')->name('configuracion.numeraciones_dian');
		Route::post('/numeracionesdian/store', 'ConfiguracionController@numeraciones_dian_store')->name('numeraciones_dian.store');
		Route::get('/numeracionesdian/create', 'ConfiguracionController@numeraciones_dian_create')->name('numeraciones_dian.create');
		Route::get('/numeracionesdian/{id}/edit', 'ConfiguracionController@numeraciones_dian_edit')->name('numeraciones_dian.edit');
		Route::post('/numeracionesdian/{id}/edit', 'ConfiguracionController@numeraciones_dian_update')->name('numeraciones_dian.update');
		Route::delete('/numeracion/{id}', 'ConfiguracionController@numeraciones_destroy')->name('numeraciones.destroy');
		Route::post('/numeracion/{id}/act_desc', 'ConfiguracionController@numeraciones_act_desc')->name('numeraciones.act_desc');
		Route::get('/datos', 'ConfiguracionController@datos')->name('configuracion.datos');
		Route::post('/datos/store', 'ConfiguracionController@datos_store')->name('datos.store');
		Route::post('/vendedores/{id}/act_desc', 'VendedoresController@act_desc')->name('vendedores.act_desc');
		Route::post('/impuestos/{id}/act_desc', 'ImpuestosController@act_desc')->name('impuestos.act_desc');
		Route::post('/retenciones/{id}/act_desc', 'RetencionesController@act_desc')->name('retenciones.act_desc');
		Route::post('/usuarios/{id}/act_desc', 'UsuariosController@act_desc')->name('usuarios.act_desc');
		Route::get('/empresa', 'ConfiguracionController@empresa')->name('configuracion.empresa');
		Route::post('/empresa/store', 'ConfiguracionController@store')->name('configuracion.empresa.store');
		Route::get('/miusuario', 'ConfiguracionController@miusuario')->name('miusuario');
		Route::post('/miusuario', 'ConfiguracionController@miusuario_store')->name('miusuario');
		
		Route::get('/recarga-saldo', 'UsuariosController@saldo')->name('recarga.index');
		Route::post('/{id}/reiniciar-saldo','UsuariosController@reiniciarSaldo')->name('recarga.reiniciar');

        Route::post('/permisosUsuario','UsuariosController@verPermisos');
        
        Route::post('/saldoUsuario','UsuariosController@verSaldo');
        Route::post('/guardarSaldo','UsuariosController@guardarSaldo')->name('recarga.usuario');
        
        Route::post('/gananciaUsuario','UsuariosController@verGanancia');
        Route::post('/guardarGanancia','UsuariosController@guardarGanancia')->name('ganancia.usuario');

        Route::post('/check_FE','ConfiguracionController@form_facturacion');

        Route::post('/guardarPermisos','UsuariosController@guardarPermisos')->name('permisos.guardar');
        Route::post('roles/eliminar', 'RolesController@eliminar')->name('roles.eliminar');
        Route::resource('roles', 'RolesController');

		Route::resource('vendedores', 'VendedoresController');
		Route::resource('impuestos', 'ImpuestosController');
		Route::resource('retenciones', 'RetencionesController');
		Route::resource('usuarios', 'UsuariosController');
		Route::resource('tiposempresa', 'TiposEmpresaController');
		Route::post('Typestoreback','TiposEmpresaController@storeback')->name('tiposempresa.storeback');

		Route::get('/personalizar_inventario/organizar', 'CamposPersonalizadosInventarioController@organizar')->name('personalizar_inventario.organizar');
		Route::post('/personalizar_inventario/organizar_store', 'CamposPersonalizadosInventarioController@organizar_store')->name('personalizar_inventario.organizar_store');
		Route::post('/personalizar_inventario/{id}/act_desc', 'CamposPersonalizadosInventarioController@act_desc')->name('personalizar_inventario.act_desc');
		Route::resource('personalizar_inventario', 'CamposPersonalizadosInventarioController');
		
		/* SERVICIOS */
		Route::get('/servicios', 'ConfiguracionController@servicios')->name('configuracion.servicios');
		Route::get('/servicio/create', 'ConfiguracionController@servicios_create')->name('servicio.create');
		Route::post('/servicio/store', 'ConfiguracionController@servicios_store')->name('servicio.store');
		Route::get('/servicio/{id}/edit', 'ConfiguracionController@servicios_edit')->name('servicio.edit');
		Route::post('/servicio/{id}/edit', 'ConfiguracionController@servicios_update')->name('servicio.update');
		Route::delete('/servicio/{id}', 'ConfiguracionController@servicios_destroy')->name('servicio.destroy');
		
		Route::get('usuarios/ingresar/{email}','UsuariosController@ingresar')->name('usuario.ingresarR');

		/*ORGANIZAR CAMPO TABLAS*/
		Route::get('/campos/{modulo}/organizar', 'CamposController@organizar')->name('campos.organizar');
		Route::post('/campos/organizar_store', 'CamposController@organizar_store')->name('campos.organizar_store');
		Route::resource('campos', 'CamposController');

		//PUERTOS

		Route::resource('puertos-conexion', 'PuertosController');
		Route::get('puertos-conexion/{id}/act_desc', 'PuertosController@act_desc')->name('puertos-conexion.act_des');
	});

	Route::post('/storetipocontactoajax','TiposEmpresaController@storeTipoContactoAjax')->name('configuracion.tipocontactoajax');

	Route::resource('configuracion', 'ConfiguracionController');
	Route::resource('soporte', 'SoporteController');

	//Reportes
	Route::group(['prefix' => 'reportes'], function() {
		Route::get('/', 'ReportesController@index')->name('reportes.index');
		Route::get('/ventas', 'ReportesController@ventas')->name('reportes.ventas');
		Route::get('/ventasItem', 'ReportesController@ventasItem')->name('reportes.ventasItem');
		Route::get('/comprasProveedor', 'ReportesController@comprasProveedor')->name('reportes.comprasProveedor');
		Route::get('/ventasCliente', 'ReportesController@ventasCliente')->name('reportes.ventasCliente');
		Route::get('/remisionesCliente', 'ReportesController@remisionesCliente')->name('reportes.remisionesCliente');
		Route::get('/cuentasCobrar', 'ReportesController@cuentasCobrar')->name('reportes.cuentasCobrar');
		Route::get('/cuentasPagar', 'ReportesController@cuentasPagar')->name('reportes.cuentasPagar');
		Route::get('/compras', 'ReportesController@compras')->name('reportes.compras');
		Route::get('/ventasVendedor', 'ReportesController@ventasVendedor')->name('reportes.ventasVendedor');
		Route::get('/rentabilidaditem', 'ReportesController@rentabilidadItem')->name('reportes.rentabilidadItem');
		Route::get('/transacciones', 'ReportesController@transacciones')->name('reportes.transacciones');
		Route::get('/valorActual', 'ReportesController@valorActual')->name('reportes.valorActual');
		Route::get('/ingresosEgresos', 'ReportesController@ingresosEgresos')->name('reportes.ingresosEgresos');
		Route::get('/categorias', 'ReportesController@categorias')->name('reportes.categorias');
		Route::get('/categoriasp', 'ReportesController@getPagosCategorias')->name('reportes.categoriasp');
		Route::get('/reportediario', 'ReportesController@getReporteDiario')->name('reportes.diario');
        Route::get('/reteiva', 'ReportesController@getReporteReteIva')->name('reportes.reteiva');
        Route::get('/facturasRemisiones', 'ReportesController@getReporteRemisionesFacturas')->name('reportes.facturasRemisiones');
        Route::get('/pagosFacRemi', 'ReportesController@getTotalPagos')->name('reportes.pagosFacRemi');
		Route::get('/reportecontactos', 'ReportesController@getReporteContactos')->name('reportes.contactos');
		Route::get('/cajas', 'ReportesController@cajas')->name('reportes.cajas');
		Route::get('/instalacion', 'ReportesController@instalacion')->name('reportes.instalacion');
		Route::get('/facturas-impagas', 'ReportesController@facturasImpagas')->name('reportes.facturasImpagas');
		Route::get('/radicados', 'ReportesController@radicados')->name('reportes.radicados');
		Route::get('/recargas', 'ReportesController@recargas')->name('reportes.recargas');
		Route::get('/puntos-de-ventas-ganancias', 'ReportesController@puntoVenta')->name('reportes.puntoVenta');
		Route::get('/puntos-de-ventas-recaudos', 'ReportesController@puntoVentaRecaudo')->name('reportes.puntoVentaRecaudo');

		//Rutas para modulo estado de cuenta cliente
		Route::get('/estadoCliente', 'ReportesController@estadoCliente')->name('reportes.estadoCliente');
		Route::get('/estadoCliente/consulta', 'ReportesController@estadoClienteShow')->name('reportes.estadoClienteShow');
	});
	//Exportar
	Route::group(['prefix' => 'exportar'], function() {
		Route::get('/ventas', 'ExportarReportesController@ventas')->name('exportar.ventas');
		Route::get('/ventasItem', 'ExportarReportesController@ventasItem')->name('exportar.ventasItem');
		Route::get('/ventasCliente', 'ExportarReportesController@ventasCliente')->name('exportar.ventasCliente');
		Route::get('/remisionesCliente', 'ExportarReportesController@remisionesCliente')->name('exportar.remisionesCliente');
		Route::get('/cuentasCobrar', 'ExportarReportesController@cuentasCobrar')->name('exportar.cuentasCobrar');
		Route::get('/cuentasPagar', 'ExportarReportesController@cuentasPagar')->name('exportar.cuentasPagar');
		Route::get('/compras', 'ExportarReportesController@compras')->name('exportar.compras');
		Route::get('/estadoCliente', 'ExportarReportesController@estadoCliente')->name('exportar.compras');
		Route::post('/reporteDiario', 'ExportarReportesController@reporteDiario')->name('exportar.reporteDiario');
		Route::get('/ventasVendedor','ExportarReportesController@ventasVendedor')->name('exportar.ventasVendedor');
        Route::get('/rentabilidaditem', 'ExportarReportesController@rentabilidadItem')->name('exportar.rentabilidadItem');
        Route::get('/transacciones', 'ExportarReportesController@transacciones')->name('exportar.transacciones');
        Route::get('/valorActual', 'ExportarReportesController@valorActual')->name('exportar.valorActual');
        Route::get('/ingresosEgresos', 'ExportarReportesController@ingresosEgresos')->name('exportar.ingresosEgresos');
        Route::get('/categorias', 'ExportarReportesController@categorias')->name('exportar.categorias');
        Route::get('/contactos', 'ExportarReportesController@contactos')->name('exportar.contactos');
        Route::get('/cajas', 'ExportarReportesController@cajas')->name('exportar.cajas');
        Route::get('/facturas-impagas', 'ExportarReportesController@facturasImpagas')->name('exportar.facturasImpagas');
        Route::get('/recargas', 'ExportarReportesController@recargas')->name('exportar.recargas');
        Route::get('/puntos-de-ventas-ganancias', 'ExportarReportesController@puntoVenta')->name('exportar.puntoVenta');
        Route::get('/puntos-de-ventas-recaudos', 'ExportarReportesController@puntoVentaRecaudo')->name('exportar.puntoVentaRecaudo');

		//rutas para modulo estado de cuenta cliente
		Route::get('/estadoCliente', 'ExportarReportesController@estadoCliente')->name('exportar.estadoCliente');
	});

	//Documentacion escrita
	Route::get("Documentacion",function(){
		$title="Documentacion";
	return view("documentacion.index",compact('title'));
})->name("documentacion");

	//Ruta tablero de google
Route::get('/GoogleAnalytics', 'GoogleAnalyticsController@index')->name('Google.index');

    //RADICADOS
        Route::group(['prefix' => 'radicados'], function() {
        	Route::post('/escalar/{id}', 'RadicadosController@escalar')->name('radicados.escalar');
        	Route::post('/solventar/{id}', 'RadicadosController@solventar')->name('radicados.solventar');
        	Route::get('/{id}/imprimir', 'RadicadosController@imprimir')->name('radicados.imprimir');
        	Route::get('/{id}/firmar', 'RadicadosController@firmar')->name('radicados.firmar');
        	Route::post('/{id}/storefirma', 'RadicadosController@storefirma')->name('radicados.storefirma');
        	Route::get('/datatable/cliente/{cliente}', 'RadicadosController@datatable_cliente')->name('radicados.datatable.cliente');
        	Route::get('/notificacionRadicado', 'RadicadosController@notificacion')->name('radicados.notificacion');
        	Route::post('/proceder/{id}', 'RadicadosController@proceder')->name('radicados.proceder');
        	Route::get('{cliente}/create', 'RadicadosController@create')->name('radicados.create_cliente');
        });
		Route::resource('radicados', 'RadicadosController');

	//SOLICITUDES

    	Route::group(['prefix' => 'solicitudes'], function() {
	    	Route::post('/solicitudes/status/{id}', 'SolicitudesController@status')->name('solicitudes.status');
    	});
    	Route::resource('solicitudes', 'SolicitudesController');
    	
    //CONTRATOS

	Route::group(['prefix' => 'contratos'], function() {
		Route::post('{id}/state', 'ContratosController@state')->name('contratos.state');
		Route::get('corte/{corte}', 'ContratosController@index')->name('contratos.corte');
		Route::get('exportar', 'ContratosController@exportar')->name('contratos.exportar');
		Route::get('exportar', 'ContratosController@exportar')->name('contratos.exportar');
		Route::get('{id}/grafica', 'ContratosController@grafica')->name('contratos.grafica');
		Route::get('{id}/graficajson', 'ContratosController@graficajson')->name('contratos.graficajson');
		Route::get('{id}/ping', 'ContratosController@conexion')->name('contratos.conexion');
		Route::get('{id}/destroy_to_mk', 'ContratosController@destroy_to_mk')->name('contratos.destroy_to_mk');
		Route::get('{id}/log', 'ContratosController@log')->name('contratos.log');
		Route::get('{id}/pings', 'ContratosController@conexion')->name('contratos.ping');
		Route::get('{id}/ping_nuevo', 'ContratosController@ping_nuevo')->name('contratos.ping_nuevo');
		Route::get('{id}/grafica-consumo', 'ContratosController@grafica_consumo')->name('contratos.grafica_consumo');
		Route::get('{cliente}/create', 'ContratosController@create')->name('contratos.create_cliente');
		Route::get('deshabilitados', 'ContratosController@disabled')->name('contratos.disabled');
		Route::get('habilitados', 'ContratosController@enabled')->name('contratos.enabled');
	});
	Route::resource('contratos', 'ContratosController');
	
	//SERVIDORES

	Route::group(['prefix' => 'servidores'], function() {
		Route::post('{id}/aplicar_cambios', 'ServidoresController@aplicar_cambios')->name('servidores.aplicar_cambios');
		Route::get('{id}/grafica', 'ServidoresController@grafica')->name('servidores.grafica');
	});
	Route::resource('servidores', 'ServidoresController');
	
	//ASIGNACIONES

	Route::group(['prefix' => 'asignaciones'], function() {
		Route::get('{id}/imprimir', 'AsignacionesController@imprimir')->name('asignaciones.imprimir');
		Route::get('config_campos_asignacion', 'AsignacionesController@show_campos_asignacion')->name('asignaciones.show_campos_asignacion');
		Route::post('campos_asignacion', 'AsignacionesController@campos_asignacion')->name('asignaciones.campos_asignacion');
	});
	Route::resource('asignaciones', 'AsignacionesController');
	
	//MENSAJERÍA
    	Route::group(['prefix' => 'mensajeria'], function() {
	    	Route::post('{id}/status', 'MensajeriaController@status')->name('mensajeria.status');
	    	Route::get('enviar', 'MensajeriaController@enviar')->name('mensajeria.enviar');
    	});
    	Route::resource('mensajeria', 'MensajeriaController');
    	
    //NOTIFICACIONES
    	Route::group(['prefix' => 'notificaciones'], function() {
	    	Route::post('{id}/status', 'NotificacionesController@status')->name('notificaciones.status');
	    	Route::get('enviar', 'NotificacionesController@enviar')->name('notificaciones.enviar');
    	});
    	Route::resource('notificaciones', 'NotificacionesController');
    	
    //WIFI
        Route::group(['prefix' => 'wifi'], function() {
		    Route::post('/{id}/status', 'WifiController@status')->name('wifi.status');
	    });
	    Route::get('/notificacionWifi', 'WifiController@notificacionWifi')->name('wifi.notificacion');

	    Route::resource('wifi', 'WifiController');
	//AUDITORIA
	Route::resource('auditorias', 'AuditoriasController');
	
	//PQRS
		Route::resource('pqrs', 'PqrsController');
		
	//PLANES DE VELOCIDAD
        Route::group(['prefix' => 'planes-velocidad'], function (){
	        Route::get('/plan-velocidad/{id}/status', 'PlanesVelocidadController@status')->name('planes-velocidad.status');
	        Route::get('/plan-velocidad/{id}/reglas', 'PlanesVelocidadController@reglas')->name('planes-velocidad.reglas');
	        Route::get('{id}/aplicar-cambios', 'PlanesVelocidadController@aplicar_cambios')->name('planes-velocidad.aplicar-cambios');
	        Route::get('{nro}/aplicando-cambios', 'PlanesVelocidadController@aplicando_cambios')->name('planes-velocidad.aplicando-cambios');
	    });
	    
	    Route::resource('planes-velocidad', 'PlanesVelocidadController');
	    
	// MIKROTIK
	    Route::group(['prefix' => 'mikrotik'], function (){
	        Route::get('/mikrotik/{id}/conectar', 'MikrotikController@conectar')->name('mikrotik.conectar');
	        Route::get('/mikrotik/{id}/reglas', 'MikrotikController@reglas')->name('mikrotik.reglas');
	        Route::get('/mikrotik/{id}/importar', 'MikrotikController@importar')->name('mikrotik.importar');
	        Route::get('/mikrotik/{id}/reiniciar', 'MikrotikController@reiniciar')->name('mikrotik.reiniciar');
	        Route::get('/{id}/log', 'MikrotikController@log')->name('mikrotik.log');
	        Route::get('{id}/grafica', 'MikrotikController@grafica')->name('mikrotik.grafica');
	        Route::get('{id}/graficajson', 'MikrotikController@graficajson')->name('mikrotik.graficajson');
	        Route::get('{id}/ips-autorizadas', 'MikrotikController@ips_autorizadas')->name('mikrotik.ips-autorizadas');
	        Route::get('{nro}/autorizar-ips', 'MikrotikController@autorizar_ips')->name('mikrotik.autorizar-ips');
	    });
	    
	    Route::resource('mikrotik', 'MikrotikController');
	    
	// PLANTILLAS
	    Route::group(['prefix' => 'plantillas'], function (){
	        Route::post('/{id}/act_desc', 'PlantillasController@act_desc')->name('plantillas.act_desc');
	        Route::get('/envio', 'PlantillasController@envio')->name('plantillas.envio');
	        Route::post('/envio_aviso', 'PlantillasController@envio_aviso')->name('plantillas.envio_aviso');
	    });
	    
	    Route::resource('plantillas', 'PlantillasController');
	    
	// AVISOS
	    Route::group(['prefix' => 'avisos'], function (){
	        Route::get('/envio/sms', 'AvisosController@sms')->name('avisos.envio.sms');
	        Route::get('/envio/email', 'AvisosController@email')->name('avisos.envio.email');
	        Route::post('/envio_aviso', 'AvisosController@envio_aviso')->name('avisos.envio_aviso');
	        Route::get('/envio/{id}/email', 'AvisosController@email')->name('avisos.envio.email.cliente');
	        Route::get('/envio/{id}/sms', 'AvisosController@sms')->name('avisos.envio.sms.cliente');
	    });
	    
	    Route::resource('avisos', 'AvisosController');
	    
	// PROMESAS DE PAGO
	    Route::group(['prefix' => 'promesas-pago'], function (){
	        Route::get('{id}/json', 'PromesasPagoController@json')->name('promesas.json');
	        Route::get('pdf/{id}/{name}', 'PromesasPagoController@Imprimir')->name('promesas.imprimir');
	    });
	    
	    Route::resource('promesas-pago', 'PromesasPagoController');
	    
	// NODOS
	    Route::group(['prefix' => 'nodos'], function (){
	        Route::get('{id}/act_des', 'NodosController@act_des')->name('nodos.act_des');
	    });
	    
	    Route::resource('nodos', 'NodosController');
	
	// ACCESS POINT (AP)
	    Route::group(['prefix' => 'access-point'], function (){
	        Route::get('{id}/act_des', 'APController@act_des')->name('access-point.act_des');
	    });
	    
	    Route::resource('access-point', 'APController');
	    
	//PING
	    Route::get('/notificacionPing', 'Controller@getNotificaciones')->name('pings.notificacion');
	    Route::resource('pings', 'PingsController');
	    
    // GRUPOS DE CORTE
	    Route::group(['prefix' => 'grupos-corte'], function (){
	        Route::get('{id}/act_des', 'GruposCorteController@act_des')->name('grupos-corte.act_des');
	    });
	    
	    Route::resource('grupos-corte', 'GruposCorteController');

	// DESCUENTOS
	    Route::group(['prefix' => 'descuentos'], function (){
	        Route::post('/aprobar', 'DescuentosController@aprobar')->name('descuentos.aprobar');
	    });

	    Route::resource('descuentos', 'DescuentosController');

	// TIPOS DE GASTOS
	    Route::group(['prefix' => 'tipos-gastos'], function (){
	        Route::get('{id}/act_des', 'TiposGastosController@act_des')->name('tipos-gastos.act_des');
	    });

	    Route::resource('tipos-gastos', 'TiposGastosController');

	//CRM
	    Route::group(['prefix' => 'crm'], function() {
	        Route::get('/cartera', 'CRMController@cartera')->name('crm.cartera');
	        Route::get('{id}/contacto', 'CRMController@contacto')->name('crm.contacto');
	        Route::get('/informe', 'CRMController@informe')->name('crm.informe');
	        Route::get('exportar', 'CRMController@exportar')->name('crm.exportar');
	        Route::get('/notificacion','CRMController@notificacion')->name('crm.notificacion');
	        Route::get('/status/{id}', 'CRMController@status')->name('crm.status');
	    });
		Route::resource('crm', 'CRMController');

	//SERVIDOR DE CORREO
	    Route::resource('servidor-correo', 'ServidorCorreoController');
});
