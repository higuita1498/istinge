<?php

namespace App\Services;

use App\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\ClientException;

class ElectronicBillingService
{
    use ConsumesExternalServices;

    protected $baseUri;

    protected $authorizationToken;

    protected $partnershipId;

    public function __construct()
    {
        $this->baseUri = "https://apivp.efacturacadena.com/";
        $this->authorizationToken = "62808bf1-d446-46ee-8120-00162e95c059";
        $this->partnershipId = "1128464945";
    }


    public function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $headers['efacturaAuthorizationToken'] = $this->authorizationToken;
        $headers['Partnership-Id'] = $this->partnershipId;
    }

    public function decodeResponse($response)
    {
        return json_decode($response); 
    }


    public function electronicInvoiceStatus($companyNit, string $documentId, string $prefix, string $documentType = '01')
    {
        try {
            return $this->makeRequest(
                'GET',
                'v1/vp/consulta/documentos',
                [
                    'nit_emisor' => $companyNit,
                    'id_documento' => $documentId,
                    'codigo_tipo_documento' => $documentType,
                    'prefijo' => $prefix,
                ],
                [],
                [],
                $isJsonRequest = false
            );
        }  catch (ClientException $e) {
            return $e->getResponse();
        }
    }
}
