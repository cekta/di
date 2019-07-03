<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\Autowire\Reader;

use Cekta\DI\Provider\Autowire\Reader\Exception\InvalidCacheKey;
use Cekta\DI\Provider\Autowire\Reader\SimpleCache;
use Cekta\DI\Provider\Autowire\ReaderInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use stdClass;

class SimpleCacheTest extends TestCase
{
    public function testMustBeReader()
    {
        $cache = $this->createMock(CacheInterface::class);
        assert($cache instanceof CacheInterface);
        $this->assertInstanceOf(ReaderInterface::class, new SimpleCache($cache));
    }

    public function testGetDependenciesCacheMiss()
    {
        $key = base64_encode(stdClass::class);
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())->method('has')
            ->with($key)
            ->willReturn(false);
        $cache->expects($this->once())->method('set')
            ->with($key, []);
        $cache->expects($this->once())->method('get')
            ->with($key)
            ->willReturn([]);
        assert($cache instanceof CacheInterface);
        $reader = new SimpleCache($cache);
        $this->assertSame([], $reader->getDependencies(stdClass::class));
    }

    public function testGetDependenciesCacheHit()
    {
        $key = base64_encode(stdClass::class);
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())->method('has')
            ->with($key)
            ->willReturn(true);
        $cache->expects($this->never())->method('set');
        $cache->expects($this->once())->method('get')
            ->with($key)
            ->willReturn([]);
        assert($cache instanceof CacheInterface);
        $reader = new SimpleCache($cache);
        $this->assertSame([], $reader->getDependencies(stdClass::class));
    }

    public function testGetDependenciesWithInvalidArgumentException()
    {
        $this->expectException(InvalidCacheKey::class);
        $this->expectExceptionMessage('Invalid key: `c3RkQ2xhc3M=` for class: `stdClass`');
        $exception = new class extends Exception implements InvalidArgumentException
        {
        };
        $key = base64_encode(stdClass::class);
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())->method('get')
            ->with($key)
            ->willThrowException($exception);
        assert($cache instanceof CacheInterface);
        $reader = new SimpleCache($cache);
        $this->assertSame([], $reader->getDependencies(stdClass::class));
    }
}
