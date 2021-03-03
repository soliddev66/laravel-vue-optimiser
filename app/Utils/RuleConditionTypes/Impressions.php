<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class Impressions extends Root
{
    public function check($campaign, $performance_data, $rule_condition, $calculation_type)
    {
        $impressions = ReportData::sum($campaign, $performance_data, 'impressions', $calculation_type);

        return parent::compare($impressions, $rule_condition->amount, $rule_condition->operation);
    }
}
