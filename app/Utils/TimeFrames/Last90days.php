<?php

namespace App\Utils\TimeFrames;

class Last90days
{
    public function get()
    {
        return [(new \DateTime)->sub(new \DateInterval('P90D')), new \DateTime];
    }
}
