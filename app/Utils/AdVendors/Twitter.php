<?php

namespace App\Utils\AdVendors;

use App\Endpoints\TwitterAPI;
use App\Jobs\PullCampaign;
use App\Models\Campaign;
use App\Models\Provider;
use Exception;
use Hborras\TwitterAdsSDK\TwitterAdsException;
use Illuminate\Support\Str;

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
                $promotable_users = $this->api()->getPromotableUsers();
                $media = $this->api()->uploadMedia($promotable_users);
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
                // TO-DO: Dispatch job
                // $campaign_data->delete();
                throw $e;
            }

            try {
                $card_data = $api->createWebsiteCard($media->media_key);
            } catch (Exception $e) {
                // TO-DO: Dispatch job
                // $campaign_data->delete();
                // $line_item_data->delete();
                throw $e;
            }

            try {
                $tweet_data = $api->createTweet($card_data, $promotable_users);
            } catch (Exception $e) {
                // TO-DO: Dispatch job
                // $campaign_data->delete();
                // $line_item_data->delete();
                // $card_data->delete();
                throw $e;
            }

            try {
                $promoted_tweet = $api->createPromotedTweet($line_item_data, $tweet_data);
            } catch (Exception $e) {
                // TO-DO: Dispatch job
                // $campaign_data->delete();
                // $line_item_data->delete();
                // $card_data->delete();
                // $tweet_data->delete();
                throw $e;
            }

            // try {
            //     $data = [
            //         'previewData' => $api->getTweetPreviews($tweet_data->id)
            //     ];
            // } catch (Exception $e) {
            //     throw $e;
            // }

            PullCampaign::dispatch(auth()->user());
        } catch (Exception $e) {
            if ($e instanceof TwitterAdsException && is_array($e->getErrors())) {
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
        $advertisers = (new TwitterAPI($user_provider))->getAdvertisers();

        $campaign_ids = [];

        foreach ($advertisers as $advertiser) {
            $campaigns = (new TwitterAPI($user_provider, $advertiser->getId()))->getCampaigns();

            if (is_array($campaigns)) {
                foreach ($campaigns as $item) {
                    $campaign = Campaign::firstOrNew([
                        'campaign_id' => $item->getId(),
                        'provider_id' => $user_provider->provider_id,
                        'user_id' => $user_provider->user_id,
                        'open_id' => $user_provider->open_id
                    ]);

                    $campaign->name = $item->getName();
                    $campaign->status = $item->getEntityStatus();
                    $campaign->budget = $item->getTotalBudgetAmountLocalMicro() ? ($item->getTotalBudgetAmountLocalMicro() / 1000000) : ($item->getDailyBudgetAmountLocalMicro() / 1000000);
                    $campaign->save();
                    $campaign_ids[] = $campaign->id;
                }
            }
        }

        Campaign::where([
            'user_id' => $user_provider->user_id,
            'provider_id' => $user_provider->provider_id,
            'open_id' => $user_provider->open_id
        ])->whereNotIn('id', $campaign_ids)->delete();
    }

    public function pullRedTrack($campaign)
    {
        $tracker = UserTracker::where('provider_id', $campaign->provider_id)
            ->where('provider_open_id', $campaign->open_id)
            ->first();

        if ($tracker) {
            $client = new Client();
            $date = Carbon::now()->format('Y-m-d');
            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=hour_of_day&sub3=[' . Str::of($campaign->name)->snake() . ']&sub9=Twitter&tracks_view=true';
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);

            foreach ($data as $i => $value) {
                $value['date'] = $date;
                $value['user_id'] = $campaign->user_id;
                $value['campaign_id'] = $campaign->id;
                $value['provider_id'] = $campaign->provider_id;
                $value['open_id'] = $campaign->open_id;
                $redtrack_report = RedtrackReport::firstOrNew([
                    'date' => $date,
                    'sub3' => '[' . Str::of($campaign->name)->snake() . ']',
                    'hour_of_day' => $value['hour_of_day']
                ]);
                foreach (array_keys($value) as $array_key) {
                    $redtrack_report->{$array_key} = $value[$array_key];
                }
                $redtrack_report->save();
            }
        }
    }
}
