<?php

namespace TestGenesis\Cache\System;

use TestGenesis\Cache\Exception\CacheWriteFailedException;
use TestGenesis\Cache\Exception\DirectoryMissingException;
use TestGenesis\Cache\Exception\InvalidCacheUnitException;
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
     * @throws InvalidCacheUnitException
     */
    public function getItem(string $key)
    {
        return $this->unserealizeCacheUnit($key)['value'];
    }

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return bool
     * @throws CacheWriteFailedException
     */
    public function setItem(string $key, $value, int $ttl = 3600): bool
    {
        $fileName = $this->getFileName($key);
        $resource = \fopen($fileName, 'wb');
        $serializedCacheUnit = \serialize(['value' => $value, 'expiresAt' => time() + $ttl]);
        if (!fwrite($resource, $serializedCacheUnit)) {
            throw new CacheWriteFailedException(\sprintf("Cant write %s to %s", $serializedCacheUnit, $fileName));
        }

        fclose($resource);

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
     * @throws InvalidCacheUnitException
     */
    public function expired(string $key): bool
    {
        return $this->unserealizeCacheUnit($key)['expiresAt'] <= \time();
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
    private function getFileName($key): string
    {
        return \sprintf("%s/%s.%s", $this->dir, \md5($key), $this->format);
    }

    /**
     * @param $key
     * @return array
     * @throws InvalidCacheUnitException
     */
    private function unserealizeCacheUnit($key): array
    {
        $serializedCacheUnit = \file_get_contents($this->getFileName($key));
        $cacheUnit = \unserialize($serializedCacheUnit);
        if (!is_array($cacheUnit)) {
            throw new InvalidCacheUnitException(\sprintf("Cache unit for %s is broken", $key));
        }

        return $cacheUnit;
    }
}
