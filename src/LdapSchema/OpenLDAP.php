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
        return Config::get('roycedb_ldap_schema.names.userprincipalname');
    }

    /**
     * {@inheritdoc}
     */
    public function distinguishedName()
    {
        return Config::get('roycedb_ldap_schema.names.distinguishedname');
    }

    /**
     * {@inheritdoc}
     */
    public function distinguishedNameSubKey()
    {
        return Config::get('roycedb_ldap_schema.names.distinguishednamesubkey');
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
        return Config::get('roycedb_ldap_schema.names.locktimeout');
    }

    /**
     * {@inheritdoc}
     */
    public function objectCategory()
    {
        return Config::get('roycedb_ldap_schema.names.objectcategory');
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassGroup()
    {
        return Config::get('roycedb_ldap_schema.names.objectclassgroup');
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassOu()
    {
        return Config::get('roycedb_ldap_schema.names.objectclassou');
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassPerson()
    {
        return Config::get('roycedb_ldap_schema.names.objectclassperson');
    }

    /**
     * {@inheritdoc}
     */
    public function objectGuid()
    {
        return Config::get('roycedb_ldap_schema.names.objectguid');
    }

    /**
     * {@inheritdoc}
     */
    public function objectGuidRequiresConversion()
    {
        return Config::get('roycedb_ldap_schema.names.objectguidrequiresconversion');
    }
}
