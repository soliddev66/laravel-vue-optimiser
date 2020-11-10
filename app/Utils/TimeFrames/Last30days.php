<?php

namespace App\Utils\TimeFrames;

class Last30days
{
    public function get()
    {
        return [(new \DateTime)->sub(new \DateInterval('P30D')), new \DateTime];
    }
}
