<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class LandingPageCTR extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $clicks = ReportData::sum($campaign, $redtrack_data, 'lp_clicks', $calculation_type);
        $views = ReportData::sum($campaign, $redtrack_data, 'lp_views', $calculation_type);

        return parent::compare($views ? $clicks / $views : INF, $rule_condition->amount, $rule_condition->operation);
    }
}
