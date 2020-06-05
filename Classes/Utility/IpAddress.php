<?php

namespace Mediatis\Formrelay\Utility;

final class IpAddress
{
    /**
     * Get the current IP Address of the user event the user is behind an proxy
     *
     * return string IP Address of the user
     */
    public static function getUserIpAdress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}
