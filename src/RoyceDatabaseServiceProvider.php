<?php

namespace Roycedev\Roycedb;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Roycedev\Roycedb\RoyceDatabase;
use Roycedev\Roycedb\Console\MakeDbTableCommand;

class RoyceDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Run service provider boot operations.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/roycedb.php';
        $this->publishes([$configPath => $this->getConfigPath()], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeDbTableCommand::class,
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/roycedb.php';
        $this->mergeConfigFrom($configPath, 'roycedb');

/*
        $this->app->alias(
            DataFormatter::class,
            DataFormatterInterface::class
        );
*/

        $this->app->singleton(RoyceDatabase::class, function () {
            $roycedb = new RoyceDatabase($this->app);

            return $roycedb;
        });

        $this->app->alias(RoyceDatabase::class, 'roycedb');

        $this->app->singleton('command.roycedb.maketable', function ($app) {
                return new MakeDbTableCommand($app['roycedb']);
        });

        $this->commands(['command.roycedb.maketable']);
    }

    protected function getConfigPath()
    {
        return config_path('roycedb.php');
    }

    /**
     * Publish the config file
     *
     * @param  string $configPath
     */
    protected function publishConfig($configPath)
    {
        $this->publishes([$configPath => config_path('roycedb.php')], 'config');
    }

    /**
     * Register the Debugbar Middleware
     *
     * @param  string $middleware
     */
    protected function registerMiddleware($middleware)
    {
        $kernel = $this->app[Kernel::class];
        $kernel->pushMiddleware($middleware);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['roycedb', 'command.roycedb.maketable', RoyceDatabase::class];
    }
}
