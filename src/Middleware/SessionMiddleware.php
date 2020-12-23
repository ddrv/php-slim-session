<?php

namespace Ddrv\Slim\Session\Middleware;

use DateTime;
use DateTimeZone;
use Ddrv\Slim\Session\Handler;
use Ddrv\Slim\Session\Tool\CookieOptions;
use Ddrv\Slim\Session\Tool\CookieOptionsDetector;
use Ddrv\Slim\Session\Tool\SessionExtractor;
use Ddrv\Slim\Session\Tool\SessionRegeneration;
use Ddrv\Slim\Session\Tool\SimpleCookieOptionsDetector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{

    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var CookieOptionsDetector
     */
    private $detector;

    /**
     * @var SessionExtractor
     */
    private $extractor;

    /**
     * @var SessionRegeneration|null
     */
    private $regeneration;

    /**
     * @var DateTimeZone
     */
    private $gmt;

    public function __construct(
        Handler $handler,
        ?CookieOptionsDetector $cookieOptionsDetector = null,
        ?SessionExtractor $sessionExtractor = null,
        ?SessionRegeneration $sessionRegeneration = null
    ) {
        $this->handler = $handler;
        $this->detector = $cookieOptionsDetector ?? new SimpleCookieOptionsDetector();
        $this->extractor = $sessionExtractor ?? new SessionExtractor();
        $this->regeneration = $sessionRegeneration;
        $this->gmt = new DateTimeZone('GMT');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cookies = $request->getCookieParams();
        $options = $this->detector->getCookieOptions($request->getUri());
        $sessionId = array_key_exists($options->getName(), $cookies) ? $cookies[$options->getName()] : null;
        if (!$sessionId) {
            $sessionId = $this->handler->generateId();
        }
        $session = $this->handler->read($sessionId);
        if ($this->regeneration) {
            $this->regeneration->visit($session);
        }
        $request = $request->withAttribute($this->extractor->getAttributeName(), $session);
        $response = $handler->handle($request);

        if ($session->isNeedRegenerate()) {
            $this->handler->destroy($sessionId);
            $sessionId = $this->handler->generateId();
        }

        $cookie = $this->createCookie($options, $sessionId);
        $this->handler->write($sessionId, $session);
        return $response->withAddedHeader('Set-Cookie', $cookie);
    }

    private function createCookie(CookieOptions $options, string $sessionId): string
    {
        $cookie = $options->getName() . '=' . $sessionId;
        $lifetime = $options->getLifetime();
        if ($lifetime > 0) {
            $expires = DateTime::createFromFormat('U', (string)(time() + $lifetime))->setTimezone($this->gmt);
            $cookie .= '; Expires=' . $expires->format(DateTime::RFC7231);
        }
        $cookie .= '; Domain=' . $options->getDomain();
        $cookie .= '; Path=' . $options->getLifetime();
        $cookie .= '; SameSite=' . $options->getSameSite();
        if ($options->isSecure()) {
            $cookie .= '; Secure';
        }
        if ($options->isHttpOnly()) {
            $cookie .= '; HttpOnly';
        }
        return $cookie;
    }
}
