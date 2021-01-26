<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;
use App\Endpoints\GeminiAPI;

class ChangeCampaignBudget extends Root
{
    public function process($campaign)
    {
        try {
            echo "Campaign's budget was being changed\n";
        } catch (Exception $e) {
            echo "Error happened. Campaign's budget wasn't being changed\n";
        }
    }
}
