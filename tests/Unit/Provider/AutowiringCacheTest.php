<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\Provider\Autowiring\Reader;
use Cekta\DI\Provider\AutowiringCache;
use Cekta\DI\Provider\Exception\ClassNotCreated;
use Cekta\DI\Provider\Exception\InvalidCacheKey;
use Cekta\DI\ProviderException;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;
use stdClass;

class AutowiringCacheTest extends TestCase
{
    public function testMustBeProvider(): void
    {
        $pool = $this->createMock(CacheItemPoolInterface::class);
        assert($pool instanceof CacheItemPoolInterface);
        $this->assertInstanceOf(ProviderInterface::class, new AutowiringCache($pool));
    }

    public function testCanProvide(): void
    {
        $pool = $this->createMock(CacheItemPoolInterface::class);
        assert($pool instanceof CacheItemPoolInterface);
        $provider = new AutowiringCache($pool);
        $this->assertTrue($provider->canProvide(stdClass::class));
    }

    public function testCanProvideInvalidName(): void
    {
        $pool = $this->createMock(CacheItemPoolInterface::class);
        assert($pool instanceof CacheItemPoolInterface);
        $provider = new AutowiringCache($pool);
        $this->assertFalse($provider->canProvide('invalid name'));
    }

    public function testCanProvideInterface(): void
    {
        $pool = $this->createMock(CacheItemPoolInterface::class);
        assert($pool instanceof CacheItemPoolInterface);
        $provider = new AutowiringCache($pool);
        $this->assertFalse($provider->canProvide(ProviderInterface::class));
    }

    /**
     * @throws ProviderException
     */
    public function testProvideCacheHit()
    {
        $pool = $this->createMock(CacheItemPoolInterface::class);
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())->method('isHit')->willReturn(true);
        $item->expects($this->once())->method('get')->willReturn([]);
        $item->expects($this->never())->method('set');
        $pool->expects($this->once())->method('getItem')->with(stdClass::class)->willReturn($item);
        assert($pool instanceof CacheItemPoolInterface);
        $provider = new AutowiringCache($pool);
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $this->assertInstanceOf(stdClass::class, $provider->provide(stdClass::class, $container));
    }

    /**
     * @throws ProviderException
     */
    public function testProvideCacheMiss(): void
    {
        $obj = new class(new stdClass(), 1)
        {
            /**
             * @var stdClass
             */
            public $class;
            /**
             * @var int
             */
            public $value;

            public function __construct(stdClass $class, int $value)
            {
                $this->class = $class;
                $this->value = $value;
            }
        };
        $name = get_class($obj);
        $pool = $this->createMock(CacheItemPoolInterface::class);
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())->method('isHit')->willReturn(false);
        $item->expects($this->once())->method('get')->willReturn([stdClass::class, 'value']);
        $item->expects($this->once())->method('set')->with([stdClass::class, 'value']);
        $pool->expects($this->once())->method('getItem')->with($name)->willReturn($item);
        assert($pool instanceof CacheItemPoolInterface);
        $provider = new AutowiringCache($pool);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('get')->willReturnMap([
            [stdClass::class, new stdClass()],
            ['value', 6]
        ]);
        assert($container instanceof ContainerInterface);
        $result = $provider->provide($name, $container);
        $this->assertInstanceOf($name, $result);
        $this->assertSame(6, $result->value);
    }

    /**
     * @throws ProviderException
     */
    public function testProvideNotCreated(): void
    {
        $this->expectException(ClassNotCreated::class);
        $name = 'class not created';
        $pool = $this->createMock(CacheItemPoolInterface::class);
        $item = $this->createMock(CacheItemInterface::class);
        $pool->method('getItem')->with($name)->willReturn($item);
        assert($pool instanceof CacheItemPoolInterface);
        $provider = new AutowiringCache($pool);
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $provider->provide($name, $container);
    }

    /**
     * @throws ProviderException
     */
    public function testProvideInvalidCacheKeyException(): void
    {
        $this->expectException(InvalidCacheKey::class);
        $name = 'some invalide cache key';
        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->expects($this->once())->method('getItem')->with($name)->willThrowException(
            new class extends RuntimeException implements InvalidArgumentException
            {
            }
        );
        assert($pool instanceof CacheItemPoolInterface);
        $provider = new AutowiringCache($pool);
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $provider->provide($name, $container);
    }

//    /**
//     * @throws ProviderException
//     */
//    public function testProvideInvalidCacheKeyTransform(): void
//    {
//        $name = '{}()/\@:test';
//        $transform = '........test';
//        $value = 'value';
//        $item = $this->createMock(CacheItemInterface::class);
//        $item->method('isHit')->willReturn(false);
//        $pool = $this->createMock(CacheItemPoolInterface::class);
//        $pool->method('getItem')->with($transform)->willReturn($item);
//        $reader = $this->createMock(Reader::class);
//        $reader->method('getDependencies')->with($name)->willReturn([]);
//        $provider = new AutowiringCache($pool, $reader);
//        $container = $this->createMock(ContainerInterface::class);
//        $result = $provider->provide($name, $container);
//    }
}
