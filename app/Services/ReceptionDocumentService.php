<?php

namespace App\Services;

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use App\Traits\ConsumesExternalServices;

class ReceptionDocumentService
{
    use ConsumesExternalServices;

    protected $baseUri;

    protected $token;

    protected $secretKey;


    public function __construct()
    {
        $this->token = config('services.eventsDocument.token');
        $this->baseUri = config('services.eventsDocument.base_uri');
    }

    public function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $headers['Authorization'] = "Bearer {$this->token}";
    }

    public function decodeResponse($response)
    {
        return json_decode($response);
    }

    public function getDocuments($nit,$ambiente)
    {
        return $this->makeRequest(
            'GET',
            "/api/v1/getdocuments/",
            [
                'nit' => $nit,
                'ambiente' => $ambiente
            ],
            [],
            [
            ],
            $isJsonRequest = false
        );
    }

    /* ***********
    tipo: 1= Acuse Recibo 2= Confirmar Recepcion
    ********** */
    public function getFormDocumentAcuse($uuid, $tipo){
        return $this->makeRequest(
            'GET',
            "/api/v1/getformdocumentacuse/",
            [
                'uuid' => $uuid,
                'tipo' => $tipo
            ],
            [],
            [
            ],
            $isJsonRequest = false
        );
    }

    public function getDocumentAcuse($uuid){
        return $this->makeRequest(
            'GET',
            "/api/v1/getdocumentacuse/",
            [
                'uuid' => $uuid
            ],
            [],
            [
            ],
            $isJsonRequest = false
        );
    }

    public function updateFormDocument($array)
    {
        // return $array;
        return $this->makeRequest(
            'POST',
            "/api/v1/updateformdocument",
            [],
            [
                "uuid" => $array['uuid'] ?? '',
                "apellidos" => $array['apellidos'] ?? '',
                "area" => $array['area'] ?? '',
                "dv" => $array['dvoriginal'] ?? '',
                "identificacion" => $array['nit'] ?? '',
                "primer_nombre" => $array['primer_nombre'] ?? '',
                "rol" => $array['rol'] ?? '',
                "segundo_nombre" => $array['segundo_nombre'] ?? '', 
                "tip_iden" => $array['tip_iden'] ?? '',
                "tipo" => $array['tipo'] ?? '',
                "claim_code" => $array['claim_code'] ?? '',
                "json_response" => $array['json_response'] ?? ''
            ],
            [
            ],
            $isJsonRequest = false
        );
    }

    public function updateDocument($array)
    {
        return $this->makeRequest(
            'POST',
            "/api/v1/updatedocument",
            [],
            [
                "uuid" => $array['uuid'],
                "estado_dian" => $array['estado_dian'],
                "acusado" => $array['acusado'],
                "confirma_recepcion" => $array['confirma_recepcion'],
                "aceptado" => $array['aceptado'],
                "rechazado" => $array['rechazado'],
                "json" => isset($array['json']) ? $array['json'] : '',
            ],
            [
            ],
            $isJsonRequest = true
        );
    }
}
