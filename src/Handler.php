<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session;

use DateTimeInterface;
use Ddrv\Slim\Session\IdGenerator\RandomIdGenerator;
use RuntimeException;

final class Handler
{

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var IdGenerator|null
     */
    private $idGenerator;

    public function __construct(Storage $storage, ?IdGenerator $idGenerator = null)
    {
        $this->storage = $storage;
        $this->idGenerator = $idGenerator ?? new RandomIdGenerator();
    }

    public function read(string $sessionId): Session
    {
        $serialized = $this->storage->read($sessionId);
        $session = Session::create($serialized);
        if ($session->getExpirationTime()->getTimestamp() < time()) {
            $this->destroy($sessionId);
            $session = Session::create();
        }
        return $session;
    }

    public function generateId(): string
    {
        $attempts = 10;
        do {
            $sessionId = $this->idGenerator->generateId();
            $attempts--;
            $success = !$this->storage->has($sessionId);
        } while (!$success && $attempts > 0);
        if (!$success) {
            throw new RuntimeException('can not generate unique session id');
        }
        return $sessionId;
    }

    public function write(?string $sessionId, Session $session, DateTimeInterface $expirationTime): string
    {
        if (!$sessionId) {
            $sessionId = $this->generateId();
        }
        $session->setExpirationTime($expirationTime);
        $serialized = $session->__toString();
        $this->storage->write($sessionId, $serialized);
        if ($session->isNeedRegenerate()) {
            $regeneratedId = $this->generateId();
            $this->storage->rename($sessionId, $regeneratedId);
            $sessionId = $regeneratedId;
        }
        return $sessionId;
    }

    public function destroy(string $sessionId): void
    {
        $this->storage->remove($sessionId);
    }

    public function removeExpiredSessions(): int
    {
        return $this->storage->removeExpiredSessions();
    }
}
