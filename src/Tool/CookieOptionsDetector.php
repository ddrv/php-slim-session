<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\Tool;

use Psr\Http\Message\UriInterface;

interface CookieOptionsDetector
{

    public function getCookieOptions(UriInterface $uri): CookieOptions;
}
