<?php

declare(strict_types=1);

namespace Ddrv\Tests\Slim\Session;

use Ddrv\Slim\Session\Handler;
use Ddrv\Slim\Session\Handler\ArrayHandler;
use Ddrv\Slim\Session\Middleware\SessionMiddleware;
use Ddrv\Slim\Session\Tool\SessionExtractor;
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
        $handler = $this->getRequestHandler($extractor);
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
        $storage = $this->getSessionHandler();
        $visits = 10;
        $extractor = new SessionExtractor();
        $mw = $this->getMiddleware($storage, $extractor, new SessionRegeneration(self::VISIT_COUNTER, $visits));
        $sessionId = $storage->generateId();
        $handler = $this->getRequestHandler($extractor);

        for ($i = 1; $i <= $visits * 3; $i++) {
            $request = $this->getRequest($sessionId);
            $response = $mw->process($request, $handler);
            $visit = (int)$response->getHeaderLine('X-Visit-Number');
            $this->assertSame($i % $visits, $visit);
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

    private function getRequestHandler(SessionExtractor $sessionExtractor): RequestHandlerInterface
    {
        return new class ($sessionExtractor, self::VISIT_COUNTER) implements RequestHandlerInterface {

            /**
             * @var SessionExtractor
             */
            private $sessionExtractor;

            /**
             * @var string
             */
            private $counter;

            public function __construct(SessionExtractor $sessionExtractor, string $counter)
            {
                $this->sessionExtractor = $sessionExtractor;
                $this->counter = $counter;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $session = $this->sessionExtractor->getSession($request);
                return (new ResponseFactory())
                    ->createResponse(200, 'OK')
                    ->withHeader('X-Visit-Number', $session->counter($this->counter))
                ;
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
        return new ArrayHandler();
    }
}
