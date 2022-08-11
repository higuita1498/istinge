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
        $this->baseUri = "https://apine.efacturacadena.com";
        $this->authorizationToken = "42e5b496-d882-4041-97ec-e3e91750805f990ef12f-36ff-454b-b020-fb19e953c37397478011-edaf-4f66-9945-81b691a718b118213614-da33-4e7b-9c22-ce78e0d55cf0";
        $this->nitAlianza = "1128464945";
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
