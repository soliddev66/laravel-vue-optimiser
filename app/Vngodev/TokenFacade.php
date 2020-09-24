<?php
namespace App\Vngodev;

use Illuminate\Support\Facades\Facade;

class TokenFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'token';
    }
}
