<?php

namespace App\Utils\AdVendors;

use Exception;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

use App\Models\Campaign;
use App\Models\Provider;
use App\Models\UserTracker;
use App\Models\RedtrackReport;
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

    public function getCampaignInstance(Campaign $campaign)
    {
        try {
            $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first(), $campaign->advertiser_id);

            $instance = $api->getCampaign($campaign->campaign_id)->toArray();

            $instance['provider'] = $campaign->provider->slug;
            $instance['open_id'] = $campaign['open_id'];
            $instance['advertiser_id'] = $campaign->advertiser_id;
            $instance['instance_id'] = $campaign['id'];

            $instance['adGroups'] = [];

            $ad_groups = $api->getAdGroups($campaign->campaign_id);

            if ($ad_groups && count($ad_groups) > 0) {
                foreach ($ad_groups as $ad_group) {
                    $instance['adGroups'][] = $ad_group->toArray();
                }

                $promoted_tweets = $api->getPromotedTweet($ad_groups[0]->getId());

                $tweets = $api->getTweet($promoted_tweets[0]->getTweetId());

                $instance['promoted_tweet_id'] = $promoted_tweets[0]->getId();

                $instance['ads'] = [];

                if ($tweets && count($tweets) > 0) {
                    foreach ($tweets as $tweet) {
                        $instance['ads'][] = $tweet->toArray();
                    }
                }
            }

            return $instance;
        } catch (Exception $e) {
            return [];
        }
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
                $campaign_data = $api->saveCampaign();
            } catch (Exception $e) {
                throw $e;
            }

            try {
                $line_item_data = $api->saveLineItem($campaign_data);
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
                return [
                    'errors' => [$e->getErrors()[0]->message]
                ];
            } else {
                return [
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        return [];
    }

    public function update(Campaign $campaign)
    {
        try {
            $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first(), $campaign->advertiser_id);

            try {
                $campaign_data = $api->saveCampaign($campaign->campaign_id);
            } catch (Exception $e) {
                throw $e;
            }

            try {
                $line_item_data = $api->saveLineItem($campaign_data, request('adGroupID'));
            } catch (Exception $e) {
                throw $e;
            }

            if (!request('saveCard')) {
                try {
                    $api->deletePromotedTweet(request('promotedAdID'));
                } catch (Exception $e) {
                    throw $e;
                }

                try {
                    $promotable_users = $api->getPromotableUsers();
                    $media = $api->uploadMedia($promotable_users);
                    $media_library = $api->createMediaLibrary($media->media_key);
                } catch (Exception $e) {
                    throw $e;
                }

                try {
                    $card_data = $api->createWebsiteCard($media->media_key);
                } catch (Exception $e) {
                    throw $e;
                }

                try {
                    $tweet_data = $api->createTweet($card_data, $promotable_users);
                } catch (Exception $e) {
                    throw $e;
                }

                try {
                    $promoted_tweet = $api->createPromotedTweet($line_item_data, $tweet_data);
                } catch (Exception $e) {
                    throw $e;
                }
            }

            PullCampaign::dispatch(auth()->user());
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }

        return [];
    }

    public function delete(Campaign $campaign)
    {
        try {
            $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first(), $campaign->advertiser_id);
            $api->deleteCampaign($campaign->campaign_id);
            $campaign->delete();
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }

        return [];
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

                    $campaign->advertiser_id = $advertiser->getId();

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
