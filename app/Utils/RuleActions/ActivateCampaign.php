<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;

class ActivateCampaign extends Root
{
    public function process($campaign)
    {
        try {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $adVendorClass)->status($campaign);
            echo 'Campaign hasn\'t been activated', "\n";
        } catch (Exception $e) {
            echo "Error happened. Campaign wasn't being activated\n";
        }
    }
}
