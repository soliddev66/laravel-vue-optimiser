<?php

namespace App\Jobs;

use App\Endpoints\TaboolaAPI;
use App\Models\TaboolaCampaign;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullTaboolaCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $provider;

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
        $this->provider = Provider::where('slug', 'taboola')->firstOrFail();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws GuzzleException
     */
    public function handle()
    {
        foreach ($this->user->providers()->where('provider_id', $this->provider->id)->get() as $key => $provider) {
            $api = new TaboolaAPI($provider);

            $advertiser_ids = collect($api->getAdvertisers()['advertisers'])->pluck('id');
            $campaigns = collect([]);

            $advertiser_ids->each(function ($id) use (&$campaigns, $api) {
                $campaigns_by_advertiser = $api->getCampaignsByAdvertiserId($id);
                if (in_array('campaigns', $campaigns_by_advertiser)) {
                    $campaigns_by_advertiser = $campaigns_by_advertiser['campaigns'];
                    foreach ($campaigns_by_advertiser as $campaign) {
                        $campaigns->push($campaign);
                    }
                }
            });

            $campaigns->each(function ($campaign) use ($provider) {
                $data = collect($campaign)->keyBy(function ($value, $key) {
                    return Str::of($key)->snake();
                });

                $data['campaign_id'] = $data['id'];
                unset($data['id']);

                $db_campaign = TaboolaCampaign::firstOrNew(['campaign_id' => $data['campaign_id']]);
                $db_campaign->provider_id = $provider->id;
                $db_campaign->open_id = $provider->open_id;
                $db_campaign->user_id = $this->user->id;

                foreach (array_keys($data->toArray()) as $index => $array_key) {
                    $db_campaign->{$array_key} = $data[$array_key];
                }

                $db_campaign->save();
            });
        }
    }
}
