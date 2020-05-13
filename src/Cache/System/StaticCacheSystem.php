<?php

namespace TestGenesis\Cache\System;

class StaticCacheSystem implements CacheSystemInterface
{
    /**
     * @var array
     */
    private array $items;

    /**
     * @param string $key
     * @return mixed
     */
    public function getItem(string $key)
    {
        return $this->items[$key]['value'];
    }

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return bool
     */
    public function setItem(string $key, $value, $ttl = 3600): bool
    {
        $this->items[$key] = [
            'value' => $value,
            'expiresAt' => time() + $ttl
        ];

        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasItem(string $key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        $this->items = [];

        return true;
    }

    public function expired(string $key): bool
    {
        return $this->items[$key]['expiresAt'] <= \time();
    }

    public function delete(string $key): bool
    {
        unset($this->items[$key]);

        return true;
    }
}