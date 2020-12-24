<?php

declare(strict_types=1);

namespace Ddrv\Tests\Slim\Session;

use Ddrv\Slim\Session\Storage;
use Ddrv\Slim\Session\Storage\ArrayStorage;
use Ddrv\Slim\Session\Storage\EncryptedStorageDecorator;

class EncryptedSessionTest extends SessionTestCase
{

    protected function getSessionHandler(): Storage
    {
        return new EncryptedStorageDecorator(new ArrayStorage(), 'secret', 3);
    }
}
