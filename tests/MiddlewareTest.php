<?php

declare(strict_types=1);

namespace Ddrv\Tests\Slim\Session;

use Ddrv\Slim\Session\Handler;
use Ddrv\Slim\Session\Handler\ArrayHandler;
use Ddrv\Slim\Session\Middleware\SessionMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class MiddlewareTest extends TestCase
{

    public const COOKIE_NAME = 'phpunit_session';

    public function testSessionId()
    {
        $mw = $this->getMiddleware($this->getSessionHandler(), 5);
        $request = $this->getRequest();
        $handler = $this->getRequestHandler();
        $response = $mw->process($request, $handler);

        $visit = (int)$response->getHeaderLine('X-Visit-Number');
        $sessionId = $response->getHeaderLine('X-Session-Id');
        $sessionCookie = $response->getHeaderLine('Set-Cookie');

        $this->assertSame(1, $visit);
        $keyValue = self::COOKIE_NAME . '=' . $sessionId;
        $len = strlen($keyValue);
        $this->assertSame($keyValue, substr($sessionCookie, 0, $len));
    }

    public function testSessionRegenerate()
    {
        $storage = $this->getSessionHandler();
        $visits = 5;
        $mw = $this->getMiddleware($storage, $visits);
        $sessionId = $storage->generateId();
        $handler = $this->getRequestHandler();

        for ($i = 1; $i <= $visits; $i++) {
            $request = $this->getRequest($sessionId);
            $response = $mw->process($request, $handler);
            $visit = (int)$response->getHeaderLine('X-Visit-Number');
            if ($i === $visits) {
                $this->assertSame(0, $visit);
                $this->assertNotSame($sessionId, $response->getHeaderLine('X-Session-Id'));
            } else {
                $this->assertSame($i, $visit);
                $this->assertSame($sessionId, $response->getHeaderLine('X-Session-Id'));
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
        return new class implements RequestHandlerInterface {

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $session = $request->getAttribute('session');
                return (new ResponseFactory())
                    ->createResponse(200, 'OK')
                    ->withHeader('X-Visit-Number', $session->counter('visit'))
                    ->withHeader('X-Session-Id', $session->id())
                ;
            }
        };
    }

    private function getMiddleware(Handler $sessionHandler, int $regenerateVisits): SessionMiddleware
    {
        $mw = new SessionMiddleware($sessionHandler, 'session', 'visit', $regenerateVisits);
        $mw->setCookieParams(self::COOKIE_NAME);
        return $mw;
    }

    private function getSessionHandler(): Handler
    {
        return new ArrayHandler();
    }
}
