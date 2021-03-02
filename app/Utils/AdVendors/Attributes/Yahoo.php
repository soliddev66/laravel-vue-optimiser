<?php

namespace App\Utils\AdVendors\Attributes;

trait Yahoo
{
    public function impressions($data)
    {
        return $data['impressions'];
    }

    public function spend($data)
    {
        return $data['spend'];
    }

    public function click($data)
    {
        return $data['click'];
    }

    public function lpClicks($data)
    {
        return $data['lp_clicks'];
    }

    public function lpViews($data)
    {
        return $data['lp_views'];
    }
}