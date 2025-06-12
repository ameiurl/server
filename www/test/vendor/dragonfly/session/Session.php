<?php

namespace Dragonfly\Session;


class Session
{
    public static function start($cookieDomain = '.cncn.com')
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (getenv('APP_ENV') === 'prod') {
                ini_set('session.cookie_domain', $cookieDomain);
            }

            try {
                session_start();
            } catch (\Exception $ex) {
                session_start();
            }

        }
    }
}
