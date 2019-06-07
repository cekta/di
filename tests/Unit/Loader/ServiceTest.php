<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Loader;

use Cekta\DI\Loader\Service;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;

class ServiceTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testInvoke()
    {
        $service = new Service(function () {
            return 123;
        });
        $container = $this->createMock(ContainerInterface::class);
        /** @var ContainerInterface $container */
        $this->assertEquals(123, $service($container));
    }

    public function testInvokeGenerate()
    {
        $container = new class implements ContainerInterface
        {
            public function get($id)
            {
                $a = [
                    'type' => 'mysql',
                    'name' => 'test'
                ];
                return $a[$id];
            }

            public function has($id)
            {
                return true;
            }
        };
        $service = new Service(function (ContainerInterface $c) {
            return "{$c->get('type')}://dbName={$c->get('name')}";
        });
        $this->assertEquals('mysql://dbName=test', $service($container));
    }
}
