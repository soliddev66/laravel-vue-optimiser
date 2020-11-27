<?php

namespace App\Utils\AdVendors;

use Exception;

use App\Models\Campaign;
use App\Models\Provider;
use App\Endpoints\TwitterAPI;

use App\Jobs\PullCampaign;

use Hborras\TwitterAdsSDK\TwitterAdsException;

class Twitter extends Root
{
    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->first();
        return new TwitterAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first(), request('advertiser') ?? null);
    }

    public function advertisers()
    {
        $advertisers = $this->api()->getAdvertisers();

        $result = [];

        foreach ($advertisers as $advertiser) {
            $result[] = [
                'id' => $advertiser->getId(),
                'name' => $advertiser->getName()
            ];
        }
        return $result;
    }

    public function countries()
    {
        return $this->api()->getCountries();
    }

    public function signUp()
    {
        $account = $this->api()->createAccount();

        return [
            'id' => $account->getId(),
            'name' => $account->getName()
        ];
    }

    public function fundingInstruments()
    {
        $funding_instruments = $this->api()->getFundingInstruments();

        $result = [];

        foreach ($funding_instruments as $funding_instrument) {
            $result[] = [
                'id' => $funding_instrument->getId(),
                'name' => $funding_instrument->getName()
            ];
        }
        return $result;
    }

    public function adGroupCategories()
    {
        return $this->api()->getAdGroupCategories();
    }

    public function store()
    {
        $data = [];
        $api = $this->api();

        try {
            try {
                $media = $this->api()->uploadMedia();
                $media_library = $this->api()->createMediaLibrary($media->media_key);
            } catch (Exception $e) {
                throw $e;
            }

            try {
                $campaign_data = $api->createCampaign();
            } catch (Exception $e) {
                throw $e;
            }

            try {
                $line_item_data = $api->createLineItem($campaign_data);
            } catch (Exception $e) {
                $campaign_data->delete();
                throw $e;
            }

            try {
                $card_data = $api->createWebsiteCard($media->media_key);
            } catch (Exception $e) {
                $campaign_data->delete();
                $line_item_data->delete();
                throw $e;
            }

            try {
                $tweet_data = $api->createTweet($card_data, $user_info->open_id);
            } catch (Exception $e) {
                $campaign_data->delete();
                $line_item_data->delete();
                $card_data->delete();
                throw $e;
            }

            try {
                $promoted_tweet = $api->createPromotedTweet($line_item_data, $tweet_data);
            } catch (Exception $e) {
                $campaign_data->delete();
                $line_item_data->delete();
                $card_data->delete();
                $tweet_data->delete();
                throw $e;
            }

            try {
                // $data = [
                //     'previewData' => $api->getTweetPreviews($tweet_data->id)
                // ];
            } catch (Exception $e) {
                throw $e;
            }

            PullCampaign::dispatch(auth()->user());
        } catch (Exception $e) {
            if ($e instanceof TwitterAdsException) {
                $data = [
                    'errors' => [$e->getErrors()[0]->message]
                ];
            } else {
                $data = [
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        return $data;
    }

    public function pullCampaign($user_provider)
    {

    }
}
