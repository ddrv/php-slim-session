<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\Tool;

use Ddrv\Slim\Session\Session;

interface SessionRegeneration
{

    public function visit(Session $session): void;
}
