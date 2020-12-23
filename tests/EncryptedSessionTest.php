<?php

declare(strict_types=1);

namespace Ddrv\Tests\Slim\Session;

use Ddrv\Slim\Session\Handler;
use Ddrv\Slim\Session\Handler\ArrayHandler;
use Ddrv\Slim\Session\Handler\EncryptedHandlerDecorator;

class EncryptedSessionTest extends SessionTestCase
{

    protected function getSessionHandler(): Handler
    {
        return new EncryptedHandlerDecorator(new ArrayHandler(), 'secret', 3);
    }
}
