<?php
namespace App\Services;

use App\Traits\ConsumesExternalServices;
use GuzzleHttp\Client;

class EmisionesService
{
    use ConsumesExternalServices;

    protected $baseUri;

    protected $secretToken;

    protected $headers;

    public function __construct()
    {
        $this->baseUri = env('URL_EMISION_DIAN', 'http://127.0.0.1:8080');
        $this->secretToken = env('EMISION_TOKEN');
        $this->headers = [
            'cache-control' => 'no-cache',
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . env('EMISION_TOKEN'),
        ];
    }

    public function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $queryParams['token'] = $this->secretToken;
    }

    public function sendEmisionsEmpresa($params){
        return $this->makeRequest(
            "GET",
            $this->baseUri . "/estatus-emision-dian",
            [$params],
            [],
            $this->headers,
            true
        );
    }

    public function getInstance(string $uuid)
    {
        return $this->makeRequest(
            "GET",
            $this->baseUri . "/instance/" . $uuid,
            [],
            [],
            $this->headers,
            true
        );
    }

    public function initSession(string $uuid, string $apiKey)
    {
        $this->headers['Authorization'] = 'Bearer ' . $apiKey;
        return $this->makeRequest(
            "POST",
            $this->baseUri . "/session/" . $uuid,
            [],
            [],
            $this->headers,
            true
        );
    }

    public function sendMessageMedia(string $uuid, string $apiKey, array $body)
    {
        $this->headers['Authorization'] = 'Bearer ' . $apiKey;
        return $this->makeRequest(
            "POST",
            $this->baseUri . "/message/send/" . $uuid,
            [],
            $body,
            $this->headers,
            true
        );
    }
}
