<?php

namespace App\Helpers;

use Token;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OutbrainClient
{
    private $userProvider;
    private $responseKey;

    public function __construct($userProvider, $responseKey = 'response') {
        $this->userProvider = $userProvider;
        $this->responseKey = $responseKey;
    }

    /**
     * @param  $method
     * @param  $endpoint
     * @param  null  $body
     * @return mixed
     * @throws GuzzleException
     */
    public function call($method, $endpoint, $body = null) {
        $client = new Client();
        $url = config('services.outbrain.api_endpoint') . '/amplify/v0.1/' . $endpoint;
        $requestBody = ['headers' => ['OB-TOKEN-V1' => $this->userProvider->token]];

        if ($body) {
            $requestBody['body'] = json_encode($body);
        }

        try {
            $response = $client->request($method, $url, $requestBody);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($this->userProvider, function () use ($client, $method, $url, $requestBody, &$response) {
                    $requestBody['headers']['OB-TOKEN-V1'] = $this->userProvider->token;
                    $response = $client->request($method, $url, $requestBody);
                });
            } else {
                throw $e;
            }
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
