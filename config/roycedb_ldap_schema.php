<?php

return [
    'names' => [
        'usermodel' => 'App\Ldap\Models\LdapUser',
        'userprincipalname' => 'uid',
        'distinguishedname' => 'dn',
        'distinguishednamesubkey' => '',
        'lockouttime' => 'pwdAccountLockedTime',
        'objectcategory' => 'objectclass',
        'objectclassgroup' => 'posixGroup',
        'objectclassou' => 'ou',
        'objectlcassperson' => 'inetorgperson',
        'objectguid' => 'entryuuid',
        'objectguidneedsconversion' => false
    ]

];
