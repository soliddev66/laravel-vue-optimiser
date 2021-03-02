<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class AvgCPC extends Root
{
    public function check($campaign, $performance_data, $rule_condition, $calculation_type)
    {
        $sum_spends = ReportData::sum($campaign, $performance_data, 'spend', $calculation_type);
        $sum_clicks = ReportData::sum($campaign, $performance_data, 'click', $calculation_type);

        return parent::compare($sum_spends / $sum_clicks, $rule_condition->amount, $rule_condition->operation);
    }
}
