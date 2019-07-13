<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\Provider\AutowiringSimpleCache;
use Cekta\DI\Provider\Exception\ClassNotCreated;
use Cekta\DI\Provider\Exception\InvalidCacheKey;
use Cekta\DI\ProviderException;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;
use stdClass;

/**
 * @covers \Cekta\DI\Provider\AutowiringSimpleCache
 */
class AutowiringSimpleCacheTest extends TestCase
{
    public function testMustBeProvider(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        assert($cache instanceof CacheInterface);
        $this->assertInstanceOf(ProviderInterface::class, new AutowiringSimpleCache($cache));
    }

    public function testCanProvide(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        assert($cache instanceof CacheInterface);
        $provider = new AutowiringSimpleCache($cache);
        $this->assertTrue($provider->canProvide(stdClass::class));
    }

    public function testCanProvideInvalidName(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        assert($cache instanceof CacheInterface);
        $provider = new AutowiringSimpleCache($cache);
        $this->assertFalse($provider->canProvide('invalid name'));
    }

    public function testCanProvideInterface(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        assert($cache instanceof CacheInterface);
        $provider = new AutowiringSimpleCache($cache);
        $this->assertFalse($provider->canProvide(ProviderInterface::class));
    }

    /**
     * @throws ProviderException
     */
    public function testProvideCacheHit(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())->method('has')
            ->with(stdClass::class)
            ->willReturn(true);
        $cache->expects($this->once())->method('get')
            ->with(stdClass::class)
            ->willReturn([]);
        assert($cache instanceof CacheInterface);
        $provider = new AutowiringSimpleCache($cache);
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $this->assertEquals(new stdClass(), $provider->provide(stdClass::class, $container));
    }

    /**
     * @throws ProviderException
     */
    public function testProvideCacheMiss(): void
    {
        $obj = new class(new stdClass(), 5)
        {
            public $class;
            public $value;

            public function __construct(stdClass $class, int $value)
            {
                $this->class = $class;
                $this->value = $value;
            }
        };
        $name = get_class($obj);
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())->method('has')
            ->with($name)
            ->willReturn(false);
        $cache->expects($this->once())->method('set')
            ->with($name, [stdClass::class, 'value']);
        $cache->expects($this->once())->method('get')
            ->with($name)
            ->willReturn([stdClass::class, 'value']);
        assert($cache instanceof CacheInterface);
        $provider = new AutowiringSimpleCache($cache);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('get')
            ->willReturnMap([
                [stdClass::class, new stdClass()],
                ['value', 6]
            ]);
        assert($container instanceof ContainerInterface);
        $result = $provider->provide($name, $container);
        $this->assertInstanceOf($name, $result);
    }

    /**
     * @throws ProviderException
     */
    public function testProvideClassNotCreated(): void
    {
        $this->expectException(ClassNotCreated::class);
        $name = 'invalid name';
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())->method('has')
            ->with($name)
            ->willReturn(false);
        assert($cache instanceof CacheInterface);
        $provider = new AutowiringSimpleCache($cache);
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $provider->provide($name, $container);
    }

    /**
     * @throws ProviderException
     */
    public function testProvideInvaliCacheKey(): void
    {
        $this->expectException(InvalidCacheKey::class);
        $name = 'some invalide cache key{}';
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())->method('has')
            ->with($name)
            ->willThrowException(new class() extends RuntimeException implements InvalidArgumentException
            {
            });
        assert($cache instanceof CacheInterface);
        $provider = new AutowiringSimpleCache($cache);
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $provider->provide($name, $container);
    }
}
