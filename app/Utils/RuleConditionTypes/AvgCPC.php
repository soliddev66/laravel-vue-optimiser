<?php

namespace App\Utils\RuleConditionTypes;

use App\Models\RedtrackReport;

class AvgCPC extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $avg_cpc = RedtrackReport::avg($redtrack_data, 'cpc');

        return parent::compare($avg_cpc, $rule_condition->amount, $rule_condition->operation);
    }
}
