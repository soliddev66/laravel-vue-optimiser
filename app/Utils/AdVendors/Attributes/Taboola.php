<?php

namespace App\Utils\AdVendors\Attributes;

trait Taboola
{
    public function impressions($data)
    {
        return $data['impressions'];
    }
}