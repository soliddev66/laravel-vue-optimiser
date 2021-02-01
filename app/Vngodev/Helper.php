<?php

namespace App\Vngodev;

use App\Jobs\PullAd;
use App\Jobs\PullAdGroup;
use App\Jobs\PullCampaign;

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
        PullCampaign::dispatch(auth()->user())->onQueue('high');
        PullAdGroup::dispatch(auth()->user())->onQueue('high');
        PullAd::dispatch(auth()->user())->onQueue('high');
    }

    public static function pullAd()
    {
        PullAd::dispatch(auth()->user())->onQueue('high');
    }
}
