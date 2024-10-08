<?php

namespace App\Http\Controllers;

use App\Empresa;
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\ItemsFactura;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiigoController extends Controller
{
    public function configurarSiigo(Request $request){
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

          if(isset($response->access_token)){
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

      public function getModalInvoice(Request $request){

        //Obtenemos los tipos de comprobantes que puede crear el cliente.
        $response_document_types = $this->getDocumentTypes();

        //Obtenemos los centros de costos
        $response_costs =  $this->getCostCenters();

        //obtenemos los tipos de pago
        $response_payments_methods = $this->getPaymentTypes();

        //obtenemos los sellers (usuarios)
        $response_users = $this->getSeller();

        if(isset($response_users['results'])){
            $response_users = $response_users['results'];
        }

        if($response_document_types){
            return response()->json([
                'status' => 200,
                'tipos_comprobante' => $response_document_types,
                'centro_costos' => $response_costs,
                'tipos_pago' => $response_payments_methods,
                'usuarios' => $response_users,
            ]);
        }else{
            return response()->json([
                'status' => 400,
                'error' => "Ha ocurrido un error"
            ]);
        }


      }

      public static function getTaxes(){

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

        if(is_array($response)){
            return response()->json([
                'status' => 200,
                'taxes' => $response
            ]);
        }else{
            return response()->json([
                'status' => 400,
                'error' => "Ha ocurrido un error"
            ]);
        }

      }

      public static function getDocumentTypes(){
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
        return $response_document_types = json_decode($response_document_types,true);
      }

      public static function getCostCenters(){

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
            return $response_costs = json_decode($response_costs,true);
      }

      public static function getPaymentTypes(){
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
        $response = json_decode($response,true);
        curl_close($curl);
        return $response;

      }

      public static function getSeller(){
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
        $response = json_decode($response,true);
        curl_close($curl);
        return $response;

      }

      public function sendInvoice(Request $request){

        // return $request;
        $factura = Factura::Find($request->factura_id);
        $cliente_factura = $factura->cliente();
        $items_factura = ItemsFactura::join('inventario','inventario.id','items_factura.producto')
        ->where('factura',$factura->id)->get();
        $empresa = Empresa::Find(1);

        $array_items_factura=[];
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
                "identification" => $cliente_factura->nit,
                "branch_office" => "0" //por defecto 0
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
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS =>$data,
        CURLOPT_HTTPHEADER => array(
            'Partner-Id: Integra',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $empresa->token_siigo,
        ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response,true);
        curl_close($curl);
        return $response;

      }




}
