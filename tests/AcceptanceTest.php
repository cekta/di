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
use Cekta\DI\Rule\Regex;
use Cekta\DI\Test\AcceptanceTest\A;
use Cekta\DI\Test\AcceptanceTest\EntrypointApplyRule;
use Cekta\DI\Test\AcceptanceTest\S;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class AcceptanceTest extends AcceptanceBase
{
    /**
     * @throws IOExceptionInterface
     * @throws InfiniteRecursion
     * @throws NotInstantiable
     * @throws LoaderMustReturnDTO
     * @throws InvalidContainerForCompile
     */
    protected function makeContainer(): ContainerInterface
    {
        return Container::build(
            filename: $this->file,
            loader: function () {
                return new LoaderDTO(
                    containers: array_merge($this->containers, [EntrypointApplyRule::class]),
                    alias: $this->alias,
                    rule: new Regex('/EntrypointApplyRule/', ['username' => 'db_username'])
                );
            },
            params: $this->params,
            fqcn: $this->fqcn,
            force_compile: true,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testApplyRule(): void
    {
        /** @var EntrypointApplyRule $obj */
        $obj = $this->container->get(EntrypointApplyRule::class);
        $this->assertSame($this->params['db_username'], $obj->username);
        $this->assertSame($this->params['password'], $obj->password);
    }

    public function testWithoutRequiredParams(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Containers: %s, %s, %s, %s, %s, %s, %s must be declared in params',
                'username',
                'password',
                S::class . '|string',
                'dsn',
                '...variadic_int',
                '...' . A::class,
                'db_username',
            )
        );
        new ($this->fqcn)([]);
    }

    /**
     * @throws InvalidContainerForCompile
     * @throws Exception
     * @throws IOExceptionInterface
     * @throws InfiniteRecursion
     * @throws NotInstantiable
     * @throws LoaderMustReturnDTO
     */
    public function testFileMustBeNOTCompiledIfExist(): void
    {
        $compiler = $this->createMock(Compiler::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('exists')
            ->willReturn(true);
        $compiler->expects($this->never())
            ->method('compile');
        $filename = 'some file not exist.php';
        Container::build(
            filename: $filename,
            loader: function () {
                return new LoaderDTO();
            },
            fqcn: get_class($this->createMock(ContainerInterface::class)),
            compiler: $compiler,
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
        Container::build(
            filename: $this->file,
            loader: function () {
                return 'invalid result';
            },
            force_compile: true
        );
    }

    /**
     * @throws IOExceptionInterface
     * @throws NotInstantiable
     * @throws InvalidContainerForCompile
     * @throws InfiniteRecursion
     * @throws LoaderMustReturnDTO
     */
    public function testMakeResultMustImplementContainerInterface(): void
    {
        $fqcn = \stdClass::class;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Invalid fqcn: `%s`, must be instanceof %s', $fqcn, ContainerInterface::class)
        );

        Container::build(
            filename: $this->file,
            loader: function () {
                return new LoaderDTO();
            },
            fqcn: $fqcn,
        );
    }

    /**
     * @throws InvalidContainerForCompile
     * @throws Exception
     * @throws IOExceptionInterface
     * @throws InfiniteRecursion
     * @throws NotInstantiable
     * @throws LoaderMustReturnDTO
     */
    public function testFileMustBeCompiledIfNotExist(): void
    {
        $filename = __DIR__ . '/' . __CLASS__ . '.' . __METHOD__;
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
}
