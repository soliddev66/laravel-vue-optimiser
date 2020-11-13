<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;
use App\Endpoints\GeminiAPI;

class PauseCampaign extends Root
{
    public function process($campaign)
    {
        try {
            $gemini = new GeminiAPI($campaign->user->providers->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());
            $campaign->status($gemini, Campaign::STATUS_PAUSED);
            echo 'Campaign was being paused', "\n";
        } catch (Exception $e) {
            echo "Campaign wasn't being paused\n";
        }
    }
}
