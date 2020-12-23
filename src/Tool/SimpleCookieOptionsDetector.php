<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\Tool;

use Psr\Http\Message\UriInterface;

final class SimpleCookieOptionsDetector implements CookieOptionsDetector
{

    /**
     * @var CookieOptions[]
     */
    private $cache = [];

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $domain;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $sameSite;

    /**
     * @var int
     */
    private $lifetime;

    /**
     * @var bool
     */
    private $httpOnly;

    /**
     * @var bool|null
     */
    private $secure;

    public function __construct(
        string $name = 'sid',
        ?string $domain = null,
        string $path = '/',
        string $sameSite = 'Lax',
        int $lifetime = 86400,
        bool $httpOnly = true,
        ?bool $secure = null
    ) {
        $this->name = $name;
        $this->domain = $domain;
        $this->path = $path;
        $this->sameSite = $sameSite;
        $this->lifetime = $lifetime;
        $this->httpOnly = $httpOnly;
        $this->secure = $secure;
    }

    public function getCookieOptions(UriInterface $uri): CookieOptions
    {
        $key = $uri->withFragment('')->withQuery('')->withPath('')->__toString();
        if (!array_key_exists($key, $this->cache)) {
            return $this->cache[$key] = new CookieOptions(
                $this->name,
                $this->domain ?? $uri->getHost(),
                $this->path,
                $this->sameSite,
                $this->lifetime,
                $this->httpOnly,
                is_null($this->secure) ? ($uri->getScheme() === 'https') : $this->secure
            );
        }
        return $this->cache[$key];
    }
}
