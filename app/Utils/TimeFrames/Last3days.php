<?php

namespace App\Utils\TimeFrames;

class Last3days
{
    public function get()
    {
        return [(new \DateTime)->sub(new \DateInterval('P3D')), new \DateTime];
    }
}
