<?php

namespace App\Utils\AdVendors;

use App\Jobs\PullCampaign;
use App\Models\Campaign;
use GuzzleHttp\Exception\GuzzleException;

class Taboola extends Root
{
    private function api()
    {
        //
    }

    public function languages()
    {
        //
    }

    public function countries()
    {
        //
    }

    /**
     * @return mixed
     * @throws GuzzleException
     */
    public function advertisers()
    {
        //
    }

    public function store()
    {
        //
    }

    public function update(Campaign $campaign)
    {
        //
    }

    public function getCampaignInstance(Campaign $campaign)
    {
        //
    }

    public function cloneCampaignName(&$instance)
    {
        //
    }

    public function status(Campaign $campaign)
    {
        //
    }

    public function pullCampaign($user_provider)
    {
        //
    }

    public function pullAdGroup($user_provider)
    {
        //
    }

    public function pullAd($user_provider)
    {
        //
    }

    public function pullRedTrack($campaign)
    {
        //
    }
}
