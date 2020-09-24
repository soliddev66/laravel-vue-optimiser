<?php

namespace App\Providers;

use App\Vngodev\Token;
use Illuminate\Support\ServiceProvider;

class TokenServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('token',function(){
            return new Token();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
