<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session\Tool;

use Ddrv\Slim\Session\Session;

final class SessionNVisitRegeneration implements SessionRegeneration
{

    /**
     * @var string
     */
    private $counter;

    /**
     * @var int
     */
    private $visits;

    public function __construct(string $counter = '__visit__', int $visits = 0)
    {
        $this->counter = $counter;
        $this->visits = $visits < 0 ? 0 : $visits;
    }

    public function visit(Session $session): void
    {
        if ($this->visits < 1) {
            return;
        }
        $visit = $session->increment($this->counter);
        if ($visit >= $this->visits) {
            $session->regenerate();
            $session->reset($this->counter);
        }
    }
}
