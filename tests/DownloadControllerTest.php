<?php

/**
 * DownloadControllerTest class.
 */

namespace Alltube\Test;

use Alltube\Controller\DownloadController;
use Alltube\Exception\ConfigException;
use Alltube\Exception\DependencyException;
use Alltube\Library\Exception\EmptyUrlException;
use Alltube\Library\Exception\RemuxException;
use Alltube\Library\Exception\YoutubedlException;
use SmartyException;

/**
 * Unit tests for the FrontController class.
 * @requires download
 */
class DownloadControllerTest extends ControllerTest
{
    /**
     * Prepare tests.
     * @throws ConfigException|SmartyException|DependencyException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new DownloadController($this->container);
    }

    /**
     * Test the download() function without the URL parameter.
     *
     * @return void
     */
    public function testDownloadWithoutUrl()
    {
        $this->assertRequestIsRedirect('download');
    }

    /**
     * Test the download() function.
     *
     * @return void
     */
    public function testDownload()
    {
        $this->assertRequestIsRedirect('download', ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU']);
    }

    /**
     * Test the download() function with a specific format.
     *
     * @return void
     */
    public function testDownloadWithFormat()
    {
        $this->assertRequestIsRedirect(
            'download',
            ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'format' => 'worst']
        );
    }

    /**
     * Test the download() function with streams enabled.
     *
     * @return void
     */
    public function testDownloadWithStream()
    {
        $config = $this->container->get('config');
        $config->setOptions(['stream' => true]);

        $this->assertRequestIsOk(
            'download',
            ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'stream' => true]
        );
    }

    /**
     * Test the download() function with an M3U stream.
     *
     * @return void
     */
    public function testDownloadWithM3uStream()
    {
        $config = $this->container->get('config');
        $config->setOptions(['stream' => true]);

        $this->assertRequestIsOk(
            'download',
            [
                'url'    => 'https://twitter.com/verge/status/813055465324056576/video/1',
                'format' => 'hls-2176',
                'stream' => true,
            ]
        );
    }

    /**
     * Test the download() function with an RTMP stream.
     *
     * @return void
     */
    public function testDownloadWithRtmpStream()
    {
        $this->markTestIncomplete('We need to find another RTMP video.');
    }

    /**
     * Test the download() function with a remuxed video.
     *
     * @return void
     */
    public function testDownloadWithRemux()
    {
        $config = $this->container->get('config');
        $config->setOptions(['remux' => true]);

        $this->assertRequestIsOk(
            'download',
            [
                'url'    => 'https://www.youtube.com/watch?v=M7IpKCZ47pU',
                'format' => 'bestvideo+bestaudio',
            ]
        );
    }

    /**
     * Test the download() function with a remuxed video but remux disabled.
     *
     * @return void
     */
    public function testDownloadWithRemuxDisabled()
    {
        $this->expectException(RemuxException::class);
        $this->getRequestResult(
            'download',
            [
                'url'    => 'https://www.youtube.com/watch?v=M7IpKCZ47pU',
                'format' => 'bestvideo+bestaudio',
            ]
        );
    }

    /**
     * Test the download() function with a missing password.
     *
     * @return void
     */
    public function testDownloadWithMissingPassword()
    {
        $this->assertRequestIsClientError('download', ['url' => 'https://vimeo.com/68375962']);
    }

    /**
     * Test the download() function with an error.
     *
     * @return void
     */
    public function testDownloadWithError()
    {
        $this->expectException(YoutubedlException::class);
        $this->getRequestResult('download', ['url' => 'https://example.com/foo']);
    }

    /**
     * Test the download() function with an video that returns an empty URL.
     * This can be caused by trying to redirect to a playlist.
     *
     * @return void
     */
    public function testDownloadWithEmptyUrl()
    {
        $this->expectException(EmptyUrlException::class);
        $this->getRequestResult(
            'download',
            ['url' => 'https://www.youtube.com/playlist?list=PLgdySZU6KUXL_8Jq5aUkyNV7wCa-4wZsC']
        );
    }

    /**
     * Test the download() function with a playlist stream.
     *
     * @return void
     * @requires OS Linux
     */
    public function testDownloadWithPlaylist()
    {
        $config = $this->container->get('config');
        $config->setOptions(['stream' => true]);

        $this->assertRequestIsOk(
            'download',
            ['url' => 'https://www.youtube.com/playlist?list=PLgdySZU6KUXL_8Jq5aUkyNV7wCa-4wZsC']
        );
    }

    /**
     * Test the download() function with an advanced conversion.
     *
     * @return void
     */
    public function testDownloadWithAdvancedConversion()
    {
        $config = $this->container->get('config');
        $config->setOptions(['convertAdvanced' => true]);

        $this->assertRequestIsOk(
            'download',
            [
                'url'           => 'https://www.youtube.com/watch?v=M7IpKCZ47pU',
                'format'        => 'best',
                'customConvert' => 'on',
                'customBitrate' => 32,
                'customFormat'  => 'flv',
            ]
        );
    }
}
