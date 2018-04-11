<?php

namespace Roycedev\Roycedb\Helpers;

/**
 * Class Str
 *
 * String helper methods.
 *
 * @package Roycedev\Roycedb\Helpers
 */
class Str
{
    /**
     * translateLdapFilter
     *
     * Converts an LDAP filter with values in hexadecimal format to
     * ascii format.
     *
     * @param $filter
     *
     * @return String
     *
     */
    public static function translateLdapFilter($filter)
    {
        $parts = explode(")", $filter);

        $translated = array();

        for ($i = 0; $i < count($parts); $i++) {
            if ($parts[$i] == "") {
                continue;
            }

            $str = str_replace("(", "", $parts[$i]);
            $str = str_replace(")", "", $str);
            $str = str_replace("&", "", $str);

            $subparts = explode("=", $str);

            $key = $subparts[0];
            $valHex = $subparts[1];
            $valAscii = "";

            $hexparts = explode("\\", $valHex);

            for ($j = 1; $j < count($hexparts); $j++) {
                $valAscii .= chr(hexdec($hexparts[$j]));
            }

            $translated[] = $key . "=" . $valAscii;
        }

        $translatedStr = "&(";

        foreach ($translated as $t) {
            $translatedStr .= "(" . $t . ")";
        }

        $translatedStr .= ")";

        return $translatedStr;
    }
}
