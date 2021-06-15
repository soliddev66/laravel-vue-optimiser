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
            'ad' => $ad
        ];
        try {
            $ad_vendor_class = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

            (new $ad_vendor_class)->adStatus($campaign, $ad->ad_group_id, $ad->ad_id, Campaign::STATUS_ACTIVE);
            $log['effect']['activated'] = true;
            echo 'Ad was being activated', "\n";
        } catch (Exception $e) {
            $log['effect']['activated'] = false;
            $log['effect']['message'] = $e->getMessage();
            echo "Ad wasn't being activated\n";
        }
    }

    public function visual($campaign, $ad, &$log)
    {
        $log['visual-effect'] = [
            'campaign' => $campaign->name,
            'ad' => $ad,
            'activated' => true
        ];
    }
}
