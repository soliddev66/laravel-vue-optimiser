<?php

namespace App\Utils\AdVendors;

use Exception;

use App\Models\Provider;
use App\Endpoints\TwitterAPI;

class Twitter extends Root
{
    public function advertisers()
    {
        $provider = Provider::where('slug', request('provider'))->first();
        $twitter = new TwitterAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first());

        return $twitter->getAdvertisers();
    }
}
