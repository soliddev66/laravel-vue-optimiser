<?php

namespace App\Helpers;

use Token;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OutbrainClient
{
    private $user_provider;
    private $response_key;

    public function __construct($user_provider, $response_key = 'response') {
        $this->user_provider = $user_provider;
        $this->response_key = $response_key;
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
        // $url = 'https://private-6ce1a-amplifyv01.apiary-mock.com/' . $endpoint;
        $url = config('services.outbrain.api_endpoint') . '/amplify/v0.1/' . $endpoint;
        $request_body = ['headers' => ['OB-TOKEN-V1' => $this->user_provider->token]];

        if ($body) {
            $request_body['body'] = json_encode($body);
        }

        try {
            $response = $client->request($method, $url, $request_body);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refreshOutbrain($this->user_provider, function () use ($client, $method, $url, $request_body, &$response) {
                    $request_body['headers']['OB-TOKEN-V1'] = $this->user_provider->token;
                    $response = $client->request($method, $url, $request_body);
                });
            } else {
                throw $e;
            }
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
