<?php

namespace Cekta\DI\Test;

use Cekta\DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class InvalidContainerTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        unlink(__DIR__ . '/InvalidContainerCompiled.php');
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testDynamic(): void
    {
        $builder = new ContainerBuilder();
        $this->scenario($builder->build());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCompiled(): void
    {
        /** @psalm-var  class-string<object> $fqcn */
        $fqcn = 'Cekta\\DI\\Test\\InvalidContainerCompiled';
        $builder = new ContainerBuilder();
        $builder->fqcn($fqcn);
        file_put_contents(__DIR__ . '/InvalidContainerCompiled.php', $builder->compile([]));
        $container = $builder->build();
        $this->assertInstanceOf($fqcn, $container);
        $this->scenario($container);
    }

    /**
     * @param ContainerInterface $container
     * @return void
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private function scenario(ContainerInterface $container): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `some invalid container` not found');
        $name = 'some invalid container';
        $this->assertFalse($container->has($name));
        $container->get($name);
    }
}
