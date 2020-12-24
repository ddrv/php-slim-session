<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\Storage;

use DateTimeInterface;
use Ddrv\Slim\Session\Storage;

final class ArrayStorage implements Storage
{

    /**
     * @var string[]
     */
    private $storage = [];

    /**
     * @var int[]
     */
    private $etime = [];

    /**
     * @inheritDoc
     */
    public function read(string $sessionId): ?string
    {
        $serialized = null;
        if (array_key_exists($sessionId, $this->storage)) {
            $serialized = $this->storage[$sessionId];
        }
        return $serialized;
    }

    /**
     * @inheritDoc
     */
    public function write(string $sessionId, string $serialized, DateTimeInterface $expirationTime): void
    {
        $this->storage[$sessionId] = $serialized;
        $this->etime[$sessionId] = $expirationTime->getTimestamp();
    }

    /**
     * @inheritdoc
     */
    public function rename(string $oldSessionId, string $newSessionId): void
    {
        if (array_key_exists($oldSessionId, $this->storage)) {
            $this->storage[$newSessionId] = $this->storage[$oldSessionId];
        }
        if (array_key_exists($oldSessionId, $this->etime)) {
            $this->etime[$newSessionId] = $this->etime[$oldSessionId];
        }
        $this->remove($oldSessionId);
    }

    /**
     * @inheritDoc
     */
    public function has(string $sessionId): bool
    {
        return array_key_exists($sessionId, $this->storage);
    }

    /**
     * @inheritDoc
     */
    public function remove(string $sessionId): void
    {
        if (array_key_exists($sessionId, $this->storage)) {
            unset($this->storage[$sessionId]);
        }
        if (array_key_exists($sessionId, $this->etime)) {
            unset($this->etime[$sessionId]);
        }
    }

    /**
     * @inheritDoc
     */
    public function removeExpiredSessions(): int
    {
        $result = 0;
        $now = time();
        foreach (array_keys($this->storage) as $sessionId) {
            $time = array_key_exists($sessionId, $this->etime) ? $this->etime[$sessionId] : $now + 1;
            if ($time <= $now) {
                $this->remove($sessionId);
                $result++;
            }
        }
        return $result;
    }
}
