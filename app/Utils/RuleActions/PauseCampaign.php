<?php

namespace App\Utils\RuleActions;

class PauseCampaign extends Root
{
    public function process($campaign)
    {

        echo 'Campaign was paused', "\n";
    }
}
