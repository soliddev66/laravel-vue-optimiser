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
        $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first(), request('selectedAdvertiser') ?? null);
        $advertisers = $api->getAdvertisers();

        $accounts = [];

        foreach ($advertisers as $advertiser) {
            $accounts[] = [
                'id' => $advertiser->getId(),
                'name' => $advertiser->getName()
            ];
        }
        return $accounts;
    }

    public function signUp()
    {
        $provider = Provider::where('slug', request('provider'))->first();
        $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first(), request('selectedAdvertiser') ?? null);

        $account = $api->createAccount();

        return [
            'id' => $account->getId(),
            'name' => $account->getName()
        ];
    }

    public function fundingInstruments()
    {
        $provider = Provider::where('slug', request('provider'))->first();
        $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first(), request('selectedAdvertiser') ?? null);

        $funding_instruments = $api->getFundingInstruments();

        var_dump($funding_instruments);

        return [
            'id' => $account->id,
            'name' => $account->name
        ];
    }

    public function createFundingInstrument()
    {

    }
}
