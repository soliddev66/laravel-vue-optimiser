<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrackerClicks extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $sum_clicks = ReportData::sum($campaign, $redtrack_data, 'clicks', $calculation_type);

        return parent::compare($sum_clicks, $rule_condition->amount, $rule_condition->operation);
    }
}
