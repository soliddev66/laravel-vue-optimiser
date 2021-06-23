<?php

namespace App\Utils\RuleActions;

use Exception;

class BlockSite extends Root
{
    public function process($campaign, $data, &$log)
    {
        $log['effect'] = [
            'campaign' => $campaign->name,
            'site' => $data
        ];
        try {
            $ad_vendor_class = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $ad_vendor_class)->addSiteBlock($campaign, $data);
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
