<?php

namespace App\Helper;

use Token;
use GuzzleHttp\Client;

class GeminiAPI
{
    private $user_info;

    public function __construct($user_info) {
        $this->user_info = $user_info;
    }

    public function call($method, $url, $body = null) {
        $client = new Client();

        $requestBody = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->user_info->token,
                'Content-Type' => 'application/json'
            ]
        ];

        if ($body) {
            $requestBody['body'] = json_encode($body);
        }

        try {
            $response = $client->request($method, $url, $requestBody);
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Token::refresh($this->user_info, function () use ($client, $method, $url, $requestBody, &$response) {
                    $requestBody['headers']['Authorization'] = 'Bearer ' . $this->user_info->token;
                    $response = $client->request($method, $url, $requestBody);
                });
            } else {
                throw $e;
            }
        }

        return json_decode($response->getBody(), true);
    }
}