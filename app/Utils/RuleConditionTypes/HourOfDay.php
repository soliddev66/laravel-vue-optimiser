<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class HourOfDay extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $hour_of_day = date('H');

        return parent::compare($hour_of_day, $rule_condition->amount, $rule_condition->operation);
    }
}
