<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\Handler;

use Ddrv\Slim\Session\Handler;

class ArrayHandler implements Handler
{
    use IdGeneratorTrait;

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
    public function read(string $sessionId): string
    {
        if (!array_key_exists($sessionId, $this->storage)) {
            return '';
        }
        return $this->storage[$sessionId];
    }

    /**
     * @inheritDoc
     */
    public function write(string $sessionId, string $serializedData): void
    {
        $this->storage[$sessionId] = $serializedData;
        $this->mtime[$sessionId] = time();
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $sessionId): void
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
                $this->destroy($sessionId);
                $result++;
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function isIdExists(string $sessionId): bool
    {
        return array_key_exists($sessionId, $this->storage);
    }
}
