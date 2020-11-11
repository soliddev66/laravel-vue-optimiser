<?php

namespace App\Utils\RuleConditionTypes;

use App\Models\RedtrackReport;

class TrafficSourceRevenue extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_revenue = RedtrackReport::sum($redtrack_data, 'revenue');

        return parent::compare($sum_revenue, $rule_condition->amount, $rule_condition->operation);
    }
}
