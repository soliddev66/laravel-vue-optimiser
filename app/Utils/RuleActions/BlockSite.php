<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;
use App\Endpoints\GeminiAPI;

class BlockSite extends Root
{
    public function process($campaign, $data, &$log)
    {
        $log['effect'] = [
            'campaign' => $campaign->name,
            'site' => $data
        ];
        try {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $adVendorClass)->addSiteBlock($campaign, $data);
            $log['effect']['blocked'] = true;
            echo "Campaign was being added site block\n";
        } catch (Exception $e) {
            $log['effect']['blocked'] = false;
            $log['effect']['message'] = $e->getMessage();
            echo "Campaign wasn't being added site block\n";
        }
    }

    public function visual($campaign, $data, &$log)
    {
        $log['visual-effect'] = [
            'campaign' => $campaign->name,
            'site' => $data,
            'blocked' => true
        ];
    }
}
