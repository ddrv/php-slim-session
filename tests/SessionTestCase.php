<?php

declare(strict_types=1);

namespace Ddrv\Tests\Slim\Session;

use Ddrv\Slim\Session\Handler;
use Ddrv\Slim\Session\Storage;
use PHPUnit\Framework\TestCase;

abstract class SessionTestCase extends TestCase
{

    /**
     * @covers \Ddrv\Slim\Session\Session::increment()
     * @covers \Ddrv\Slim\Session\Session::decrement()
     * @covers \Ddrv\Slim\Session\Session::reset()
     * @covers \Ddrv\Slim\Session\Storage::generateId()
     * @covers \Ddrv\Slim\Session\Storage::read()
     * @covers \Ddrv\Slim\Session\Storage::write()
     */
    public function testCounter()
    {
        $handler = $this->getSessionHandler();
        $id = $handler->generateId();
        for ($i = 0; $i < 10; $i++) {
            $session = $handler->read($id);
            $this->assertSame($i, $session->counter('phpunit'));
            $session->increment('phpunit');
            $handler->write($id, $session);
        }
        for ($j = $i; $j >= 5; $j--) {
            $session = $handler->read($id);
            $this->assertSame($j, $session->counter('phpunit'));
            $session->decrement('phpunit');
            $handler->write($id, $session);
        }
        $session = $handler->read($id);
        $session->reset('phpunit');
        $this->assertSame(0, $session->counter('phpunit'));
    }

    /**
     * @covers \Ddrv\Slim\Session\Session::flash()
     * @covers \Ddrv\Slim\Session\Session::has()
     * @covers \Ddrv\Slim\Session\Session::get()
     * @covers \Ddrv\Slim\Session\Storage::generateId()
     * @covers \Ddrv\Slim\Session\Storage::read()
     * @covers \Ddrv\Slim\Session\Storage::write()
     */
    public function testFlash()
    {
        $handler = $this->getSessionHandler();
        $id = $handler->generateId();

        $session = $handler->read($id);
        $this->assertFalse($session->has('phpunit'));
        $session->flash('phpunit', 'flash-message');
        $this->assertSame('flash-message', $session->get('phpunit'));
        $handler->write($id, $session);

        $session = $handler->read($id);
        $this->assertTrue($session->has('phpunit'));
        $this->assertSame('flash-message', $session->get('phpunit'));
        $handler->write($id, $session);

        $session = $handler->read($id);
        $this->assertFalse($session->has('phpunit'));
        $handler->write($id, $session);
    }

    /**
     * @covers \Ddrv\Slim\Session\Session::flash()
     * @covers \Ddrv\Slim\Session\Session::has()
     * @covers \Ddrv\Slim\Session\Session::get()
     * @covers \Ddrv\Slim\Session\Storage::generateId()
     * @covers \Ddrv\Slim\Session\Storage::read()
     * @covers \Ddrv\Slim\Session\Storage::write()
     */
    public function testValues()
    {
        $handler = $this->getSessionHandler();
        $id = $handler->generateId();

        $session = $handler->read($id);
        $this->assertFalse($session->has('phpunit'));
        $this->assertNull($session->get('phpunit'));
        $session->set('phpunit', 'value');
        $this->assertSame('value', $session->get('phpunit'));
        $handler->write($id, $session);

        $session = $handler->read($id);
        $this->assertTrue($session->has('phpunit'));
        $this->assertSame('value', $session->get('phpunit'));
        $handler->write($id, $session);

        $session = $handler->read($id);
        $this->assertTrue($session->has('phpunit'));
        $session->set('phpunit', null);
        $this->assertNull($session->get('phpunit', 'default'));
        $handler->write($id, $session);

        $session = $handler->read($id);
        $this->assertTrue($session->has('phpunit'));
        $session->remove('phpunit');
        $handler->write($id, $session);

        $session = $handler->read($id);
        $this->assertFalse($session->has('phpunit'));
        $handler->write($id, $session);
    }

    final protected function getSessionHandler(): Handler
    {
        return new Handler($this->getSessionStorage());
    }

    abstract protected function getSessionStorage(): Storage;
}
