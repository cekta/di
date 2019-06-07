<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit;

use Cekta\DI\Container;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @covers \Cekta\DI\Container
 */
class ContainerTest extends TestCase
{
    public function testGetInvalidName()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `invalid name` not found');

        $container = new Container(...[]);
        $container->get('invalid name');
    }

    public function testGet()
    {
        $provider = new class implements ProviderInterface
        {
            public function provide(string $name, ContainerInterface $container)
            {
                return 123;
            }

            public function hasProvide(string $name): bool
            {
                return true;
            }
        };
        $container = new Container($provider);
        $this->assertEquals(123, $container->get('a'));
    }

    public function testHas()
    {
        $provider = new class implements ProviderInterface
        {

            public function provide(string $name, ContainerInterface $container)
            {
                return 123;
            }

            public function hasProvide(string $name): bool
            {
                return $name === 'magic' ? true : false;
            }
        };
        $container = new Container($provider);
        $this->assertTrue($container->has('magic'));
        $this->assertFalse($container->has('invalid name'));
    }
}
