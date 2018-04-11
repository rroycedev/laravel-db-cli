# RoyceDatabase - Laravel version 1.0.2

## Requirements

Version 1.0.2

To use Adldap2-Laravel, your application and server must meet the following requirements:

- Laravel 5.*
- PHP 7.0 or greater
- PHP LDAP extension enabled
- An LDAP Server

## Index

* [Installation](#installation)
* [Usage](#usage)
* Auth Driver
  * [Installation & Basic Setup](docs/auth.md#installation)
  * [Quick Start - From Scratch](docs/quick-start.md)
  * [Upgrading](docs/auth.md#upgrading-from-3-to-4)
  * [Features](docs/auth.md#features)
    * [Providers](docs/auth.md#providers)
    * [Scopes](docs/auth.md#scopes)
    * [Rules](docs/auth.md#rules)
    * [Events](docs/auth.md#events)
    * [Synchronizing Attributes](docs/auth.md#syncing-attributes)
    * [Model Binding](docs/auth.md#model-binding)
    * [Login Fallback](docs/auth.md#fallback)
    * [Single Sign On (SSO) Middleware](docs/auth.md#middleware)
    * [Password Synchronization](docs/auth.md#password-synchronization)
    * [Importing Users](docs/importing.md)

## Installation

Run the following command in the root of your project:

```bash
composer require adldap2/adldap2-laravel
```

> **Note**: If you are using laravel 5.5 or higher you can skip the service provider
> and facade registration and continue with publishing the configuration file.

Once finished, insert the service provider in your `config/app.php` file:

```php
Adldap\Laravel\AdldapServiceProvider::class,
```

Then insert the facade:

```php
'Adldap' => Adldap\Laravel\Facades\Adldap::class
```

Publish the configuration file by running:

```bash
php artisan vendor:publish --tag="adldap"
```

Now you're all set!

## Usage

First, configure your LDAP connection in the `config/adldap.php` file.

Then, you can perform methods on your default connection through the `Adldap` facade like so:

```php
use Adldap\Laravel\Facades\Adldap;

// Finding a user:
$user = Adldap::search()->users()->find('john doe');

// Searching for a user:
$search = Adldap::search()->where('cn', '=', 'John Doe')->get();

// Running an operation under a different connection:
$users = Adldap::getProvider('other-connection')->search()->users()->get();

// Creating a user:
$user = Adldap::make()->user([
    'cn' => 'John Doe',
]);

// Saving a user:
$user->save();
```

If you do not specify an alternate connection using `getProvider()`, your
`default` connection will be utilized for all methods.

Upon performing operations without specifying a connection, your default
connection will be connected to and bound automatically
using your configured username and password.

If you would prefer, you can also inject the Adldap interface into your controllers,
which gives you access to all of your LDAP connections and resources as the facade.

```php
use Adldap\AdldapInterface;

class UserController extends Controller
{
    /**
     * @var Adldap
     */
    protected $ldap;
    
    /**
     * Constructor.
     *
     * @param AdldapInterface $adldap
     */
    public function __construct(AdldapInterface $ldap)
    {
        $this->ldap = $ldap;
    }
    
    /**
     * Displays the all LDAP users.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = $this->ldap->search()->users()->get();
        
        return view('users.index', compact('users'));
    }
    
    /**
     * Displays the specified LDAP user.
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = $this->ldap->search()->findByGuid($id);
        
        return view('users.show', compact('user'));
    }
}
```

To see more usage in detail, please visit the [Adldap2](http://github.com/Adldap2/Adldap2) repository.
