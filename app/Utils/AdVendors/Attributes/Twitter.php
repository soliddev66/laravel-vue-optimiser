<?php

namespace App\Utils\AdVendors\Attributes;

trait Twitter
{
    public function impressions($data)
    {
        return json_decode($data['data'])[0]->metrics->impressions;
    }
}