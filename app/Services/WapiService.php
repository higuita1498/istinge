<?php

namespace App\Services;

use App\Traits\ConsumesExternalServices;

class WapiService
{
    use ConsumesExternalServices;

    protected $baseUri;

    protected $secretToken;

    protected $headers;

    public function __construct()
    {
        $this->baseUri = env('WAPI_URL', 'http://127.0.0.1:8080');
        $this->secretToken = strval(env('WAPI_TOKEN'));
        $this->headers = [
            'cache-control' => 'no-cache',
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . env('WAPI_TOKEN'),
        ];
    }

    public function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $queryParams['token'] = $this->secretToken;
    }

    public function getInstance(string $uuid)
    {
        return $this->makeRequest(
            "GET",
            $this->baseUri . "/api/v1/channel/wbot/" . $uuid,
            [],
            [],
            $this->headers,
            true
        );
    }

    public function initSession(string $uuid)
    {
        return $this->makeRequest(
            "POST",
            $this->baseUri . "/api/v1/start-session/" . $uuid,
            [],
            [],
            $this->headers,
            true
        );
    }

    public function sendMessageMedia(string $uuid, string $apiKey, array $body)
    {
        return $this->makeRequest(
            "POST",
            $this->baseUri . "/api/v1/send/" . $uuid,
            [],
            $body,
            $this->headers,
            true
        );
    }
}
