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
     * LocaleManager instance.
     *
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * LocaleMiddleware constructor.
     *
     * @param ContainerInterface $container Slim dependency container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->localeManager = $container->get('locale');
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
        foreach ($this->localeManager->getSupportedLocales() as $locale) {
            $parsedLocale = AcceptLanguage::parse($locale);
            if (isset($proposedLocale['language'])
                && $parsedLocale[1]['language'] == $proposedLocale['language']
                && $parsedLocale[1]['region'] == $proposedLocale['region']
            ) {
                return new Locale($proposedLocale['language'].'_'.$proposedLocale['region']);
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
        $curLocale = $this->localeManager->getLocale();
        if (!isset($curLocale)) {
            if (isset($headers[0])) {
                $this->localeManager->setLocale(
                    AcceptLanguage::detect([$this, 'testLocale'], new Locale('pt_BR'), $headers[0])
                );
            } else {
                $this->localeManager->setLocale(new Locale('pt_BR'));
            }
        }

        return $next($request, $response);
    }
}
