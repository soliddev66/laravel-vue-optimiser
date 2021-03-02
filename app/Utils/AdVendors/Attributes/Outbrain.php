<?php

namespace App\Utils\AdVendors\Attributes;

trait Outbrain
{
    public function impressions($data, $calculation_type)
    {
        return json_decode($data['data'])->summary->impressions;
    }

    public function spend($data, $calculation_type)
    {
        return json_decode($data['data'])->summary->spend;
    }

    public function click($data, $calculation_type)
    {
        return json_decode($data['data'])->summary->clicks;
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