<?php

namespace App\Support;

class Permissions
{
    public static function groups()
    {
        return config('permissions.groups');
    }

    /** All permission keys */
    public static function keys()
    {
        $keys = [];
        foreach (self::groups() as $group) {
            $keys = array_merge($keys, array_keys($group['items']));
        }

        return $keys;
    }

    public static function label($key)
    {
        foreach (self::groups() as $group) {
            if (isset($group['items'][$key])) {
                return $group['items'][$key];
            }
        }

        return $key;
    }

    /** Permissions granted by the general manager role (everything except the courier own-shipments restriction) */
    public static function adminGrants()
    {
        return array_values(array_diff(self::keys(), ['scope.own_shipments']));
    }
}
