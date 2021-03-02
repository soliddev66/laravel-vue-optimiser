<?php

namespace App\Utils\AdVendors\Attributes;

trait Outbrain
{
    public function impressions($data)
    {
        return json_decode($data['data'])->summary->impressions;
    }
}