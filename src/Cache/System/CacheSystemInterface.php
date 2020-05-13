<?php

namespace TestGenesis\Cache\System;

interface CacheSystemInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function getItem(string $key);

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return bool
     */
    public function setItem(string $key, $value, int $ttl = 3600): bool;

    /**
     * @param string $key
     * @return bool
     */
    public function hasItem(string $key): bool;

    /**
     * @return bool
     */
    public function clear(): bool;

    /**
     * @param string $key
     * @return bool
     */
    public function expired(string $key): bool;

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;
}
