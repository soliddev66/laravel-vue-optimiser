<?php

namespace App\Utils\TimeFrames;

class ThisMonth
{
    public function get()
    {
        return [new \DateTime('first day of this month'), new \DateTime('last day of this month')];
    }
}
