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

    public function clicks($data, $calculation_type)
    {
        return $data['clicks'];
    }

    public function lpClicks($data, $calculation_type)
    {
        return $data['lp_clicks'];
    }

    public function lpViews($data, $calculation_type)
    {
        return $data['lp_views'];
    }

    public function revenue($data, $calculation_type)
    {
        return $data['revenue'];
    }

    public function profit($data, $calculation_type)
    {
        return $data['profit'];
    }

    public function cost($data, $calculation_type)
    {
        return $data['cost'];
    }

    public function conversions($data, $calculation_type)
    {
        return $data['conversions'];
    }
}