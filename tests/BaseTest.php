<?php

/**
 * PlaylistArchiveStreamTest class.
 */

namespace Alltube\Test;

use OndraM\CiDetector\CiDetector;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Test;

/**
 * Abstract class used by every test.
 */
abstract class BaseTest extends TestCase
{
    /**
     * Get the config file used in tests.
     *
     * @return string Path to file
     */
    protected function getConfigFile(): string
    {
        return __DIR__ . '/../config/config_test.yml';
    }

    /**
     * Prepare tests.
     */
    protected function setUp(): void
    {
        $this->checkRequirements();
    }

    /**
     * Check tests requirements.
     * @return void
     */
    protected function checkRequirements()
    {
        $ciDetector = new CiDetector();
        $annotations = Test::parseTestMethodAnnotations(
            static::class,
            $this->getName()
        );
        $requires = [];

        if (isset($annotations['class']['requires'])) {
            $requires = array_merge($requires, $annotations['class']['requires']);
        }
        if (isset($annotations['method']['requires'])) {
            $requires = array_merge($requires, $annotations['method']['requires']);
        }

        foreach ($requires as $require) {
            if ($require == 'download' && $ciDetector->isCiDetected()) {
                $this->markTestSkipped('Do not run tests that download videos on CI.');
            }
        }
    }
}
