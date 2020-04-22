<?php

namespace Ddrv\Slim\Session\Middleware;

use DateTime;
use DateTimeZone;
use Ddrv\Slim\Session\Handler;
use Ddrv\Slim\Session\Session;
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
     * @var string
     */
    private $cookieName;

    /**
     * @var string
     */
    private $cookieDomain;

    /**
     * @var string
     */
    private $cookiePath;

    /**
     * @var string
     */
    private $cookieSameSite;

    /**
     * @var int
     */
    private $cookieTTL;

    /**
     * @var bool
     */
    private $cookieSecure;

    /**
     * @var bool
     */
    private $cookieHttpOnly;

    /**
     * @var string
     */
    private $attributeName;

    /**
     * @var int
     */
    private $regenerateVisits;

    /**
     * @var string
     */
    private $counterKey;

    public function __construct(
        Handler $handler,
        string $attributeName = 'session',
        string $counterKey = 'visit',
        int $regenerateVisits = 0
    ) {
        $this->handler = $handler;
        $this->attributeName = $attributeName;
        $this->regenerateVisits = $regenerateVisits;
        $this->counterKey = $counterKey;
        $this->setCookieParams('sid');
    }

    public function setCookieParams(
        string $name,
        ?string $domain = null,
        string $path = '/',
        ?string $sameSite = null,
        int $TTL = 86400,
        bool $secure = false,
        bool $httpOnly = false
    ) {
        $this->cookieName = $name;
        $this->cookiePath = $path;
        $this->cookieDomain = $domain;
        if (!is_null($sameSite)) {
            $sameSite = ucfirst(strtolower($sameSite));
            if (!in_array($sameSite, ['Lax', 'Strict'])) {
                $sameSite = 'Strict';
            }
        }
        $this->cookieSameSite = $sameSite;
        $this->cookieSecure = $secure;
        $this->cookieHttpOnly = $httpOnly;
        $this->cookieTTL = $TTL;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cookies = $request->getCookieParams();
        $sessionId = array_key_exists($this->cookieName, $cookies) ? $cookies[$this->cookieName] : null;
        $session = new Session($this->handler);
        $session->start($sessionId);

        if ($this->regenerateVisits > 0) {
            $visit = $session->increment($this->counterKey);
            if ($visit >= $this->regenerateVisits) {
                $session->regenerate();
                $session->reset($this->counterKey);
            }
        }
        $response = $handler->handle($request->withAttribute($this->attributeName, $session));
        $sessionCookie = $this->getCookie($session->id());
        $session->write();
        return $response->withAddedHeader('Set-Cookie', $sessionCookie);
    }

    private function getCookie(string $sessionId): string
    {
        $cookie = $this->cookieName . '=' . $sessionId;
        if ($this->cookieTTL > 0) {
            $expires = (DateTime::createFromFormat('U', (string)(time() + $this->cookieTTL)))
                ->setTimezone(new DateTimeZone('GMT'))
            ;
            $cookie .= '; Expires=' . $expires->format(DateTime::RFC7231);
        }
        $cookie .= '; Path=' . $this->cookiePath;
        if ($this->cookieDomain) {
            $cookie .= '; Domain=' . $this->cookieDomain;
        }
        if ($this->cookieSameSite) {
            $cookie .= '; SameSite=' . $this->cookieSameSite;
        }
        if ($this->cookieSecure) {
            $cookie .= '; Secure';
        }
        if ($this->cookieHttpOnly) {
            $cookie .= '; HttpOnly';
        }
        return $cookie;
    }
}
