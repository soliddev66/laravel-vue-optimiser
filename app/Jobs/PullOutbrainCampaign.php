<?php

namespace App\Jobs;

use App\Endpoints\OutbrainAPI;
use App\Models\OutbrainCampaign;
use App\Models\User;
use App\Models\UserProvider;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class PullOutbrainCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $user_provider;
    private $outbrain_api;

    /**
     * Create a new job instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;

        // Other
        $this->user_provider = UserProvider::where('user_id', $this->user->id)->latest()->firstOrFail();
        $this->outbrain_api = new OutbrainAPI($this->user_provider);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws GuzzleException
     */
    public function handle()
    {
        $marketers_ids = collect($this->outbrain_api->getMarketers()['marketers'])->pluck('id');
        $campaigns = collect([]);

        $marketers_ids->each(function ($id) use (&$campaigns) {
            $campaigns_by_marketer = $this->outbrain_api->getCampaignsByMarketerId($id)['campaigns'];
            foreach ($campaigns_by_marketer as $campaign) {
                $campaigns->push($campaign);
            }
        });

        $campaigns->each(function ($campaign) {
            $data = collect($campaign)->keyBy(function ($value, $key) {
                return Str::of($key)->snake();
            });

            $data['campaign_id'] = $data['id'];
            unset($data['id']);

            $db_campaign = OutbrainCampaign::firstOrNew(['campaign_id' => $data['campaign_id']]);
            $db_campaign->provider_id = $this->user_provider->id;
            $db_campaign->open_id = $this->user_provider->open_id;
            $db_campaign->user_id = $this->user->id;

            foreach (array_keys($data->toArray()) as $index => $array_key) {
                $db_campaign->{$array_key} = $data[$array_key];
            }

            $db_campaign->save();
        });
    }
}
