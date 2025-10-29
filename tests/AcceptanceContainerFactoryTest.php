<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\ContainerFactory;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\NotInstantiable;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * only for backward compatibility with ContainerFactory
 * @deprecated use Container
 */
class AcceptanceContainerFactoryTest extends AcceptanceBase
{
    protected string $file = __DIR__ . '/AcceptanceTest/ContainerFactory.php';
    protected string $fqcn = 'Cekta\DI\Test\AcceptanceTest\ContainerFactory';

    protected function makeContainer(): ContainerInterface
    {
        $factory = new ContainerFactory();
        return $factory->make(
            filename: $this->file,
            fqcn: $this->fqcn,
            force_compile: true,
            containers: $this->containers,
            params: $this->params,
            alias: $this->alias,
            definitions: [
                'example-def' => function () {
                    return 123;
                }
            ],
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDefinitionToLazy(): void
    {
        $this->assertSame(123, $this->container->get('example-def'));
    }

    /**
     * @throws InvalidContainerForCompile
     * @throws Exception
     * @throws IOExceptionInterface
     * @throws InfiniteRecursion
     * @throws NotInstantiable
     */
    public function testFileMustBeNOTCompiledIfExist(): void
    {
        $compiler = $this->createMock(Compiler::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('exists')
            ->willReturn(true);
        $compiler->expects($this->never())
            ->method('compile');
        $factory = new ContainerFactory(
            $compiler,
            $filesystem
        );
        $factory->make(
            filename: __CLASS__ . '.' . __METHOD__,
            fqcn: get_class($this->createMock(ContainerInterface::class)),
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testCompilerOverload(): void
    {
        $compiler = $this->createMock(Compiler::class);
        $factory = new ContainerFactory($compiler);
        $property = (new \ReflectionClass($factory))
            ->getProperty('compiler');
        $property->setAccessible(true);
        $this->assertSame($compiler, $property->getValue($factory));
    }
}
