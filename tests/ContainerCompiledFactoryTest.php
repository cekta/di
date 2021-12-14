<?php

namespace Cekta\DI\Test;

use Cekta\DI\ContainerCompiledFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ContainerCompiledFactoryTest extends TestCase
{
    public function testMakeCustom(): void
    {
        $factory = new ContainerCompiledFactory(ContainerCompiled::class);
        $params = ['test_param' => 'value'];
        $result = $factory->make($params);
        assert($result instanceof ContainerCompiled);
        $this->assertSame($params, $result->params);
    }

    public function testIsClassExist(): void
    {
        $factory = new ContainerCompiledFactory();
        $this->assertFalse($factory->isClassExist());
        $factory = new ContainerCompiledFactory(ContainerCompiled::class);
        $this->assertTrue($factory->isClassExist());
    }

    public function testReturnNotContainerInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('make must return instance of ContainerInterface');
        $factory = new ContainerCompiledFactory(\stdClass::class);
        $factory->make([]);
    }
}
