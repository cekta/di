<?php

namespace Cekta\DI\Test;

use Cekta\DI\ContainerBuilder;
use Cekta\DI\Test\Fixture\Example;
use Generator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class InvalidContainerTest extends TestCase
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     */
    public function test(): void
    {
        $builder = new ContainerBuilder();
        $this->assert($builder->build());

        $builder->fqcn('Cekta\\DI\\Test\\InvalidContainerCompiled');
        file_put_contents(__DIR__ . '/InvalidContainerCompiled.php', $builder->compile([]));
        $this->assert($builder->build());
        unlink(__DIR__ . '/InvalidContainerCompiled.php');
    }

    /**
     * @param ContainerInterface $container
     * @return void
     * @throws NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function assert(ContainerInterface $container): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `some invalid container` not found');
        $name = 'some invalid container';
        $this->assertFalse($container->has($name));
        $container->get($name);
    }
}
