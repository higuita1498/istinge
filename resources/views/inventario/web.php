<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('', 'HomeController@home')->name('home');
Route::get('/home', 'HomeController@home')->name('home');
Route::get('/carrito/{empresa}', 'HomeController@carrito')->name('carrito');

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login')->name('login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');

//generador_facturas_recurrentes
Route::get('4bb4ccecbb7823c435b493195a785829', 'RecurrentesController@generar_factura')->name('generar_factura');
Route::get('3bd8497e1b07070fc7e1927d29669055', 'PagosRecurrentesController@generar_pagos')->name('generar_pagos');


Route::get('olvido_pass', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@enviar')->name('password.email');
Route::get('change_pass/{token}', 'Auth\ResetPasswordController@recuperar_pass')->name('pass.change');
Route::post('save_pass', 'Auth\ResetPasswordController@cambiar_pass')->name('pass.save');

Route::group(['prefix' => 'master', 'middleware' => ['auth', 'master']], function() {
	Route::get('/', 'HomeController@index')->name('master');
	Route::group(['prefix' => 'empresas'], function() {
		Route::post('desactivar/{id}', 'EmpresasController@desactivar')->name('empresas.desactivar');
        Route::post('activar/{id}', 'EmpresasController@activar')->name('empresas.activar');
        Route::get('inactivas', 'EmpresasController@inactivas')->name('empresas.inactivas');
	});
	Route::resource('empresas', 'EmpresasController');
	Route::get('edit', 'UsuariosController@my_edit')->name('user.editar');
	Route::post('edit', 'UsuariosController@my_update')->name('user.editar');
	Route::resource('atencionsoporte', 'SoporteController');
});

Route::group(['prefix' => 'empresa', 'middleware' => ['auth']], function() {
	Route::get('/', 'HomeController@index')->name('empresa');
	Route::group(['prefix' => 'contactos'], function() {
		Route::get('clientes', 'ContactosController@clientes')->name('contactos.clientes');
		Route::get('proveedores', 'ContactosController@proveedores')->name('contactos.proveedores');
		Route::get('clientes/json', 'ContactosController@json')->name('contactos.clientes.json');
		Route::get('{id}/json', 'ContactosController@json')->name('contactos.json');
		Route::get('exportar/{tipo?}', 'ContactosController@exportar')->name('contactos.exportar');
		Route::get('importar', 'ContactosController@importar')->name('contactos.importar');
		Route::get('ejemplo', 'ContactosController@ejemplo')->name('contactos.ejemplo');
		Route::post('importar', 'ContactosController@cargando')->name('contactos.importar');
		Route::get('/create/modal', 'ContactosController@create_modal')->name('contactos.create.modal');
	});
	Route::resource('contactos', 'ContactosController');


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
	Route::resource('logistica', 'LogisticaController');

	//Facturas de Venta

	Route::group(['prefix' => 'facturas'], function() {
		Route::get('/cliente/{id}', 'FacturasController@cliente_factura_json')->name('facturas.cliente.json');
		Route::get('/{id}/json', 'FacturasController@factura_json')->name('facturas.json');
		Route::get('/{id}/clientejson', 'FacturasController@cliente_factura_json_all')->name('facturas.clientejson');
		Route::get('/{id}/facturajson', 'FacturasController@items_factura_json')->name('facturas.facturajson');
		Route::get('/{id}/copia', 'FacturasController@copia')->name('facturas.copia');
		Route::get('/{id}/pdf', 'FacturasController@pdf')->name('facturas.pdf');
		Route::get('/{id}/copia', 'FacturasController@copia')->name('facturas.copia');
		Route::get('/{id}/imprimir', 'FacturasController@Imprimir')->name('facturas.imprimir');
		Route::get('/{id}/imprimircopia', 'FacturasController@Imprimircopia')->name('facturas.imprimircopia');
		Route::get('/{id}/enviar', 'FacturasController@enviar')->name('facturas.enviar');
		Route::get('/{id}/enviarcopia', 'FacturasController@enviarcopia')->name('facturas.enviarcopia');		
		Route::get('/create_cliente/{cliente}', 'FacturasController@create_cliente')->name('factura.create.cliente');
		Route::get('/create_item/{producto}', 'FacturasController@create_item')->name('factura.create_item');
		Route::get('/datatable/producto/{producto}', 'FacturasController@datatable_producto')->name('factura.datatable.producto');		
		Route::get('/datatable/cliente/{producto}', 'FacturasController@datatable_cliente')->name('factura.datatable.cliente');	
		Route::post('{id}/anular', 'FacturasController@anular')->name('factura.anular');	
		Route::post('{id}/cerrar', 'FacturasController@cerrar')->name('factura.cerrar');


	});
	Route::resource('facturas', 'FacturasController');

	//Cotizaciones
	
	Route::group(['prefix' => 'cotizaciones'], function() {
		Route::get('/{id}/imprimir', 'CotizacionesController@Imprimir')->name('cotizaciones.imprimir');
		Route::get('/{id}/enviar', 'CotizacionesController@enviar')->name('cotizaciones.enviar');
		Route::get('/{id}/facturar', 'CotizacionesController@facturar')->name('cotizaciones.facturar');
		Route::get('/datatable/producto/{producto}', 'CotizacionesController@datatable_producto')->name('cotizaciones.datatable.producto');
		Route::get('/datatable/cliente/{cliente}', 'CotizacionesController@datatable_cliente')->name('cotizaciones.datatable.cliente');
	});
	Route::resource('cotizaciones', 'CotizacionesController');
	Route::group(['prefix' => 'ingresos'], function() {
		Route::get('/create/{cliente}/{factura}', 'IngresosController@create')->name('ingresos.create_id');
		Route::get('/create_cuenta/{cuenta}', 'IngresosController@create')->name('ingresos.create_cuenta');

		Route::get('/pendiente/{cliente}/{id?}', 'IngresosController@pendiente')->name('ingresos.pendiente');
		Route::get('/ingpendiente/{cliente}/{id?}', 'IngresosController@ingpendiente')->name('ingpendiente.pendiente');
		Route::get('/{id}/imprimir', 'IngresosController@Imprimir')->name('ingresos.imprimir');
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
		Route::get('/{id}/enviar', 'IngresosRController@enviar')->name('ingresosr.enviar');
		Route::post('{id}/anular', 'IngresosRController@anular')->name('ingresosr.anular');
	});

	Route::group(['prefix' => 'notascredito'], function() {
		Route::get('/{id}/imprimir', 'NotascreditoController@Imprimir')->name('notascredito.imprimir');
		Route::get('/{id}/enviar', 'NotascreditoController@enviar')->name('notascredito.enviar');

		Route::get('/datatable/producto/{producto}', 'NotascreditoController@datatable_producto')->name('notascredito.datatable.producto');
		Route::get('/datatable/cliente/{cliente}', 'NotascreditoController@datatable_cliente')->name('notascredito.datatable.cliente');
	});
	Route::resource('notascredito', 'NotascreditoController');
	Route::group(['prefix' => 'remisiones'], function() {
		Route::get('/{id}/imprimir', 'RemisionesController@Imprimir')->name('remisiones.imprimir');
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
		Route::get('/{id}/enviar', 'PagosController@enviar')->name('pagos.enviar');
		Route::post('{id}/anular', 'PagosController@anular')->name('pagos.anular');
	});

	 

	Route::resource('pagos', 'PagosController');
	Route::group(['prefix' => 'facturasp'], function() {
		Route::get('/proveedor/{id}', 'FacturaspController@proveedor_factura_json')->name('facturasp.proveedor.json');
		Route::get('/{id}/json', 'FacturaspController@facturap_json')->name('facturasp.json');


		Route::get('/create_proveedor/{proveedor}', 'FacturaspController@create')->name('facturasp.create.proveedor');
		Route::get('/create_item/{producto}', 'FacturaspController@create_item')->name('facturasp.create_item');
		Route::get('/datatable/producto/{producto}', 'FacturaspController@datatable_producto')->name('facturasp.datatable.producto');
		Route::get('/datatable/cliente/{producto}', 'FacturaspController@datatable_cliente')->name('facturap.datatable.cliente');	
	});	


	Route::resource('facturasp', 'FacturaspController');
	Route::group(['prefix' => 'notasdebito'], function() {
		Route::get('/datatable/producto/{producto}', 'NotasdebitoController@datatable_producto')->name('notasdebito.datatable.producto');
		Route::get('/datatable/cliente/{cliente}', 'NotasdebitoController@datatable_cliente')->name('notasdebito.datatable.cliente');
	});

	Route::resource('notasdebito', 'NotasdebitoController');


	Route::group(['prefix' => 'ordenes'], function() {
		Route::get('{id}/imprimir', 'OrdenesController@Imprimir')->name('ordenes.imprimir');
		Route::post('{id}/anular', 'OrdenesController@anular')->name('ordenes.anular');
		Route::post('{id}/facturar', 'OrdenesController@facturar')->name('ordenes.facturar');
		Route::get('{id}/enviar', 'OrdenesController@enviar')->name('ordenes.enviar');
		Route::get('/datatable/producto/{producto}', 'OrdenesController@datatable_producto')->name('ordenes.datatable.producto');
		Route::get('/datatable/cliente/{cliente}', 'OrdenesController@datatable_cliente')->name('ordenes.datatable.cliente');	
	});		
		
	
	
	Route::resource('ordenes', 'OrdenesController');
	Route::get('/bancos/datatable/{id}', 'BancosController@datatable_movimientos')->name('bancos.movimientos.cuenta');
	Route::get('/bancos/datatable/cliente/{cliente}', 'BancosController@datatable_movimientos_cliente')->name('bancos.cliente.movimientos.cuenta');

	Route::get('/bancos/transferencia/{id}', 'BancosController@create_transferencia')->name('bancos.transferencia');
	Route::post('/bancos/transferencia/{id}', 'BancosController@store_transferencia')->name('bancos.transferencia');
	Route::post('/bancos/act_desac/{id}', 'CategoriasController@default')->name('bancos.act_desac');

	Route::resource('bancos', 'BancosController');
	Route::resource('pagosrecurrentes', 'PagosRecurrentesController');
	Route::group(['prefix' => 'categorias'], function() {
		Route::get('/create/{id}', 'CategoriasController@create')->name('categorias.create_id');
		Route::post('/create/{id}/act_desc', 'CategoriasController@act_desc')->name('categorias.act_desc');
		Route::post('/default/{id}', 'CategoriasController@default')->name('categorias.default');
		Route::post('/quitar', 'CategoriasController@quitar')->name('categorias.quitar');

		
	});

	Route::resource('categorias', 'CategoriasController');
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
		Route::resource('vendedores', 'VendedoresController');
		Route::resource('impuestos', 'ImpuestosController');
		Route::resource('retenciones', 'RetencionesController');
		Route::resource('usuarios', 'UsuariosController');
		Route::resource('tiposempresa', 'TiposEmpresaController');
		Route::get('/personalizar_inventario/organizar', 'CamposPersonalizadosInventarioController@organizar')->name('personalizar_inventario.organizar');

		Route::post('/personalizar_inventario/organizar_store', 'CamposPersonalizadosInventarioController@organizar_store')->name('personalizar_inventario.organizar_store');
		
		Route::post('/personalizar_inventario/{id}/act_desc', 'CamposPersonalizadosInventarioController@act_desc')->name('personalizar_inventario.act_desc');
		Route::resource('personalizar_inventario', 'CamposPersonalizadosInventarioController');

	});

	Route::resource('configuracion', 'ConfiguracionController');
	Route::resource('soporte', 'SoporteController');

	//Reportes
	Route::group(['prefix' => 'reportes'], function() {
		Route::get('/', 'ReportesController@index')->name('reportes.index');
		Route::get('/ventas', 'ReportesController@ventas')->name('reportes.ventas');
	});
	//Exportar
	Route::group(['prefix' => 'exportar'], function() {
		Route::get('/ventas', 'ExportarReportesController@ventas')->name('exportar.ventas');
	});

});
