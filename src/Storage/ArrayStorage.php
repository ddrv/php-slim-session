<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\Storage;

use Ddrv\Slim\Session\Storage;
use Ddrv\Slim\Session\Session;

final class ArrayStorage implements Storage
{
    use IdGenerator;

    /**
     * @var string[]
     */
    private $storage = [];

    /**
     * @var int[]
     */
    private $mtime = [];

    /**
     * @inheritDoc
     */
    public function read(string $sessionId): Session
    {
        $serialized = null;
        if (array_key_exists($sessionId, $this->storage)) {
            $serialized = $this->storage[$sessionId];
        }
        return Session::create($serialized);
    }

    /**
     * @inheritDoc
     */
    public function write(string $sessionId, Session $session): void
    {
        $this->storage[$sessionId] = $session->__toString();
        $this->mtime[$sessionId] = time();
    }

    /**
     * @inheritDoc
     */
    public function remove(string $sessionId): void
    {
        if (array_key_exists($sessionId, $this->storage)) {
            unset($this->storage[$sessionId]);
        }
        if (array_key_exists($sessionId, $this->mtime)) {
            unset($this->mtime[$sessionId]);
        }
    }

    /**
     * @inheritDoc
     */
    public function garbageCollect(int $maxLifeTime): int
    {
        $result = 0;
        foreach (array_keys($this->storage) as $sessionId) {
            $time = array_key_exists($sessionId, $this->mtime) ? $this->mtime[$sessionId] : 0;
            if ($time < $maxLifeTime) {
                $this->remove($sessionId);
                $result++;
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function has(string $sessionId): bool
    {
        return array_key_exists($sessionId, $this->storage);
    }
}
