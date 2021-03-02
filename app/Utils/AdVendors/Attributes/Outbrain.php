<?php

namespace App\Utils\AdVendors\Attributes;

trait Outbrain
{
    public function impressions($data)
    {
        return json_decode($data['data'])->summary->impressions;
    }

    public function spend($data)
    {
        return json_decode($data['data'])->summary->spend;
    }

    public function click($data)
    {
        return json_decode($data['data'])->summary->clicks;
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