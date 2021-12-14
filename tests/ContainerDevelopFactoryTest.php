<?php

namespace Cekta\DI\Test;

use Cekta\DI\Container;
use Cekta\DI\ContainerDevelopFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

class ContainerDevelopFactoryTest extends TestCase
{
    public function testMake(): void
    {
        $factory = new ContainerDevelopFactory();
        $result = $factory->make([], [], []);
        $this->assertSame(Container::class, get_class($result));
    }

    public function testMakeCustom(): void
    {
        $factory = new ContainerDevelopFactory(ContainerDevelop::class);
        $params = ['test_param' => 'value'];
        $interfaces = ['test_interface' => 'value'];
        $definitions = ['test_definition' => 'value'];
        $result = $factory->make($params, $interfaces, $definitions);
        assert($result instanceof ContainerDevelop);
        $this->assertSame($params, $result->params);
        $this->assertSame($interfaces, $result->interfaces);
        $this->assertSame($definitions, $result->definitions);
    }

    public function testReturnNotContainerInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('make must return instance of ContainerInterface');
        $factory = new ContainerDevelopFactory(stdClass::class);
        $factory->make([], [], []);
        $this->assertTrue(true);
    }
}
