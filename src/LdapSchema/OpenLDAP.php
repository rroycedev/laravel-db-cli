<?php

namespace Roycedev\Roycedb\LdapSchema;

use Illuminate\Support\Facades\Config;

use Roycedev\Roycedb\LdapSchema\BaseSchema;

class OpenLDAP extends BaseSchema
{

    public function userModel()
    {
        return "App\Ldap\Models\LdapUser";
    }

    public function userPrincipalName()
    {
        return Config::get('roycedb_ldap_schema.userprincipalname');
    }

    /**
     * {@inheritdoc}
     */
    public function distinguishedName()
    {
        return Config::get('roycedb_ldap_schema.distinguishedname');
    }

    /**
     * {@inheritdoc}
     */
    public function distinguishedNameSubKey()
    {
        return Config::get('roycedb_ldap_schema.distinguishednamesubkey');
    }

    /**
     * {@inheritdoc}
     */
    public function filterEnabled()
    {
        return sprintf('(!(%s=*))', $this->lockoutTime());
    }

    /**
     * {@inheritdoc}
     */
    public function filterDisabled()
    {
        return sprintf('(%s=*)', $this->lockoutTime());
    }

    /**
     * {@inheritdoc}
     */
    public function lockoutTime()
    {
        return Config::get('roycedb_ldap_schema.locktimeout');
    }

    /**
     * {@inheritdoc}
     */
    public function objectCategory()
    {
        return Config::get('roycedb_ldap_schema.objectcategory');
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassGroup()
    {
        return Config::get('roycedb_ldap_schema.objectclassgroup');
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassOu()
    {
        return Config::get('roycedb_ldap_schema.objectclassou');
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassPerson()
    {
        return Config::get('roycedb_ldap_schema.objectclassperson');
    }

    /**
     * {@inheritdoc}
     */
    public function objectGuid()
    {
        return Config::get('roycedb_ldap_schema.objectguid');
    }

    /**
     * {@inheritdoc}
     */
    public function objectGuidRequiresConversion()
    {
        return Config::get('roycedb_ldap_schema.objectguidrequiresconversion');
    }
}
