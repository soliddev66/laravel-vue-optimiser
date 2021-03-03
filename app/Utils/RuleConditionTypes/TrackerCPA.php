<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrackerCPA extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $costs = ReportData::sum($campaign, $redtrack_data, 'cost', $calculation_type);
        $conversions = ReportData::sum($campaign, $redtrack_data, 'conversions', $calculation_type);

        return parent::compare($conversions ? $costs / $conversions : INF, $rule_condition->amount, $rule_condition->operation);
    }
}
