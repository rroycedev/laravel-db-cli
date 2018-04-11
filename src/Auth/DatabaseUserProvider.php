<?php

namespace Roycedev\Roycedb\Auth;

use Illuminate\Support\Facades\DB;
use Adldap\Laravel\Commands\Import;
use Adldap\Laravel\Commands\SyncPassword;
use Adldap\Laravel\Events\AuthenticatedWithCredentials;
use Adldap\Laravel\Events\AuthenticationSuccessful;
use Adldap\Laravel\Events\DiscoveredWithCredentials;
use Adldap\Laravel\Events\Imported;
use Adldap\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Roycedev\Roycedb\LdapSchema\OpenLDAP;
use Symfony\Component\Debug\Exception\FatalErrorException;
use \Adldap\Laravel\Facades\Adldap;
use \Adldap\Laravel\Facades\Resolver;

class DatabaseUserProvider extends Provider
{
    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * The Eloquent user model.
     *
     * @var string
     */
    protected $model;

    /**
     * The fallback user provider.
     *
     * @var UserProvider
     */
    protected $fallback;

    /**
     * The currently authenticated LDAP user.
     *
     * @var User|null
     */
    protected $user;

    /**
     * Constructor.
     *
     * @param Hasher $hasher
     * @param string $model
     */
    public function __construct(Hasher $hasher, $model)
    {
        $this->model = $model;
        $this->hasher = $hasher;

        $this->setFallback(new EloquentUserProvider($hasher, $model));
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $driverName = Config::get('roycedb.driver.name');

        if ($driverName == "adldap") {
            return $this->fallback->retrieveById($identifier);
        }

        $model = $this->createModel();

        return $model->newQuery()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();
        
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed   $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $driverName = Config::get('roycedb.driver.name');

        if ($driverName == "adldap") {
            $ldapCredentials = array("username" => $credentials["email"], "password" => $credentials["password"]);

            $schema = new OpenLDAP();
    
            Config::set('adldap.connections.default.schema', Config::get('roycedb.driver.schema'));

            Config::set('adldap_auth.usenames.ldap.discover', $schema->userPrincipalName());
            Config::set('adldap_auth.usenames.ldap.authenticate', $schema->userPrincipalName());
    
            Config::set('adldap_auth.usernames.eloquent', 'username');
    
            Config::set('adldap_auth.sync_attributes', ['username' => 'uid', 'first_name' => 'givenname', 'last_name' => 'sn', 'email' => 'mail']);
    
            // Retrieve the LDAP user who is authenticating.
            $user = \Adldap\Laravel\Facades\Resolver::byCredentials($ldapCredentials);
    
            if ($user instanceof User) {
                // Set the currently authenticating LDAP user.
                $this->user = $user;
    
                Event::fire(new DiscoveredWithCredentials($user));
    
                // Import / locate the local user account.
                $import = new Import($user, $this->createModel(), $ldapCredentials);
    
                return Bus::dispatch($import);
            }
    
            if ($this->isFallingBack()) {
                return $this->fallback->retrieveByCredentials($ldapCredentials);
            }   
        }
        else {
            if (empty($credentials) ||
            (count($credentials) === 1 &&
             array_key_exists('password', $credentials))) {
                 return;
            }
 
            // First we will add each credential element to the query as a where clause.
            // Then we can execute the query and, if we found a user, return it in a
            // Eloquent User "model" that will be utilized by the Guard instances.
            $query = $this->createModel()->newQuery();
    
            foreach ($credentials as $key => $value) {
                if (! Str::contains($key, 'password')) {
                    $query->where($key, $value);
                }
            }
    
            return $query->first();
             
        }
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $model, array $credentials)
    {
        $driverName = Config::get('roycedb.driver.name');

        if ($driverName == "adldap") {
            if ($this->user instanceof User) {
                // If an LDAP user was discovered, we can go
                // ahead and try to authenticate them.
                if (Resolver::authenticate($this->user, $credentials)) {
                    Event::fire(new AuthenticatedWithCredentials($this->user, $model));

                    // Here we will perform authorization on the LDAP user. If all
                    // validation rules pass, we will allow the authentication
                    // attempt. Otherwise, it is automatically rejected.
                    if ($this->passesValidation($this->user, $model)) {
                        // Here we can now synchronize / set the users password since
                        // they have successfully passed authentication
                        // and our validation rules.
                        Bus::dispatch(new SyncPassword($model, $credentials));

                        $model->save();

                        if ($model->wasRecentlyCreated) {
                            // If the model was recently created, they
                            // have been imported successfully.
                            Event::fire(new Imported($this->user, $model));
                        }

                        Event::fire(new AuthenticationSuccessful($this->user, $model));

                        return true;
                    }

                    Event::fire(new AuthenticationRejected($this->user, $model));
                }

                // LDAP Authentication failed.
                return false;
            }

            if ($this->isFallingBack() && $model->exists) {
                // If the user exists in our local database already and fallback is
                // enabled, we'll perform standard eloquent authentication.
                return $this->fallback->validateCredentials($model, $credentials);
            }

            return false;
        }

        $plain = $credentials['password'];

        return $this->hasher->check($plain, $user->getAuthPassword());
    }

    /**
     * Set the fallback user provider.
     *
     * @param UserProvider $provider
     *
     * @return void
     */
    public function setFallback(UserProvider $provider)
    {
        $this->fallback = $provider;
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        $class = '\\' . ltrim($this->model, '\\');

        return new $class;
    }

    /**
     * Perform all missing method calls on the underlying EloquentUserProvider fallback.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->fallback, $name], $arguments);
    }

    /**
     * Determines if login fallback is enabled.
     *
     * @return bool
     */
    protected function isFallingBack(): bool
    {
        $driverName = Config::get('roycedb.driver.name');

        if ($driverName == "adldap") {
            return Config::get('adldap_auth.login_fallback', false);
        }

        return false;
    }
}
