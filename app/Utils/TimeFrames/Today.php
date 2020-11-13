<?php

namespace App\Utils\TimeFrames;

class Today
{
    public function get()
    {
        return [new \DateTime, new \DateTime];
    }
}
