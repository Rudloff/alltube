<?php

/**
 * VideoTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\Exception\ConfigException;
use Alltube\Exception\DependencyException;
use Alltube\Library\Downloader;
use Alltube\Library\Exception\AlltubeLibraryException;
use Alltube\Library\Exception\AvconvException;
use Alltube\Library\Exception\InvalidProtocolConversionException;
use Alltube\Library\Exception\PasswordException;
use Alltube\Library\Exception\PlaylistConversionException;
use Alltube\Library\Exception\RemuxException;
use Alltube\Library\Exception\WrongPasswordException;
use Alltube\Library\Exception\YoutubedlException;
use Alltube\Library\Video;
use SmartyException;

/**
 * Unit tests for the Video class.
 * @requires download
 * @todo Split Downloader and Video tests.
 */
class VideoTest extends ContainerTest
{
    /**
     * Downloader instance used in tests.
     *
     * @var Downloader
     */
    private $downloader;

    /**
     * Video format used in tests.
     *
     * @var string
     */
    private $format;

    /**
     * Prepare tests.
     *
     * @throws ConfigException
     * @throws DependencyException
     * @throws SmartyException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->downloader = $this->container->get('config')->getDownloader();
        $this->format = 'best';
    }

    /**
     * Test getExtractors function.
     *
     * @return void
     * @throws AlltubeLibraryException
     */
    public function testGetExtractors()
    {
        $this->assertContains('youtube', $this->downloader->getExtractors());
    }

    /**
     * Test getUrl function.
     *
     * @param string $url URL
     * @param string $format Format
     * @param string $filename Filename
     * @param string $extension File extension
     * @param string $domain Domain
     *
     * @return void
     * @throws AlltubeLibraryException
     * @dataProvider urlProvider
     * @dataProvider m3uUrlProvider
     * @dataProvider remuxUrlProvider
     */
    public function testgetUrl(
        string $url,
        string $format,
        string $filename,
        string $extension,
        string $domain
    ) {
        $video = new Video($this->downloader, $url, $format);
        foreach ($video->getUrl() as $videoURL) {
            $this->assertStringContainsString($domain, $videoURL);
        }
    }

    /**
     * Test getUrl function with a protected video.
     *
     * @return void
     * @throws AlltubeLibraryException
     */
    public function testgetUrlWithPassword()
    {
        $video = new Video($this->downloader, 'https://vimeo.com/68375962', 'best', 'youtube-dl');
        foreach ($video->getUrl() as $videoURL) {
            $this->assertStringContainsString('vimeocdn.com', $videoURL);
        }
    }

    /**
     * Test getUrl function with a protected video and no password.
     *
     * @return void
     * @throws AlltubeLibraryException
     */
    public function testgetUrlWithMissingPassword()
    {
        $this->expectException(PasswordException::class);
        $video = new Video($this->downloader, 'https://vimeo.com/68375962', $this->format);
        $video->getUrl();
    }

    /**
     * Test getUrl function with a protected video and a wrong password.
     *
     * @return void
     * @throws AlltubeLibraryException
     */
    public function testgetUrlWithWrongPassword()
    {
        $this->expectException(WrongPasswordException::class);
        $video = new Video($this->downloader, 'https://vimeo.com/68375962', 'best', 'foo');
        $video->getUrl();
    }

    /**
     * Test getUrl function errors.
     *
     * @param string $url URL
     *
     * @return void
     * @throws AlltubeLibraryException
     * @dataProvider ErrorUrlProvider
     */
    public function testgetUrlError(string $url)
    {
        $this->expectException(YoutubedlException::class);
        $video = new Video($this->downloader, $url, $this->format);
        $video->getUrl();
    }

    /**
     * Provides URLs for tests.
     *
     * @return array[]
     */
    public function urlProvider(): array
    {
        return [
            [
                'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'best[protocol^=http]',
                'It_s_Not_Me_It_s_You_-_Hearts_Under_Fire-M7IpKCZ47pU',
                'mp4',
                'googlevideo.com',
            ],
            [
                'https://www.youtube.com/watch?v=RJJ6FCAXvKg', '18',
                'Heart_Attack_-_Demi_Lovato_' .
                'Sam_Tsui_Against_The_Current-RJJ6FCAXvKg',
                'mp4',
                'googlevideo.com',
            ],
            [
                'https://www.bbc.co.uk/programmes/b039g8p7', 'bestaudio/best',
                'Kaleidoscope_Leonard_Cohen-b039d07m',
                'flv',
                'bbcodspdns.fcod.llnwd.net',
            ],
            [
                'https://vimeo.com/24195442', 'http-720p',
                'Carving_the_Mountains-24195442',
                'mp4',
                'akamaized.net',
            ]
        ];
    }

    /**
     * Provides URLs for remux tests.
     *
     * @return array[]
     */
    public function remuxUrlProvider(): array
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
     * Provides M3U8 URLs for tests.
     *
     * @return array[]
     */
    public function m3uUrlProvider(): array
    {
        return [
            [
                'https://twitter.com/verge/status/813055465324056576/video/1', 'hls-2176',
                'The_Verge_-_This_tiny_origami_robot_can_self-fold_and_complete_tasks-813055465324056576',
                'mp4',
                'video.twimg.com',
            ]
        ];
    }

    /**
     * Provides RTMP URLs for tests.
     *
     * @return array[]
     */
    public function rtmpUrlProvider(): array
    {
        return [
            [
                'http://www.rtvnh.nl/video/131946', 'rtmp-264',
                'Ketting_van_strandgasten-131946',
                'flv',
                'lb-nh-vod.cdn.streamgate.nl',
            ],
        ];
    }

    /**
     * Provides incorrect URLs for tests.
     *
     * @return array[]
     */
    public function errorUrlProvider(): array
    {
        return [
            ['https://example.com/video'],
        ];
    }

    /**
     * Test getJSON function.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @throws AlltubeLibraryException
     * @dataProvider urlProvider
     * @dataProvider m3uUrlProvider
     */
    public function testGetJson(string $url, string $format)
    {
        $video = new Video($this->downloader, $url, $format);
        $info = $video->getJson();
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
     * @throws AlltubeLibraryException
     * @dataProvider ErrorURLProvider
     */
    public function testGetJsonError(string $url)
    {
        $this->expectException(YoutubedlException::class);
        $video = new Video($this->downloader, $url, $this->format);
        $video->getJson();
    }

    /**
     * Test getFilename function.
     *
     * @param string $url URL
     * @param string $format Format
     * @param string $filename Filename
     * @param string $extension File extension
     *
     * @return void
     * @throws AlltubeLibraryException
     * @dataProvider urlProvider
     * @dataProvider m3uUrlProvider
     * @dataProvider remuxUrlProvider
     */
    public function testGetFilename(string $url, string $format, string $filename, string $extension)
    {
        $video = new Video($this->downloader, $url, $format);
        $this->assertEquals($video->getFilename(), $filename . '.' . $extension);
    }

    /**
     * Test getFilename function errors.
     *
     * @param string $url URL
     *
     * @return void
     * @throws AlltubeLibraryException
     * @dataProvider ErrorUrlProvider
     */
    public function testGetFilenameError(string $url)
    {
        $this->expectException(YoutubedlException::class);
        $video = new Video($this->downloader, $url, $this->format);
        $video->getFilename();
    }

    /**
     * Test getAudioStream function.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @throws AlltubeLibraryException
     * @dataProvider urlProvider
     */
    public function testGetAudioStream(string $url, string $format)
    {
        $video = new Video($this->downloader, $url, $format);
        $this->assertStream($this->downloader->getAudioStream($video));
    }

    /**
     * Test getAudioStream function without ffmpeg.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @throws AlltubeLibraryException
     * @throws ConfigException
     * @dataProvider urlProvider
     */
    public function testGetAudioStreamFfmpegError(string $url, string $format)
    {
        $this->expectException(AvconvException::class);
        $config = new Config(['ffmpeg' => 'foobar']);
        $downloader = $config->getDownloader();

        $video = new Video($this->downloader, $url, $format, $this->format);
        $downloader->getAudioStream($video);
    }

    /**
     * Test getAudioStream function with a M3U8 file.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @throws AlltubeLibraryException
     * @dataProvider m3uUrlProvider
     */
    public function testGetAudioStreamM3uError(string $url, string $format)
    {
        $this->expectException(InvalidProtocolConversionException::class);
        $video = new Video($this->downloader, $url, $format);
        $this->downloader->getAudioStream($video);
    }

    /**
     * Test getAudioStream function with a DASH URL.
     *
     * @return void
     * @throws AlltubeLibraryException
     */
    public function testGetAudioStreamDashError()
    {
        $this->expectException(InvalidProtocolConversionException::class);
        $video = new Video($this->downloader, 'https://vimeo.com/251997032', 'bestaudio/best');
        $this->downloader->getAudioStream($video);
    }

    /**
     * Test getAudioStream function with a playlist.
     *
     * @return void
     * @throws AlltubeLibraryException
     */
    public function testGetAudioStreamPlaylistError()
    {
        $this->expectException(PlaylistConversionException::class);
        $video = new Video(
            $this->downloader,
            'https://www.youtube.com/playlist?list=PLgdySZU6KUXL_8Jq5aUkyNV7wCa-4wZsC',
            'best'
        );
        $this->downloader->getAudioStream($video);
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
        $this->assertIsResource($stream);
        $this->assertFalse(feof($stream));
    }

    /**
     * Test getM3uStream function.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @throws AlltubeLibraryException
     * @dataProvider m3uUrlProvider
     */
    public function testGetM3uStream(string $url, string $format)
    {
        $video = new Video($this->downloader, $url, $format);
        $this->assertStream($this->downloader->getM3uStream($video));
    }

    /**
     * Test getRemuxStream function.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @throws AlltubeLibraryException
     * @dataProvider remuxUrlProvider
     */
    public function testGetRemuxStream(string $url, string $format)
    {
        $video = new Video($this->downloader, $url, $format);
        $this->assertStream($this->downloader->getRemuxStream($video));
    }

    /**
     * Test getRemuxStream function with a video with only one URL.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @throws AlltubeLibraryException
     * @dataProvider urlProvider
     */
    public function testGetRemuxStreamWithWrongVideo(string $url, string $format)
    {
        $this->expectException(RemuxException::class);
        $video = new Video($this->downloader, $url, $format);
        $this->downloader->getRemuxStream($video);
    }

    /**
     * Test getRtmpStream function.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider rtmpUrlProvider
     */
    public function testGetRtmpStream(string $url, string $format)
    {
        $this->markTestIncomplete('We need to find another RTMP video.');
    }

    /**
     * Test getM3uStream function without ffmpeg.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @throws AlltubeLibraryException
     * @throws ConfigException
     * @dataProvider m3uUrlProvider
     */
    public function testGetM3uStreamFfmpegError(string $url, string $format)
    {
        $this->expectException(AvconvException::class);
        $config = new Config(['ffmpeg' => 'foobar']);
        $downloader = $config->getDownloader();

        $video = new Video($downloader, $url, $format);
        $downloader->getM3uStream($video);
    }

    /**
     * Test getConvertedStream function without ffmpeg.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @throws AlltubeLibraryException
     * @dataProvider urlProvider
     */
    public function testGetConvertedStream(string $url, string $format)
    {
        $video = new Video($this->downloader, $url, $format);
        $this->assertStream($this->downloader->getConvertedStream($video, 32, 'flv'));
    }

    /**
     * Test getConvertedStream function with a M3U8 file.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @throws AlltubeLibraryException
     * @dataProvider m3uUrlProvider
     */
    public function testGetConvertedStreamM3uError(string $url, string $format)
    {
        $this->expectException(InvalidProtocolConversionException::class);
        $video = new Video($this->downloader, $url, $format);
        $this->downloader->getConvertedStream($video, 32, 'flv');
    }
}
