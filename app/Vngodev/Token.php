<?php

namespace App\Vngodev;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Token
 */
class Token
{
    public $db_provider;

    function __construct($db_provider)
    {
        $this->db_provider = $db_provider;
    }

    public function refresh()
    {
        $postKey = (version_compare(ClientInterface::MAJOR_VERSION, '6') === 1) ? 'form_params' : 'body';
        $client = new Client();

        $basic_auth_key = base64_encode(env('YAHOO_CLIENT_ID') . ':' . env('YAHOO_CLIENT_SECRET'));

        $response = $client->post('https://api.login.yahoo.com/oauth2/get_token', [
            'headers' => [
                'Authorization' => 'Basic ' . $basic_auth_key,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            $postKey => [
                'client_id' => env('YAHOO_CLIENT_ID'),
                'client_secret' => env('YAHOO_CLIENT_SECRET'),
                'redirect_uri' => env('YAHOO_REDIRECT'),
                'refresh_token' => $this->db_provider->refresh_token,
                'grant_type' => 'refresh_token'
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        return $data;
    }
}