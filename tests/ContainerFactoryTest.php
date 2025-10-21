<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\ContainerFactory;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\NotInstantiable;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class ContainerFactoryTest extends TestCase
{
    /**
     * @throws InvalidContainerForCompile
     * @throws IOExceptionInterface
     * @throws InfiniteRecursion
     * @throws NotInstantiable
     * @throws Exception
     */
    public function testForceCompile(): void
    {
        $compiler = $this->createMock(Compiler::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('exists')
            ->willReturn(true);
        $compiler->expects($this->once())
            ->method('compile');
        $factory = new ContainerFactory(
            $compiler,
            $filesystem
        );
        $factory->make(
            filename: 'not important3',
            fqcn: get_class($this->createMock(ContainerInterface::class)),
            force_compile: true,
        );
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
            filename: 'not important2',
            fqcn: get_class($this->createMock(ContainerInterface::class)),
        );
    }
}
