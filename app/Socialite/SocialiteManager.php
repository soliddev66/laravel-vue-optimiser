<?php

namespace App\Socialite;

class SocialiteManager extends \Laravel\Socialite\SocialiteManager
{
    protected function createYahooDriver()
    {
        $config = $this->config->get('services.yahoo');

        return $this->buildProvider('App\Socialite\Two\YahooProvider', $config);
    }
}
