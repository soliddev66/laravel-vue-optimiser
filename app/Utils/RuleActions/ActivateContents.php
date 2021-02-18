<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;
use App\Endpoints\GeminiAPI;

class ActivateContents extends Root
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

            (new $adVendorClass)->adStatus($campaign, $ad->ad_group_id, $ad->ad_id, Campaign::STATUS_ACTIVE);
            $log['effect']['activated'] = true;
            echo 'Ad was being activated', "\n";
        } catch (Exception $e) {
            echo "Ad wasn't being activated\n";
        }
    }

    public function visual($campaign, $ad, &$log)
    {
        $log['effect'] = [
            'campaign' => $campaign->name,
            'ad_group_id' => $ad->ad_group_id,
            'ad_id' => $ad->ad_id
        ];
    }
}
