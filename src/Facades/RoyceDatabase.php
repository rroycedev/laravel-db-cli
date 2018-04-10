<?php

namespace Roycedev\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class RoyceDatabase extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'roycedb';
    }
}
