<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrackerEPC extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_revenues = ReportData::sum($redtrack_data, 'revenue');
        $sum_clicks = ReportData::sum($redtrack_data, 'clicks');

        return parent::compare($sum_revenues / $sum_clicks, $rule_condition->amount, $rule_condition->operation);
    }
}
