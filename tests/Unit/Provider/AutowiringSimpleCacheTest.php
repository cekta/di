<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\Provider\Autowiring;
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
        $autowiring = $this->createMock(Autowiring::class);
        $cache = $this->createMock(CacheInterface::class);
        assert($cache instanceof CacheInterface);
        assert($autowiring instanceof Autowiring);
        $this->assertInstanceOf(ProviderInterface::class, new AutowiringSimpleCache($cache, $autowiring));
    }

    public function testCanProvide(): void
    {
        $autowiring = $this->createMock(Autowiring::class);
        $cache = $this->createMock(CacheInterface::class);
        assert($cache instanceof CacheInterface);
        assert($autowiring instanceof Autowiring);
        $provider = new AutowiringSimpleCache($cache, $autowiring);
        $this->assertTrue($provider->canBeProvided(stdClass::class));
        $this->assertFalse($provider->canBeProvided('invalid name'));
        $this->assertFalse($provider->canBeProvided(ProviderInterface::class));
    }

    /**
     * @throws ProviderException
     */
    public function testProvideCacheHit(): void
    {
        $autowiring = $this->createMock(Autowiring::class);
        $autowiring->expects($this->never())->method('getDependencies');
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())->method('has')
            ->with(stdClass::class)
            ->willReturn(true);
        $cache->expects($this->once())->method('get')
            ->with(stdClass::class)
            ->willReturn([]);
        assert($cache instanceof CacheInterface);
        assert($autowiring instanceof Autowiring);
        $provider = new AutowiringSimpleCache($cache, $autowiring);
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $this->assertEquals(new stdClass(), $provider->provide(stdClass::class, $container));
    }

    /**
     * @throws ProviderException
     */
    public function testProvideCacheMiss(): void
    {
        $name = 'mocked';
        $autowiring = $this->createMock(Autowiring::class);
        $autowiring->expects($this->once())->method('getDependencies')->with($name)->willReturn([
            stdClass::class,
            'value'
        ]);
        $autowiring->expects($this->once())->method('create')->with($name)->willReturn(new stdClass());
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
        assert($autowiring instanceof Autowiring);
        $provider = new AutowiringSimpleCache($cache, $autowiring);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('get')
            ->willReturnMap([
                [stdClass::class, new stdClass()],
                ['value', 6]
            ]);
        assert($container instanceof ContainerInterface);
        $result = $provider->provide($name, $container);
        $this->assertInstanceOf(stdClass::class, $result);
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
