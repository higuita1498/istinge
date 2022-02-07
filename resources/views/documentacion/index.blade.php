<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="Juan José Tuiran S.A.S">
	<meta name="keyword" content="">
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<title></title>
	<link rel="stylesheet" href="{{asset('css/style.css')}}">
	<link rel="stylesheet" href="{{asset('css/documentacion.css')}}">
	<link href="{{asset('vendors/fontawesome/css/all.css')}}" rel="stylesheet" />
</head>
<body>

</body>
</html>
<div class="content-wrapper">
	<center><h1><img src="/images/favicon.png" style="margin-right: 5px;">PRIMEROS PASOS PARA UTILIZAR NUESTRO SISTEMA<img src="/images/favicon.png" style="margin-left: 5px;"></h1></center>
	<center>
		<div class="card-body color card-body-margen card-body-documentacion">
			<h1 class="card-title" style="font-size: 20px;">1. FACTURACION</h1>
			<div class="texto">
				<h3 class="card-subtitle">Numeraciones de Documentos</h3>
				<p>
					En este apartado podrás indicar el número con el cual vas a crear tus próximos documentos. Si vienes de otro sistema contable puedes colocar fácilmente en que numeración terminó y comenzar desde ese punto. Recuerda que para modificar las numeraciones tienes que hacer click sobre el boton <button type="button" class="btn btn-primary" onclick="false">Modificar</button> y posteriormente después de terminar de modificar tus numeraciones hacer click sobre el boton <button class="btn btn-success">Guardar</button>
					<img class="img-documentacion" src="/images/Documentacion/doc-1.png">
					Acontinuación te explicaremos que significa cada uno de los anteriores items que puedes ver en numeraciones de documentos:<br><br>
					<strong class="documentacion-strong">Siguiente número de recibos de caja:</strong>Es el siguiente número que vas a tener <br>
					<strong class="documentacion-strong">Siguiente número de comprobantes de pago:</strong><br>
					<strong class="documentacion-strong">Siguiente número de nota crédito:</strong><br>
					<strong class="documentacion-strong">Siguiente número de remisiones:</strong><br>
					<strong class="documentacion-strong">Siguiente número de recibos de caja para remisiones:</strong><br>
					<strong class="documentacion-strong">Siguiente número de cotizaciones: </strong><br>
					<strong class="documentacion-strong">Siguiente número de órdenes de compra:</strong><br>
				</p>
				<h3 class="card-subtitle">Numeración para facturas de venta</h3>
				<p>
					En este apartado podrás crear númeraciones a tu gusto y segun las necesidades de tu negocio. Para poder ver el listado de númeraciones recuerda primeramente agregar una nueva numeración 
					<img class="img-documentacion" src="/images/Documentacion/doc-2.png">
					Para agregar una nueva numeración recuerda hacer click sobre el boton <a href="#" onclick="return false;" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nueva numeración</a> Al hacer click sobre el boton éste te llevará al lugar donde podrás crear tu numeración:
					<img class="img-documentacion" src="/images/Documentacion/doc-3.png">
					Acontinuación te explicaremos que significa cada uno de los anteriores items que puedes ver en nueva numeración:<br><strong><small>(Recuerda que los campos marcados por <span class="text-danger">*</span> son obligatorios)</small></strong><br><br> 
					<strong class="documentacion-strong"><span class="text-danger">*</span>Nombre: </strong><br>
					<strong class="documentacion-strong">Prefijo:</strong><br>
					<strong class="documentacion-strong"><span class="text-danger">*</span>Número inicial:</strong><br>
					<strong class="documentacion-strong">Número final:</strong><br>
					<strong class="documentacion-strong">Vigencia desde:</strong><br>
					<strong class="documentacion-strong">Vigencia hasta:</strong><br>
					<strong class="documentacion-strong">Preferida:</strong><br>
					<strong class="documentacion-strong">Número de resolucion:</strong><br>
					<strong class="documentacion-strong">Resolución:</strong><br><br>
					Al dar click en <button class="btn btn-success">Guardar</button> te redireccionará a númeraciones de documentos y finalmente podrás ver tu primer item en el listado que te ofrece gestordepartes:
					<img class="img-documentacion" src="/images/Documentacion/doc-4.png">
					El campo <strong>Buscar</strong> te permite filtrar por cualquier atributo: Nombre,Preferida,Estado,Resolución,Prefijo y Siguiente número. <br>
					Acciones disponibles para éste item:<br><br>
					<strong class="documentacion-strong">Editar:</strong><a href="#" onclick="return false" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a><br>
					<strong class="documentacion-strong">Eliminar:</strong><button class="btn btn-outline-danger  btn-icons" type="button" title="Eliminar" onclick="return false"><i class="fas fa-times"></i></button><br>
					<strong class="documentacion-strong">Desactivar:</strong><button class="btn btn-outline-secondary  btn-icons" type="button" title="Desactivar" onclick="return false"><i class="fas fa-power-off"></i></button><br>
				</p>
			</div>
		</div>
		<div class="card-body color card-body-margen card-body-documentacion">
			<h1 class="card-title" style="font-size: 20px;">2. EMPRESA</h1>
			<div class="texto">
				<h3 class="card-subtitle">Modificar información de la empresa</h3>
				<p>En este apartado podrás modificar los campos de tu empresa que fueron creados al momento del registro.
					<img class="img-documentacion" src="/images/Documentacion/doc-5.png">
					Acontinuación te explicaremos que significa cada uno de los anteriores items que puedes ver en modificar información de la empresa:<br><strong><small>(Recuerda que los campos marcados por <span class="text-danger">*</span> son obligatorios)</small></strong><br><br>
					<strong class="documentacion-strong">Tipo de identificación:</strong><br>
					<strong class="documentacion-strong">Identificación:</strong><br>
					<strong class="documentacion-strong">Tipo de persona:</strong><br>
					<strong class="documentacion-strong">Nombre:</strong><br>
					<strong class="documentacion-strong">Sitio web:</strong><br>
					<strong class="documentacion-strong">Dirección:</strong><br>
					<strong class="documentacion-strong">Teléfono:</strong><br>
					<strong class="documentacion-strong">Correo electrónico:</strong><br>
					<strong class="documentacion-strong">Símbolo de moneda:</strong><br>
					<strong class="documentacion-strong">Prefijo Telefónico:</strong><br>
					<strong class="documentacion-strong">Logo:</strong><br>
					<strong class="documentacion-strong">Precisión decimal:</strong><br>
					<strong class="documentacion-strong">Separador decimal para vistas y exportables:</strong><br>
					<strong class="documentacion-strong">Imagen por defecto para items sin imagen:</strong><br><br>
					Después de modificar tu empresa y cumpliendo con los requisitos de campos obligatorios puedes darle al boton <button class="btn btn-success">Guardar</button>
				</p>
			</div>
		</div>
		<div class="card-body color card-body-margen card-body-documentacion">
			<h1 class="card-title" style="font-size: 20px;">3. CONTACTOS</h1>
			<div class="texto">
				<h3 class="card-subtitle">Nuevo Contacto</h3>
				<p>
					En éste apartado podrás crear tus nuevos contactos sean tipo cliente o proveedor como lo vemos en la siguiente imagen:
					<img class="img-documentacion" src="/images/Documentacion/doc-6.png">
					Recuerda que cuentas con ayudas extras que se direncian por llevar este icono: <i data-tippy-content="Identificación de la persona" class="icono far fa-question-circle" tabindex="0"></i>
					Acontinuación te explicaremos que significa cada uno de los anteriores items que puedes ver en nuevo contacto:<br><strong><small>(Recuerda que los campos marcados por <span class="text-danger">*</span> son obligatorios)</small></strong><br><br>
					<strong class="documentacion-strong">Tipo de identificación:</strong><br>
					<strong class="documentacion-strong">Identificación:</strong><br>
					<strong class="documentacion-strong">Nombre:</strong><br>
					<strong class="documentacion-strong">Dirección:</strong><br>
					<strong class="documentacion-strong">Ciudad:</strong><br>
					<strong class="documentacion-strong">Correo Electronico:</strong><br>
					<strong class="documentacion-strong">Teléfono:</strong><br>
					<strong class="documentacion-strong">Teléfono 2:</strong><br>
					<strong class="documentacion-strong">Fax:</strong><br>
					<strong class="documentacion-strong">Celular:</strong><br>
					<strong class="documentacion-strong">Tipo de contacto:</strong><br>
					<strong class="documentacion-strong">Tipo de empresa:</strong><br>
					<strong class="documentacion-strong">Lista de Precios:</strong><br>
					<strong class="documentacion-strong">Vendedor:</strong><br>
					<strong class="documentacion-strong">Observaciones:</strong><br>
				</p>
				<h3 class="card-subtitle">¿Qué es el boton <button class="btn btn-outline-primary" onclick="return false" type="button">Asociar Persona</button> ?</h3>
				<p>En asociar personas en caso de ser un cliente tipo empresa puedes asociar las personas pertenecientes a este tipo de cliente, ejemplo:
					<p style="font-style: italic;" >Mi contacto es una empresa llamada importadora severino, pero quiero tener en mi programa el registro de varios empleados de ésta.</p>
					<img class="img-documentacion" src="/images/Documentacion/doc-7.png"><br><br>
					<strong class="documentacion-strong">Enviar Notificaciones:</strong><br><br>
					Después de crear tu nuevo contacto tipo cliente o proveedor y asociar las personas pertenecientes a la empresa (en caso de ser cliente tipo empresa) puedes darle al boton <button class="btn btn-success">Guardar</button>
				</p>
			</div>
		</div>
		<div class="card-body color card-body-margen card-body-documentacion">
			<h1 class="card-title" style="font-size: 20px;">4. NUEVA FACTURA</h1>
			<div class="texto">
				<h3 class="card-subtitle">Nueva Factura</h3>
				<p>
					Es importante tener claro que para poder crear nuestra primera factura de venta debemos tener una <strong>numeracion de factura de venta</strong> con el estado de <strong>preferida: si</strong>
					<img class="img-documentacion" src="/images/Documentacion/doc-8.png"><br><br>
					Ahora sí al estar como preferida vamos a crear nuestra primera factura de venta con esta numeración que escogimos.	
					<img class="img-documentacion" src="/images/Documentacion/doc-9.png">
					<h4>Recuerda que el "<b class="text-primary">No. </b> p100" es el número que configuraste para seguir tu facturación y el prefijo "p" es el que configuraste en el apartado de numeraciones de documentos</h4> 
					Si das click en la opcion <strong>"Más opciones"</strong> Se desplegarán nuevos items.
					Acontinuación te explicaremos que significa cada uno de los anteriores items que puedes ver en nueva factura:<br><strong><small>(Recuerda que los campos marcados por <span class="text-danger">*</span> son obligatorios)</small></strong><br><br>
					<strong class="documentacion-strong">Cliente:</strong><br>
					<strong class="documentacion-strong">Identificación:</strong><br>
					<strong class="documentacion-strong">Teléfono:</strong><br>
					<strong class="documentacion-strong">Observaciones:</strong><br>
					<strong class="documentacion-strong">Fecha:</strong><br>
					<strong class="documentacion-strong">Plazo:</strong><br>
					<strong class="documentacion-strong">Vencimiento:</strong><br>
					<strong class="documentacion-strong">Bodega:</strong><br>
					<strong class="documentacion-strong">Vendedor:</strong><br>
					<strong class="documentacion-strong">Lista de precios:</strong><br>
					<strong class="documentacion-strong">Términos y condiciones:</strong><br>
					<strong class="documentacion-strong">Notas:</strong><br>
					<strong class="documentacion-strong">Agregar Pago:</strong><br>
					<strong class="documentacion-strong">Crear una nueva:</strong><br>
					<strong class="documentacion-strong">Imprimir:</strong><br>
					<strong class="documentacion-strong">Enviar por correo:</strong><br>

					Agrega los productos necesarios para tu factura de venta segun tus necesidades en esta opción:
					<img class="img-documentacion" src="/images/Documentacion/doc-10.png">
					Para agregar mas de un producto puedes dar click en: <button class="btn btn-outline-primary"  type="button">Agregar línea</button><br>
					Después de llenar los campos de tu factura de venta y cumpliendo con los requisitos de campos obligatorios puedes darle al boton <button class="btn btn-success">Guardar</button>
				</p>
			</div>
		</div>
		<div class="card-body color card-body-margen card-body-documentacion">
			<h1 class="card-title" style="font-size: 20px;">5. NUEVO ITEM</h1>
			<div class="texto">
				<h3 class="card-subtitle">Nuevo Producto</h3>
				<p>
					En éste apartado podrás crear tus nuevos prodcutos como lo vemos en la siguiente imagen:
					<img class="img-documentacion" src="/images/Documentacion/doc-11.png">
					Acontinuación te explicaremos que significa cada uno de los anteriores items que puedes ver en nueva factura:<br><strong><small>(Recuerda que los campos marcados por <span class="text-danger">*</span> son obligatorios)</small></strong><br><br>
					<strong class="documentacion-strong">Nombre del producto:</strong><br>
					<strong class="documentacion-strong">Referencia:</strong><br>
					<strong class="documentacion-strong">Categoria:</strong><br>
					<strong class="documentacion-strong">Impuesto:</strong><br>
					<strong class="documentacion-strong">Precio de venta:</strong><br>
					<strong class="documentacion-strong">Agregar otra lista de precio:</strong>Al presionar "Agregar otra lista de precio" se desplegarán unos campos nuevos que son los siguientes:<img class="img-documentacion" src="/images/Documentacion/doc-12.png"><br>
					<strong class="documentacion-strong">Descripción:</strong><br>
					<strong class="documentacion-strong">Imagen (opcional):</strong><br>
					<strong class="documentacion-strong">¿Estará el producto público en la web?:</strong><br>
					<strong class="documentacion-strong">Imagenes extras:</strong><br>
					<strong class="documentacion-strong">¿Producto inventariable?:</strong> Si escoge la opcion sí deberás ir hasta: <strong>Inventario > Bodegas > Nueva Bodega</strong> y finalamente llenar el siguiente formulario: <img class="img-documentacion" src="/images/Documentacion/doc-17.png"><br>
					<strong class="documentacion-strong">Marca y Linea:</strong>Estos items son campos agregados por el usuario en la parte de configuración > Campos extras Inventario > campos (Más adelante hablaremos de como crearlos e implementarlos)<br>
					Después de crear tu nuevo prodcuto y cumpliendo con los requisitos de campos obligatorios puedes darle al boton <button class="btn btn-success">Guardar</button>
				</p>
				<h3 class="card-subtitle">Nuevo campo extra</h3>
				<p>
					En este apartado podrás crear tus <strong>nuevos campos extras para tus productos de inventario</strong>
					<img class="img-documentacion" src="/images/Documentacion/doc-14.png">
					Acontinuación te explicaremos que significa cada uno de los anteriores items que puedes ver en Nuevo campo extra:<br><strong><small>(Recuerda que los campos marcados por <span class="text-danger">*</span> son obligatorios)</small></strong><br><br> 
					<strong class="documentacion-strong">Nombre:</strong><br>
					<strong class="documentacion-strong">Campo:</strong><br>
					<strong class="documentacion-strong">Descripción:</strong><br>
					<strong class="documentacion-strong">Nro de caracteres:</strong><br>
					<strong class="documentacion-strong">¿Es requerido?:</strong><br>
					<strong class="documentacion-strong">Valor por defecto:</strong><br>
					<strong class="documentacion-strong">¿Activar el autocompletar?:</strong><br>
					Después de llenar el formulario de nuevo campo extra y cumpliendo con los requisitos de campos obligatorios puedes darle al boton <button class="btn btn-success">Guardar</button>
				</p>
			</div>
		</div>
		<div class="card-body color card-body-margen card-body-documentacion">
			<h1 class="card-title" style="font-size: 20px;">6. IMPUESTO</h1>
			<div class="texto">
				<h3 class="card-subtitle">Nuevo tipo de impuesto</h3>
				<p>
					En éste apartado podrás crear tus nuevos impuestos como lo vemos en la siguiente imagen:
					<img class="img-documentacion" src="/images/Documentacion/doc-13.png">
					Acontinuación te explicaremos que significa cada uno de los anteriores items que puedes ver en nuevo tipo de impuesto:<br><strong><small>(Recuerda que los campos marcados por <span class="text-danger">*</span> son obligatorios)</small></strong><br><br>
					<strong class="documentacion-strong">Nombre:</strong><br>
					<strong class="documentacion-strong">Porcentaje:</strong><br>
					<strong class="documentacion-strong">Tipo de impuesto:</strong><br>
					<strong class="documentacion-strong">Descripción:</strong><br>
					Después de llenar el formulario de nuevo tipo de impuesto y cumpliendo con los requisitos de campos obligatorios puedes darle al boton <button class="btn btn-success">Guardar</button>
				</p>
			</div>
		</div>
		<div class="card-body color card-body-margen card-body-documentacion">
			<h1 class="card-title" style="font-size: 20px;">7. BANCOS</h1>
			<div class="texto">
				<h3 class="card-subtitle">Nuevo Banco</h3>
				<p>
					En éste apartado podrás crear tus nuevos Bancos en los cuales te llegarán los pagos futuros como lo vemos en la siguiente imagen:
					<img class="img-documentacion" src="/images/Documentacion/doc-15.png">
					Acontinuación te explicaremos que significa cada uno de los anteriores items que puedes ver en nuevo tipo de impuesto:<br><strong><small>(Recuerda que los campos marcados por <span class="text-danger">*</span> son obligatorios)</small></strong><br><br>
					<strong class="documentacion-strong">Tipo de la cuenta:</strong><br>
					<strong class="documentacion-strong">Nombre de la cuenta:</strong><br>
					<strong class="documentacion-strong">Número de la cuenta:</strong><br>
					<strong class="documentacion-strong">Saldo inicial:</strong><br>
					<strong class="documentacion-strong">Fecha:</strong><br>
					<strong class="documentacion-strong">Descripción:</strong><br>
					Después de llenar el formulario de nuevo banco y cumpliendo con los requisitos de campos obligatorios puedes darle al boton <button class="btn btn-success">Guardar</button>
				</p>
			</div>
		</div>
		<div class="card-body color card-body-margen card-body-documentacion">
			<h1 class="card-title" style="font-size: 20px;">8. RETENCIONES</h1>
			<div class="texto">
				<h3 class="card-subtitle">Nuevo tipo de retención</h3>
				<p>
					En éste apartado podrás crear tus nuevos tipos de retención en los cuales podrás retener un porcentaje de la venta final como lo vemos en la imagen:
					<img class="img-documentacion" src="/images/Documentacion/doc-16.png">
					Acontinuación te explicaremos que significa cada uno de los anteriores items que puedes ver en nuevo tipo de impuesto:<br><strong><small>(Recuerda que los campos marcados por <span class="text-danger">*</span> son obligatorios)</small></strong><br><br>
					<strong class="documentacion-strong">Nombre de retención:</strong><br>
					<strong class="documentacion-strong">Porcentaje:</strong><br>
					<strong class="documentacion-strong">Tipo de retención:</strong><br>
					<strong class="documentacion-strong">Descripción:</strong><br>
					Después de llenar el formulario de nuevo tipo de retención y cumpliendo con los requisitos de campos obligatorios puedes darle al boton <button class="btn btn-success">Guardar</button>
				</p>
			</div>
		</div>
	</center>
</div>
