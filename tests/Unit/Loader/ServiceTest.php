<?php

namespace Cekta\DI\Test\Loader;

use Cekta\DI\Loader\Service;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ServiceTest extends TestCase
{
    final public function testInvoke(): void
    {
        $this->assertEquals(
            'test',
            (new Service(static function () {
                return 'test';
            }))($this->createMock(ContainerInterface::class))
        );
    }

    final public function testInvokeGenerate(): void
    {
        /** @expected mysql:name */
        $this->assertEquals(
            'mysql:test',
            (new Service(static function (ContainerInterface $c) {
                return $c->get('type').$c->get('name');
            }))(
                new class implements ContainerInterface
                {
                    public function get($id)
                    {
                        return [
                            'type' => 'mysql:',
                            'name' => 'test'
                        ][$id];
                    }

                    public function has($id)
                    {
                        return true;
                    }
                }
            )
        );
    }
}
