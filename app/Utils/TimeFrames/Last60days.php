<?php

namespace App\Utils\TimeFrames;

class Last60days
{
    public function get()
    {
        return [(new \DateTime)->sub(new \DateInterval('P60D')), new \DateTime];
    }
}
