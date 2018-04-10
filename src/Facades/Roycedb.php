<?php namespace Roycedev\Roycedb\Facades;

class Roycedb extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return Roycedb::class;
    }
}
