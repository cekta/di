<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\ContainerFactory;
use Cekta\DI\Exception\InvalidContainerForCompile;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use stdClass;
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
     * @throws IOExceptionInterface
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
        $property = (new ReflectionClass($factory))
            ->getProperty('compiler');
        $property->setAccessible(true);
        $this->assertSame($compiler, $property->getValue($factory));
    }

    /**
     * @throws IOExceptionInterface
     */
    public function testMakeResultMustImplementContainerInterface(): void
    {
        $fqcn = stdClass::class;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Invalid fqcn: `%s`, must be instanceof %s', $fqcn, ContainerInterface::class)
        );
        (new ContainerFactory())->make(
            filename: $this->file,
            fqcn: $fqcn,
        );
    }

    /**
     * @throws IOExceptionInterface
     */
    public function testInvalidContainerForCompile(): void
    {
        $container = 'invalid container name for ReflectionClass';
        $this->expectException(InvalidContainerForCompile::class);
        $this->expectExceptionMessage(
            sprintf(
                'Invalid container:`%s` for compile, stack: %s',
                $container,
                implode(', ', [$container])
            ),
        );
        $factory = new ContainerFactory();
        $factory->make(
            filename: 'any name not important.php',
            containers: [
                $container
            ],
        );
    }
}
