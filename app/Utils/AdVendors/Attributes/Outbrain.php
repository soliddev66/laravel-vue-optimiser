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
                return $data['impressions'];
        }

    }

    public function spend($data, $calculation_type)
    {
        switch ($calculation_type) {
            case 1:
                return json_decode($data['data'])->summary->spend;
            case 2:
            default:
                return $data['cost'];
        }
    }

    public function click($data, $calculation_type)
    {
        switch ($calculation_type) {
            case 1:
                return json_decode($data['data'])->summary->clicks;
            case 2:
            default:
                return $data['clicks'];
        }
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