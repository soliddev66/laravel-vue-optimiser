<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\User;
use App\Vngodev\Token;
use GuzzleHttp\Client;
use Hborras\TwitterAdsSDK\TwitterAds;
use Hborras\TwitterAdsSDK\TwitterAds\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class PullCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Yahoo Gemini
        foreach ($this->user->providers()->where('provider_id', 1)->get() as $key => $provider) {
            Token::refresh($provider, function () use ($provider) {
                $data = $this->getCampaigns($provider);
                $this->saveCampaigns($data['response'], $provider);
                $this->cleanData($data['response'], $provider);
            });
        }
        // Twitter
        foreach ($this->user->providers()->where('provider_id', 3)->get() as $key => $provider) {
            $api_key = env('TWITTER_CLIENT_ID');
            $api_secret = env('TWITTER_CLIENT_SECRET');
            $access_token = $provider->token;
            $access_token_secret = $provider->secret_token;

            // // Create Twitter Ads Api Instance
            // $api = TwitterAds::init($api_key, $api_secret, $access_token, $access_token_secret, null, env('TWITTER_SANDBOX'));
            // $accounts = $api->getAccounts();
        }
    }

    private function getCampaigns($provider)
    {
        $client = new Client();
        $response = $client->request('GET', env('BASE_URL') . '/v3/rest/campaign', [
            'headers' => [
                'Authorization' => 'Bearer ' . $provider->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private function saveCampaigns($campaigns, $provider)
    {
        foreach ($campaigns as $key => $campaign) {
            $data = collect($campaign)->keyBy(function ($value, $key) {
                return Str::of($key)->snake();
            });
            $data['user_id'] = $this->user->id;
            $data['campaign_id'] = $data['id'];
            $data['provider_id'] = $provider->id;
            $data['open_id'] = $provider->open_id;
            $data['name'] = $data['campaign_name'];
            $data = $data->toArray();
            unset($data['campaign_name']);
            unset($data['id']);
            $new_campaign = Campaign::firstOrNew(['campaign_id' => $data['campaign_id']]);
            foreach (array_keys($data) as $index => $array_key) {
                $new_campaign->{$array_key} = $data[$array_key];
            }
            $new_campaign->save();
        }
    }

    private function cleanData($campaigns, $provider)
    {
        $user_campaigns = $this->user->campaigns->toArray();
        if (count($campaigns) !== count($user_campaigns)) {
            foreach ($user_campaigns as $key => $user_campaign) {
                $should_delete = true;
                foreach ($campaigns as $campaign) {
                    if ($campaign['id'] == $user_campaign['campaign_id']) {
                        $should_delete = false;
                    }
                }
                if ($should_delete) {
                    Campaign::where('campaign_id', $user_campaign['campaign_id'])->first()->delete();
                }
            }
        }
    }
}
