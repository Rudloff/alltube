<?php

/**
 * VideoTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\Exception\EmptyUrlException;
use Alltube\Exception\PasswordException;
use Alltube\Video;
use Exception;

/**
 * Unit tests for the Video class.
 * @requires download
 */
class VideoTest extends BaseTest
{
    /**
     * Test getExtractors function.
     *
     * @return void
     * @throws PasswordException
     */
    public function testGetExtractors()
    {
        $this->assertContains('youtube', Video::getExtractors());
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
     * @throws PasswordException
     * @throws EmptyUrlException
     * @dataProvider urlProvider
     * @dataProvider m3uUrlProvider
     * @dataProvider remuxUrlProvider
     */
    public function testgetUrl(
        $url,
        $format,
        /* @scrutinizer ignore-unused */ $filename,
        /* @scrutinizer ignore-unused */ $extension,
        $domain
    ) {
        $video = new Video($url, $format);
        foreach ($video->getUrl() as $videoURL) {
            $this->assertStringContainsString($domain, $videoURL);
        }
    }

    /**
     * Test getUrl function with a protected video.
     *
     * @return void
     * @throws EmptyUrlException
     * @throws PasswordException
     */
    public function testgetUrlWithPassword()
    {
        $video = new Video('http://vimeo.com/68375962', 'best', 'youtube-dl');
        foreach ($video->getUrl() as $videoURL) {
            $this->assertStringContainsString('vimeocdn.com', $videoURL);
        }
    }

    /**
     * Test getUrl function with a protected video and no password.
     *
     * @return void
     * @throws EmptyUrlException
     * @throws PasswordException
     */
    public function testgetUrlWithMissingPassword()
    {
        $this->expectException(Exception::class);
        $video = new Video('http://vimeo.com/68375962');
        $video->getUrl();
    }

    /**
     * Test getUrl function with a protected video and a wrong password.
     *
     * @return void
     * @throws EmptyUrlException
     * @throws PasswordException
     */
    public function testgetUrlWithWrongPassword()
    {
        $this->expectException(Exception::class);
        $video = new Video('http://vimeo.com/68375962', 'best', 'foo');
        $video->getUrl();
    }

    /**
     * Test getUrl function errors.
     *
     * @param string $url URL
     *
     * @return void
     * @throws EmptyUrlException
     * @throws PasswordException
     * @dataProvider      ErrorUrlProvider
     */
    public function testgetUrlError($url)
    {
        $this->expectException(Exception::class);
        $video = new Video($url);
        $video->getUrl();
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
                'https://www.youtube.com/watch?v=RJJ6FCAXvKg', 18,
                'Heart_Attack_-_Demi_Lovato_' .
                'Sam_Tsui_Against_The_Current-RJJ6FCAXvKg',
                'mp4',
                'googlevideo.com',
            ],
            [
                'http://www.bbc.co.uk/programmes/b039g8p7', 'bestaudio/best',
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
     * Provides M3U8 URLs for tests.
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
            ]
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
    public function errorUrlProvider()
    {
        return [
            ['http://example.com/video'],
        ];
    }

    /**
     * Test getJSON function.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider urlProvider
     * @dataProvider m3uUrlProvider
     * @throws PasswordException
     */
    public function testGetJson($url, $format)
    {
        $video = new Video($url, $format);
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
     * @dataProvider      ErrorURLProvider
     * @throws PasswordException
     */
    public function testGetJsonError($url)
    {
        $this->expectException(Exception::class);
        $video = new Video($url);
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
     * @dataProvider urlProvider
     * @dataProvider m3uUrlProvider
     * @dataProvider remuxUrlProvider
     * @throws PasswordException
     */
    public function testGetFilename($url, $format, $filename, $extension)
    {
        $video = new Video($url, $format);
        $this->assertEquals($video->getFilename(), $filename . '.' . $extension);
    }

    /**
     * Test getFilename function errors.
     *
     * @param string $url URL
     *
     * @return void
     * @dataProvider      ErrorUrlProvider
     * @throws PasswordException
     */
    public function testGetFilenameError($url)
    {
        $this->expectException(Exception::class);
        $video = new Video($url);
        $video->getFilename();
    }

    /**
     * Test getAudioStream function.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider urlProvider
     * @throws Exception
     */
    public function testGetAudioStream($url, $format)
    {
        $video = new Video($url, $format);
        $this->assertStream($video->getAudioStream());
    }

    /**
     * Test getAudioStream function without avconv.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider      urlProvider
     */
    public function testGetAudioStreamAvconvError($url, $format)
    {
        $this->expectException(Exception::class);
        Config::setOptions(['avconv' => 'foobar']);

        $video = new Video($url, $format);
        $video->getAudioStream();
    }

    /**
     * Test getAudioStream function with a M3U8 file.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider m3uUrlProvider
     */
    public function testGetAudioStreamM3uError($url, $format)
    {
        $this->expectException(Exception::class);
        $video = new Video($url, $format);
        $video->getAudioStream();
    }

    /**
     * Test getAudioStream function with a DASH URL.
     *
     * @return void
     */
    public function testGetAudioStreamDashError()
    {
        $this->expectException(Exception::class);
        $video = new Video('https://vimeo.com/251997032', 'bestaudio/best');
        $video->getAudioStream();
    }

    /**
     * Test getAudioStream function with a playlist.
     *
     * @return void
     */
    public function testGetAudioStreamPlaylistError()
    {
        $this->expectException(Exception::class);
        $video = new Video(
            'https://www.youtube.com/playlist?list=PLgdySZU6KUXL_8Jq5aUkyNV7wCa-4wZsC',
            'best'
        );
        $video->getAudioStream();
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
     * @dataProvider m3uUrlProvider
     * @throws Exception
     */
    public function testGetM3uStream($url, $format)
    {
        $video = new Video($url, $format);
        $this->assertStream($video->getM3uStream());
    }

    /**
     * Test getRemuxStream function.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider remuxUrlProvider
     * @throws Exception
     */
    public function testGetRemuxStream($url, $format)
    {
        $video = new Video($url, $format);
        $this->assertStream($video->getRemuxStream());
    }

    /**
     * Test getRemuxStream function with a video with only one URL.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider urlProvider
     */
    public function testGetRemuxStreamWithWrongVideo($url, $format)
    {
        $this->expectException(Exception::class);
        $video = new Video($url, $format);
        $video->getRemuxStream();
    }

    /**
     * Test getRtmpStream function.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider rtmpUrlProvider
     * @throws Exception
     */
    public function testGetRtmpStream($url, $format)
    {
        $this->markTestIncomplete('We need to find another RTMP video.');

        $video = new Video($url, $format);

        $this->assertStream($video->getRtmpStream());
    }

    /**
     * Test getM3uStream function without avconv.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider m3uUrlProvider
     */
    public function testGetM3uStreamAvconvError($url, $format)
    {
        $this->expectException(Exception::class);
        Config::setOptions(['avconv' => 'foobar']);

        $video = new Video($url, $format);
        $video->getM3uStream();
    }

    /**
     * Test getConvertedStream function without avconv.
     *
     * @param string $url URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider urlProvider
     * @throws Exception
     */
    public function testGetConvertedStream($url, $format)
    {
        $video = new Video($url, $format);
        $this->assertStream($video->getConvertedStream(32, 'flv'));
    }

    /**
     * Test getConvertedStream function with a M3U8 file.
     *
     * @param string $url    URL
     * @param string $format Format
     *
     * @return void
     * @dataProvider m3uUrlProvider
     */
    public function testGetConvertedStreamM3uError($url, $format)
    {
        $this->expectException(Exception::class);
        $video = new Video($url, $format);
        $video->getConvertedStream(32, 'flv');
    }
}
