<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class CTR extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $clicks = ReportData::sum($campaign, $redtrack_data, 'lpClicks', $calculation_type);
        $views = ReportData::sum($campaign, $redtrack_data, 'lpViews', $calculation_type);

        return parent::compare($views ? $clicks / $views * 100 : INF, $rule_condition->amount, $rule_condition->operation);
    }
}
