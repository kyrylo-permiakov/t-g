<?php

namespace TestGenesis\Cache\ConfigValueObject;

use TestGenesis\Cache\System\CacheSystemInterface;

class Config
{
    /**
     * @var CacheSystemInterface
     */
    private CacheSystemInterface $class;

    /**
     * @var int
     */
    private int $priority;

    /**
     * Config constructor.
     * @param CacheSystemInterface $class
     * @param int $priority
     */
    public function __construct(CacheSystemInterface $class, int $priority)
    {
        $this->class = $class;
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getClass(): CacheSystemInterface
    {
        return $this->class;
    }

    /**
     * @return mixed
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

}
