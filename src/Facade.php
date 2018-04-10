<?php namespace Roycedev\Roycedb;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return RoyceDatabase::class;
    }
}
