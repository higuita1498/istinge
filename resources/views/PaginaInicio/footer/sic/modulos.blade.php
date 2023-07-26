@extends('layouts.includes.inicio') 
@section('content2') 
<div class="full">
	<div class="enlamitad-2">
		<center>
			<div class="card-paginicio card-text-principal">
				<h2 class="card-title card-title-module"><img src="/images/favicon.png" style="margin-right: 15px;margin-bottom: -15px;">¡MIRA LAS CARACTERISTICAS DE CADA MODULO!<img src="/images/favicon.png" style="margin-left: 15px; margin-bottom: -15px;"></h2>
			</div>
			<div class="card-paginicio">
				<h2 class="card-title">CONTACTOS</h2>
				<ul style="list-style: none;">
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/cliente.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">CLIENTES</h2>
							<p>Crea tus usuarios tipo cliente y lleva toda la informacion de sus facturas, cotizaciones, notas credito, pagos y deudas.</p>
						</div>

					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/proveedores.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">PROVEEDORES</h2>
							<p>Crea tus proveedores y lleva toda la informacion de sus pedidos, facturas pagos y deudas. </p>
						</div>
					</li>
				</ul>
			</div>
			<div class="card-paginicio">
				<h2 class="card-title">INVENTARIO</h2>
				<ul style="list-style: none;">
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/item-de-venta.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">ITEMS DE VENTA</h2>
							<p>Crea tus itmes de venta y asigna su información requerida, puedes elegir entre inventariable o no.</p>
						</div>
					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/valor-de-inventario.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">VALOR DE INVENTARIO</h2>
							<p>Mira de una forma rápida el valor de tu inventario, sin necesidad de sacar calculadora, nuestro programa ya lo calcula para ti.</p>
						</div>
					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/ajustes-de-inventario.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">AJUSTES DE INVENTARIO</h2>
							<p>Si te equivocaste ingresando la cantidad del producto, no te preocupes, ajustalo en un sencillo formulario</p>
						</div>
					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/gestion-de-items.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">GESTION DE ITEMS</h2>
							<p>Actualiza e Importa tus prodcutos mediante un excel de la manera mas sencilla, también puedes trasladar tus prodcutos entre bodegas.</p>
						</div>
					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/lista-de-precios.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">LISTA DE PRECIOS</h2>
							<p>Si tienes precios especificos para un determinado cliente, puedes crear tus propios precios (es como un descuento que se le hace al total de productos a vender).</p>
						</div>
					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/bodega.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">BODEGAS</h2>
							<p>Crea tus bodegas para llevar de una forma mas organizada tu inventario, a la hora de crear los productos, tu decides a que bodega quieres que vayan.</p>
						</div>
					</li>
				</ul>
			</div>
			<div class="card-paginicio">
				<h2 class="card-title">INGRESOS</h2>
				<ul style="list-style: none;">
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/facturas-de-venta.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">FACTURAS DE VENTA</h2>
							<p>Crea tus facturas de venta y lleva un historial de ellas (la cantidad de facturas depende del plan que escojas)</p>
						</div>
					</li>
					{{--<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/facturas-recurrentes.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">FACTURAS RECURRENTES</h2>
							<p>Crea tus facturas recurrents y lleva un historial de ellas (la cantidad de facturas depende del plan que escojas)</p>
						</div>
					</li>--}}
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/pagos-recibidos--.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">PAGOS RECIBIDOS</h2>
							<p>Puedes llevar un registro de los pagos recibidos por tus proveedores, empresas o cientes</p>
						</div>
					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/notas-de-credito.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">NOTAS DE CREDITO</h2>
							<p>Registra las notas de creditos de tus clientes y lleva un historial de ellas. Anula o abona a una factura ya emitida. </p>
						</div>
					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/cotizaciones.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">COTIZACIONES</h2>
							<p>Registra las cotizaciones de tus clientes que en un futuro se volveran ventas.</p>
						</div>
					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/pagos-recibidos.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">PAGOS RECIBIDOS RECURRENTES</h2>
								<p>Puedes llevar un registro de los pagos  recurrentes recibidos por tus proveedores, empresas o cientes</p>
						</div>
					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/remisiones.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">REMISIONES</h2>
							<p>Registre remisiones cuando exista una relación de compra entre dos partes, extendiendola a la hora en la que una de las partes hace entrega de artículos o productos a la otra.</p>
						</div>
					</li>
				</ul>
			</div>
			<div class="card-paginicio">
				<h2 class="card-title">GASTOS</h2>
				<ul style="list-style: none;">
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/pagos.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">PAGOS</h2>
							<p>Registra los pagos que hayas hecho a tus proveedores o empresas aliadas.</p>
						</div>
					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/facturas-y-proveedores.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">FACTURAS DE PROVEEDORES</h2>
							<p>Lleva un registro de las facturas de proveedores que tu creas</p>
						</div>
					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/pagos-recibidos.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">PAGOS RECURRENTES</h2>
							<p>Son cobros automatizados, que se realizan periodicamente; te notificaremos cuando la fecha de un pago este próxima a realizarse.  </p>
						</div>
					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/notas-de-credito.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">NOTAS DEBITO</h2>
							<p>Lleva un registro de las notas de debito emitidad, notifica si ha aumentado la cantidad de la deuda por algún motivo.</p>
						</div>
					</li>
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/facturas-de-venta.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">ORDENES DE COMPRA</h2>
							<p>Lleva un registro de las ordenes de compra, así podrás tener un control de lo que quieres comprar en un presente o futuro.</p>
						</div>
					</li>
				</ul>
			</div>
			<div class="card-paginicio">
				<h2 class="card-title">LOGISTICA</h2>
				<ul style="list-style: none;">
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/facturas-y-proveedores.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">LOGISTICA</h2>
							<p>Este modulo es para aquellas empresas que quieren llevar un registro de los domicilios que salen de tu empresa</p>
						</div>
					</li>
				</ul>
			</div>
			<div class="card-paginicio">
				<h2 class="card-title">BANCOS</h2>
				<ul style="list-style: none;">
				<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/bodega.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">BANCOS</h2>
							<p>Crea los bancos en los que quieres que te depositen los dineros. Puedes crear tantos bancos como quieras.</p>
						</div>
					</li>
					
			</div>
			<div class="card-paginicio">
				<h2 class="card-title">CATEGORIAS</h2>
				<ul style="list-style: none;">
				<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/item-de-venta.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">CATEGORIAS</h2>
							<p>Organice a su medida el plan único de cuentas.</p>
						</div>
					</li>
				</ul>
			</div>
			<div class="card-paginicio">
				<h2 class="card-title">SOPORTE</h2>
				<ul style="list-style: none;">
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/proveedores.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">SOPORTE</h2>
							<p>Si tienes dudas tecnicas, éste es uno de los variados medios que tenemos para brindarte soporte.</p>
						</div>
					</li>
				</ul>
			</div>
			<div class="card-paginicio">
				<h2 class="card-title">REPORTES</h2>
				<ul style="list-style: none;">
					<li class="list-module">
						<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/remisiones.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">REPORTES</h2>
							<p>Uno de los modulos mas importantes que querrás ver: los reportes, mira los reportes por diferentes aspectos, para tomar futuras decisiones en tu empresa</p>
						</div>
					</li>
				</ul>
			</div>
			<div class="card-paginicio">
				<h2 class="card-title">PAGINA WEB</h2>
				<ul style="list-style: none;">
					<li class="list-module">
							<div class="module-icon" style="background-image: url('../images/PagInicio/modulos/valor-de-inventario.png')"></div>
						<div class="module-content">
							<h2 class="card-title espec-title">PAGINA WEB</h2>
							<p>!Puedes tener acceso a tu pagina web¡, Promociona tus productos en la pagin web que te brindamos, nada mas tienes que agregar los productos en GestorU y automaticamente apareceran en tu pagina web.</p>
						</div>
					</li>
				
				</ul>
			</div>
		</center>
	</div>
</div>
@endsection