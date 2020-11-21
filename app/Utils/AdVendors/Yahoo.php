<?php

namespace App\Utils\AdVendors;

use Exception;

use App\Models\Provider;
use App\Endpoints\GeminiAPI;

class Yahoo extends Root
{
    public function advertisers()
    {
        $provider = Provider::where('slug', request('provider'))->first();
        $gemini = new GeminiAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first());

        return $gemini->getAdvertisers();
    }
}
