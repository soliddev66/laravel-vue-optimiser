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

    public function click($data, $calculation_type)
    {
        return $data['clicks'];
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