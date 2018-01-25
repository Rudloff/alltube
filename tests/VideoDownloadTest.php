<?php
/**
 * VideoDownloadTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\VideoDownload;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the VideoDownload class.
 */
class VideoDownloadTest extends TestCase
{
    /**
     * VideoDownload instance.
     *
     * @var VideoDownload
     */
    private $download;

    /**
     * Config class instance.
     *
     * @var Config
     */
    private $config;

    /**
     * Initialize properties used by test.
     */
    protected function setUp()
    {
        if (PHP_OS == 'WINNT') {
            $configFile = 'config_test_windows.yml';
        } else {
            $configFile = 'config_test.yml';
        }
        $this->config = Config::getInstance('config/'.$configFile);
        $this->download = new VideoDownload($this->config);
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
        $this->config->youtubedl = 'foo';
        new VideoDownload($this->config);
    }

    /**
     * Test VideoDownload constructor with wrong Python path.
     *
     * @return void
     * @expectedException Exception
     */
    public function testConstructorWithMissingPython()
    {
        $this->config->python = 'foo';
        new VideoDownload($this->config);
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
     * @dataProvider m3uUrlProvider
     * @dataProvider rtmpUrlProvider
     * @dataProvider remuxUrlProvider
     */
    public function testGetURL(
        $url,
        $format,
        /* @scrutinizer ignore-unused */ $filename,
        /* @scrutinizer ignore-unused */ $extension,
        $domain
    ) {
        $videoURL = $this->download->getURL($url, $format);
        $this->assertContains($domain, $videoURL[0]);
    }

    /**
     * Test getURL function with a protected video.
     *
     * @return void
     */
    public function testGetURLWithPassword()
    {
        $videoURL = $this->download->getURL('http://vimeo.com/68375962', null, 'youtube-dl');
        $this->assertContains('vimeocdn.com', $videoURL[0]);
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
                'It_s_Not_Me_It_s_You_-_Hearts_Under_Fire-M7IpKCZ47pU',
                'mp4',
                'googlevideo.com',
            ],
            [
                'https://www.youtube.com/watch?v=RJJ6FCAXvKg', 22,
                'Heart_Attack_-_Demi_Lovato_'.
                'Sam_Tsui_Against_The_Current-RJJ6FCAXvKg',
                'mp4',
                'googlevideo.com',
            ],
            [
                'https://vimeo.com/24195442', 'best[protocol^=http]',
                'Carving_the_Mountains-24195442',
                'mp4',
                'vimeocdn.com',
            ],
            [
                'http://www.bbc.co.uk/programmes/b039g8p7', 'bestaudio/best',
                'Leonard_Cohen_Kaleidoscope_-_BBC_Radio_4-b039d07m',
                'flv',
                'bbcodspdns.fcod.llnwd.net',
            ],
            [
                'http://www.rtl2.de/sendung/grip-das-motormagazin/folge/folge-203-0', 'bestaudio/best',
                'GRIP_sucht_den_Sommerkonig-folge-203-0',
                'f4v',
                'edgefcs.net',
            ],
            [
                'https://openload.co/embed/qTsjMEUtN4U', 'best[protocol^=http]',
                'aup-the-lego-ninjago-movie-2017-1508463762.MP4.mp4-qTsjMEUtN4U',
                'mp4',
                'openload.co',
            ],
        ];
    }

    /**
     * Provides M3U8 URLs for tests.
     *
     * @return array[]
     */
    public function remuxUrlProvider()
    {
        return [
            [
                'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'bestvideo+bestaudio',
                'It_s_Not_Me_It_s_You_-_Hearts_Under_Fire-M7IpKCZ47pU',
                'mp4',
                'googlevideo.com',
            ],
        ];
    }

    /**
     * Provides URLs for remux tests.
     *
     * @return array[]
     */
    public function m3uUrlProvider()
    {
        return [
            [
                'https://twitter.com/verge/status/813055465324056576/video/1', 'hls-2176',
                'The_Verge_-_This_tiny_origami_robot_can_self-fold_and_complete_tasks-813055465324056576',
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
                'http://www.canalc2.tv/video/12163', 'rtmp',
                'Terrasses_du_Numerique-12163',
                'flv',
                'vod-flash.u-strasbg.fr',
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
     * @dataProvider urlProvider
     * @dataProvider m3uUrlProvider
     * @dataProvider rtmpUrlProvider
     */
    public function testGetJSON($url, $format)
    {
        $info = $this->download->getJSON($url, $format);
        $this->assertObjectHasAttribute('webpage_url', $info);
        $this->assertObjectHasAttribute('url', $info);
        $this->assertObjectHasAttribute('ext', $info);
        $this->assertObjectHasAttribute('title', $info);
        $this->assertObjectHasAttribute('extractor_key', $info);
        $this->assertObjectHasAttribute('format', $info);
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
     * @dataProvider rtmpUrlProvider
     * @dataProvider remuxUrlProvider
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
     * @dataProvider rtmpUrlProvider
     * @dataProvider remuxUrlProvider
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
        $this->config->avconv = 'foobar';
        $download = new VideoDownload($this->config);
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
     * Assert that a stream is valid.
     *
     * @param resource $stream Stream
     *
     * @return void
     */
    private function assertStream($stream)
    {
        $this->assertInternalType('resource', $stream);
        $this->assertFalse(feof($stream));
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
        $this->assertStream(
            $this->download->getM3uStream(
                $this->download->getJSON($url, $format)
            )
        );
    }

    /**
     * Test getRemuxStream function.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider remuxUrlProvider
     */
    public function testGetRemuxStream($url, $format)
    {
        $urls = $this->download->getURL($url, $format);
        if (count($urls) > 1) {
            $this->assertStream($this->download->getRemuxStream($urls));
        }
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
        $this->assertStream(
            $this->download->getRtmpStream(
                $this->download->getJSON($url, $format)
            )
        );
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
        $this->config->avconv = 'foobar';
        $download = new VideoDownload($this->config);
        $video = $download->getJSON($url, $format);
        $download->getM3uStream($video);
    }

    /**
     * Test getPlaylistArchiveStream function.
     *
     * @return void
     * @requires OS Linux
     */
    public function testGetPlaylistArchiveStream()
    {
        $video = $this->download->getJSON(
            'https://www.youtube.com/playlist?list=PLgdySZU6KUXL_8Jq5aUkyNV7wCa-4wZsC',
            'best'
        );
        $this->assertStream($this->download->getPlaylistArchiveStream($video, 'best'));
    }

    /**
     * Test getConvertedStream function without avconv.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider urlProvider
     */
    public function testGetConvertedStream($url, $format)
    {
        $this->assertStream($this->download->getConvertedStream($url, $format, 32, 'flv'));
    }

    /**
     * Test getConvertedStream function with a M3U8 file.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @expectedException Exception
     * @dataProvider m3uUrlProvider
     */
    public function testGetConvertedStreamM3uError($url, $format)
    {
        $this->download->getConvertedStream($url, $format, 32, 'flv');
    }
}
