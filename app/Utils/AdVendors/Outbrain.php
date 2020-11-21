<?php

namespace App\Utils\AdVendors;

use App\Endpoints\OutbrainAPI;
use Exception;

use App\Models\Provider;
use App\Endpoints\GeminiAPI;
use GuzzleHttp\Exception\GuzzleException;

class Outbrain extends Root
{
    /**
     * @return mixed
     * @throws GuzzleException
     */
    public function advertisers()
    {
        $provider = Provider::where('slug', request('provider'))->first();
        $outbrain = new OutbrainAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first());

        return $outbrain->getMarketers();
    }
}
