<?php

/**
 * SessionManager class.
 */

namespace Alltube;

use Aura\Session\Session;
use Aura\Session\SessionFactory;

/**
 * Manage sessions.
 */
class SessionManager
{
    /**
     * Current session.
     *
     * @var Session
     */
    private static $session;

    /**
     * Get the current session.
     *
     * @return Session
     */
    public static function getSession()
    {
        if (!isset(self::$session)) {
            $session_factory = new SessionFactory();
            self::$session = $session_factory->newInstance($_COOKIE);
        }

        return self::$session;
    }
}
