<?php

namespace App\Utils\AdVendors\Attributes;

trait Twitter
{
    public function impressions($data)
    {
        return json_decode($data['data'])[0]->metrics->impressions;
    }

    public function spend($data)
    {
        return json_decode($data['data'])[0]->metrics->billed_charge_local_micro[0] / 1000000;
    }

    public function click($data)
    {
        return json_decode($data['data'])[0]->metrics->clicks;
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