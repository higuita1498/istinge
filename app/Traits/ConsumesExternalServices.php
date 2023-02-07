<?php

namespace App\Traits;

use GuzzleHttp\Client;

trait ConsumesExternalServices
{

    public function makeRequest(string $method, string $requestUrl, array $queryParams = [], array $formParams = [], array $headers = [], bool $isJsonRequest = false)
    {

        if (!isset($this->options)) {
            $this->options = [];
        }

        $client = new Client(array_merge($this->options, [
            'base_uri' => $this->baseUri,
        ]));
        
        if (method_exists($this, 'resolveAuthorization')) {
            $this->resolveAuthorization($queryParams, $formParams, $headers);
        }
                
        // if($isJsonRequest){
        //     dd($this->baseUri,$queryParams,$formParams,$headers,$requestUrl);
        // }


        try {
            $response = $client->request($method, $requestUrl, [
                $isJsonRequest ? 'json' : 'form_params' => $formParams,
                'headers' => $headers,
                'query' => $queryParams,
            ]);
        } catch (\Throwable $th) { 
            Log::error($th);
            return $response = array(
                'statusCode' => 400,
                'errorMessage' => "Error al realizar la petición",
                'th' => $th
            );
            // return $this->decodeResponse($response);
        }
    
        $response = $response->getBody()->getContents();
          
        if (method_exists($this, 'decodeResponse')) {
            $response =  $this->decodeResponse($response);
        }
        
        
        return $response;
    }

    public function makeRequestTwo(string $method, string $requestUrl, array $queryParams = [], array $formParams = [], array $headers = [], bool $isJsonRequest = false)
    {

        if (!isset($this->options)) {
            $this->options = [];
        }

        $client = new Client(array_merge($this->options, [
            'base_uri' => $this->baseUri,
        ]));
        
        // dd($formParams);
        
        if (method_exists($this, 'resolveAuthorization')) {
            $this->resolveAuthorization($queryParams, $formParams, $headers);
        }
                
        // if($isJsonRequest){
        //     dd($this->baseUri,$queryParams,$formParams,$headers,$requestUrl);
        // }


        try {
            $response = $client->request($method, $requestUrl, [
                $isJsonRequest ? 'json' : 'form_params' => $formParams,
                'headers' => $headers,
                'query' => $queryParams,
            ]);
        } catch (\Throwable $th) { 
            Log::error($th);
            return $response = array(
                'statusCode' => 400,
                'errorMessage' => "Error al realizar la petición",
                'th' => $th
            );
            // return $this->decodeResponse($response);
        }
    
        $response = $response->getBody()->getContents();
          
        if (method_exists($this, 'decodeResponse')) {
            $response =  $this->decodeResponse($response);
        }
        
        
        return $response;
    }
}
