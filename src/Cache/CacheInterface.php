<?php

namespace TestGenesis\cache;

interface CacheInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return mixed
     */
    public function set(string $key, $value, $ttl = 3600);
}
