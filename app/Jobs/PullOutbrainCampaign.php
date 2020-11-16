<?php

namespace App\Jobs;

use App\Endpoints\OutbrainAPI;
use App\Models\OutbrainCampaign;
use App\Models\Provider;
use App\Models\User;
use App\Models\UserProvider;
use App\Vngodev\Token;
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
    private $provider;
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
        $this->provider = Provider::where('slug', 'outbrain')->firstOrFail();
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
            $api = new OutbrainAPI($provider);

            $marketers_ids = collect($api->getMarketers()['marketers'])->pluck('id');
            $campaigns = collect([]);

            $marketers_ids->each(function ($id) use (&$campaigns, $api) {
                $campaigns_by_marketer = $api->getCampaignsByMarketerId($id);
                if (in_array('campaigns', $campaigns_by_marketer)) {
                    $campaigns_by_marketer = $campaigns_by_marketer['campaigns'];
                    foreach ($campaigns_by_marketer as $campaign) {
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

                $db_campaign = OutbrainCampaign::firstOrNew(['campaign_id' => $data['campaign_id']]);
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
