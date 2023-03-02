<?php

namespace Alltube\Factory;

use Consolidation\Log\Logger;
use Consolidation\Log\LoggerManager;
use Consolidation\Log\LogOutputStyler;
use Slim\Container;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LoggerFactory
 * @package Alltube
 */
class LoggerFactory
{
    /**
     * @param Container $container
     * @return LoggerManager
     */
    public static function create(Container $container): LoggerManager
    {
        $config = $container->get('config');
        if ($config->debug) {
            $verbosity = OutputInterface::VERBOSITY_DEBUG;
        } else {
            $verbosity = OutputInterface::VERBOSITY_NORMAL;
        }

        $loggerManager = new LoggerManager();

        $logger = new Logger(new ConsoleOutput($verbosity));
        $logger->setLogOutputStyler(new LogOutputStyler());

        $loggerManager->add('default', $logger);

        return $loggerManager;
    }
}
