<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrackerCVR extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $revenues = ReportData::sum($campaign, $redtrack_data, 'revenue', $calculation_type);
        $clicks = ReportData::sum($campaign, $redtrack_data, 'clicks', $calculation_type);

        return parent::compare($clicks ? $revenues / $clicks * 100 : INF, $rule_condition->amount, $rule_condition->operation);
    }
}
