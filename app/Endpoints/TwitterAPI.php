<?php

namespace App\Endpoints;

use Carbon\Carbon;

use App\Helpers\GeminiClient;

use Hborras\TwitterAdsSDK\TwitterAds;

class TwitterAPI
{
    private $client;

    public function __construct($user_info)
    {
        $this->client = TwitterAds::init(env('TWITTER_CLIENT_ID'), env('TWITTER_CLIENT_SECRET'), $user_info->token, $user_info->secret_token, null, env('TWITTER_SANDBOX'));
    }

    public function getAdvertisers()
    {
        return $this->client->getAccounts()->getCollection();
    }
}