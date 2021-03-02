<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class EstimatedROI extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition)
    {
        $sum_profits = ReportData::sum($campaign, $redtrack_data, 'profit');
        $sum_costs = ReportData::sum($campaign, $redtrack_data, 'cost');

        return parent::compare($sum_profits / $sum_costs * 100, $rule_condition->amount, $rule_condition->operation);
    }
}
