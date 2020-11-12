<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrackerROI extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_profits = ReportData::sum($redtrack_data, 'profit');
        $sum_costs = ReportData::sum($redtrack_data, 'cost');

        return parent::compare($sum_profits / $sum_costs * 100, $rule_condition->amount, $rule_condition->operation);
    }
}
