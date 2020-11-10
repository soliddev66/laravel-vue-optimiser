<?php

namespace App\Utils\TimeFrames;

class Yesterday
{
    public function get()
    {
        return [(new \DateTime)->sub(new \DateInterval('P1D')), (new \DateTime)->sub(new \DateInterval('P1D'))];
    }
}
