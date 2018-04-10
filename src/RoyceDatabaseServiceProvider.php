<?php

namespace Roycedev\Laravel;

use Adldap\AdldapInterface;
use Adldap\Auth\BindException;
use Adldap\Connections\ConnectionInterface;
use Adldap\Connections\Provider;
use Adldap\Laravel\Exceptions\ConfigurationMissingException;
use Adldap\Schemas\SchemaInterface;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Roycedev\RoyceDatabase;

class RoyceDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Run service provider boot operations.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->isLumen()) {
            return;
        }

        $config = __DIR__ . '/Config/config.php';

        $this->publishes([
            $config => config_path('roycedb.php'),
        ], 'roycedb');

        $this->mergeConfigFrom($config, 'roycedb');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Bind the RoyceDatabase instance to the IoC
        $this->app->singleton('roycedb', function (Container $app) {
            $config = $app->make('config')->get('roycedb');

            // Verify configuration exists.
            if (is_null($config)) {
                $message = 'RoyceDatabase configuration could not be found. Try re-publishing using `php artisan vendor:publish --tag="roycedb"`.';

                throw new ConfigurationMissingException($message);
            }

            return $this->addProviders($this->newRoyceDatabase(), $config['connections']);
        });

        // Bind the RoyceDatabase contract to the RoyceDatabase object
        // in the IoC for dependency injection.
        $this->app->singleton(AdldapInterface::class, 'roycedb');

        // Register Amazon Artisan commands
        $this->commands([
            'Roycedev\Laravel\Commands\Console\MakeDbTable',
        ]);

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['roycedb'];
    }

    /**
     * Adds providers to the specified RoyceDatabase instance.
     *
     * If a provider is configured to auto connect,
     * this method will throw a BindException.
     *
     * @param RoyceDatabase $royceDb
     * @param array  $connections
     *
     * @throws \Adldap\Auth\BindException
     *
     * @return RoyceDatabase
     */
    protected function addProviders(RoyceDatabase $royceDb, array $connections = [])
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

            // Add the provider to the RoyceDatabase container.
            $royceDb->addProvider($provider, $name);
        }

        return $royceDb;
    }

    /**
     * Returns a new RoyceDatabase instance.
     *
     * @return RoyceDatabase
     */
    protected function newRoyceDatabase()
    {
        return new RoyceDatabase();
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
