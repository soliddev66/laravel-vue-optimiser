<?php

namespace App\Utils\AdVendors\Attributes;

trait Yahoo
{
    public function impressions($data, $calculation_type)
    {
        return $data['impressions'];
    }

    public function spend($data, $calculation_type)
    {
        return $data['spend'];
    }

    public function click($data, $calculation_type)
    {
        return $data['click'];
    }

    public function lpClicks($data, $calculation_type)
    {
        return $data['lp_clicks'];
    }

    public function lpViews($data, $calculation_type)
    {
        return $data['lp_views'];
    }
}