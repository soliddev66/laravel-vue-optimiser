<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrackerCPA extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_costs = ReportData::sum($redtrack_data, 'cost');
        $sum_conversions = ReportData::sum($redtrack_data, 'conversions');

        return parent::compare($sum_costs / $sum_conversions, $rule_condition->amount, $rule_condition->operation);
    }
}
