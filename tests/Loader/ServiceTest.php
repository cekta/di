<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Loader;

use Cekta\DI\Loader\Service;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

class ServiceTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testInvoke(): void
    {
        $service = new Service(static function () {
            return 'test';
        });
        assert($this->container instanceof ContainerInterface);
        $this->assertEquals('test', $service($this->container));
    }

    public function testInvokeDeep(): void
    {
        $this->container->expects(static::never())->method('has');
        $this->container->expects(static::exactly(2))->method('get')
            ->willReturnCallback(function ($id) {
                return ['type' => 'mysql', 'name' => 'test'][$id];
            });
        $service = new Service(function (ContainerInterface $c) {
            return "{$c->get('type')}:{$c->get('name')}";
        });
        assert($this->container instanceof ContainerInterface);
        $this->assertEquals('mysql:test', $service($this->container));
    }

    public function testCreateObjectWithoutArgument()
    {
        $service = Service::createObject(stdClass::class, []);
        assert($this->container instanceof ContainerInterface);
        $this->assertInstanceOf(stdClass::class, $service($this->container));
    }

    public function testCreateObjectWithArguments()
    {
        $obj = new class ()
        {
            private $a;
            private $b;

            public function __construct(int $a = 1, int $b = 1)
            {
                $this->a = $a;
                $this->b = $b;
            }
        };
        $name = get_class($obj);
        $service = Service::createObject($name, ['a', 'b']);
        $this->container->expects($this->exactly(2))->method('get')->willReturnMap([
            ['a', 5],
            ['b', 6]
        ]);
        assert($this->container instanceof ContainerInterface);
        $this->assertInstanceOf($name, $service($this->container));
    }
}
