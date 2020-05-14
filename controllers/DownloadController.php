<?php

/**
 * DownloadController class.
 */

namespace Alltube\Controller;

use Alltube\Exception\EmptyUrlException;
use Alltube\Exception\PasswordException;
use Alltube\Stream\ConvertedPlaylistArchiveStream;
use Alltube\Stream\PlaylistArchiveStream;
use Alltube\Stream\YoutubeStream;
use Alltube\Video;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;

/**
 * Controller that returns a video or audio file.
 */
class DownloadController extends BaseController
{
    /**
     * Redirect to video file.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    public function download(Request $request, Response $response)
    {
        $url = $request->getQueryParam('url');

        if (isset($url)) {
            $this->video = new Video($url, $this->getFormat($request), $this->getPassword($request));

            try {
                if ($this->config->convert && $request->getQueryParam('audio')) {
                    // Audio convert.
                    return $this->getAudioResponse($request, $response);
                } elseif ($this->config->convertAdvanced && !is_null($request->getQueryParam('customConvert'))) {
                    // Advance convert.
                    return $this->getConvertedResponse($request, $response);
                }

                // Regular download.
                return $this->getDownloadResponse($request, $response);
            } catch (PasswordException $e) {
                return $response->withRedirect(
                    $this->container->get('router')->pathFor('info') .
                        '?' . http_build_query($request->getQueryParams())
                );
            } catch (Exception $e) {
                $response->getBody()->write($e->getMessage());

                return $response->withHeader('Content-Type', 'text/plain')->withStatus(500);
            }
        } else {
            return $response->withRedirect($this->container->get('router')->pathFor('index'));
        }
    }

    /**
     * Return a converted MP3 file.
     *
     * @param Request $request PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     * @throws PasswordException
     * @throws Exception
     */
    private function getConvertedAudioResponse(Request $request, Response $response)
    {
        $from = $request->getQueryParam('from');
        $to = $request->getQueryParam('to');

        $response = $response->withHeader(
            'Content-Disposition',
            'attachment; filename="' .
            $this->video->getFileNameWithExtension('mp3') . '"'
        );
        $response = $response->withHeader('Content-Type', 'audio/mpeg');

        if ($request->isGet() || $request->isPost()) {
            try {
                $process = $this->video->getAudioStream($from, $to);
            } catch (Exception $e) {
                // Fallback to default format.
                $this->video = $this->video->withFormat($this->defaultFormat);
                $process = $this->video->getAudioStream($from, $to);
            }
            $response = $response->withBody(new Stream($process));
        }

        return $response;
    }

    /**
     * Return the MP3 file.
     *
     * @param Request $request PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     * @throws PasswordException
     */
    private function getAudioResponse(Request $request, Response $response)
    {
        try {
            // First, we try to get a MP3 file directly.
            if (!empty($request->getQueryParam('from')) || !empty($request->getQueryParam('to'))) {
                throw new Exception('Force convert when we need to seek.');
            }

            if ($this->config->stream) {
                $this->video = $this->video->withFormat('mp3');

                return $this->getStream($request, $response);
            } else {
                $this->video = $this->video->withFormat('mp3[protocol=https]/mp3[protocol=http]');

                $urls = $this->video->getUrl();

                return $response->withRedirect($urls[0]);
            }
        } catch (PasswordException $e) {
            $frontController = new FrontController($this->container);

            return $frontController->password($request, $response);
        } catch (Exception $e) {
            // If MP3 is not available, we convert it.
            $this->video = $this->video->withFormat('bestaudio/best');

            return $this->getConvertedAudioResponse($request, $response);
        }
    }

    /**
     * Get a video/audio stream piped through the server.
     *
     * @param Request $request PSR-7 request
     *
     * @param Response $response PSR-7 response
     * @return Response HTTP response
     * @throws EmptyUrlException
     * @throws PasswordException
     * @throws Exception
     */
    private function getStream(Request $request, Response $response)
    {
        if (isset($this->video->entries)) {
            if ($this->config->convert && $request->getQueryParam('audio')) {
                $stream = new ConvertedPlaylistArchiveStream($this->video);
            } else {
                $stream = new PlaylistArchiveStream($this->video);
            }
            $response = $response->withHeader('Content-Type', 'application/zip');
            $response = $response->withHeader(
                'Content-Disposition',
                'attachment; filename="' . $this->video->title . '.zip"'
            );

            return $response->withBody($stream);
        } elseif ($this->video->protocol == 'rtmp') {
            $response = $response->withHeader('Content-Type', 'video/' . $this->video->ext);
            $body = new Stream($this->video->getRtmpStream());
        } elseif ($this->video->protocol == 'm3u8' || $this->video->protocol == 'm3u8_native') {
            $response = $response->withHeader('Content-Type', 'video/' . $this->video->ext);
            $body = new Stream($this->video->getM3uStream());
        } else {
            $headers = [];
            $range = $request->getHeader('Range');

            if (!empty($range)) {
                $headers['Range'] = $range;
            }
            $stream = $this->video->getHttpResponse($headers);

            $response = $response->withHeader('Content-Type', $stream->getHeader('Content-Type'));
            $response = $response->withHeader('Content-Length', $stream->getHeader('Content-Length'));
            $response = $response->withHeader('Accept-Ranges', $stream->getHeader('Accept-Ranges'));
            $response = $response->withHeader('Content-Range', $stream->getHeader('Content-Range'));
            if ($stream->getStatusCode() == 206) {
                $response = $response->withStatus(206);
            }

            if (isset($this->video->downloader_options->http_chunk_size)) {
                // Workaround for Youtube throttling the download speed.
                $body = new YoutubeStream($this->video);
            } else {
                $body = $stream->getBody();
            }
        }
        if ($request->isGet()) {
            $response = $response->withBody($body);
        }
        $response = $response->withHeader(
            'Content-Disposition',
            'attachment; filename="' .
                $this->video->getFilename() . '"'
        );

        return $response;
    }

    /**
     * Get a remuxed stream piped through the server.
     *
     * @param Response $response PSR-7 response
     * @param Request $request PSR-7 request
     *
     * @return Response HTTP response
     * @throws PasswordException
     * @throws Exception
     */
    private function getRemuxStream(Request $request, Response $response)
    {
        if (!$this->config->remux) {
            throw new Exception($this->localeManager->t('You need to enable remux mode to merge two formats.'));
        }
        $stream = $this->video->getRemuxStream();
        $response = $response->withHeader('Content-Type', 'video/x-matroska');
        if ($request->isGet()) {
            $response = $response->withBody(new Stream($stream));
        }

        return $response->withHeader(
            'Content-Disposition',
            'attachment; filename="' . $this->video->getFileNameWithExtension('mkv') . '"'
        );
    }

    /**
     * Get approriate HTTP response to download query.
     * Depends on whether we want to stream, remux or simply redirect.
     *
     * @param Request $request PSR-7 request
     *
     * @param Response $response PSR-7 response
     * @return Response HTTP response
     * @throws EmptyUrlException
     * @throws PasswordException
     * @throws Exception
     */
    private function getDownloadResponse(Request $request, Response $response)
    {
        try {
            $videoUrls = $this->video->getUrl();
        } catch (EmptyUrlException $e) {
            /*
            If this happens it is probably a playlist
            so it will either be handled by getStream() or throw an exception anyway.
             */
            $videoUrls = [];
        }
        if (count($videoUrls) > 1) {
            return $this->getRemuxStream($request, $response);
        } elseif ($this->config->stream && (isset($this->video->entries) || $request->getQueryParam('stream'))) {
            return $this->getStream($request, $response);
        } else {
            if (empty($videoUrls[0])) {
                throw new Exception($this->localeManager->t("Can't find URL of video."));
            }

            return $response->withRedirect($videoUrls[0]);
        }
    }

    /**
     * Return a converted video file.
     *
     * @param Request $request PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     * @throws PasswordException
     * @throws Exception
     */
    private function getConvertedResponse(Request $request, Response $response)
    {
        $response = $response->withHeader(
            'Content-Disposition',
            'attachment; filename="' .
            $this->video->getFileNameWithExtension($request->getQueryParam('customFormat')) . '"'
        );
        $response = $response->withHeader('Content-Type', 'video/' . $request->getQueryParam('customFormat'));

        if ($request->isGet() || $request->isPost()) {
            $process = $this->video->getConvertedStream(
                $request->getQueryParam('customBitrate'),
                $request->getQueryParam('customFormat')
            );
            $response = $response->withBody(new Stream($process));
        }

        return $response;
    }
}
