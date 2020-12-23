<?php

namespace Ddrv\Slim\Session\Handler;

use Ddrv\Slim\Session\Handler;
use Ddrv\Slim\Session\Session;

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
     * @var int
     */
    private $saltLen;

    /**
     * @param Handler $handler session handler
     * @param string $secret encryption key string
     * @param int $saltLen
     */
    public function __construct(Handler $handler, string $secret, int $saltLen = 16)
    {
        $this->secret = $secret;
        $this->handler = $handler;
        $this->saltLen = $saltLen;
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
    public function read(string $sessionId): Session
    {
        $wrapper = $this->handler->read($sessionId);
        if (!$wrapper) {
            return Session::create();
        }
        /** @var string $encrypted */
        $encrypted = $wrapper->get('encrypted', '');
        $session = Session::create($this->decrypt($encrypted, $this->secret, $this->saltLen));
        if ($session instanceof Session) {
            return $session;
        }
        return Session::create();
    }

    /**
     * @inheritDoc
     */
    public function write(string $sessionId, Session $session): void
    {
        $encrypted = $this->encrypt($session->__toString(), $this->secret, $this->saltLen);
        $wrapper = Session::create();
        $wrapper->set('encrypted', $encrypted);
        $this->handler->write($sessionId, $wrapper);
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
