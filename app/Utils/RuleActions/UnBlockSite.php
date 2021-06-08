<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;
use App\Endpoints\GeminiAPI;

class UnBlockSite extends Root
{
    public function process($campaign, $data, &$log)
    {
        $log['effect'] = [
            'campaign' => $campaign->name,
            'site' => $data
        ];
        try {
            $ad_vendor_class = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $ad_vendor_class)->removeSiteBlock($campaign, $data);
            $log['effect']['unblocked'] = true;
            echo "Campaign was being disabled site block\n";
        } catch (Exception $e) {
            $log['effect']['unblocked'] = false;
            $log['effect']['message'] = $e->getMessage();
            echo "Campaign wasn't being disabled site block\n";
        }
    }

    public function visual($campaign, $data, &$log)
    {
        $log['visual-effect'] = [
            'campaign' => $campaign->name,
            'site' => $data,
            'unblocked' => true
        ];
    }
}
