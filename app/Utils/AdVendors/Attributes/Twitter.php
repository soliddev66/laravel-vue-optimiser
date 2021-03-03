<?php

namespace App\Utils\AdVendors\Attributes;

trait Twitter
{
    public function impressions($data, $calculation_type)
    {
        return json_decode($data['data'])[0]->metrics->impressions;
    }

    public function spend($data, $calculation_type)
    {
        return json_decode($data['data'])[0]->metrics->billed_charge_local_micro[0] / 1000000;
    }

    public function clicks($data, $calculation_type)
    {
        return json_decode($data['data'])[0]->metrics->clicks;
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