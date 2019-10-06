<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\AutowiringCache;
use Cekta\DI\Provider\Exception\InvalidCacheKey;
use Cekta\DI\ProviderExceptionInterface;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;
use stdClass;

class AutowiringCacheTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $pool;
    /**
     * @var MockObject
     */
    private $item;
    /**
     * @var MockObject
     */
    private $container;
    /**
     * @var AutowiringCache
     */
    private $provider;
    /**
     * @var MockObject
     */
    private $autowiring;

    protected function setUp(): void
    {
        $this->pool = $this->createMock(CacheItemPoolInterface::class);
        $this->item = $this->createMock(CacheItemInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->autowiring = $this->createMock(Autowiring::class);
        assert($this->pool instanceof CacheItemPoolInterface);
        assert($this->autowiring instanceof Autowiring);
        $this->provider = new AutowiringCache($this->pool, $this->autowiring);
    }

    public function testMustBeProvider(): void
    {
        $this->assertInstanceOf(ProviderInterface::class, $this->provider);
    }

    public function testCanProvide(): void
    {
        $this->assertTrue($this->provider->canProvide(stdClass::class));
    }

    public function testCanProvideInvalidName(): void
    {
        $this->assertFalse($this->provider->canProvide('invalid name'));
    }

    public function testCanProvideInterface(): void
    {
        $this->assertFalse($this->provider->canProvide(ProviderInterface::class));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvideCacheHit()
    {
        $this->item->expects($this->once())->method('isHit')->willReturn(true);
        $this->item->expects($this->once())->method('get')->willReturn([]);
        $this->item->expects($this->never())->method('set');
        $this->pool->expects($this->once())
            ->method('getItem')
            ->with(stdClass::class)
            ->willReturn($this->item);
        assert($this->container instanceof ContainerInterface);
        $this->assertInstanceOf(stdClass::class, $this->provider->provide(stdClass::class)($this->container));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvideCacheMiss(): void
    {
        $this->autowiring->expects($this->once())->method('getDependencies')
            ->with(stdClass::class)
            ->willReturn([]);
        $this->item->expects($this->once())->method('isHit')->willReturn(false);
        $this->item->expects($this->once())->method('get')->willReturn([]);
        $this->item->expects($this->once())->method('set')->with([]);
        $this->pool->expects($this->once())->method('getItem')
            ->with(stdClass::class)
            ->willReturn($this->item);
        $this->pool->expects($this->once())->method('save');
        assert($this->container instanceof ContainerInterface);
        $this->assertInstanceOf(stdClass::class, $this->provider->provide(stdClass::class)($this->container));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvideInvalidCacheKeyException(): void
    {
        $this->expectException(InvalidCacheKey::class);
        $name = 'some_invalide_cache_key';
        $this->pool->expects($this->once())->method('getItem')->with($name)->willThrowException(
            new class extends RuntimeException implements InvalidArgumentException
            {
            }
        );
        assert($this->container instanceof ContainerInterface);
        $this->provider->provide($name);
    }

    public function testGetCacheKeyForLongValue(): void
    {
        $id = str_repeat('a', 65);
        $this->assertSame(hash('sha256', $id), AutowiringCache::getCacheKey($id));
        $this->assertSame(
            str_repeat('a', 64),
            AutowiringCache::getCacheKey(str_repeat('a', 64))
        );
    }

    public function testGetCacheKeyWithIllegalCharters(): void
    {
        $id = 'a\\=+^';
        $this->assertSame('a____', AutowiringCache::getCacheKey($id));
    }
}
