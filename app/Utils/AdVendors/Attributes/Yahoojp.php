<?php

namespace App\Utils\AdVendors\Attributes;

trait Yahoojp
{
    public function impressions($data)
    {
        return $data['imps'];
    }

    public function spend($data)
    {
        return $data['cost'];
    }

    public function click($data)
    {
        return $data['click_cnt'];
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