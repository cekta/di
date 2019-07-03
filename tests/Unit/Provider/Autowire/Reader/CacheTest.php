<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\Autowire\Reader;

use Cekta\DI\Provider\Autowire\Reader\Cache;
use Cekta\DI\Provider\Autowire\Reader\Exception\InvalidCacheKey;
use Cekta\DI\Provider\Autowire\ReaderInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use stdClass;

class CacheTest extends TestCase
{
    public function testMustBeReader()
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        assert($cache instanceof CacheItemPoolInterface);
        $this->assertInstanceOf(ReaderInterface::class, new Cache($cache));
    }

    public function testGetDependenciesCacheMiss()
    {
        $key = base64_encode(stdClass::class);
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())->method('isHit')
            ->willReturn(false);
        $item->expects($this->once())->method('set')
            ->with([]);
        $item->expects($this->once())->method('get')
            ->willReturn([]);
        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->expects($this->once())->method('getItem')
            ->with($key)
            ->willReturn($item);
        assert($pool instanceof CacheItemPoolInterface);
        $reader = new Cache($pool);
        $this->assertSame([], $reader->getDependencies(stdClass::class));
    }

    public function testGetDependenciesCacheHit()
    {
        $key = base64_encode(stdClass::class);
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())->method('isHit')
            ->willReturn(true);
        $item->expects($this->never())->method('set');
        $item->expects($this->once())->method('get')
            ->willReturn([]);
        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->expects($this->once())->method('getItem')
            ->with($key)
            ->willReturn($item);
        assert($pool instanceof CacheItemPoolInterface);
        $reader = new Cache($pool);
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
        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->expects($this->once())->method('getItem')
            ->with($key)
            ->willThrowException($exception);
        assert($pool instanceof CacheItemPoolInterface);
        $reader = new Cache($pool);
        $this->assertSame([], $reader->getDependencies(stdClass::class));
    }
}
