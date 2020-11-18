<?php

namespace App\Endpoints;

use Carbon\Carbon;

use App\Helpers\GeminiClient;

use Hborras\TwitterAdsSDK\TwitterAds;

class TwitterAPI
{
    private $client;

    public function __construct($user_info, $account_id)
    {
        $this->client = TwitterAds::init(env('TWITTER_CLIENT_ID'), env('TWITTER_CLIENT_SECRET'), $user_info->token, $user_info->secret_token, $account_id, env('TWITTER_SANDBOX'));
    }

    public function getAdvertisers()
    {
        return $this->client->getAccounts()->getCollection();
    }

    public function createAccount()
    {
        return $this->client->post('accounts')->getBody()->data;
    }
}