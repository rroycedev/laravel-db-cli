<?php

namespace Roycedev\Roycedb;

use Adldap\Laravel\Listeners\BindsLdapUserModel;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Roycedev\Roycedb\Auth\DatabaseUserProvider;
use Roycedev\Roycedb\LdapSchema\OpenLDAP;
use Roycedev\Roycedb\Resolvers\ResolverInterface;
use Roycedev\Roycedb\Resolvers\UserResolver;
use Roycedev\Roycedb\Roycedb;

class RoyceDatabaseAuthServiceProvider extends ServiceProvider
{
    /**
     * Run service provider boot operations.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/roycedb_ldap_schema.php' => config_path('roycedb_ldap_schema.php'),
            __DIR__ . '/../config/roycedb.php' => config_path('roycedb.php'),
            __DIR__ . '/../config/roycedb_auth.php' => config_path('roycedb_auth.php'),
        ], 'roycedb');

        $auth = Auth::getFacadeRoot();

        if (method_exists($auth, 'provider')) {
            $auth->provider('roycedb', function ($app, array $config) {
                return $this->makeUserProvider($app['hash'], $config);
            });
        } else {
            $auth->extend('roycedb', function ($app) {
                return $this->makeUserProvider($app['hash'], $app['config']['auth']);
            });
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $schema = new OpenLDAP();

        Config::set('adldap.connections.default.schema', 'Roycedev\Roycedb\LdapSchema\OpenLDAP');

        Config::set('adldap_auth.usernames.ldap.discover', $schema->userPrincipalName());
        Config::set('adldap_auth.usernames.ldap.authenticate', $schema->userPrincipalName());

        Config::set('adldap_auth.usernames.eloquent', 'username');

        Config::set('adldap_auth.sync_attributes', ['id' => 'uidnumber', 'username' => 'uid', 'name' => 'cn']);

        $this->registerBindings();

        $this->registerListeners();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['auth'];
    }

    /**
     * Registers the application bindings.
     *
     * @return void
     */
    protected function registerBindings()
    {
        $this->app->bind(ResolverInterface::class, function () {
            $ad = $this->app->make(Roycedb::class);

            $ur = new UserResolver($ad);

            return $ur;
        });
    }

    /**
     * Registers the event listeners.
     *
     * @return void
     */
    protected function registerListeners()
    {
        // Here we will register the event listener that will bind the users LDAP
        // model to their Eloquent model upon authentication (if configured).
        // This allows us to utilize their LDAP model right
        // after authentication has passed.
        Event::listen(Authenticated::class, BindsLdapUserModel::class);

        if ($this->isLogging()) {
            // If logging is enabled, we will set up our event listeners that
            // log each event fired throughout the authentication process.
            foreach ($this->getLoggingEvents() as $event => $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * Returns a new Adldap user provider.
     *
     * @param Hasher $hasher
     * @param array  $config
     *
     * @throws InvalidArgumentException
     *
     * @return \Illuminate\Contracts\Auth\UserProvider
     */
    protected function makeUserProvider(Hasher $hasher, array $config)
    {
        $provider = Config::get('roycedb_auth.provider', DatabaseUserProvider::class);

        // The DatabaseUserProvider has some extra dependencies needed,
        // so we will validate that we have them before
        // constructing a new instance.
        if ($provider == DatabaseUserProvider::class) {
            $model = array_get($config, 'model');

            if (!$model) {
                throw new InvalidArgumentException(
                    "No model is configured. You must configure a model to use with the {$provider}."
                );
            }

            return new $provider($hasher, $model);
        }

        return new $provider;
    }

    /**
     * Determines if authentication requests are logged.
     *
     * @return bool
     */
    protected function isLogging()
    {
        return Config::get('adldap_auth.logging.enabled', false);
    }

    /**
     * Returns the configured authentication events to log.
     *
     * @return array
     */
    protected function getLoggingEvents()
    {
        return Config::get('adldap_auth.logging.events', []);
    }
}
