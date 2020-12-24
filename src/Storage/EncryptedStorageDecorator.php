<?php

namespace Ddrv\Slim\Session\Storage;

use DateTimeInterface;
use Ddrv\Slim\Session\Storage;

final class EncryptedStorageDecorator implements Storage
{

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var int
     */
    private $saltLen;

    /**
     * @param Storage $storage session storage
     * @param string $secret encryption key string
     * @param int $saltLen
     */
    public function __construct(Storage $storage, string $secret, int $saltLen = 16)
    {
        $this->storage = $storage;
        $this->secret = $secret;
        $this->saltLen = $saltLen;
    }

    /**
     * @inheritDoc
     */
    public function read(string $sessionId): ?string
    {
        $encrypted = $this->storage->read($sessionId);
        if (!$encrypted) {
            return null;
        }
        return $this->decrypt($encrypted, $this->secret, $this->saltLen);
    }

    /**
     * @inheritDoc
     */
    public function write(string $sessionId, string $serialized, DateTimeInterface $expirationTime): void
    {
        $encrypted = $this->encrypt($serialized, $this->secret, $this->saltLen);
        $this->storage->write($sessionId, $encrypted, $expirationTime);
    }

    public function rename(string $oldSessionId, string $newSessionId): void
    {
        $this->storage->rename($oldSessionId, $newSessionId);
    }

    public function has(string $sessionId): bool
    {
        return $this->storage->has($sessionId);
    }

    /**
     * @inheritDoc
     */
    public function remove(string $sessionId): void
    {
        $this->storage->remove($sessionId);
    }

    /**
     * @inheritDoc
     */
    public function removeExpiredSessions(): int
    {
        return $this->storage->removeExpiredSessions();
    }

    private function decrypt(string $encrypted, string $secret, int $saltLen): string
    {
        $decoded = base64_decode($encrypted);
        $salt = substr($decoded, 0, $saltLen);
        $ct = substr($decoded, $saltLen);

        $rounds = 3;
        $data = $secret . $salt;
        $hash = array();
        $hash[0] = hash('sha256', $data, true);
        $result = $hash[0];
        for ($i = 1; $i < $rounds; $i++) {
            $hash[$i] = hash('sha256', $hash[$i - 1] . $data, true);
            $result .= $hash[$i];
        }
        $key = substr($result, 0, 32);
        $iv  = substr($result, 32, 16);

        return openssl_decrypt($ct, 'AES-256-CBC', $key, true, $iv);
    }

    private function encrypt(string $data, string $secret, int $saltLen): string
    {
        $salt = openssl_random_pseudo_bytes($saltLen);
        $salted = '';
        $dx = '';
        while (strlen($salted) < 48) {
            $dx = hash('sha256', $dx . $secret . $salt, true);
            $salted .= $dx;
        }

        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32, 16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, true, $iv);
        return base64_encode($salt . $encrypted);
    }
}
