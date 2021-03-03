<?php

namespace App\Utils\AdVendors\Attributes;

use Exception;

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

    public function clicks($data, $calculation_type)
    {
        return $data['click_cnt'];
    }

    public function lpClicks($data, $calculation_type)
    {
        throw new Exception('No attribute was found.');
    }

    public function lpViews($data, $calculation_type)
    {
        throw new Exception('No attribute was found.');
    }

    public function revenue($data, $calculation_type)
    {
        throw new Exception('No attribute was found.');
    }

    public function profit($data, $calculation_type)
    {
        throw new Exception('No attribute was found.');
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