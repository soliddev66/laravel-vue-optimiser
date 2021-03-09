<?php

namespace App\Utils\AdVendors\Attributes;

trait Outbrain
{
    public function impressions($data, $calculation_type)
    {
        switch ($calculation_type) {
            case 1:
                return json_decode($data['data'])->summary->impressions;
            case 2:
            default:
                if (isset($data['impressions'])) {
                    return $data['impressions'];
                }
        }

        throw new Exception('No attribute was found.');
    }

    public function spend($data, $calculation_type)
    {
        switch ($calculation_type) {
            case 1:
                return json_decode($data['data'])->summary->spend;
            case 2:
            default:
                if (isset($data['cost'])) {
                    return $data['cost'];
                }
        }

        throw new Exception('No attribute was found.');
    }

    public function clicks($data, $calculation_type)
    {
        switch ($calculation_type) {
            case 1:
                return json_decode($data['data'])->summary->clicks;
            case 2:
            default:
                if (isset($data['cost'])) {
                    return $data['clicks'];
                }
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
        if (isset($data['cost'])) {
            return $data['cost'];
        }

        throw new Exception('No attribute was found.');
    }

    public function conversions($data, $calculation_type)
    {
        if (isset($data['conversions'])) {
            return $data['conversions'];
        }

        throw new Exception('No attribute was found.');
    }

    public function prelpClicks($data)
    {
        if (isset($data['prelp_clicks'])) {
            return $data['prelp_clicks'];
        }

        throw new Exception('No attribute was found.');
    }
}