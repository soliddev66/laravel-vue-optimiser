<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class EstimatedROI extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $profits = ReportData::sum($campaign, $redtrack_data, 'profit', $calculation_type);
        $costs = ReportData::sum($campaign, $redtrack_data, 'cost', $calculation_type);

        return parent::compare($costs ? $profits / $costs * 100 : INF, $rule_condition->amount, $rule_condition->operation);
    }
}
