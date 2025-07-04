<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\ContainerFactory;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\NotInstantiable;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class ContainerFactoryTest extends TestCase
{
    private MockObject $compiler_mock;
    private MockObject $filesystem;
    private ContainerFactory $factory;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->compiler_mock = $this->createMock(Compiler::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->factory = new ContainerFactory($this->compiler_mock, $this->filesystem);
    }

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
        $this->compiler_mock->expects($this->once())
            ->method('compile');
        $this->filesystem->method('exists')
            ->willReturn(false);

        $this->factory->make(
            $filename,
            get_class($this->createMock(ContainerInterface::class))
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
        $this->filesystem->method('exists')
            ->willReturn(true);
        $this->compiler_mock->expects($this->never())
            ->method('compile');
        $this->factory->make(
            'not important2',
            get_class($this->createMock(ContainerInterface::class))
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
        $this->filesystem->method('exists')
            ->willReturn(true);
        $this->compiler_mock->expects($this->once())
            ->method('compile');
        $this->factory->make(
            'not important3',
            get_class($this->createMock(ContainerInterface::class)),
            true
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

        $this->factory->make(filename: __FILE__, fqcn: $fqcn);
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

        $this->filesystem->method('dumpFile')
            ->willThrowException(new IOException('some message'));
        $this->factory->make(filename: 'not important4', force_compile: true);
    }
}
