<?php

namespace TestGenesis\Cache;

use TestGenesis\Cache\ConfigValueObject\Config;
use TestGenesis\Cache\Exception\SamePriorityException;
use TestGenesis\Cache\System\CacheSystemInterface;

class Cache implements CacheInterface
{
    /**
     * @var CacheSystemInterface[]
     */
    private array $systems;

    /**
     * Cache constructor.
     * @param Config ...$configs
     * @throws SamePriorityException
     */
    public function __construct(Config ...$configs)
    {
        foreach ($configs as $config) {
            if (isset($this->systems[$config->getPriority()])) {
                throw new SamePriorityException(
                    \sprintf("Same priority: %d already defined in cache layers", $config->getPriority())
                );
            }

            $this->systems[$config->getPriority()] = $config->getClass();
        }

        \krsort($this->systems);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        foreach ($this->systems as $system) {
            if ($system->hasItem($key)) {
                if (!$system->expired($key)) {
                    return $system->getItem($key);
                }

                $system->delete($key);
            }
        }

        return null;
    }

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     */
    public function set(string $key, $value, $ttl = 3600)
    {
        foreach ($this->systems as $system) {
            $system->setItem($key, $value, $ttl);
        }
    }
}
