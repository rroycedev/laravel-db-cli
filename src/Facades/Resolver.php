<?php

namespace Roycedev\Roycedb\Facades;

use Illuminate\Support\Facades\Facade;
use Roycedev\Roycedb\Resolvers\ResolverInterface;

class Resolver extends Facade
{
    /**
     * {@inheritdoc}
     */
    public static function getFacadeAccessor()
    {
        return ResolverInterface::class;
    }
}
