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
        $this->baseUri = config('services.billing.base_uri');
        $this->authorizationToken = config('services.billing.authorization_token');
        $this->partnershipId = config('services.billing.partnership_id');
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
