<?php

namespace App\Vngodev;

use App\Models\RedtrackReport;
use App\Models\User;
use App\Models\UserTracker;
use Carbon\Carbon;
use GuzzleHttp\Client;

/**
 * RedTrack
 */
class RedTrack
{
    public function __construct()
    {
        //
    }

    public static function crawl()
    {
        foreach (User::all() as $key => $user) {
            foreach ($user->campaigns as $index => $campaign) {
                $tracker = UserTracker::where('provider_id', $campaign->provider_id)->where('provider_open_id', $campaign->open_id)->first();
                if ($tracker) {
                    $client = new Client();
                    $date = Carbon::now()->format('Y-m-d');
                    $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub6,hour_of_day&sub6=' . $campaign->campaign_id . '&tracks_view=true';
                    $response = $client->get($url);

                    $data = json_decode($response->getBody(), true);

                    foreach ($data as $i => $value) {
                        $value['date'] = $date;
                        $value['user_id'] = $campaign->user_id;
                        $value['campaign_id'] = $campaign->id;
                        $redtrack_report = RedtrackReport::firstOrNew(['date' => $date, 'hour_of_day' => $value['hour_of_day']]);
                        foreach (array_keys($value) as $array_key) {
                            $redtrack_report->{$array_key} = $value[$array_key];
                        }
                        $redtrack_report->save();
                    }
                }
            }
        }
    }
}
