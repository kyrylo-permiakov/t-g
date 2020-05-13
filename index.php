<?php

use TestGenesis\Cache\Cache;
use TestGenesis\Cache\ConfigValueObject\Config;
use TestGenesis\Cache\System\FileCacheSystem;
use TestGenesis\Cache\System\StaticCacheSystem;

require __DIR__ . '/vendor/autoload.php';

$staticCacheConfig = new Config(new StaticCacheSystem(), 10);
$fileCacheConfig = new Config(new FileCacheSystem(__DIR__ . "/Storage"), 11);
$cache = new Cache($staticCacheConfig, $fileCacheConfig);
$cache->set('key', 'value', 3600);
$a = $cache->get('key');
var_dump($a);