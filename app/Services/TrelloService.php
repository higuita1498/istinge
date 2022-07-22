<?php

namespace App\Services;

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use App\Traits\ConsumesExternalServices;

class TrelloService
{
    use ConsumesExternalServices;

    protected $baseUri;

    protected $secretToken;

    protected $secretKey;

    protected $boardId;


    public function __construct()
    {
        $this->baseUri = config('services.trello.base_uri');
        $this->secretToken = config('services.trello.secret_token');
        $this->secretKey = config('services.trello.secret_key');
        $this->boardId = config('services.trello.board_id');
    }

    public function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $queryParams['key'] = $this->secretKey;
        $queryParams['token'] = $this->secretToken;
    }

    public function decodeResponse($response)
    {
        return json_decode($response);
    }

    public function getBoard()
    {
        return $this->makeRequest(
            'GET',
            "/1/boards/{$this->boardId}",
            [],
            [],
            [],
            $isJsonRequest = true
        );
    }

    public function getListsBoard()
    {
        return $this->makeRequest(
            'GET',
            "/1/boards/{$this->boardId}/lists",
            [],
            [],
            [],
            $isJsonRequest = true
        );
    }


    public function getCardsList(string $listId)
    {
        try {
            return $this->makeRequest(
                'GET',
                "/1/lists/{$listId}/cards",
                [],
                [],
                [],
                $isJsonRequest = true
            );
        } catch (RequestException $e) {

            return Psr7\Message::toString($e->getResponse());
        }
    }


    public function getCard(string $cardId)
    {
        try {
            return $this->makeRequest(
                'GET',
                "/1/cards/{$cardId}",
                [],
                [],
                [],
                $isJsonRequest = true
            );
        } catch (RequestException $e) {

            return Psr7\Message::toString($e->getResponse());
        }
    }


    public function getCardMembers(string $cardId)
    {
        try {
            return $this->makeRequest(
                'GET',
                "/1/cards/{$cardId}/members",
                [],
                [],
                [],
                $isJsonRequest = true
            );
        } catch (RequestException $e) {

            return Psr7\Message::toString($e->getResponse());
        }
    }


    public function getMember(string $memberId)
    {
        try {
            return $this->makeRequest(
                'GET',
                "/1/members/{$memberId}",
                [],
                [],
                [],
                $isJsonRequest = true
            );
        } catch (RequestException $e) {

            return Psr7\Message::toString($e->getResponse());
        }
    }


    public function getCardLabels(string $cardId)
    {
        try {
            return $this->makeRequest(
                'GET',
                "/1/cards/{$cardId}/labels",
                [],
                [],
                [],
                $isJsonRequest = true
            );
        } catch (RequestException $e) {

            return Psr7\Message::toString($e->getResponse());
        }
    }
    

    public function getLabel(string $labelId)
    {
        try {
            return $this->makeRequest(
                'GET',
                "/1/labels/{$labelId}",
                [],
                [],
                [],
                $isJsonRequest = true
            );
        } catch (RequestException $e) {

            return Psr7\Message::toString($e->getResponse());
        }
    }
}
