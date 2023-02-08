<?php

namespace App\Http\Controllers;

use App\Contacto;
use App\RecepcionComprobante;
use App\TipoEmpresa;
use DB;
use App\Vendedor;
use Illuminate\Http\Request;
use Auth;
use App\Services\ReceptionDocumentService;
use App\Services\EventReceptionDocumentService;
use App\TipoIdentificacion;
use GuzzleHttp\Client;
use App\Empresa;
use App\EventoEmitido;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class RecepcionComprobantesController extends Controller
{

    protected $documentApi;
    protected $eventDian;

     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ReceptionDocumentService $documentApi, EventReceptionDocumentService $eventDian)
    {
        $this->middleware('auth');
        view()->share(['seccion' => 'compras', 'title' => 'Recepción', 'icon' => 'fas fa-cogs']);

        $this->documentApi = $documentApi;
        $this->eventDian = $eventDian;
    }


    public function index()
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Recepción de comprobantes', 'subseccion' => 'documents']);

        $tipos_empresa = TipoEmpresa::where('empresa', auth()->user()->empresa)->get();
        $vendedores = Vendedor::where('empresa', auth()->user()->empresa)->get();
        $tipo_usuario = 2;

        return view('recepcion.documentos', compact('tipos_empresa', 'vendedores', 'tipo_usuario'));
    }


    public function documents(Request $request){

        // dd($request->nombre, $request->identificacion, $request->telefono, $request->vendedor);
        $modoLectura = auth()->user()->modo_lectura();
        //probando api este nit despues erá dinámico.

        $empresa = Empresa::find(Auth::user()->empresa);
        $nit = $empresa->nit;
        //ambiente recepcion 1= produccion 2= pruebas
        $response = $this->documentApi->getDocuments($nit,1);

        if($response->status == 200){
            $documentos = collect($response->documents);

            return DataTables::collection($documentos)
            ->addColumn('acciones',function($documento){
                return view('recepcion.acciones',compact('documento'));
            })
            ->editColumn('supplierNit', function($documento){
                if(RecepcionComprobante::emisor($documento->supplierNit)){
                    return RecepcionComprobante::emisor($documento->supplierNit)->nombre;
                }
            })
            ->editColumn('pdfUrl', function($documento){
               return "<a class='btn btn-outline-info btn-icons' title='Ver pdf del proveedor' href='" .  $documento->pdfUrl . "'" . "target='_blank'>" . '<i class="fas fa-search"></i>' . "</a>";
            })
            ->editColumn('created_at',function($documento){
                return RecepcionComprobante::formatFecha($documento->created_at);
            })
            ->editColumn('documentTypeCode',function($documento){
                return RecepcionComprobante::tipoDocumento($documento);
            })
            ->editColumn('estado_dian', function ($documento) {
                    return $documento->estado_dian == 1 ? 'Aceptada' : '';
                })
            ->editColumn('acusado', function ($documento) {
                return RecepcionComprobante::textoEventodian($documento);
            })
            ->rawColumns(['acciones','pdfUrl'])
            ->toJson();
        }
    }

    /* ***********
    Obtiene el formulario de acuse de recibo, solo el formulario.
    **************/
    public function modificarAcuseRecibo($uuid){

        $identificaciones = TipoIdentificacion::all();
        $document = $this->documentApi->getFormDocumentAcuse($uuid,1);

        return response()->json([
            'identificaciones' => $identificaciones,
            'document' => $document
        ]);
    }

    /* ***********
    Obtiene el formulario de recepcion, solo el formulario
    **************/
    public function modificarRecepcionBien($uuid){
        $identificaciones = TipoIdentificacion::all();
        $document = $this->documentApi->getFormDocumentAcuse($uuid,2);

        return response()->json([
            'identificaciones' => $identificaciones,
            'document' => $document
        ]);
    }

    /* ***********
    Actualiza la información del formulario de acuse o confirmación de  recepcion
    Y envía la petición a la DIAN para acusar o confirmar.
    **************/

    public function storeModificarAcuseRecibo(Request $request){

        $request = $request->all();

        //actualizamos el formulario del documento (acuse recibo o confirmacion bienes)
        $response = $this->documentApi->updateFormDocument($request);

        //buscamos el codigo DIAN del tipo de identifiacion
        $request['codigo_tipo_identificacion'] = DB::table('tipos_identificacion')->where('id',$request['tip_iden'])->first()->codigo_dian;

        //Enviamos la información a la DIAN notificando acuse de recibo (1) o confirmando recepcion (2).
        $dianResponse = $this->eventDian->event3032($request,$response->document);

        if(is_array($dianResponse) && $dianResponse['statusCode'] == 400){

            //antes de retornar una respuesta de que no fue posible, si estamos emitiendo el estado 032 vamos a intentar emitir nuevamente el 030
            if($request['tipo'] == 2){
                $uuid = $request['uuid'];
                $form = $this->documentApi->getFormDocumentAcuse($uuid,1);
                $form = (array) $form->formulario;
                $newDianResponse = $this->eventDian->event3032($form,$response->document);
            }

            if(isset($newDianResponse) && is_array($newDianResponse) && $newDianResponse['statusCode'] == 400){
                $request['json_response'] = $dianResponse['th'];
                $this->documentApi->updateFormDocument($request);

                return response()->json([
                    'status' => 400,
                    'messageError' => "El documento no pudo ser procesado correctamente, intente nuevamente"
                ]);
            }else{
                //Enviamos nuevamente la información a la DIAN notificando acuse de recibo (1) o confirmando recepcion (2).
                $dianResponse = $this->eventDian->event3032($request,$response->document);

                if(is_array($dianResponse) && $dianResponse['statusCode'] == 400){
                     return response()->json([
                        'status' => 400,
                        'messageError' => "El documento no pudo ser procesado correctamente, intente nuevamente"
                    ]);
                }

            }
        }

        $reposnseSave = [
            "uuid" => $request['uuid'],
            "json_response" => $dianResponse
        ];

        $other = $this->documentApi->updateFormDocument($reposnseSave);

        $arrayEventsUpdate = [
            "uuid" => $request['uuid'],
            "estado_dian" => 0,
            "acusado" => 0,
            "confirma_recepcion" => 0,
            "aceptado" => 0,
            "rechazado" => 0,
            "json" => 0,
        ];


        if($dianResponse->statusCode == 200 && $dianResponse->statusDian == 200){

            $arrayEventsUpdate['estado_dian'] = 1;
            //guardamos el historial de eventos emitidos
            $evento = new EventoEmitido();
            $evento->empresa_id = Auth::user()->empresa;
            $evento->documentId = $response->document->documentId;

            if($dianResponse->eventCode == "030"){
                $arrayEventsUpdate['acusado'] = 1;
                $evento->tipo = 1;
            }
            if($dianResponse->eventCode == "032"){
                $arrayEventsUpdate['confirma_recepcion'] = 1;
                $evento->tipo = 2;
            }
             $arrayEventsUpdate['json'] = $dianResponse;
             $evento->save();

        }
        //documento ya ha sido procesado le colocamos los estados de una vez
        else if($dianResponse->statusCode == 200 && $dianResponse->statusDian == 409 && $dianResponse->warningsDian == "Regla: 90, Rechazo: Documento procesado anteriormente."){
            $arrayEventsUpdate['estado_dian'] = 1;
            if($dianResponse->eventCode == "030"){$arrayEventsUpdate['acusado'] = 1;}
            if($dianResponse->eventCode == "032"){$arrayEventsUpdate['confirma_recepcion'] = 1;}
            $arrayEventsUpdate['json'] = $dianResponse;

            //guardamos el historial de eventos emitidos


        }

        //si tenemos una respuesta positiva marcamos el acuse (1) o la confirmacion (2) por medio de la api (+ la aceptacion dian)
        $updateStateDian = $this->documentApi->updateDocument($arrayEventsUpdate);
        return  response()->json([$updateStateDian]);
    }

    /* ***********
    Aceptamos o rechazmos en la DIAN el documento
    **************/
    public function aceptoRechzaoDocumento(Request $request){

        $data = $request->all();
        //actualizamos el formulario del documento (acuse recibo o confirmacion bienes)
        $response = $this->documentApi->updateFormDocument($data);
        //Enviamos la información a la DIAN notificando acuse de recibo (1) o confirmando recepcion (2).
        //si es 3 estamos aceptando el documento si es 4 estamos rechazando
        if($data['tipo'] == 3){
            $dianResponse = $this->eventDian->event282933($data,$response->document);
        }else if($data['tipo'] == 4){
            $dianResponse = $this->eventDian->event31($data,$response->document);
        }


        if(is_array($dianResponse) && $dianResponse['statusCode'] == 400){

             //MANERA DE OBTENER EL CUERPO DE UNA RESPUESTA GUZZLE!!
            if(isset($dianResponse['th'])){
                $responseDian = json_decode($dianResponse['th']->getResponse()->getBody()->getContents(),true);
            }

            $texto = "";
            if(isset($responseDian['errorReason'])){
                $texto = strtolower($responseDian['errorReason']);
            }

            if($texto !== "el documento tiene un estado de aceptación expresa por tanto no es posible procesar el estado enviado"){
                //antes de retornar una respuesta de que no fue posible, si estamos emitiendo el estado 033 o 031 vamos a intentar emitir nuevamente el 032
                $uuid = $response->document->uuid;
                $form = $this->documentApi->getFormDocumentAcuse($uuid,2);
                $form = (array) $form->formulario;
                $newDianResponse = $this->eventDian->event3032($form,$response->document);
                if(isset($newDianResponse) && is_array($newDianResponse) && $newDianResponse['statusCode'] == 400){
                    return response()->json([
                      'status' => 400,
                      'messageError' => "El documento no pudo ser procesado correctamente, intente nuevamente."
                    ]);
                }else{
                    //volvemos a hacer el intento de emitir la información
                     if($data['tipo'] == 3){
                        $dianResponse = $this->eventDian->event282933($data,$response->document);
                    }else if($data['tipo'] == 4){
                        $dianResponse = $this->eventDian->event31($data,$response->document);
                    }

                    if(is_array($dianResponse) && $dianResponse['statusCode'] == 400){
                        return response()->json([
                          'status' => 400,
                          'messageError' => "El documento no pudo ser procesado correctamente, intente nuevamente."
                        ]);
                    }
                }
            }
      }

      $arrayEventsUpdate = [
          "uuid" => $data['uuid'],
          "estado_dian" => 0,
          "acusado" => 0,
          "confirma_recepcion" => 0,
          "aceptado" => 0,
          "rechazado" => 0,
          "json",
      ];

        //guardamos el historial de eventos emitidos
        $evento = new EventoEmitido();
        $evento->empresa_id = Auth::user()->empresa;
        $evento->documentId = $response->document->documentId;

      if(isset($dianResponse->statusCode) && $dianResponse->statusCode == 200 && $dianResponse->statusDian == 200 ||
      isset($responseDian) && isset($responseDian['errorReason']) &&
      $responseDian['errorReason'] === "El documento tiene un estado de aceptación expresa por tanto no es posible procesar el estado enviado"){

          $arrayEventsUpdate['estado_dian'] = 1;
          if($data['tipo'] == 3){
              $arrayEventsUpdate['aceptado'] = 1;
              $evento->tipo = 3;
          }
          if($data['tipo'] == 4){
              $arrayEventsUpdate['rechazado'] = 1;
              $evento->tipo = 4;
          }

          $evento->save();
          $arrayEventsUpdate['json'] = $dianResponse;
      }

      //si tenemos una respuesta positiva marcamos el acuse (1) o la confirmacion (2) por medio de la api (+ la aceptacion dian)
      $updateStateDian = $this->documentApi->updateDocument($arrayEventsUpdate);
      return  response()->json([$updateStateDian]);
    }
}
