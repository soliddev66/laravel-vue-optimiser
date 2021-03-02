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

    public function click($data, $calculation_type)
    {
        return json_decode($data['data'])[0]->metrics->clicks;
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