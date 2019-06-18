<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Loader;

use Cekta\DI\Loader\Service;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ServiceTest extends TestCase
{
    public function testInvoke(): void
    {
        $service = new Service(static function () {
            return 'test';
        });

        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        static::assertEquals('test', $service($container));
    }

    public function testInvokeDeep(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $container->expects(static::never())->method('has');
        $container->expects(static::exactly(2))->method('get')
            ->willReturnCallback(static function ($id) {
                return ['type' => 'mysql', 'name' => 'test'][$id];
            });

        $service = new Service(static function (ContainerInterface $c) {
            return "{$c->get('type')}:{$c->get('name')}";
        });

        static::assertEquals('mysql:test', $service($container));
    }
}
