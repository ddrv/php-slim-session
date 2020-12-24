<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\IdGenerator;

use Ddrv\Slim\Session\IdGenerator;
use Exception;

final class RandomIdGenerator implements IdGenerator
{

    /**
     * @var int
     */
    private $len;

    /**
     * @var string
     */
    private $symbols;

    public function __construct(int $len = 30, string $symbols = '0123456789abcdefghijklmnopqrstuvwxyz')
    {
        $this->len = $len;
        $this->symbols = $symbols;
    }

    /**
     * @inheritDoc
     */
    public function generateId(): string
    {
        $rand = '';
        $max = mb_strlen($this->symbols) - 1;
        for ($i = 0; $i < $this->len; $i++) {
            try {
                $num = random_int(0, $max);
            } catch (Exception $e) {
                $num = rand(0, $max);
            }
            $rand .= mb_substr($this->symbols, $num, 1);
        }
        return $rand;
    }
}
