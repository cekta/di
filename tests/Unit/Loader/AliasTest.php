<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Loader;

use Cekta\DI\Loader\Alias;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;

class AliasTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testInvoke()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->with($this->equalTo('a'))
            ->willReturn(123);
        $alias = new Alias('a');
        /** @var ContainerInterface $container */
        $this->assertEquals(123, $alias($container));
    }
}
