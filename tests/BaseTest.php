<?php

/**
 * PlaylistArchiveStreamTest class.
 */

namespace Alltube\Test;

use PHPUnit\Framework\TestCase;

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
        $annotations = $this->getAnnotations();
        $requires = [];

        if (isset($annotations['class']['requires'])) {
            $requires = array_merge($requires, $annotations['class']['requires']);
        }
        if (isset($annotations['method']['requires'])) {
            $requires = array_merge($requires, $annotations['method']['requires']);
        }

        foreach ($requires as $require) {
            if ($require == 'download' && getenv('CI')) {
                $this->markTestSkipped('Do not run tests that download videos on CI.');
            }
        }
    }
}
