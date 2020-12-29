<?php

namespace App\Endpoints;

use Carbon\Carbon;
use App\Helpers\TaboolaClient;

class TaboolaAPI
{
    private $client;

    private $account_id;

    public function __construct($user_info)
    {
        $this->account_id = $user_info->open_id;

        $this->client = new TaboolaClient($user_info);
    }

    public function getAdvertisers()
    {
        return $this->client->call('GET', $this->account_id . '/advertisers');
    }

    public function getCampaigns()
    {
        return $this->client->call('GET', 'valiantmedia-sc/campaigns/?fetch_level=R');
    }

    public function getCountries()
    {
        return $this->client->call('GET', 'resources/countries');
    }
}