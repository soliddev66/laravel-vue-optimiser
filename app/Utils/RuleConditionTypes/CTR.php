<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class CTR extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $sum_clicks = ReportData::sum($campaign, $redtrack_data, 'lpClicks', $calculation_type);
        $sum_views = ReportData::sum($campaign, $redtrack_data, 'lpViews', $calculation_type);

        return parent::compare($sum_clicks / $sum_views * 100, $rule_condition->amount, $rule_condition->operation);
    }
}
