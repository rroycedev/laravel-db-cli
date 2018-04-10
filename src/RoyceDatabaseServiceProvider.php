<?php

namespace Roycedev\Roycedb;

use Roycedev\Roycedb\Models\User;
use Roycedev\Roycedb\Auth\DatabaseUserProvider;
use Roycedev\Roycedb\Facades\Roycedb;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
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
        $configPath = __DIR__ . '/../config/config.php';
        $this->publishes([$configPath => $this->getConfigPath()], 'roycedb');

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
        // Bind the Adldap instance to the IoC
        $this->app->singleton('roycedb', function (Container $app) {
            echo "Inside register singletons\n";
            exit(1);

            $config = $app->make('config')->get('roycedb');

            // Verify configuration exists.
            if (is_null($config)) {
                $message = 'Roycedb configuration could not be found. Try re-publishing using `php artisan vendor:publish --tag="roycedb"`.';

                throw new ConfigurationMissingException($message);
            }

            return $this->addProviders($this->newRoycedb(), $config['connections']);
        });

        // Bind the Adldap contract to the Adldap object
        // in the IoC for dependency injection.
        $this->app->singleton(AdldapInterface::class, 'roycedb');

        $this->commands([MakeDbTableCommand::class]);
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
        return ['roycedb', 'command.roycedb.maketable', Roycedb::class];
    }


    /**
     * Adds providers to the specified Adldap instance.
     *
     * If a provider is configured to auto connect,
     * this method will throw a BindException.
     *
     * @param Adldap $adldap
     * @param array  $connections
     *
     * @throws \Adldap\Auth\BindException
     *
     * @return Adldap
     */
    protected function addProviders(Roycedb $roycedb, array $connections = [])
    {
        // Go through each connection and construct a Provider.
        foreach ($connections as $name => $settings) {
            // Create a new provider.
            $provider = $this->newProvider(
                $settings['connection_settings'],
                new $settings['connection'],
                new $settings['schema']
            );

            if ($this->shouldAutoConnect($settings)) {
                try {
                    $provider->connect();
                } catch (BindException $e) {
                    // We'll catch and log bind exceptions so
                    // any connection issues fail gracefully
                    // in our application.
                    Log::error($e);
                }
            }

            // Add the provider to the Adldap container.
            $roycedb->addProvider($provider, $name);
        }

        return $roycedb;
    }

    /**
     * Returns a new Adldap instance.
     *
     * @return Adldap
     */
    protected function newRoycedb()
    {
        return new Roycedb();
    }

    /**
     * Returns a new Provider instance.
     *
     * @param array                    $configuration
     * @param ConnectionInterface|null $connection
     * @param SchemaInterface          $schema
     *
     * @return Provider
     */
    protected function newProvider($configuration = [], ConnectionInterface $connection = null, SchemaInterface $schema = null)
    {
        return new Provider($configuration, $connection, $schema);
    }

    /**
     * Determine if the given settings is configured for auto-connecting.
     *
     * @param array $settings
     *
     * @return bool
     */
    protected function shouldAutoConnect(array $settings)
    {
        return array_key_exists('auto_connect', $settings)
            && $settings['auto_connect'] === true;
    }

    /**
     * Determines if the current application is Lumen.
     *
     * @return bool
     */
    protected function isLumen()
    {
        return str_contains($this->app->version(), 'Lumen');
    }    
}
