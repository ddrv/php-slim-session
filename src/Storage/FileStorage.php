<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\Storage;

use Ddrv\Slim\Session\Storage;
use Ddrv\Slim\Session\Session;

class FileStorage implements Storage
{
    use IdGenerator;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $path, string $name)
    {
        $this->path = $path;
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    final public function read(string $sessionId): Session
    {
        $file = $this->getFileName($sessionId);
        $serialized = null;
        if (is_readable($file)) {
            $serialized = file_get_contents($file);
        }
        return Session::create($serialized);
    }

    /**
     * @inheritDoc
     */
    final public function write(string $sessionId, Session $session): void
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
        file_put_contents($this->getFileName($sessionId), $session->__toString());
    }

    /**
     * @inheritDoc
     */
    final public function remove(string $sessionId): void
    {
        unlink($this->getFileName($sessionId));
    }

    /**
     * @inheritDoc
     */
    final public function garbageCollect(int $maxLifeTime): int
    {
        $result = 0;
        $files = glob($this->path . DIRECTORY_SEPARATOR . $this->name . '_*');
        foreach ($files as $file) {
            $time = filemtime($file);
            if ($time < $maxLifeTime) {
                unlink($file);
                $result++;
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    final protected function has(string $sessionId): bool
    {
        return file_exists($this->getFileName($sessionId));
    }

    /**
     * Return name of session file.
     *
     * @param string $sessionId session ID
     * @return string name of session file
     */
    private function getFileName(string $sessionId): string
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->name . '_' . $sessionId;
    }
}
