<?php

declare(strict_types=1);

namespace Ddrv\Slim\Session;

final class Session
{
    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var string|null
     */
    private $id = null;

    /**
     * @var bool
     */
    private $isStarted = false;

    /**
     * @var array
     */
    private $data;

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
        $this->data = [
            'data' => [],
            'prev' => [],
            'flash' => [],
            'counter' => [],
        ];
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function start(?string $id = null): void
    {
        if ($this->isStarted) {
            return;
        }
        if (!$id) {
            $id = $this->handler->generateId();
        }
        $this->id = $id;
        $string = $this->handler->read($this->id);
        $data = empty($string) ? [] : unserialize($string);
        $this->data['data'] = array_key_exists('data', $data) ? (array)$data['data'] : [];
        $this->data['counter'] = array_key_exists('counter', $data) ? (array)$data['counter'] : [];
        $this->data['prev'] = array_key_exists('flash', $data) ? (array)$data['flash'] : [];
        $this->data['flash'] = [];
        $this->isStarted = true;
    }

    public function regenerate(): void
    {
        if (!$this->isStarted) {
            return;
        }
        $id = $this->id;
        $this->id = $this->handler->generateId();
        $this->handler->destroy($id);
        $this->handler->write($this->id, serialize($this->data));
    }

    public function write(): void
    {
        if (!$this->isStarted) {
            return;
        }
        $this->handler->write($this->id, serialize($this->data));
        $this->isStarted = false;
    }

    /**
     * @void
     */
    public function destroy(): void
    {
        if (!$this->isStarted) {
            return;
        }
        $this->handler->destroy($this->id);
        $this->isStarted = false;
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
}
