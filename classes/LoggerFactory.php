<?php

namespace Alltube;

use Consolidation\Log\Logger;
use Consolidation\Log\LogOutputStyler;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class LoggerFactory
 * @package Alltube
 */
class LoggerFactory
{

    /**
     * @return Logger
     */
    public static function create()
    {
        $config = Config::getInstance();
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
