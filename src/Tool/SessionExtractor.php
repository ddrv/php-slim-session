<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\Tool;

use Ddrv\Slim\Session\Session;
use Psr\Http\Message\ServerRequestInterface;

final class SessionExtractor
{

    /**
     * @var string
     */
    private $attribute;

    public function __construct(string $attribute = '__session__')
    {
        $this->attribute = $attribute;
    }

    public function getSession(ServerRequestInterface $request): ?Session
    {
        return $request->getAttribute($this->getAttributeName());
    }

    public function getAttributeName(): string
    {
        return $this->attribute;
    }
}
