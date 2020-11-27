<?php

namespace App\Endpoints;

use Carbon\Carbon;
use App\Helpers\TaboolaClient;

class TaboolaAPI
{
    private $client;

    public function __construct($user_info)
    {
        $this->client = new TaboolaClient($user_info);
    }

    public function getAdvertisers()
    {
        return $this->client->call('GET', 'advertiser');
    }
}