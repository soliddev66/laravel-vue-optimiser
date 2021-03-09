<?php

namespace App\Utils\AdVendors\Attributes;

use Exception;

trait Yahoojp
{
    public function impressions($data, $calculation_type)
    {
        if (isset($data['impressions'])) {
            return $data['impressions'];
        } else if (isset($data['imps'])) {
            return $data['imps'];
        }

        throw new Exception('No attribute was found.');
    }

    public function spend($data, $calculation_type)
    {
        return $data['cost'];
    }

    public function clicks($data, $calculation_type)
    {
        if (isset($data['clicks'])) {
            return $data['clicks'];
        } else if (isset($data['click_cnt'])) {
            return $data['click_cnt'];
        }

        throw new Exception('No attribute was found.');
    }

    public function lpClicks($data, $calculation_type)
    {
        if (isset($data['lp_clicks'])) {
            return $data['lp_clicks'];
        }

        throw new Exception('No attribute was found.');
    }

    public function lpViews($data, $calculation_type)
    {
        if (isset($data['lp_views'])) {
            return $data['lp_views'];
        }

        throw new Exception('No attribute was found.');
    }

    public function revenue($data, $calculation_type)
    {
        if (isset($data['revenue'])) {
            return $data['revenue'];
        }

        throw new Exception('No attribute was found.');
    }

    public function profit($data, $calculation_type)
    {
        if (isset($data['profit'])) {
            return $data['profit'];
        }

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

    public function prelpClicks($data)
    {
        if (isset($data['prelp_clicks'])) {
            return $data['prelp_clicks'];
        }

        throw new Exception('No attribute was found.');
    }
}