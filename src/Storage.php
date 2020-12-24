<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session;

interface Storage
{

    /**
     * Read session data by session ID.
     *
     * @param string $sessionId session ID
     *
     * @return Session session serialized string
     */
    public function read(string $sessionId): string;

    /**
     * Write serialized string of session data to storage.
     *
     * @param string $sessionId session ID
     *
     * @param string $serialized session serialized data
     */
    public function write(string $sessionId, string $serialized): void;


    /**
     * Change session ID.
     *
     * @param string $oldSessionId old session ID
     * @param string $newSessionId new session ID
     */
    public function rename(string $oldSessionId, string $newSessionId): void;

    /**
     * Check for exists Session ID.
     *
     * @param string $sessionName
     * @return bool
     */
    public function has(string $sessionName): bool;

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
