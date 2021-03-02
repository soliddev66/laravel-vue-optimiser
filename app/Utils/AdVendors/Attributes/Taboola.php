<?php

namespace App\Utils\AdVendors\Attributes;

trait Taboola
{
    public function impressions($data)
    {
        return $data['impressions'];
    }

    public function spend($data)
    {
        return $data['spent'];
    }

    public function click($data)
    {
        return $data['clicks'];
    }

    public function lpClicks($data)
    {
        //
    }

    public function lpViews($data)
    {
        //
    }
}