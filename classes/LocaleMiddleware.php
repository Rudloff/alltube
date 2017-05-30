<?php
/**
 * LocaleMiddleware class.
 */

namespace Alltube;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Teto\HTTP\AcceptLanguage;

/**
 * Detect user locale.
 */
class LocaleMiddleware
{
    /**
     * LocaleMiddleware constructor.
     *
     * @param ContainerInterface $container Slim dependency container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->locale = $container->get('locale');
    }

    /**
     * Test if a locale can be used for the current user.
     *
     * @param array $proposedLocale Locale array created by AcceptLanguage::parse()
     *
     * @return string Locale name if chosen, nothing otherwise
     */
    public function testLocale(array $proposedLocale)
    {
        foreach ($this->locale->getSupportedLocales() as $locale => $name) {
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
     * Main middleware function.
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
        $curLocale = $this->locale->getLocale();
        if (!isset($curLocale)) {
            $this->locale->setLocale(
                AcceptLanguage::detect([$this, 'testLocale'], 'en_US', $headers[0])
            );
        }

        return $next($request, $response);
    }
}
