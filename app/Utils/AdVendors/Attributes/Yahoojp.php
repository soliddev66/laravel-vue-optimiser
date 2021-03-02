<?php

namespace App\Utils\AdVendors\Attributes;

trait Yahoojp
{
    public function impressions($data)
    {
        return $data['imps'];
    }
}