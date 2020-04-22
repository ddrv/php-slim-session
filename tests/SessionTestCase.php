<?php

declare(strict_types=1);

namespace Ddrv\Tests\Slim\Session;

use Ddrv\Slim\Session\Handler;
use Ddrv\Slim\Session\Session;
use PHPUnit\Framework\TestCase;

abstract class SessionTestCase extends TestCase
{

    public function testCounter()
    {
        $session = $this->getSession();
        $session->start();
        $id = $session->id();
        $session->write();
        for ($i = 0; $i < 10; $i++) {
            $session->start($id);
            $this->assertSame($i, $session->counter('phpunit'));
            $session->increment('phpunit');
            $session->write();
        }
        for ($j = $i; $j >= 5; $j--) {
            $session->start($id);
            $this->assertSame($j, $session->counter('phpunit'));
            $session->decrement('phpunit');
            $session->write();
        }
        $session->reset('phpunit');
        $this->assertSame(0, $session->counter('phpunit'));
    }

    public function testFlash()
    {
        $session = $this->getSession();
        $session->start();
        $id = $session->id();
        $session->write();

        $session->start($id);
        $this->assertFalse($session->has('phpunit'));

        $session->flash('phpunit', 'flash-message');
        $this->assertSame('flash-message', $session->get('phpunit'));
        $session->write();

        $session->start($id);
        $this->assertTrue($session->has('phpunit'));
        $this->assertSame('flash-message', $session->get('phpunit'));
        $session->write();

        $session->start($id);
        $this->assertFalse($session->has('phpunit'));
        $session->write();
    }

    public function testValues()
    {
        $session = $this->getSession();
        $session->start();
        $id = $session->id();
        $session->write();

        $session->start($id);
        $this->assertFalse($session->has('phpunit'));
        $this->assertNull($session->get('phpunit'));
        $session->set('phpunit', 'value');
        $this->assertSame('value', $session->get('phpunit'));
        $session->write();

        $session->start($id);
        $this->assertTrue($session->has('phpunit'));
        $this->assertSame('value', $session->get('phpunit'));
        $session->write();

        $session->start($id);
        $this->assertTrue($session->has('phpunit'));
        $session->set('phpunit', null);
        $this->assertNull($session->get('phpunit', 'default'));
        $session->write();

        $session->start($id);
        $this->assertTrue($session->has('phpunit'));
        $session->remove('phpunit');
        $session->write();

        $session->start($id);
        $this->assertFalse($session->has('phpunit'));
        $session->write();
    }

    private function getSession(): Session
    {
        return new Session($this->getSessionHandler());
    }

    abstract protected function getSessionHandler(): Handler;
}
