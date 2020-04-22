<?php

declare(strict_types=1);

namespace Ddrv\Tests\Slim\Session;

use Ddrv\Slim\Session\Handler;
use Ddrv\Slim\Session\Handler\ArrayHandler;

class SessionTest extends SessionTestCase
{

    protected function getSessionHandler(): Handler
    {
        return new ArrayHandler();
    }
}
