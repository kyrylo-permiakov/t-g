<?php

namespace TestGenesis\Cache\System;

use TestGenesis\Cache\Exception\CacheWriteFailedException;
use TestGenesis\Cache\Exception\DirectoryMissingException;
use TestGenesis\Cache\Exception\NotWritableDirectoryException;

class FileCacheSystem implements CacheSystemInterface
{
    /**
     * @var string
     */
    private string $dir;

    /**
     * @var string
     */
    private string $format = 'txt';

    /**
     * FileCacheSystem constructor.
     * @param string $dir
     * @throws DirectoryMissingException
     * @throws NotWritableDirectoryException
     */
    public function __construct(string $dir)
    {
        if (!is_dir($dir)) {
            throw new DirectoryMissingException(\sprintf("%s must exist as a directory", $dir));
        }
        if (!is_writable($dir)) {
            throw new NotWritableDirectoryException(\sprintf("%s must be writable directory", $dir));
        }

        $this->dir = $dir;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getItem(string $key)
    {
        $serializedCacheUnit = \file_get_contents($this->getFileName($key));
        $cacheUnit = \unserialize($serializedCacheUnit);

        return $cacheUnit['value'];
    }

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return bool
     * @throws CacheWriteFailedException
     */
    public function setItem(string $key, $value, $ttl = 3600): bool
    {
        $fileName = $this->getFileName($key);
        $resource = \fopen($fileName, 'w');
        $serializedCacheUnit = \serialize(['value' => $value, 'expiresAt' => time() + $ttl]);
        if (!fwrite($resource, $serializedCacheUnit)) {
            throw new CacheWriteFailedException(\sprintf("Cant write %s to %s", $serializedCacheUnit, $fileName));
        }

        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasItem(string $key): bool
    {
        return \file_exists($this->getFileName($key));
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        $files = glob($this->dir);
        foreach ($files as $file) {
            unlink($file);
        }

        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function expired(string $key): bool
    {
        $serializedCacheUnit = \file_get_contents($this->getFileName($key));
        $cacheUnit = \unserialize($serializedCacheUnit);

        return $cacheUnit['expiresAt'] <= \time();
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return unlink($this->getFileName($key));
    }

    /**
     * @param $key
     * @return string
     */
    private function getFileName($key)
    {
        return \sprintf("%s/%s.%s", $this->dir, \md5($key), $this->format);
    }
}
