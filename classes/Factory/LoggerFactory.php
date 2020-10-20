<?php

namespace Alltube\Factory;

use Consolidation\Log\Logger;
use Consolidation\Log\LogOutputStyler;
use Slim\Container;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class LoggerFactory
 * @package Alltube
 */
class LoggerFactory
{

    /**
     * @param Container $container
     * @return Logger
     */
    public static function create(Container $container)
    {
        $config = $container->get('config');
        if ($config->debug) {
            $verbosity = ConsoleOutput::VERBOSITY_DEBUG;
        } else {
            $verbosity = ConsoleOutput::VERBOSITY_NORMAL;
        }

        $logger = new Logger(new ConsoleOutput($verbosity));
        $logger->setLogOutputStyler(new LogOutputStyler());

        return $logger;
    }
}
