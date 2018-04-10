<?php

namespace Roycedev\Roycedb\Models;

use DateTime;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class User
 *
 * Represents an LDAP user.
 *
 * @package Adldap\Models
 */
class User extends Entry implements Authenticatable
{
}
