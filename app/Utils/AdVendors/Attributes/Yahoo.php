<?php

namespace App\Utils\AdVendors\Attributes;

trait Yahoo
{
    public function impressions($data)
    {
        return $data['impressions'];
    }
}