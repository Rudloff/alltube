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
        $this->download = new VideoDownload(Config::getInstance('config_test.yml'));
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
     * @param string $url       URL
     * @param string $format    Format
     * @param string $filename  Filename
     * @param string $extension File extension
     * @param string $domain    Domain
     *
     * @return void
     * @dataProvider urlProvider
     */
    public function testGetURL($url, $format, $filename, $extension, $domain)
    {
        $videoURL = $this->download->getURL($url, $format);
        $this->assertContains($domain, $videoURL);
    }

    /**
     * Test getURL function with a protected video.
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
                'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'best[protocol^=http]',
                "It's Not Me, It's You - Hearts Under Fire-M7IpKCZ47pU",
                'mp4',
                'googlevideo.com',
            ],
            [
                'https://www.youtube.com/watch?v=RJJ6FCAXvKg', 22,
                "'Heart Attack' - Demi Lovato ".
                '(Sam Tsui & Against The Current)-RJJ6FCAXvKg',
                'mp4',
                'googlevideo.com',
            ],
            [
                'https://vimeo.com/24195442', 'best[protocol^=http]',
                'Carving the Mountains-24195442',
                'mp4',
                'vimeocdn.com',
            ],
            [
                'http://www.bbc.co.uk/programmes/b039g8p7', 'bestaudio/best',
                'Leonard Cohen, Kaleidoscope - BBC Radio 4-b039d07m',
                'flv',
                'bbcodspdns.fcod.llnwd.net',
            ],
            [
                'http://www.rtl2.de/sendung/grip-das-motormagazin/folge/folge-203-0', 'bestaudio/best',
                'GRIP sucht den Sommerkönig-folge-203-0',
                'f4v',
                'edgefcs.net',
            ],
        ];
    }

    /**
     * Provides M3U8 URLs for tests.
     *
     * @return array[]
     */
    public function m3uUrlProvider()
    {
        return [
            [
                'https://twitter.com/verge/status/813055465324056576/video/1', 'best',
                'The Verge - This tiny origami robot can self-fold and complete tasks-813055465324056576',
                'mp4',
                'video.twimg.com',
            ],
        ];
    }

    /**
     * Provides RTMP URLs for tests.
     *
     * @return array[]
     */
    public function rtmpUrlProvider()
    {
        return [
            [
                'http://www.rtl2.de/sendung/grip-das-motormagazin/folge/folge-203-0', 'bestaudio/best',
                'GRIP sucht den Sommerkönig-folge-203-0',
                'f4v',
                'edgefcs.net',
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
     * @dataProvider m3uUrlProvider
     */
    public function testGetJSON($url, $format)
    {
        $info = $this->download->getJSON($url, $format);
        $this->assertObjectHasAttribute('webpage_url', $info);
        $this->assertObjectHasAttribute('url', $info);
        $this->assertObjectHasAttribute('ext', $info);
        $this->assertObjectHasAttribute('title', $info);
        $this->assertObjectHasAttribute('extractor_key', $info);
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
        $this->download->getJSON($url);
    }

    /**
     * Test getFilename function.
     *
     * @param string $url       URL
     * @param string $format    Format
     * @param string $filename  Filename
     * @param string $extension File extension
     *
     * @return void
     * @dataProvider urlProvider
     * @dataProvider m3uUrlProvider
     */
    public function testGetFilename($url, $format, $filename, $extension)
    {
        $videoFilename = $this->download->getFilename($url, $format);
        $this->assertEquals($videoFilename, $filename.'.'.$extension);
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
     * @param string $url      URL
     * @param string $format   Format
     * @param string $filename Filename
     *
     * @return void
     * @dataProvider urlProvider
     * @dataProvider m3uUrlProvider
     */
    public function testGetAudioFilename($url, $format, $filename)
    {
        $videoFilename = $this->download->getAudioFilename($url, $format);
        $this->assertEquals($videoFilename, $filename.'.mp3');
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
        $download = new VideoDownload(new Config(['avconv'=>'foobar']));
        $download->getAudioStream($url, $format);
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
        $download = new VideoDownload(new Config(['curl'=>'foobar', 'rtmpdump'=>'foobar']));
        $download->getAudioStream($url, $format);
    }

    /**
     * Test getAudioStream function with a M3U8 file.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @expectedException Exception
     * @dataProvider m3uUrlProvider
     */
    public function testGetAudioStreamM3uError($url, $format)
    {
        $this->download->getAudioStream($url, $format);
    }

    /**
     * Test getM3uStream function.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider m3uUrlProvider
     */
    public function testGetM3uStream($url, $format)
    {
        $video = $this->download->getJSON($url, $format);
        $stream = $this->download->getM3uStream($video);
        $this->assertInternalType('resource', $stream);
        $this->assertFalse(feof($stream));
    }

    /**
     * Test getRtmpStream function.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider rtmpUrlProvider
     */
    public function testGetRtmpStream($url, $format)
    {
        $video = $this->download->getJSON($url, $format);
        $stream = $this->download->getRtmpStream($video);
        $this->assertInternalType('resource', $stream);
        $this->assertFalse(feof($stream));
    }

    /**
     * Test getM3uStream function without avconv.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @expectedException Exception
     * @dataProvider m3uUrlProvider
     */
    public function testGetM3uStreamAvconvError($url, $format)
    {
        $download = new VideoDownload(new Config(['avconv'=>'foobar']));
        $video = $download->getJSON($url, $format);
        $download->getM3uStream($video);
    }
}
