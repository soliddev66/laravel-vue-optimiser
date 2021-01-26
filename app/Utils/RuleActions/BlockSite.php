<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;
use App\Endpoints\GeminiAPI;

class BlockSite extends Root
{
    public function process($campaign, $data)
    {
        try {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $adVendorClass)->addSiteBlock($campaign, $data);
            echo "Campaign was being added site block\n";
        } catch (Exception $e) {
            echo "Campaign wasn't being added site block\n";
        }
    }
}
