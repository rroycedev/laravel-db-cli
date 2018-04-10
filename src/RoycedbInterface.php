<?php

namespace Roycedev\Roycedb;

use Adldap\Connections\ConnectionInterface;
use Adldap\Connections\ProviderInterface;
use Roycedev\Roycedb\SchemaInterface;

interface RoycedbInterface
{
    /**
     * Add a provider by the specified name.
     *
     * @param mixed               $configuration
     * @param string              $name
     * @param ConnectionInterface $connection
     * @param SchemaInterface     $schema
     *
     * @return $this
     *
     * @throws \InvalidArgumentException When an invalid type is given as the configuration argument.
     */
    public function addProvider($configuration = [], $name, ConnectionInterface $connection = null, SchemaInterface $schema = null);

    /**
     * Returns all of the connection providers.
     *
     * @return array
     */
    public function getProviders();

    /**
     * Retrieves a Provider using it's specified name.
     *
     * @param string $name
     *
     * @throws AdldapException When the specified provider does not exist.
     *
     * @return ProviderInterface
     */
    public function getProvider($name);

    /**
     * Sets the default provider.
     *
     * @param string $name
     *
     * @throws AdldapException When the specified provider does not exist.
     */
    public function setDefaultProvider($name);

    /**
     * Retrieves the first default provider.
     *
     * @throws AdldapException When no default provider exists.
     *
     * @return ProviderInterface
     */
    public function getDefaultProvider();

    /**
     * Removes a provider by the specified name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function removeProvider($name);

    /**
     * Connects to the specified provider.
     *
     * If no username and password is given, then the providers
     * configured admin credentials are used.
     *
     * @param string|null $name
     * @param string|null $username
     * @param string|null $password
     *
     * @return ProviderInterface
     */
    public function connect($name = null, $username = null, $password = null);

    /**
     * Call methods upon the default provider dynamically.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters);
}
