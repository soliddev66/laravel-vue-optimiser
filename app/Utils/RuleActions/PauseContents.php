<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;
use App\Endpoints\GeminiAPI;

class PauseContents extends Root
{
    public function process($campaign, $ad, &$log)
    {
        $log['effect'] = [
            'campaign' => $campaign->name,
            'ad_group_id' => $ad->ad_group_id,
            'ad_id' => $ad->ad_id
        ];

        try {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $adVendorClass)->adStatus($campaign, $ad->ad_group_id, $ad->ad_id, Campaign::STATUS_PAUSED);
            $log['effect']['paused'] = true;
            echo 'Ad was being paused', "\n";
        } catch (Exception $e) {
            echo "Ad wasn't being paused\n";
            $log['effect']['paused'] = false;
            $log['effect']['message'] = $e->getMessage();
        }
    }

    public function visual($campaign, $ad, &$log)
    {
        $log['visual-effect'] = [
            'campaign' => $campaign->name,
            'ad_group_id' => $ad->ad_group_id,
            'ad_id' => $ad->ad_id,
            'paused' => true
        ];
    }
}
