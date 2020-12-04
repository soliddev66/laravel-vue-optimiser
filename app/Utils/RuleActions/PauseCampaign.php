<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;
use App\Endpoints\GeminiAPI;

class PauseCampaign extends Root
{
    public function process($campaign)
    {
        try {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $adVendorClass)->status($campaign);
            echo 'Campaign was being paused', "\n";
        } catch (Exception $e) {
            echo "Campaign wasn't being paused\n";
        }
    }
}
