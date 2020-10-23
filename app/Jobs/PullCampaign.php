<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\User;
use GuzzleHttp\Client;
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
        foreach ($this->user->providers as $key => $provider) {
            try {
                $data = $this->getCampaigns($provider);
                $this->saveCampaigns($data['response'], $provider);
                $this->cleanData($data['response'], $provider);
            } catch (Exception $e) {
                if ($e->getCode() == 401) {
                    Token::refresh($provider, function () use ($provider) {
                        $data = $this->getCampaigns($provider);
                        $this->saveCampaigns($data['response'], $provider);
                    });
                }
            }
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
        //
    }
}
