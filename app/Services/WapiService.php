<?php
namespace App\Services;

use App\Traits\ConsumesExternalServices;
use GuzzleHttp\Client;

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
            "/instance/" . $uuid,
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
            "/session/" . $uuid,
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
            "/message/send/" . $uuid,
            [],
            $body,
            $this->headers,
            true
        );
    }
}
