<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\AutowiringCache;
use Cekta\DI\Provider\AutowiringSimpleCache;
use Cekta\DI\Provider\Exception\InvalidCacheKey;
use Cekta\DI\ProviderExceptionInterface;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
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
    /**
     * @var MockObject
     */
    private $container;
    /**
     * @var MockObject
     */
    private $cache;
    /**
     * @var AutowiringSimpleCache
     */
    private $provider;
    /**
     * @var MockObject
     */
    private $autowiring;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->autowiring = $this->createMock(Autowiring::class);
        assert($this->cache instanceof CacheInterface);
        assert($this->autowiring instanceof Autowiring);
        $this->provider = new AutowiringSimpleCache($this->cache, $this->autowiring);
    }

    public function testMustBeProvider(): void
    {
        $this->assertInstanceOf(ProviderInterface::class, $this->provider);
    }

    public function testCanProvide(): void
    {
        $this->assertTrue($this->provider->canProvide(stdClass::class));
    }

    public function testCanProvideInvalideName(): void
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
    public function testProvideCacheHit(): void
    {
        $this->autowiring->expects($this->never())->method('getDependencies');
        $this->cache->expects($this->once())->method('get')
            ->with(stdClass::class)
            ->willReturn([]);
        assert($this->container instanceof ContainerInterface);
        $this->assertEquals(new stdClass(), $this->provider->provide(stdClass::class)($this->container));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvideCacheMiss(): void
    {
        $this->autowiring->expects($this->once())->method('getDependencies')
            ->with(stdClass::class)
            ->willReturn([]);
        $this->cache->expects($this->once())->method('set')
            ->with(stdClass::class, []);
        $this->cache->expects($this->once())->method('get')
            ->with(stdClass::class)
            ->willReturn(null);
        assert($this->container instanceof ContainerInterface);
        $result = $this->provider->provide(stdClass::class)($this->container);
        $this->assertInstanceOf(stdClass::class, $result);
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvideWithArguments()
    {
        $obj = new class () {
            public $a;
            public $b;

            public function __construct($a = 1, $b = 2)
            {
                $this->a = $a;
                $this->b = $b;
            }
        };
        $this->cache->method('get')
            ->with(AutowiringCache::getCacheKey(get_class($obj)))
            ->willReturn(['a', 'b']);
        $this->container->method('get')
            ->willReturnCallback(function (string $name) {
                $a = [
                    'a' => 5,
                    'b' => 6
                ];
                return $a[$name];
            });
        $result = $this->provider->provide(get_class($obj))($this->container);
        $this->assertSame(5, $result->a);
        $this->assertSame(6, $result->b);
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvideInvalidCacheKey(): void
    {
        $this->expectException(InvalidCacheKey::class);
        $name = 'some_invalide_cache_key';
        $exception = new class () extends RuntimeException implements InvalidArgumentException {
        };
        $this->cache->expects($this->once())->method('get')
            ->with($name)
            ->willThrowException($exception);
        assert($this->container instanceof ContainerInterface);
        $this->provider->provide($name);
    }
}
