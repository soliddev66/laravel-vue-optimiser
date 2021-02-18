<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;
use App\Endpoints\GeminiAPI;

class PauseCampaign extends Root
{
    public function process($campaign, &$log)
    {
        $log['effect'] = [
            'campaign' => $campaign->name
        ];

        try {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $adVendorClass)->status($campaign);
            $log['effect']['paused'] = true;
            echo 'Campaign was being paused', "\n";
        } catch (Exception $e) {
            echo "Campaign wasn't being paused\n";
        }
    }

    public function visual($campaign, &$log)
    {
        $log['visual-effect'] = [
            'campaign' => $campaign->name
        ];
    }
}
