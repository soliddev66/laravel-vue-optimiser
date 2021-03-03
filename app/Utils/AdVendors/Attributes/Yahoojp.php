<?php

namespace App\Utils\AdVendors\Attributes;

trait Yahoojp
{
    public function impressions($data, $calculation_type)
    {
        return $data['imps'];
    }

    public function spend($data, $calculation_type)
    {
        return $data['cost'];
    }

    public function click($data, $calculation_type)
    {
        return $data['click_cnt'];
    }

    public function lpClicks($data, $calculation_type)
    {
        //
    }

    public function lpViews($data, $calculation_type)
    {
        //
    }
}