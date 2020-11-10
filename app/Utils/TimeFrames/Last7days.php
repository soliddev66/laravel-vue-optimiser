<?php

namespace App\Utils\TimeFrames;

class Last7days
{
    public function get()
    {
        return [(new \DateTime)->sub(new \DateInterval('P7D')), new \DateTime];
    }
}
