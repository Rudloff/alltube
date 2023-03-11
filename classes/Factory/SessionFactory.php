<?php

/**
 * SessionFactory class.
 */

namespace Alltube\Factory;

use Aura\Session\Session;
use Aura\Session\SessionFactory as AuraSessionFactory;
use Slim\Container;

/**
 * Manage sessions.
 */
class SessionFactory
{
    /**
     * Get the current session.
     *
     * @param Container $container
     * @return Session
     */
    public static function create(Container $container): Session
    {
        $session_factory = new AuraSessionFactory();
        $session = $session_factory->newInstance($_COOKIE);

        $session->setCookieParams(['httponly' => true]);

        $request = $container->get('request');
        if (
            in_array('https', $request->getHeader('X-Forwarded-Proto'))
            || $request->getUri()->getScheme() == 'https'
        ) {
            $session->setCookieParams(['secure' => true]);
        }

        return $session;
    }
}
