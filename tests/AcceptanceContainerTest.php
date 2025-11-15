<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Container;
use Cekta\DI\Exception\InvalidLoaderResult;
use Cekta\DI\LoaderDTO;
use Cekta\DI\Test\AcceptanceTest\A;
use Cekta\DI\Test\AcceptanceTest\EntrypointAutowiring;
use Cekta\DI\Test\AcceptanceTest\EntrypointBugOfAlias;
use Cekta\DI\Test\AcceptanceTest\EntrypointSharedDependency;
use Cekta\DI\Test\AcceptanceTest\S;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class AcceptanceContainerTest extends AcceptanceBase
{
    /**
     * @throws IOExceptionInterface
     */
    protected function makeContainer(): ContainerInterface
    {
        return Container::build(
            filename: $this->file,
            loader: function () {
                return new LoaderDTO(
                    containers: $this->containers,
                    alias: $this->alias,
                );
            },
            params: $this->params,
            fqcn: $this->fqcn,
            force_compile: true,
        );
    }

    public function testWithoutRequiredParams(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Containers: %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s must be declared in params',
                'username',
                'password',
                S::class . '|string',
                'dsn',
                'argument_to_custom_param',
                'argument_to_custom_alias_value',
                EntrypointSharedDependency::class . '$argument_to_custom_param',
                'argument_to_custom_alias_custom_value',
                '...' . EntrypointSharedDependency::class . '$variadic_int',
                '...variadic_int',
                '...' . A::class,
            )
        );
        new ($this->fqcn)([]);
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
     */
    public function testLoaderReturnInvalidResult(): void
    {
        $this->expectException(InvalidLoaderResult::class);
        $this->expectExceptionMessage(sprintf('callable must return instanceof %s', LoaderDTO::class));
        Container::build(
            filename: $this->file,
            // @phpstan-ignore-next-line
            loader: function () {
                return 'invalid result';
            },
            force_compile: true
        );
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws IOExceptionInterface
     * @throws NotFoundExceptionInterface
     * @see https://github.com/cekta/di/issues/146
     */
    public function testParamMaxPriority(): void
    {
        $expected = 'value from params';
        $filename = __DIR__ . '/ContainerTeatParamMaxPriority.php';
        file_exists($filename) && unlink($filename);
        $container = Container::build(
            filename: $filename,
            fqcn: 'Cekta\DI\Test\ContainerTeatParamMaxPriority',
            params: [
                'some_argument_name' => $expected,
            ],
            loader: function () {
                return new LoaderDTO(
                    containers: [EntrypointBugOfAlias::class],
                    alias: [
                        'some_argument_name' => 'invalid name',
                    ],
                );
            }
        );
        $this->assertSame(
            $expected,
            $container->get(AcceptanceTest\EntrypointBugOfAlias::class)->some_argument_name
        );
        file_exists($filename) && unlink($filename);
    }
}
