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
    private $userProvider;
    private $outbrainAPI;

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
        $this->userProvider = UserProvider::where('user_id', $this->user->id)->latest()->firstOrFail();
        $this->outbrainAPI = new OutbrainAPI($this->userProvider);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws GuzzleException
     */
    public function handle()
    {
        $marketersIds = collect($this->outbrainAPI->getMarketers()['marketers'])->pluck('id');
        $campaigns = collect([]);

        $marketersIds->each(function ($id) use (&$campaigns) {
            $campaignsByMarketer = $this->outbrainAPI->getCampaignsByMarketerId($id)['campaigns'];
            foreach ($campaignsByMarketer as $campaign) {
                $campaigns->push($campaign);
            }
        });

        $campaigns->each(function ($campaign) {
            $data = collect($campaign)->keyBy(function ($value, $key) {
                return Str::of($key)->snake();
            });

            $data['campaign_id'] = $data['id'];
            unset($data['id']);

            $dbCampaign = OutbrainCampaign::firstOrNew(['campaign_id' => $data['campaign_id']]);
            $dbCampaign->provider_id = $this->userProvider->id;
            $dbCampaign->open_id = $this->userProvider->open_id;
            $dbCampaign->user_id = $this->user->id;

            foreach (array_keys($data->toArray()) as $index => $array_key) {
                $dbCampaign->{$array_key} = $data[$array_key];
            }

            $dbCampaign->save();
        });
    }
}
