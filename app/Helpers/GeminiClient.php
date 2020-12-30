<?php

namespace App\Helpers;

use Token;
use Exception;

use GuzzleHttp\Client;

class GeminiClient
{
    private $user_info;

    public function __construct($user_info) {
        $this->user_info = $user_info;
    }

    public function call($method, $endpoint, $body = null) {
        $client = new Client();

        $url = env('BASE_URL') . '/v3/rest/' . $endpoint;

        $request_body = ['headers' => [
            'Authorization' => 'Bearer ' . $this->user_info->token,
            'Content-Type' => 'application/json'
        ]];

        if ($body) {
            $request_body['body'] = json_encode($body);
        }

        try {
            $response = $client->request($method, $url, $request_body);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($this->user_info, function () use ($client, $method, $url, $request_body, &$response) {
                    $request_body['headers']['Authorization'] = 'Bearer ' . $this->user_info->token;
                    $response = $client->request($method, $url, $request_body);
                });
            } else {
                throw $e;
            }
        }

        return json_decode($response->getBody(), true)['response'];
    }
}