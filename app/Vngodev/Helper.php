<?php

namespace App\Vngodev;

use App\Jobs\PullAd;
use App\Jobs\PullAdGroup;
use App\Jobs\PullAdGroups;
use App\Jobs\PullAds;
use App\Jobs\PullCampaign;
use App\Jobs\PullCampaigns;

class Helper
{
    public static function encodeUrl($url)
    {
        return preg_replace_callback('#://([^/]+)/([^?]+)#', function ($match) {
            return '://' . $match[1] . '/' . join('/', array_map('rawurlencode', explode('/', $match[2])));
        }, $url);
    }

    public static function pullCampaign()
    {
        PullCampaigns::dispatch()->onQueue('high');
        PullAdGroups::dispatch()->onQueue('high');
        PullAds::dispatch()->onQueue('high');
    }

    public static function pullAd()
    {
        PullAds::dispatch()->onQueue('high');
    }
}
