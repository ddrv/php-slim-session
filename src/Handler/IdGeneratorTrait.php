<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\Handler;

trait IdGeneratorTrait
{

    /**
     * @var string
     */
    private $symbols;

    /**
     * @inheritDoc
     */
    public function generateId(): string
    {
        if (!$this->symbols) {
            $this->symbols = str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 30);
        }
        do {
            $id = substr(str_shuffle($this->symbols), 0, 30);
        } while ($this->isIdExists($id));
        return $id;
    }

    /**
     * Check for exists session ID.
     *
     * @param string $sessionId session ID
     * @return bool true if session ID exists in storage
     */
    abstract protected function isIdExists(string $sessionId): bool;
}
