<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session;

use DateTimeInterface;

interface Storage
{

    /**
     * Read session data by session ID.
     *
     * @param string $sessionId session ID
     * @return string|null session serialized string
     */
    public function read(string $sessionId): ?string;

    /**
     * Write serialized string of session data to storage.
     *
     * @param string $sessionId session ID
     * @param string $serialized session serialized data
     * @param DateTimeInterface $expirationTime
     */
    public function write(string $sessionId, string $serialized, DateTimeInterface $expirationTime): void;

    /**
     * Check for exists Session ID.
     *
     * @param string $sessionId
     * @return bool
     */
    public function has(string $sessionId): bool;

    /**
     * Remove session data from storage.
     *
     * @param string $sessionId session ID
     */
    public function remove(string $sessionId): void;

    /**
     * Remove session data from storage.
     *
     * @return int count of removed sessions.
     */
    public function removeExpiredSessions(): int;
}
