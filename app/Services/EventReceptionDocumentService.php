<?php

namespace App\Services;

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use App\Traits\ConsumesExternalServices;

class EventReceptionDocumentService
{
    use ConsumesExternalServices;

    protected $baseUri;

    protected $token;

    protected $ambiente;


    public function __construct()
    {
        $this->token = config('services.eventsDocument.token');
        $this->baseUri = config('services.eventsDocument.base_uri');
        $this->ambiente = config('services.eventsDocument.ambiente');
    }

    public function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $headers['efacturaAuthorizationToken'] = $this->token;
    }

    public function decodeResponse($response)
    {
        return json_decode($response);
    }
    
    /*
    numeraciones:
    0=acuse recibo
    1=confirma recepcion
    2=acepto
    3=rechazo
    */
    public function event3032($form,$document)
    {
        //si no es nit entonces el campo dv viaja null
        if($form['tip_iden'] != 6){
            $form['dvoriginal'] = "";
        }

        // dd($this->token,$this->baseUri,$this->ambiente);
        
        return $this->makeRequestTwo(
            'POST',
            "/" . $this->ambiente . "/recepcion/estados",
            [],
            [
                "supplierId"=> $document->supplierNit,
                "receiverId"=> $document->receiverNit, 
                "partnershipId"=> $document->partnershipId, 
                "documentTypeCode"=> $document->documentTypeCode,
                "documentId"=> $document->documentId,
                "username" => $form['primer_nombre'] . " " . $form['segundo_nombre'],
                "eventId" => $form['tipo'] == 1 ? $document->numeraciones[0]->nro : $document->numeraciones[1]->nro,
                "documentStatus"=> [
                   "statusCode"=> $form['tipo'] == 1 ? "030" : "032",
                   "statusDate"=> $document->createdAt,
                   "statusReason"=> "",
                    "statusNote"=> "",
                    "id"=> isset($form['nit']) ? $form['nit'] : $form['identificacion'],
                    "idDv"=> isset($form['dvoriginal']) ? $form['dvoriginal'] : $form['dv'],
                    "idType"=> isset($form['codigo_tipo_identificacion']) ? $form['codigo_tipo_identificacion'] : $form['tip_iden'],
                    "firstName"=> $form['primer_nombre'] . " " . $form['segundo_nombre'],
                    "familyName"=> $form['apellidos'],
                    "jobTitle"=> isset($form['rol']) ? $form['rol'] : $form['cargo'],
                    "OrganizationDepartment"=> $form['area']
                ]
            ],
            [
            ],
            $isJsonRequest = true
        );
    }
    
    /*
    numeraciones:
    0=acuse recibo
    1=confirma recepcion
    2=acepto
    3=rechazo
    */
    public function event282933($form,$document)
    {
        //si no es nit entonces el campo dv viaja null
        if(isset($form['tip_iden']) && $form['tip_iden'] != 6){
            $form['dvoriginal'] = "";
        }

        // dd($this->token,$this->baseUri,$this->ambiente);
        
        return $this->makeRequestTwo(
            'POST',
            "/" . $this->ambiente . "/recepcion/estados",
            [],
            [
                "supplierId" => $document->supplierNit,
                "receiverId" => $document->receiverNit, 
                "partnershipId" => $document->partnershipId, 
                "documentTypeCode" => $document->documentTypeCode,
                "documentId" => $document->documentId,
                "username"  => $form['primer_nombre'],
                "eventId"  => $document->numeraciones[2]->nro,
                "documentStatus" => [
                    "statusCode" => "033",
                    "statusDate" => $document->createdAt,
                    "statusReason" => "",
                    "statusNote" => ""
                ]
            ],
            [
            ],
            $isJsonRequest = true
        );
    }
    
     /*
    numeraciones:
    0=acuse recibo
    1=confirma recepcion
    2=acepto
    3=rechazo
    */
    public function event31($form,$document)
    {

        // dd($this->token,$this->baseUri,$this->ambiente);
        return $this->makeRequestTwo(
            'POST',
            "/" . $this->ambiente . "/recepcion/estados",
            [],
            [
                "supplierId" => $document->supplierNit,
                "receiverId" => $document->receiverNit, 
                "partnershipId" => $document->partnershipId, 
                "documentTypeCode" => $document->documentTypeCode,
                "documentId" => $document->documentId,
                "username"  => $form['primer_nombre'],
                "eventId"  => $document->numeraciones[3]->nro,
                "documentStatus" => [
                    "statusCode" => "031",
                    "statusDate" => $document->createdAt,
                    "statusReason" => "",
                    "statusNote" => "",
                    "claimCode" => $form['claim_code']
                ]
            ],
            [
            ],
            $isJsonRequest = true
        );
    }
}
