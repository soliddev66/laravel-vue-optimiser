<?php

namespace App\Socialite;

class SocialiteManager extends \Laravel\Socialite\SocialiteManager
{
    protected function createYahoojpDriver()
    {
        $config = $this->config->get('services.yahoojp');

        return $this->buildProvider('App\Socialite\Two\YahoojpProvider', $config);
    }

    protected function createYahooDriver()
    {
        $config = $this->config->get('services.yahoo');

        return $this->buildProvider('App\Socialite\Two\YahooProvider', $config);
    }

    protected function createTaboolaDriver()
    {
        $config = $this->config->get('services.taboola');

        return $this->buildProvider('App\Socialite\Two\TaboolaProvider', $config);
    }
}
