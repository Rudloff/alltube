<?php
/**
 * VideoDownloadTest class.
 */
namespace Alltube\Test;

use Alltube\Config;
use Alltube\VideoDownload;

/**
 * Unit tests for the VideoDownload class.
 */
class VideoDownloadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * VideoDownload instance.
     *
     * @var VideoDownload
     */
    private $download;

    /**
     * Initialize properties used by test.
     */
    protected function setUp()
    {
        $this->download = new VideoDownload();
    }

    /**
     * Destroy properties after test.
     */
    protected function tearDown()
    {
        Config::destroyInstance();
    }

    /**
     * Test VideoDownload constructor with wrong youtube-dl path.
     *
     * @return void
     * @expectedException Exception
     */
    public function testConstructorWithMissingYoutubedl()
    {
        new VideoDownload(
            new Config(['youtubedl' => 'foo'])
        );
    }

    /**
     * Test VideoDownload constructor with wrong Python path.
     *
     * @return void
     * @expectedException Exception
     */
    public function testConstructorWithMissingPython()
    {
        new VideoDownload(
            new Config(['python' => 'foo'])
        );
    }

    /**
     * Test listExtractors function.
     *
     * @return void
     */
    public function testListExtractors()
    {
        $extractors = $this->download->listExtractors();
        $this->assertContains('youtube', $extractors);
    }

    /**
     * Test getURL function.
     *
     * @param string $url      URL
     * @param string $format   Format
     * @param string $filename Filename
     * @param string $domain   Domain
     *
     * @return void
     * @dataProvider urlProvider
     */
    public function testGetURL($url, $format, $filename, $domain)
    {
        $videoURL = $this->download->getURL($url, $format);
        $this->assertContains($domain, $videoURL);
    }

    /**
     * Test getURL function with a protected video
     *
     * @return void
     */
    public function testGetURLWithPassword()
    {
        $this->assertContains('vimeocdn.com', $this->download->getURL('http://vimeo.com/68375962', null, 'youtube-dl'));
    }

    /**
     * Test getURL function with a protected video and no password.
     *
     * @return void
     * @expectedException \Alltube\PasswordException
     */
    public function testGetURLWithMissingPassword()
    {
        $this->download->getURL('http://vimeo.com/68375962');
    }

    /**
     * Test getURL function with a protected video and a wrong password.
     *
     * @return void
     * @expectedException Exception
     */
    public function testGetURLWithWrongPassword()
    {
        $this->download->getURL('http://vimeo.com/68375962', null, 'foo');
    }

    /**
     * Test getURL function errors.
     *
     * @param string $url URL
     *
     * @return void
     * @expectedException Exception
     * @dataProvider      ErrorUrlProvider
     */
    public function testGetURLError($url)
    {
        $this->download->getURL($url);
    }

    /**
     * Provides URLs for tests.
     *
     * @return array[]
     */
    public function urlProvider()
    {
        return [
            [
                'https://www.youtube.com/watch?v=M7IpKCZ47pU', null,
                "It's Not Me, It's You - Hearts Under Fire-M7IpKCZ47pU.mp4",
                'googlevideo.com',
                "It's Not Me, It's You - Hearts Under Fire-M7IpKCZ47pU.mp3",
            ],
            [
                'https://www.youtube.com/watch?v=RJJ6FCAXvKg', 22,
                "'Heart Attack' - Demi Lovato ".
                '(Sam Tsui & Against The Current)-RJJ6FCAXvKg.mp4',
                'googlevideo.com',
                "'Heart Attack' - Demi Lovato ".
                '(Sam Tsui & Against The Current)-RJJ6FCAXvKg.mp3',
            ],
            [
                'https://vimeo.com/24195442', null,
                'Carving the Mountains-24195442.mp4',
                'vimeocdn.com',
                'Carving the Mountains-24195442.mp3',
            ],
            [
                'http://www.bbc.co.uk/programmes/b039g8p7', 'bestaudio/best',
                'Leonard Cohen, Kaleidoscope - BBC Radio 4-b039d07m.flv',
                'bbcodspdns.fcod.llnwd.net',
                'Leonard Cohen, Kaleidoscope - BBC Radio 4-b039d07m.mp3',
            ],
            [
                'http://www.rtl2.de/sendung/grip-das-motormagazin/folge/folge-203-0', 'bestaudio/best',
                'GRIP sucht den Sommerkönig-folge-203-0.f4v',
                'edgefcs.net',
                'GRIP sucht den Sommerkönig-folge-203-0.mp3',
            ],
        ];
    }

    /**
     * Provides incorrect URLs for tests.
     *
     * @return array[]
     */
    public function errorUrlProvider()
    {
        return [
            ['http://example.com/video'],
        ];
    }

    /**
     * Test getJSON function.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider URLProvider
     */
    public function testGetJSON($url, $format)
    {
        $info = $this->download->getJSON($url, $format);
        $this->assertObjectHasAttribute('webpage_url', $info);
        $this->assertObjectHasAttribute('url', $info);
        $this->assertObjectHasAttribute('ext', $info);
        $this->assertObjectHasAttribute('title', $info);
        $this->assertObjectHasAttribute('formats', $info);
        $this->assertObjectHasAttribute('_filename', $info);
    }

    /**
     * Test getJSON function errors.
     *
     * @param string $url URL
     *
     * @return void
     * @expectedException Exception
     * @dataProvider      ErrorURLProvider
     */
    public function testGetJSONError($url)
    {
        $videoURL = $this->download->getJSON($url);
    }

    /**
     * Test getFilename function.
     *
     * @param string $url      URL
     * @param string $format   Format
     * @param string $filename Filename
     *
     * @return void
     * @dataProvider urlProvider
     */
    public function testGetFilename($url, $format, $filename)
    {
        $videoFilename = $this->download->getFilename($url, $format);
        $this->assertEquals($videoFilename, $filename);
    }

    /**
     * Test getFilename function errors.
     *
     * @param string $url URL
     *
     * @return void
     * @expectedException Exception
     * @dataProvider      ErrorUrlProvider
     */
    public function testGetFilenameError($url)
    {
        $this->download->getFilename($url);
    }

    /**
     * Test getAudioFilename function.
     *
     * @param string $url           URL
     * @param string $format        Format
     * @param string $filename      Filename
     * @param string $domain        Domain
     * @param string $audioFilename MP3 audio file name
     *
     * @return void
     * @dataProvider urlProvider
     */
    public function testGetAudioFilename($url, $format, $filename, $domain, $audioFilename)
    {
        $videoFilename = $this->download->getAudioFilename($url, $format);
        $this->assertEquals($videoFilename, $audioFilename);
    }

    /**
     * Test getAudioStream function.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider urlProvider
     */
    public function testGetAudioStream($url, $format)
    {
        $stream = $this->download->getAudioStream($url, $format);
        $this->assertInternalType('resource', $stream);
        $this->assertFalse(feof($stream));
    }

    /**
     * Test getAudioStream function without avconv.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @expectedException Exception
     * @dataProvider      urlProvider
     */
    public function testGetAudioStreamAvconvError($url, $format)
    {
        $config = \Alltube\Config::getInstance();
        $config->avconv = 'foobar';
        $this->download->getAudioStream($url, $format);
    }

    /**
     * Test getAudioStream function without curl or rtmpdump.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @expectedException Exception
     * @dataProvider      urlProvider
     */
    public function testGetAudioStreamCurlError($url, $format)
    {
        $config = \Alltube\Config::getInstance();
        $config->curl = 'foobar';
        $config->rtmpdump = 'foobar';
        $this->download->getAudioStream($url, $format);
    }
}
