<?php

namespace App\Jobs;

use App\Models\OutbrainCampaign;
use App\Models\OutbrainRedtrackReport;
use App\Models\RedtrackDomainStat;
use App\Models\RedtrackReport;
use App\Models\UserTracker;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullOutbrainRedTrack implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $campaign;

    /**
     * Create a new job instance.
     *
     * @param  OutbrainCampaign  $campaign
     * @return void
     */
    public function __construct(OutbrainCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws GuzzleException
     */
    public function handle()
    {
        $tracker = UserTracker::where('provider_id', $this->campaign->provider_id)
            ->where('provider_open_id', $this->campaign->open_id)
            ->first();

        if ($tracker) {
            $client = new Client();
            $date = Carbon::now()->format('Y-m-d');
            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=hour_of_day&sub6=' . $this->campaign->campaign_id . '&tracks_view=true';
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);

            foreach ($data as $i => $value) {
                $value['date'] = $date;
                $value['user_id'] = $this->campaign->user_id;
                $value['campaign_id'] = $this->campaign->id;
                $redtrack_report = OutbrainRedtrackReport::firstOrNew([
                    'date' => $date,
                    'sub6' => $this->campaign->campaign_id,
                    'hour_of_day' => $value['hour_of_day']
                ]);
                foreach (array_keys($value) as $array_key) {
                    $redtrack_report->{$array_key} = $value[$array_key];
                }
                $redtrack_report->save();
            }

            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub1&sub6=' . $this->campaign->campaign_id . '&tracks_view=true';
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);

            foreach ($data as $i => $value) {
                $value['date'] = $date;
                $value['user_id'] = $this->campaign->user_id;
                $value['campaign_id'] = $this->campaign->id;
                $redtrack_report = RedtrackDomainStat::firstOrNew([
                    'date' => $date,
                    'sub1' => $value['sub1']
                ]);
                foreach (array_keys($value) as $array_key) {
                    $redtrack_report->{$array_key} = $value[$array_key];
                }
                $redtrack_report->save();
            }
        }

    }
}
