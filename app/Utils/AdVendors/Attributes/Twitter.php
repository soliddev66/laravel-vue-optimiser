<?php

namespace App\Utils\AdVendors\Attributes;

trait Twitter
{
    public function impressions($data, $calculation_type)
    {
        if (isset($data['impressions'])) {
            return $data['impressions'];
        } else if (isset($data['data'])) {
            $data = json_decode($data['data']);

            if (isset($data[0]) && isset($data[0]->metrics)) {
                return $data[0]->metrics->impressions;
            }
        }

        throw new Exception('No attribute was found.');
    }

    public function spend($data, $calculation_type)
    {
        if (isset($data['cost'])) {
            return $data['cost'];
        } else if (isset($data['data'])) {
            $data = json_decode($data['data']);

            if (isset($data[0]) && isset($data[0]->metrics)) {
                return $data[0]->metrics->billed_charge_local_micro[0] / 1000000;
            }
        }

        throw new Exception('No attribute was found.');
    }

    public function clicks($data, $calculation_type)
    {
        if (isset($data['clicks'])) {
            return $data['clicks'];
        } else if (isset($data['data'])) {
            $data = json_decode($data['data']);

            if (isset($data[0]) && isset($data[0]->metrics)) {
                return $data[0]->metrics->clicks;
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
}