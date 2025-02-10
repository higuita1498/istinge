<?php

namespace App\Http\Controllers;

use App\Empresa;
use App\Impuesto;
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Inventario\Inventario;
use App\Retencion;
use App\Vendedor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiigoController extends Controller
{
    public function configurarSiigo(Request $request)
    {
        $empresa = Empresa::find(Auth::user()->empresa);

        if ($empresa) {

            //Probando conexion de la api.
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.siigo.com/auth',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    'username' => $request->usuario_siigo,
                    'access_key' => $request->api_key_siigo,
                ]),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response);

            if (isset($response->access_token)) {
                $empresa->usuario_siigo = $request->usuario_siigo;
                $empresa->api_key_siigo = $request->api_key_siigo;
                $empresa->token_siigo = $response->access_token;
                $empresa->fecha_token_siigo = Carbon::now();
                $empresa->save();
                return 1;
            }

            return 0;
            // dd($response->response[0]->name);
        }
    }

    public function getModalInvoice(Request $request)
    {

        //Obtenemos los tipos de comprobantes que puede crear el cliente.
        $response_document_types = $this->getDocumentTypes();

        //Obtenemos los centros de costos
        $response_costs =  $this->getCostCenters();

        //obtenemos los tipos de pago
        $response_payments_methods = $this->getPaymentTypes();

        //obtenemos los sellers (usuarios)
        $response_users = $this->getSeller();

        if (isset($response_users['results'])) {
            $response_users = $response_users['results'];
        }

        if ($response_document_types) {
            return response()->json([
                'status' => 200,
                'tipos_comprobante' => $response_document_types,
                'centro_costos' => $response_costs,
                'tipos_pago' => $response_payments_methods,
                'usuarios' => $response_users,
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'error' => "Ha ocurrido un error"
            ]);
        }
    }

    public static function getTaxes()
    {

        $empresa = Empresa::Find(1);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.siigo.com/v1/taxes',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Partner-Id: Integra',
                'Authorization: Bearer ' . $empresa->token_siigo,
            ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);

        if (is_array($response)) {
            return response()->json([
                'status' => 200,
                'taxes' => $response
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'error' => "Ha ocurrido un error"
            ]);
        }
    }

    public static function getDocumentTypes()
    {
        $empresa = Empresa::Find(1);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.siigo.com/v1/document-types?type=FV',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Partner-Id: Integra',
                'Authorization: Bearer ' . $empresa->token_siigo,
            ),
        ));

        $response_document_types = curl_exec($curl);
        curl_close($curl);
        return $response_document_types = json_decode($response_document_types, true);
    }

    public static function getCostCenters()
    {

        $empresa = Empresa::Find(1);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.siigo.com/v1/cost-centers',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Partner-Id: Integra',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $empresa->token_siigo,
            ),
        ));

        $response_costs = curl_exec($curl);
        curl_close($curl);
        return $response_costs = json_decode($response_costs, true);
    }

    public static function getPaymentTypes()
    {
        $empresa = Empresa::Find(1);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.siigo.com/v1/payment-types?document_type=FV',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Partner-Id: Integra',
                'Authorization: Bearer ' . $empresa->token_siigo,
            ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response, true);
        curl_close($curl);
        return $response;
    }

    public static function getSeller()
    {
        $empresa = Empresa::Find(1);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.siigo.com/v1/users',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Partner-Id: Integra',
                'Authorization: Bearer ' . $empresa->token_siigo,
            ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response, true);
        curl_close($curl);
        return $response;
    }

    public function sendInvoice(Request $request)
    {

        // return $request;
        $factura = Factura::Find($request->factura_id);
        $cliente_factura = $factura->cliente();
        $items_factura = ItemsFactura::join('inventario', 'inventario.id', 'items_factura.producto')
            ->where('factura', $factura->id)->get();
        $empresa = Empresa::Find(1);
        $departamento = $cliente_factura->departamento();
        $municipio = $cliente_factura->municipio();

        $array_items_factura = [];
        foreach ($items_factura as $item) {
            $array_items_factura[] = [
                "code" => $item['ref'],
                "quantity" => round($item['cant']),
                "price" => $item['precio']
            ];
        }

        $data = [
            "document" => [
                "id" => $request->tipo_comprobante
            ],
            "date" => $factura->fecha,
            "customer" => [
                "person_type" => $cliente_factura->dv != null ? 'Company' : 'Person',
                "id_type" => $cliente_factura->dv != null ? "31" : "13", //13 cedula 31 nit
                "identification" => $cliente_factura->nit,
                "branch_office" => "0", //por defecto 0
                "name" => [
                    $cliente_factura->nombre,
                    $cliente_factura->apellido1 . " " . $cliente_factura->apellido2
                ],
                "address" => [
                    "address" => $cliente_factura->direccion,
                    "city" => [
                        "country_code" => $cliente_factura->fk_idpais,
                        "country_name" => "Colombia",
                        "state_code" => $departamento->codigo,
                        "state_name" => $departamento->nombre,
                        "city_code" => $municipio->codigo,
                        "city_name" => $municipio->nombre
                    ],
                    "postal_code" => $cliente_factura->cod_postal
                ],
                "phones" => [
                    [
                        "indicative" => "57",
                        "number" => $cliente_factura->celular,
                        "extension" => ""
                    ]
                ],
                "contacts" => [
                    [
                        "first_name" => $cliente_factura->nombre,
                        "last_name" => $cliente_factura->apellido1 . " " . $cliente_factura->apellido2,
                        "email" => $cliente_factura->email,
                        "phone" => [
                            "indicative" => "57",
                            "number" => $cliente_factura->celular,
                            "extension" => ""
                        ]
                    ]
                ]
            ],
            "seller" => $request->usuario,
            'items' => $array_items_factura,
            "payments" => [
                [
                    "id" => $request->tipos_pago,
                    "value" => round($factura->total()->total),
                    "due_date" => $factura->vencimiento
                ]
            ]
        ];

        return $data;
        //Envio a curl invoice

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.siigo.com/v1/invoices',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'Partner-Id: Integra',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $empresa->token_siigo,
            ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response, true);
        curl_close($curl);
        return $response;
    }

    public function impuestosSiigo()
    {
        $empresa = Empresa::Find(1);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.siigo.com/v1/taxes',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Partner-Id: Integra',
                'Authorization: Bearer ' . $empresa->token_siigo,
            ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);
        return $response;
    }

    public function mapeoImpuestos()
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Mapeo de impuestos', 'icon' => 'fa fa-cogs', 'seccion' => 'Configuración']);
        $impuestos = Impuesto::where('estado', 1)->get()->where('porcentaje', '!=', 0);
        $retenciones = Retencion::where('estado', 1)->where('porcentaje', '!=', 0)->get();
        $impuestosSiigo = $this->impuestosSiigo();
        return view('siigo.impuestos', compact('impuestos','retenciones','impuestosSiigo'));
    }

    public function storeImpuestos(Request $request){

        for($i = 0; $i < count($request->imp); $i++){
            $impuesto = Impuesto::find($request->imp[$i]);
            $impuesto->siigo_id = $request->siigo_imp[$i];
            $impuesto->save();
        }

        for($i = 0; $i < count($request->ret); $i++){
            $retencion = Retencion::find($request->ret[$i]);
            $retencion->siigo_id = $request->siigo_ret[$i];
            $retencion->save();
        }

        return redirect()->route('siigo.mapeo_impuestos')->with('success', 'Impuesto y Retenciones guardados correctamente.');
    }

    public function mapeoVendedores(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Mapeo de vendedores', 'icon' => 'fa fa-cogs', 'seccion' => 'Configuración']);
        $vendedores = Vendedor::where('estado', 1)->get();
        $vendedoresSiigo = $this->getSeller()['results'];

        return view('siigo.vendedores', compact('vendedores','vendedoresSiigo'));
    }

    public function storeVendedores(Request $request){
        for($i = 0; $i < count($request->vendedores); $i++){
            $vendedor = Vendedor::find($request->vendedores[$i]);
            $vendedor->siigo_id = $request->siigo_vendedores[$i];
            $vendedor->save();
        }

        return redirect()->route('siigo.mapeo_vendedores')->with('success', 'Vendedores guardados correctamente.');
    }

    public function getProducts(){
        $empresa = Empresa::Find(1);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.siigo.com/v1/products?limit=1000&offset=0&order_by=code&order_direction=asc&status=active',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Partner-Id: Integra',
                'Authorization: Bearer ' . $empresa->token_siigo,
            ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response, true);
        curl_close($curl);
        return $response;
    }

    public function mapeoProductos(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Mapeo de productos', 'icon' => 'fa fa-cogs', 'seccion' => 'Configuración']);
        $productos = Inventario::where('status', 1)->get();
        $productosSiigo = $this->getProducts()['results'];

        return view('siigo.productos', compact('productos','productosSiigo'));
    }

    public function storeProductos(Request $request){
        for($i = 0; $i < count($request->productos); $i++){
            $producto = Inventario::find($request->productos[$i]);
            $producto->siigo_id = $request->siigo_productos[$i];
            $producto->save();
        }

        return redirect()->route('siigo.mapeo_productos')->with('success', 'Productos guardados correctamente.');
    }
}
