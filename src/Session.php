<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session;

final class Session
{

    /**
     * @var array
     */
    private $data;

    /**
     * @var bool
     */
    private $isNeedRegenerate = false;

    private function __construct()
    {
        $this->data = [
            'data' => [],
            'prev' => [],
            'flash' => [],
            'counter' => [],
        ];
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $this->data['data'][$key] = $value;
        if (array_key_exists($key, $this->data['prev'])) {
            unset($this->data['prev'][$key]);
        }
        if (array_key_exists($key, $this->data['flash'])) {
            unset($this->data['flash'][$key]);
        }
    }

    /**
     * @param string $key
     */
    public function remove(string $key): void
    {
        if (array_key_exists($key, $this->data['data'])) {
            unset($this->data['data'][$key]);
        }
        if (array_key_exists($key, $this->data['prev'])) {
            unset($this->data['prev'][$key]);
        }
        if (array_key_exists($key, $this->data['flash'])) {
            unset($this->data['flash'][$key]);
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function flash(string $key, $value): void
    {
        $this->data['flash'][$key] = $value;
        if (array_key_exists($key, $this->data['prev'])) {
             unset($this->data['prev'][$key]);
        }
        if (array_key_exists($key, $this->data['data'])) {
             unset($this->data['data'][$key]);
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return (array_key_exists($key, $this->data['data'])
            || array_key_exists($key, $this->data['flash'])
            || array_key_exists($key, $this->data['prev'])
        );
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        foreach (['flash', 'prev', 'data'] as $place) {
            if (array_key_exists($key, $this->data[$place])) {
                return $this->data[$place][$key];
            }
        }
        return $default;
    }

    /**
     * @param string $counter
     * @return int
     */
    public function counter(string $counter): int
    {
        if (array_key_exists($counter, $this->data['counter'])) {
            return $this->data['counter'][$counter];
        }
        return 0;
    }

    /**
     * @param string $counter
     * @return int
     */
    public function reset(string $counter): int
    {
        $this->data['counter'][$counter] = 0;
        return 0;
    }

    /**
     * @param string $counter
     * @return int
     */
    public function increment(string $counter): int
    {
        if (!array_key_exists($counter, $this->data['counter'])) {
            $this->data['counter'][$counter] = 0;
        }
        $this->data['counter'][$counter]++;
        return $this->data['counter'][$counter];
    }

    /**
     * @param string $counter
     * @return int
     */
    public function decrement(string $counter): int
    {
        if (!array_key_exists($counter, $this->data['counter'])) {
            $this->data['counter'][$counter] = 0;
        }
        $this->data['counter'][$counter]--;
        return $this->data['counter'][$counter];
    }

    public function regenerate(): void
    {
        $this->isNeedRegenerate = true;
    }

    public function isNeedRegenerate(): bool
    {
        return $this->isNeedRegenerate;
    }

    public function __toString(): string
    {
        return serialize($this->data);
    }

    public static function create(?string $serialized = null): self
    {
        $session = new self();
        $data = $serialized ? unserialize($serialized) : [];
        $session->data['data'] = array_key_exists('data', $data) ? (array)$data['data'] : [];
        $session->data['counter'] = array_key_exists('counter', $data) ? (array)$data['counter'] : [];
        $session->data['prev'] = array_key_exists('flash', $data) ? (array)$data['flash'] : [];
        $session->data['flash'] = [];
        return $session;
    }
}
