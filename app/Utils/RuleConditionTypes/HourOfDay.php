<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class HourOfDay extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        return parent::compare(date('H'), $rule_condition->amount, $rule_condition->operation);
    }
}
