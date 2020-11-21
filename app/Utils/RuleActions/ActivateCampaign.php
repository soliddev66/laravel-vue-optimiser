<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;
use App\Endpoints\GeminiAPI;

class ActivateCampaign extends Root
{
    public function process($campaign)
    {
        try {
            $gemini = new GeminiAPI($campaign->user->providers->where('provider_id', $campaign->provider->id)->where('open_id', $campaign->open_id)->first());
            $campaign->status($gemini, Campaign::STATUS_ACTIVE);
            echo 'Campaign hasn''t been activated', "\n";
        } catch (Exception $e) {
            echo "Error happened. Campaign wasn't being activated\n";
        }
    }
}
