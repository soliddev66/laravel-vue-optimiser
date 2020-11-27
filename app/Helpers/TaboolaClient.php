<?php

namespace App\Helpers;

use Token;
use Exception;

use GuzzleHttp\Client;

class TaboolaClient
{
    private $user_info;

    public function __construct($user_info) {
        $this->user_info = $user_info;
    }

    public function call($method, $endpoint, $body = null) {
        $client = new Client();

        $url = config('services.outbrain.api_endpoint') . '/backstage/api/1.0/' . $endpoint;

        $api_request = ['headers' => [
            'Authorization' => 'Bearer ' . $this->user_info->token,
            'Content-Type' => 'application/json'
        ]];

        if ($body) {
            $api_request['body'] = json_encode($body);
        }

        try {
            $response = $client->request($method, $url, $api_request);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($this->user_info, function () use ($client, $method, $url, $api_request, &$response) {
                    $api_request['headers']['Authorization'] = 'Bearer ' . $this->user_info->token;
                    $response = $client->request($method, $url, $api_request);
                });
            } else {
                throw $e;
            }
        }

        return json_decode($response->getBody(), true)['response'];
    }
}