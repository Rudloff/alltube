<?php

/**
 * DownloadController class.
 */

namespace Alltube\Controller;

use Alltube\Config;
use Alltube\Library\Exception\EmptyUrlException;
use Alltube\Library\Exception\InvalidProtocolConversionException;
use Alltube\Library\Exception\PasswordException;
use Alltube\Library\Exception\AlltubeLibraryException;
use Alltube\Library\Exception\PlaylistConversionException;
use Alltube\Library\Exception\PopenStreamException;
use Alltube\Library\Exception\RemuxException;
use Alltube\Library\Exception\WrongPasswordException;
use Alltube\Library\Exception\YoutubedlException;
use Alltube\Stream\ConvertedPlaylistArchiveStream;
use Alltube\Stream\PlaylistArchiveStream;
use Alltube\Stream\YoutubeStream;
use Graby\HttpClient\Plugin\ServerSideRequestForgeryProtection\Exception\InvalidURLException;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use Slim\Http\Stream;

/**
 * Controller that returns a video or audio file.
 */
class DownloadController extends BaseController
{
    /**
     * Redirect to video file.
     *
     * @param Request $request PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     * @throws AlltubeLibraryException
     * @throws InvalidURLException
     */
    public function download(Request $request, Response $response): Response
    {
        $url = $this->getVideoPageUrl($request);

        $this->video = $this->downloader->getVideo($url, $this->getFormat($request), $this->getPassword($request));

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
            $frontController = new FrontController($this->container);

            return $frontController->password($request, $response);
        } catch (WrongPasswordException $e) {
            return $this->displayError($request, $response, $this->localeManager->t('Wrong password'));
        } catch (PlaylistConversionException $e) {
            return $this->displayError(
                $request,
                $response,
                $this->localeManager->t('Conversion of playlists is not supported.')
            );
        } catch (InvalidProtocolConversionException $e) {
            if (in_array($this->video->protocol, ['m3u8', 'm3u8_native'])) {
                return $this->displayError(
                    $request,
                    $response,
                    $this->localeManager->t('Conversion of M3U8 files is not supported.')
                );
            } elseif ($this->video->protocol == 'http_dash_segments') {
                return $this->displayError(
                    $request,
                    $response,
                    $this->localeManager->t('Conversion of DASH segments is not supported.')
                );
            } else {
                throw $e;
            }
        }
    }

    /**
     * Return a converted MP3 file.
     *
     * @param Request $request PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     * @throws AlltubeLibraryException
     */
    private function getConvertedAudioResponse(Request $request, Response $response): Response
    {
        $from = null;
        $to = null;
        if ($this->config->convertSeek) {
            $from = $request->getQueryParam('from');
            $to = $request->getQueryParam('to');
        }

        $response = $response->withHeader(
            'Content-Disposition',
            'attachment; filename="' .
            $this->video->getFileNameWithExtension('mp3') . '"'
        );
        $response = $response->withHeader('Content-Type', 'audio/mpeg');

        if ($request->isGet() || $request->isPost()) {
            $process = $this->downloader->getAudioStream($this->video, $this->config->audioBitrate, $from, $to);
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
     * @throws AlltubeLibraryException
     * @throws EmptyUrlException
     * @throws PasswordException
     * @throws WrongPasswordException
     */
    private function getAudioResponse(Request $request, Response $response): Response
    {
        if (!empty($request->getQueryParam('from')) || !empty($request->getQueryParam('to'))) {
            // Force convert when we need to seek.
            $this->video = $this->video->withFormat('bestaudio/' . $this->defaultFormat);

            return $this->getConvertedAudioResponse($request, $response);
        } else {
            try {
                // First, we try to get a MP3 file directly.
                if ($this->config->stream) {
                    $this->video = $this->video->withFormat('mp3');

                    return $this->getStream($request, $response);
                } else {
                    $this->video = $this->video->withFormat(Config::addHttpToFormat('mp3'));

                    $urls = $this->video->getUrl();

                    return $response->withRedirect($urls[0]);
                }
            } catch (YoutubedlException $e) {
                // If MP3 is not available, we convert it.
                $this->video = $this->video->withFormat('bestaudio/' . $this->defaultFormat);

                return $this->getConvertedAudioResponse($request, $response);
            }
        }
    }

    /**
     * Get a video/audio stream piped through the server.
     *
     * @param Request $request PSR-7 request
     *
     * @param Response $response PSR-7 response
     * @return Response HTTP response
     * @throws AlltubeLibraryException
     */
    private function getStream(Request $request, Response $response): Response
    {
        if (isset($this->video->entries)) {
            if ($this->config->convert && $request->getQueryParam('audio')) {
                $stream = new ConvertedPlaylistArchiveStream($this->downloader, $this->video);
            } else {
                $stream = new PlaylistArchiveStream($this->downloader, $this->video);
            }
            $response = $response->withHeader('Content-Type', 'application/zip');
            $response = $response->withHeader(
                'Content-Disposition',
                'attachment; filename="' . $this->video->title . '.zip"'
            );

            return $response->withBody($stream);
        } elseif ($this->video->protocol == 'rtmp') {
            $response = $response->withHeader('Content-Type', 'video/' . $this->video->ext);
            $body = new Stream($this->downloader->getRtmpStream($this->video));
        } elseif ($this->video->protocol == 'm3u8' || $this->video->protocol == 'm3u8_native') {
            $response = $response->withHeader('Content-Type', 'video/' . $this->video->ext);
            $body = new Stream($this->downloader->getM3uStream($this->video));
        } else {
            $headers = [];
            $range = $request->getHeader('Range');

            if (!empty($range)) {
                $headers['Range'] = $range;
            }
            $stream = $this->downloader->getHttpResponse($this->video, $headers);

            $response = $response->withHeader('Content-Type', $stream->getHeader('Content-Type'));
            $response = $response->withHeader('Content-Length', $stream->getHeader('Content-Length'));
            $response = $response->withHeader('Accept-Ranges', $stream->getHeader('Accept-Ranges'));
            $response = $response->withHeader('Content-Range', $stream->getHeader('Content-Range'));
            if ($stream->getStatusCode() == StatusCode::HTTP_PARTIAL_CONTENT) {
                $response = $response->withStatus(StatusCode::HTTP_PARTIAL_CONTENT);
            }

            if (isset($this->video->downloader_options->http_chunk_size)) {
                // Workaround for Youtube throttling the download speed.
                $body = new YoutubeStream($this->downloader, $this->video);
            } else {
                $body = $stream->getBody();
            }
        }
        if ($request->isGet()) {
            $response = $response->withBody($body);
        }

        return $response->withHeader(
            'Content-Disposition',
            'attachment; filename="' .
            $this->video->getFilename() . '"'
        );
    }

    /**
     * Get a remuxed stream piped through the server.
     *
     * @param Request $request PSR-7 request
     *
     * @param Response $response PSR-7 response
     * @return Response HTTP response
     * @throws AlltubeLibraryException
     */
    private function getRemuxStream(Request $request, Response $response): Response
    {
        if (!$this->config->remux) {
            throw new RemuxException('You need to enable remux mode to merge two formats.');
        }
        $stream = $this->downloader->getRemuxStream($this->video);
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
     * @throws AlltubeLibraryException
     */
    private function getDownloadResponse(Request $request, Response $response): Response
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
                throw new EmptyUrlException("Can't find URL of video.");
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
     * @throws AlltubeLibraryException
     * @throws InvalidProtocolConversionException
     * @throws PasswordException
     * @throws PlaylistConversionException
     * @throws WrongPasswordException
     * @throws YoutubedlException
     * @throws PopenStreamException
     */
    private function getConvertedResponse(Request $request, Response $response): Response
    {
        $response = $response->withHeader(
            'Content-Disposition',
            'attachment; filename="' .
            $this->video->getFileNameWithExtension($request->getQueryParam('customFormat')) . '"'
        );
        $response = $response->withHeader('Content-Type', 'video/' . $request->getQueryParam('customFormat'));

        if ($request->isGet() || $request->isPost()) {
            $process = $this->downloader->getConvertedStream(
                $this->video,
                $request->getQueryParam('customBitrate'),
                $request->getQueryParam('customFormat')
            );
            $response = $response->withBody(new Stream($process));
        }

        return $response;
    }
}
