<?php
/**
 * VideoDownloadTest class
 *
 * PHP Version 5.3.10
 *
 * @category Youtube-dl
 * @package  Youtubedl
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GNU General Public License http://www.gnu.org/licenses/gpl.html
 * @link     http://rudloff.pro
 * */

require_once __DIR__.'/../common.php';
require_once __DIR__.'/../download.php';

/**
 * Unit tests for the VideoDownload class
 *
 * PHP Version 5.3.10
 *
 * @category Youtube-dl
 * @package  Youtubedl
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GNU General Public License http://www.gnu.org/licenses/gpl.html
 * @link     http://rudloff.pro
 * */
class VideoDownloadTest extends PHPUnit_Framework_TestCase
{
    static private $_testVideoURL = 'https://www.youtube.com/watch?v=RJJ6FCAXvKg';

    /**
     * Test getVersion function
     * @return void
     */
    public function testGetVersion()
    {
        $this->assertStringMatchesFormat('%i.%i.%i', VideoDownload::getVersion());
    }

    /**
     * Test getUA function
     * @return void
     */
    public function testGetUA()
    {
        $this->assertStringStartsWith('Mozilla/', VideoDownload::getUA());
    }

    /**
     * Test listExtractors funtion
     * @return void
     */
    public function testListExtractors()
    {
        $extractors = VideoDownload::listExtractors();
        $this->assertNotEmpty($extractors);
        $this->assertInternalType('array', $extractors);
    }

    /**
     * Test getURL function
     * @return void
     */
    public function testGetURL()
    {
        $url = VideoDownload::getURL(self::$_testVideoURL);
        $this->assertArrayHasKey('success', $url);
        $this->assertArrayHasKey('url', $url);
    }
}
