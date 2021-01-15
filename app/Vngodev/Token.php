<?php

namespace App\Vngodev;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Token
 */
class Token
{
    function __construct()
    {
        //
    }

    public static function refresh($user_info, callable $callback = null)
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
                'refresh_token' => $user_info->refresh_token,
                'grant_type' => 'refresh_token'
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $user_info->token = $data['access_token'];
        $user_info->refresh_token = $data['refresh_token'];
        $user_info->expires_in = Carbon::now()->addSeconds($data['expires_in']);
        $user_info->save();

        if ($callback) {
            $callback();
        }
    }

    public static function refreshOutbrain($user_info, callable $callback = null)
    {
        $client = new Client();
        $response = $client->request('GET', config('services.outbrain.api_endpoint') . '/amplify/v0.1/login', [
            'Authorization' => 'Basic ' . $user_info->basic_auth
        ]);

        $user_info->token = json_decode($response->getBody()->getContents(), true)['OB-TOKEN-V1'];
        $user_info->expires_in = Carbon::now()->addDays(30);
        $user_info->save();

        if ($callback) {
            $callback();
        }
    }

    public static function refreshTaboola($user_info, callable $callback = null)
    {
        $client = new Client();
        $form_params = [
            'client_id' => config('services.taboola.client_id'),
            'client_secret' => config('services.taboola.client_secret'),
            'refresh_token' => $user_info->refresh_token,
            'grant_type' => 'refresh_token'
        ];
        $response = $client->request('POST', config('services.taboola.api_endpoint') . '/backstage/oauth/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => $form_params
        ]);
        $response_data = json_decode($response->getBody()->getContents(), true);

        $user_info->token = $response_data['access_token'];
        $user_info->refresh_token = $response_data['refresh_token'];
        $user_info->expires_in = Carbon::now()->addSeconds($response_data['expires_in']);
        $user_info->save();

        if ($callback) {
            $callback();
        }
    }

    public static function refreshYahooJP($user_info, callable $callback = null)
    {
        $client = new Client();

        $response = $client->get('https://biz-oauth.yahoo.co.jp/oauth/v1/token', [
            'query' => [
                'client_id' => env('YAHOOJP_CLIENT_ID'),
                'client_secret' => env('YAHOOJP_CLIENT_SECRET'),
                'refresh_token' => $user_info->refresh_token,
                'grant_type' => 'refresh_token'
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $user_info->token = $data['access_token'];
        $user_info->expires_in = Carbon::now()->addSeconds($data['expires_in']);
        $user_info->save();

        if ($callback) {
            $callback();
        }
    }
}