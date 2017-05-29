<?php
/**
 * LocaleMiddleware class.
 */
namespace Alltube;

use Slim\Http\Request;
use Slim\Http\Response;
use Teto\HTTP\AcceptLanguage;

/**
 * Detect user locale.
 */
class LocaleMiddleware
{
    /**
     * Supported locales.
     *
     * @var array
     */
    private $locales = ['fr_FR', 'zh_CN'];

    /**
     * Test if a locale can be used for the current user.
     *
     * @param array $proposedLocale Locale array created by AcceptLanguage::parse()
     *
     * @return string Locale name if chosen, nothing otherwise
     */
    public function testLocale(array $proposedLocale)
    {
        foreach ($this->locales as $locale) {
            $parsedLocale = AcceptLanguage::parse($locale);
            if (isset($proposedLocale['language'])
                && $parsedLocale[1]['language'] == $proposedLocale['language']
                && $parsedLocale[1]['region'] == $proposedLocale['region']
            ) {
                return $proposedLocale['language'].'_'.$proposedLocale['region'];
            }
        }
    }

    /**
     * Main middleware function
     *
     * @param Request  $request  PSR request
     * @param Response $response PSR response
     * @param callable $next     Next middleware
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $headers = $request->getHeader('Accept-Language');
        $locale = AcceptLanguage::detect([$this, 'testLocale'], 'en_US', $headers[0]);
        putenv('LANG='.$locale);
        setlocale(LC_ALL, [$locale, $locale.'.utf8']);

        return $next($request, $response);
    }
}
