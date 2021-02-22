<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;

class ActivateCampaign extends Root
{
    public function process($campaign, &$log, $rule_data = null)
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
            $log['effect']['activated'] = false;
            $log['effect']['message'] = $e->getMessage();
            echo "Error happened. Campaign wasn't being activated\n";
        }
    }

    public function visual($campaign, &$log, $rule_data = null)
    {
        $log['visual-effect'] = [
            'campaign' => $campaign->name,
            'activated' => true
        ];
    }
}
