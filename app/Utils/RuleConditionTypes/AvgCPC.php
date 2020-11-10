<?php

namespace App\Utils\RuleConditionTypes;

use App\Models\RedtrackReport;

class AvgCPC extends Root
{
    public function check($red_tracks, $rule_condition)
    {
        $avg_cpc = RedtrackReport::avg($red_tracks, 'cpc');

        return parent::compare($avg_cpc, $rule_condition->amount, $rule_condition->operation);
    }
}
