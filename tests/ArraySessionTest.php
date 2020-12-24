<?php

declare(strict_types=1);

namespace Ddrv\Tests\Slim\Session;

use Ddrv\Slim\Session\Storage;
use Ddrv\Slim\Session\Storage\ArrayStorage;

class ArraySessionTest extends SessionTestCase
{

    protected function getSessionHandler(): Storage
    {
        return new ArrayStorage();
    }
}
