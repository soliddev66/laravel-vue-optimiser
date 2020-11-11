<?php

namespace App\Utils\RuleConditionTypes;

use App\Models\RedtrackReport;

class EstimatedNET extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_profit = RedtrackReport::sum($redtrack_data, 'profit');

        return parent::compare($sum_profit, $rule_condition->amount, $rule_condition->operation);
    }
}
