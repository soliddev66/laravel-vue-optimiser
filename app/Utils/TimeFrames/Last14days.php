<?php

namespace App\Utils\TimeFrames;

class Last14days
{
    public function get()
    {
        return [(new \DateTime)->sub(new \DateInterval('P14D')), new \DateTime];
    }
}
