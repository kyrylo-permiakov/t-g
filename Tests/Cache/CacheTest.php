<?php

namespace TestGenesis\Tests\Cache;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TestGenesis\Cache\Cache;
use TestGenesis\Cache\ConfigValueObject\Config;
use TestGenesis\Cache\Exception\SamePriorityException;
use TestGenesis\Cache\System\FileCacheSystem;
use TestGenesis\Cache\System\StaticCacheSystem;

class CacheTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $staticConfig;

    /**
     * @var MockObject
     */
    private $fileConfig;


    protected function setUp(): void
    {
        $this->staticConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getClass', 'getPriority'])
            ->getMock();

        $this->fileConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getClass', 'getPriority'])
            ->getMock();
    }

    public function testGetCacheWhenStaticEnabled()
    {
        $key = 'randomTestKey';

        $staticCacheSystem = $this->getMockBuilder(StaticCacheSystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItem', 'hasItem', 'expired'])
            ->getMock();

        $staticCacheSystem->expects($this->once())->method('hasItem')
            ->with($this->equalTo($key))
            ->will($this->returnValue(true));

        $staticCacheSystem->expects($this->once())->method('expired')
            ->with($this->equalTo($key))
            ->will($this->returnValue(false));

        $staticCacheSystem->expects($this->once())->method('getItem')
            ->with($this->equalTo($key))
            ->will($this->returnValue('randomTestValue'));

        $this->staticConfig->expects($this->once())->method('getClass')
            ->will($this->returnValue($staticCacheSystem));

        $this->staticConfig->expects($this->exactly(2))->method('getPriority')
            ->will($this->returnValue(2));

        $cache = new Cache($this->staticConfig);
        $this->assertEquals('randomTestValue', $cache->get('randomTestKey'));
    }

    public function testGetCacheWhenFileEnabled()
    {
        $key = 'randomTestKey';

        $fileCacheSystem = $this->getMockBuilder(FileCacheSystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItem', 'hasItem', 'expired'])
            ->getMock();

        $fileCacheSystem->expects($this->once())->method('hasItem')
            ->with($this->equalTo($key))
            ->will($this->returnValue(true));

        $fileCacheSystem->expects($this->once())->method('expired')
            ->with($this->equalTo($key))
            ->will($this->returnValue(false));

        $fileCacheSystem->expects($this->once())->method('getItem')
            ->with($this->equalTo($key))
            ->will($this->returnValue('randomTestValue'));

        $this->fileConfig->expects($this->once())->method('getClass')
            ->will($this->returnValue($fileCacheSystem));

        $this->fileConfig->expects($this->exactly(2))->method('getPriority')
            ->will($this->returnValue(2));

        $cache = new Cache($this->fileConfig);
        $this->assertEquals('randomTestValue', $cache->get('randomTestKey'));
    }

    public function testGetWhenCacheIsExpired()
    {
        $key = 'randomTestKey';

        $fileCacheSystem = $this->getMockBuilder(FileCacheSystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['delete', 'hasItem', 'expired'])
            ->getMock();

        $fileCacheSystem->expects($this->once())->method('hasItem')
            ->with($this->equalTo($key))
            ->will($this->returnValue(true));

        $fileCacheSystem->expects($this->once())->method('expired')
            ->with($this->equalTo($key))
            ->will($this->returnValue(true));

        $fileCacheSystem->expects($this->once())->method('delete')
            ->with($this->equalTo($key))
            ->will($this->returnValue(true));

        $this->fileConfig->expects($this->once())->method('getClass')
            ->will($this->returnValue($fileCacheSystem));

        $this->fileConfig->expects($this->exactly(2))->method('getPriority')
            ->will($this->returnValue(2));

        $cache = new Cache($this->fileConfig);
        $this->assertEquals(null, $cache->get('randomTestKey'));
    }

    public function testSamePriorityException()
    {
        $key = 'randomTestKey';

        $this->fileConfig->expects($this->exactly(2))->method('getPriority')
            ->will($this->returnValue(2));

        $this->staticConfig->expects($this->exactly(2))->method('getPriority')
            ->will($this->returnValue(2));

        $this->expectException(SamePriorityException::class);
        $cache = new Cache($this->staticConfig, $this->fileConfig);
    }

    public function testEmptyCache()
    {
        $key = 'randomTestKey';

        $fileCacheSystem = $this->getMockBuilder(FileCacheSystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasItem'])
            ->getMock();

        $fileCacheSystem->expects($this->once())->method('hasItem')
            ->with($this->equalTo($key))
            ->will($this->returnValue(false));


        $this->fileConfig->expects($this->once())->method('getClass')
            ->will($this->returnValue($fileCacheSystem));

        $this->fileConfig->expects($this->exactly(2))->method('getPriority')
            ->will($this->returnValue(2));

        $staticCacheSystem = $this->getMockBuilder(StaticCacheSystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasItem'])
            ->getMock();

        $staticCacheSystem->expects($this->once())->method('hasItem')
            ->with($this->equalTo($key))
            ->will($this->returnValue(false));


        $this->staticConfig->expects($this->once())->method('getClass')
            ->will($this->returnValue($staticCacheSystem));

        $this->staticConfig->expects($this->exactly(2))->method('getPriority')
            ->will($this->returnValue(5));

        $cache = new Cache($this->staticConfig, $this->fileConfig);
        $this->assertEquals(null, $cache->get('randomTestKey'));
    }

    public function testSet()
    {
        $staticCacheSystem = $this->getMockBuilder(StaticCacheSystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setItem'])
            ->getMock();

        $staticCacheSystem->expects($this->once())
            ->method('setItem')
            ->with($this->equalTo('randomTestKey'), $this->equalTo('randomTestValue'), 3600)
            ->will($this->returnValue(true));

        $this->staticConfig->expects($this->once())->method('getClass')
            ->will($this->returnValue($staticCacheSystem));

        $this->staticConfig->expects($this->exactly(2))->method('getPriority')
            ->will($this->returnValue(2));

        $cache = new Cache($this->staticConfig);
        $cache->set('randomTestKey', 'randomTestValue', 3600);
    }
}