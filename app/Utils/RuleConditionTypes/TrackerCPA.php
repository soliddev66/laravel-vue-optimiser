<?php

namespace App\Utils\RuleConditionTypes;

use App\Models\RedtrackReport;

class TrackerCPA extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_cost = RedtrackReport::sum($redtrack_data, 'cost');
        $sum_conversions = RedtrackReport::sum($redtrack_data, 'conversions');

        return parent::compare($sum_cost / $sum_conversions, $rule_condition->amount, $rule_condition->operation);
    }
}
