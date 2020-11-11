<?php

namespace App\Utils\RuleConditionTypes;

use App\Models\RedtrackReport;

class TrafficSourceROI extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_profit = RedtrackReport::sum($redtrack_data, 'profit');
        $sum_cost = RedtrackReport::sum($redtrack_data, 'cost');

        return parent::compare($sum_profit / $sum_cost * 100, $rule_condition->amount, $rule_condition->operation);
    }
}
