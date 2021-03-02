<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrackerROI extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $sum_profits = ReportData::sum($campaign, $redtrack_data, 'profit', $calculation_type);
        $sum_costs = ReportData::sum($campaign, $redtrack_data, 'cost', $calculation_type);

        return parent::compare($sum_profits / $sum_costs * 100, $rule_condition->amount, $rule_condition->operation);
    }
}
