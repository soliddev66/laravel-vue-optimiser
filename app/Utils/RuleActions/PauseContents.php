<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;
use App\Endpoints\GeminiAPI;

class PauseContents extends Root
{
    public function process($campaign, $ad)
    {
        try {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);

            (new $adVendorClass)->adStatus($campaign, $ad->ad_group_id, $ad->ad_id, Campaign::STATUS_PAUSED);
            echo 'Ad was being paused', "\n";
        } catch (Exception $e) {
            echo "Ad wasn't being paused\n";
        }
    }
}
