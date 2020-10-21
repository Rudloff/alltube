<?php

/**
 * SessionFactory class.
 */

namespace Alltube\Factory;

use Aura\Session\Session;

/**
 * Manage sessions.
 */
class SessionFactory
{

    /**
     * Get the current session.
     *
     * @return Session
     */
    public static function create()
    {
        $session_factory = new \Aura\Session\SessionFactory();
        return $session_factory->newInstance($_COOKIE);
    }
}
