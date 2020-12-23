<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session;

interface Handler
{

    /**
     * Read session data by session ID.
     *
     * @param string $sessionId session ID
     *
     * @return Session session object
     */
    public function read(string $sessionId): Session;

    /**
     * Generate unused session ID.
     *
     * @return string session ID
     */
    public function generateId(): string;

    /**
     * Write serialized string of session data to storage.
     *
     * @param string $sessionId session ID
     *
     * @param Session $session session object
     */
    public function write(string $sessionId, Session $session): void;

    /**
     * Remove session data from storage and close session.
     *
     * @param string $sessionId session ID
     */
    public function destroy(string $sessionId): void;

    /**
     * Remove sessions olden maximal life time.
     *
     * @param int $maxLifeTime maximal life time, unix timestamp
     *
     * @return int quantity of removed sessions
     */
    public function garbageCollect(int $maxLifeTime): int;
}
