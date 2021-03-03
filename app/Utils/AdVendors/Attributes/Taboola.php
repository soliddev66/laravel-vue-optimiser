<?php

namespace App\Utils\AdVendors\Attributes;

trait Taboola
{
    public function impressions($data, $calculation_type)
    {
        return $data['impressions'];
    }

    public function spend($data, $calculation_type)
    {
        return $data['spent'];
    }

    public function clicks($data, $calculation_type)
    {
        return $data['clicks'];
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
        throw new Exception('No attribute was found.');
    }

    public function conversions($data, $calculation_type)
    {
        throw new Exception('No attribute was found.');
    }
}