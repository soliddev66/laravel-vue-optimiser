<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;

class ActivateCampaign extends Root
{
    public function process($campaign, &$log)
    {
        $log['effect'] = [
            'campaign' => $campaign->name
        ];

        try {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $adVendorClass)->status($campaign);
            $log['effect']['activated'] = true;
            echo 'Campaign hasn\'t been activated', "\n";
        } catch (Exception $e) {
            echo "Error happened. Campaign wasn't being activated\n";
        }
    }

    public function visual($campaign, &$log)
    {
        $log['effect'] = [
            'campaign' => $campaign->name
        ];
    }
}
