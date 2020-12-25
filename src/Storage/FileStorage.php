<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\Storage;

use DateTimeInterface;
use Ddrv\Slim\Session\Storage;

final class FileStorage implements Storage
{

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
    public function read(string $sessionId): ?string
    {
        $file = $this->getSessionFileName($sessionId);
        $serialized = null;
        if (is_readable($file)) {
            $serialized = file_get_contents($file);
        }
        return $serialized;
    }

    /**
     * @inheritDoc
     */
    public function write(string $sessionId, string $serialized, DateTimeInterface $expirationTime): void
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
        file_put_contents($this->getSessionFileName($sessionId), $serialized);
        file_put_contents($this->getEtimeFileName($sessionId), $expirationTime->getTimestamp());
    }

    /**
     * @inheritDoc
     */
    public function remove(string $sessionId): void
    {
        $session = $this->getSessionFileName($sessionId);
        $etime = $this->getEtimeFileName($sessionId);
        if (file_exists($session)) {
            unlink($session);
        }
        if (file_exists($etime)) {
            unlink($etime);
        }
    }

    /**
     * @inheritDoc
     */
    public function has(string $sessionId): bool
    {
        return file_exists($this->getSessionFileName($sessionId));
    }

    /**
     * @inheritDoc
     */
    public function removeExpiredSessions(): int
    {
        $now = time();
        $result = 0;
        $files = glob($this->path . DIRECTORY_SEPARATOR . $this->name . '_*.etime');
        foreach ($files as $file) {
            $time = (int)file_get_contents($file);
            if ($time < $now) {
                $sessionId = mb_substr(pathinfo($file, PATHINFO_BASENAME), 0, -6);
                $this->remove($sessionId);
                $result++;
            }
        }
        return $result;
    }

    /**
     * Return name of session file.
     *
     * @param string $sessionId session ID
     * @return string name of session file
     */
    private function getSessionFileName(string $sessionId): string
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->name . '_' . $sessionId;
    }

    /**
     * Return name of session file.
     *
     * @param string $sessionId session ID
     * @return string name of session file
     */
    private function getEtimeFileName(string $sessionId): string
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->name . '_' . $sessionId . '.etime';
    }
}
