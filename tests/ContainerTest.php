<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Container;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\LoaderMustReturnDTO;
use Cekta\DI\Exception\NotInstantiable;
use Cekta\DI\LoaderDTO;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class ContainerTest extends TestCase
{
    /**
     * @throws InvalidContainerForCompile
     * @throws Exception
     * @throws IOExceptionInterface
     * @throws InfiniteRecursion
     * @throws NotInstantiable
     */
    public function testFileMustBeCompiledIfNotExist(): void
    {
        $filename = __DIR__ . '/not important1';
        $compiler = $this->createMock(Compiler::class);
        $filesystem = $this->createMock(Filesystem::class);
        $compiler->expects($this->once())
            ->method('compile');
        $filesystem->method('exists')
            ->willReturn(false);
        Container::build(
            filename: $filename,
            loader: function () {
                return new LoaderDTO();
            },
            fqcn: get_class($this->createMock(ContainerInterface::class)),
            compiler: $compiler,
            filesystem: $filesystem
        );
        if (file_exists($filename)) {
            unlink($filename);
        }
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
        Container::build(
            filename: 'not important2',
            loader: function () {
                return [];
            },
            fqcn: get_class($this->createMock(ContainerInterface::class)),
            compiler: $compiler,
            filesystem: $filesystem
        );
    }

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
        Container::build(
            filename: 'not important3',
            loader: function () {
                return new LoaderDTO();
            },
            fqcn: get_class($this->createMock(ContainerInterface::class)),
            force_compile: true,
            compiler: $compiler,
            filesystem: $filesystem,
        );
    }

    /**
     * @throws IOExceptionInterface
     * @throws NotInstantiable
     * @throws InvalidContainerForCompile
     * @throws InfiniteRecursion
     */
    public function testMakeResultMustImplementContainerInterface(): void
    {
        $fqcn = stdClass::class;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Invalid fqcn: `%s`, must be instanceof %s', $fqcn, ContainerInterface::class)
        );

        Container::build(
            filename: __FILE__,
            loader: function () {
                return [];
            },
            fqcn: $fqcn,
        );
    }

    /**
     * @throws InvalidContainerForCompile
     * @throws IOExceptionInterface
     * @throws InfiniteRecursion
     * @throws NotInstantiable
     */
    public function testCantCreateFile(): void
    {
        $this->expectException(IOExceptionInterface::class);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('dumpFile')
            ->willThrowException(new IOException('some message'));
        Container::build(
            filename: 'not important4',
            loader: function () {
                return new LoaderDTO();
            },
            force_compile: true,
            filesystem: $filesystem
        );
    }

    /**
     * @throws IOExceptionInterface
     * @throws InfiniteRecursion
     * @throws NotInstantiable
     * @throws InvalidContainerForCompile
     */
    public function testLoaderReturnInvalidResult(): void
    {
        $this->expectException(LoaderMustReturnDTO::class);
        Container::build('test.php', function () {
            return 'invalid result';
        });
    }
}
