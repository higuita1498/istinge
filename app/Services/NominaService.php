<?php

namespace App\Services;

use App\Traits\ConsumesExternalServices;

class NominaService
{
    use ConsumesExternalServices;

    protected $baseUri;

    protected $authorizationToken;

    protected $nitAlianza;


    public function __construct()
    {
        $this->baseUri = config('services.nomina.base_uri');
        $this->authorizationToken = config('services.nomina.authorization_token');
        $this->nitAlianza = config('services.nomina.nit_alianza');
    }


    public function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $headers['nominaAuthorizationToken'] = $this->authorizationToken;
        $headers['nitAlianza'] = $this->nitAlianza;
    }

    public function decodeResponse($response)
    {
        return json_decode($response);
    }


    public function electronicPayrollStatus($employerNit, $payrollNumber, $xmlType = 102)
    {
        return $this->makeRequest(
            'GET',
            '/v1/ne/consulta/documentos',
            [
                'empleadorNit' => $employerNit,
                'numeroNomina' => $payrollNumber,
                'tipoXml' => $xmlType
            ],
            [],
            [],
            $isJsonRequest = false
        );
    }
}
