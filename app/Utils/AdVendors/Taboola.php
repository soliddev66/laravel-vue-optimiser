<?php

namespace App\Utils\AdVendors;

use App\Models\Provider;
use App\Endpoints\TaboolaAPI;

class Taboola extends Root
{
    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->first();

        return new TaboolaAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first());
    }

    public function advertisers()
    {
        return $this->api()->getAdvertisers()['results'];
    }

    public function countries()
    {
        return $this->api()->getCountries()['results'];
    }
}