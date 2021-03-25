<?php

namespace App\Utils\AdVendors\Attributes;

use Exception;

trait Taboola
{
    public function impressions($data, $calculation_type)
    {
        return $data['impressions'];
    }

    public function spend($data, $calculation_type)
    {
        if (isset($data['spent'])) {
            return $data['spent'];
        } else if (isset($data['cost'])) {
            return $data['cost'];
        }

        throw new Exception('No attribute was found.');
    }

    public function clicks($data, $calculation_type)
    {
        return $data['clicks'];
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
        if (isset($data['spent'])) {
            return $data['spent'];
        } else if (isset($data['cost'])) {
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