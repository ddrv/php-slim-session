<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session;

interface IdGenerator
{

    /**
     * Generate session ID.
     *
     * @return string session ID
     */
    public function generateId(): string;
}
