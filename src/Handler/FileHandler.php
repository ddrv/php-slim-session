<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\Handler;

use Ddrv\Slim\Session\Handler;

class FileHandler implements Handler
{
    use IdGeneratorTrait;

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
    public function read(string $sessionId): string
    {
        $file = $this->getFileName($sessionId);
        if (!is_readable($file)) {
            return '';
        }
        return file_get_contents($file);
    }

    /**
     * @inheritDoc
     */
    public function write(string $sessionId, string $serializedData): void
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
        file_put_contents($this->getFileName($sessionId), $serializedData);
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $sessionId): void
    {
        unlink($this->getFileName($sessionId));
    }

    /**
     * @inheritDoc
     */
    public function garbageCollect(int $maxLifeTime): int
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
    protected function isIdExists(string $sessionId): bool
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
