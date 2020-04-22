<?php

namespace Ddrv\Slim\Session\Handler;

use Ddrv\Slim\Session\Handler;

class EncryptedHandlerDecorator implements Handler
{

    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var string
     */
    private $secret;

    /**
     * @param Handler $handler session handler
     * @param string $secret encryption key string
     */
    public function __construct($handler, string $secret)
    {
        $this->secret = $secret;
        $this->handler = $handler;
    }

    /**
     * @inheritDoc
     */
    public function generateId(): string
    {
        return $this->handler->generateId();
    }

    /**
     * @inheritDoc
     */
    public function read(string $sessionId): string
    {
        $encrypted = $this->handler->read($sessionId);
        if (!$encrypted) {
            return '';
        } else {
            return $this->decrypt($encrypted, $this->secret);
        }
    }

    /**
     * @inheritDoc
     */
    public function write(string $sessionId, string $serializedData): void
    {
        $encrypted = $this->encrypt($serializedData, $this->secret);
        $this->handler->write($sessionId, $encrypted);
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $sessionId): void
    {
        $this->handler->destroy($sessionId);
    }

    /**
     * @inheritDoc
     */
    public function garbageCollect(int $maxLifeTime): int
    {
        return $this->handler->garbageCollect($maxLifeTime);
    }

    private function decrypt(string $encrypted, string $secret): string
    {
        $decoded = base64_decode($encrypted);
        $salt = substr($decoded, 0, 16);
        $ct = substr($decoded, 16);

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

    private function encrypt(string $data, string $secret): string
    {
        $salt = openssl_random_pseudo_bytes(16);
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
