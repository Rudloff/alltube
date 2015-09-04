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
        $this->assertContains('youtube', $extractors);
    }

    /**
     * Test getURL function
     * @param string $url    URL
     * @param string $format Format
     * @return void
     * @dataProvider URLProvider
     */
    public function testGetURL($url, $format)
    {
        $videoURL = VideoDownload::getURL($url, $format);
        $this->assertArrayHasKey('success', $videoURL);
        $this->assertArrayHasKey('url', $videoURL);
    }

    /**
     * Test getURL function errors
     * @param string $url URL
     * @return void
     * @expectedException Exception
     * @dataProvider ErrorURLProvider
     */
    public function testGetURLError($url)
    {
        $videoURL = VideoDownload::getURL($url);
    }

    /**
     * Provides URLs for tests
     * @return void
     */
    public function URLProvider()
    {
        return array(
            array(
                'https://www.youtube.com/watch?v=M7IpKCZ47pU', null,
                "It's Not Me, It's You - Hearts Under Fire-M7IpKCZ47pU.mp4"
            ),
            array(
                'https://www.youtube.com/watch?v=RJJ6FCAXvKg', 22,
                "'Heart Attack' - Demi Lovato ".
                "(Sam Tsui & Against The Current)-RJJ6FCAXvKg.mp4"
            ),
            array(
                'https://vimeo.com/24195442', null,
                "Carving the Mountains-24195442.mp4"
            ),
        );
    }

    /**
     * Provides incorrect URLs for tests
     * @return void
     */
    public function errorURLProvider()
    {
        return array(
            array('http://example.com/video')
        );
    }

    /**
     * Test getFilename function
     * @param string $url    URL
     * @param string $format Format
     * @param string $result Expected filename
     * @return void
     * @dataProvider URLProvider
     */
    public function testGetFilename($url, $format, $result)
    {
        $filename = VideoDownload::getFilename($url, $format);
        $this->assertEquals($filename, $result);
    }

    /**
     * Test getJSON function
     * @param string $url    URL
     * @param string $format Format
     * @return void
     * @dataProvider URLProvider
     */
    public function testGetJSON($url, $format)
    {
        $info = VideoDownload::getJSON($url, $format);
        $this->assertObjectHasAttribute('webpage_url', $info);
        $this->assertObjectHasAttribute('url', $info);
        $this->assertObjectHasAttribute('ext', $info);
        $this->assertObjectHasAttribute('title', $info);
        $this->assertObjectHasAttribute('formats', $info);
        $this->assertObjectHasAttribute('_filename', $info);
    }

    /**
     * Test getJSON function errors
     * @param string $url URL
     * @return void
     * @expectedException Exception
     * @dataProvider ErrorURLProvider
     */
    public function testGetJSONError($url)
    {
        $videoURL = VideoDownload::getJSON($url);
    }
}
