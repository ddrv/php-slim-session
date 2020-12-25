<?php

declare(strict_types=1);

namespace Ddrv\Tests\Slim\Session;

use Ddrv\Slim\Session\Handler;
use Ddrv\Slim\Session\Storage\ArrayStorage;
use Ddrv\Slim\Session\Middleware\SessionMiddleware;
use Ddrv\Slim\Session\Tool\SessionExtractor;
use Ddrv\Slim\Session\Tool\SessionNVisitRegeneration;
use Ddrv\Slim\Session\Tool\SessionRegeneration;
use Ddrv\Slim\Session\Tool\SimpleCookieOptionsDetector;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class MiddlewareTest extends TestCase
{

    public const COOKIE_NAME = 'phpunit_session';
    public const VISIT_COUNTER = 'visit';

    /**
     * @covers \Ddrv\Slim\Session\Middleware\SessionMiddleware::getCookie()
     * @covers \Ddrv\Slim\Session\Middleware\SessionMiddleware::setCookieParams()
     * @covers \Ddrv\Slim\Session\Middleware\SessionMiddleware::process()
     */
    public function testSessionId()
    {
        $extractor = new SessionExtractor();
        $mw = $this->getMiddleware($this->getSessionHandler(), $extractor);
        $request = $this->getRequest();
        $handler = $this->getRequestHandler();
        $response = $mw->process($request, $handler);
        $sessionCookie = $response->getHeaderLine('Set-Cookie');
        $keyValue = self::COOKIE_NAME . '=';
        $len = strlen($keyValue);
        $this->assertSame($keyValue, substr($sessionCookie, 0, $len));
    }

    /**
     * @covers \Ddrv\Slim\Session\Middleware\SessionMiddleware::getCookie()
     * @covers \Ddrv\Slim\Session\Middleware\SessionMiddleware::setCookieParams()
     * @covers \Ddrv\Slim\Session\Middleware\SessionMiddleware::process()
     * @covers \Ddrv\Slim\Session\Tool\SessionRegeneration::visit()
     */
    public function testSessionRegenerate()
    {
        $sessionHandler = $this->getSessionHandler();
        $visits = 5;
        $extractor = new SessionExtractor();
        $regeneration = new SessionNVisitRegeneration(self::VISIT_COUNTER, $visits);
        $mw = $this->getMiddleware($sessionHandler, $extractor, $regeneration);
        $sessionId = $sessionHandler->generateId();
        $server = $this->getRequestHandler();

        for ($i = 1; $i <= $visits * 3; $i++) {
            $request = $this->getRequest($sessionId);
            $response = $mw->process($request, $server);
            $cookie = $response->getHeaderLine('Set-Cookie');
            $expected = trim(explode(';', $cookie)[0]);
            $actual = self::COOKIE_NAME . '=' . $sessionId;
            if ($i % $visits === 0) {
                $this->assertNotSame($expected, $actual);
                $sessionId = explode('=', $expected . '=')[1];
            } else {
                $this->assertSame($expected, $actual);
                $this->assertTrue(strpos($cookie, $actual) === 0);
            }
        }
    }

    private function getRequest(?string $sessionId = null): ServerRequestInterface
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', 'http://example.com/', []);
        if ($sessionId) {
            $request = $request->withCookieParams([self::COOKIE_NAME => $sessionId]);
        }
        return $request;
    }

    private function getRequestHandler(): RequestHandlerInterface
    {
        return new class () implements RequestHandlerInterface {

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return (new ResponseFactory())->createResponse(200, 'OK');
            }
        };
    }

    private function getMiddleware(
        Handler $sessionHandler,
        SessionExtractor $sessionExtractor,
        ?SessionRegeneration $sessionRegeneration = null
    ): SessionMiddleware {
        $cookieOptionsDetector = new SimpleCookieOptionsDetector(self::COOKIE_NAME);
        return new SessionMiddleware($sessionHandler, $cookieOptionsDetector, $sessionExtractor, $sessionRegeneration);
    }

    private function getSessionHandler(): Handler
    {
        return new Handler(new ArrayStorage());
    }
}
