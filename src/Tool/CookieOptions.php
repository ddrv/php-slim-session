<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\Tool;

final class CookieOptions
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
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
     * @var bool
     */
    private $secure;

    public function __construct(
        string $name,
        string $domain,
        string $path = '/',
        string $sameSite = 'Lax',
        int $lifetime = 86400,
        bool $httpOnly = false,
        bool $secure = false
    ) {
        $this->name = $name;
        $this->domain = $domain;
        $this->path = $path;
        $sameSite = ucfirst(strtolower($sameSite));
        $this->lifetime = $lifetime;
        $this->httpOnly = $httpOnly;
        $this->secure = $secure;
        if (!in_array($sameSite, ['Lax', 'Strict', 'None'])) {
            $sameSite = 'Lax';
        }
        if ($sameSite === 'None' && !$secure) {
            $sameSite = 'Lax';
        }
        $this->sameSite = $sameSite;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSameSite(): string
    {
        return $this->sameSite;
    }

    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    public function isSecure(): bool
    {
        return $this->secure;
    }
}
