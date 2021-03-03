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

    public function clicks($data, $calculation_type)
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