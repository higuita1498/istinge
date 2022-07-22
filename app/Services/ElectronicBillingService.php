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
        $this->baseUri = "https://apine.efacturacadena.com";
        $this->authorizationToken = "42e5b496-d882-4041-97ec-e3e91750805f990ef12f-36ff-454b-b020-fb19e953c37397478011-edaf-4f66-9945-81b691a718b118213614-da33-4e7b-9c22-ce78e0d55cf0";
        $this->partnershipId = 1128464945;
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
