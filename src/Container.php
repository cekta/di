<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\NotInstantiable;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class Container
{
    /**
     * @param string $filename
     * @param callable(): array{
     *     containers: array<string>,
     *     alias: array<string, string>,
     *     factories: array<string>,
     *     singletons: array<string>} $provider
     * @param array<string, mixed> $params
     * @param array<string, callable> $definitions
     * @param string $fqcn
     * @param bool $force_compile
     *
     * @return ContainerInterface
     *
     * @throws IOExceptionInterface if file not writable, or other problem with IO
     * @throws InfiniteRecursion Invalid compile, infinite recursion in dependencies
     * @throws InvalidContainerForCompile
     * @throws NotInstantiable if container cant be created (interface or abstract class)
     */
    public static function make(
        string $filename,
        callable $provider,
        array $params = [],
        array $definitions = [],
        string $fqcn = 'App\Container',
        bool $force_compile = false,
    ): ContainerInterface {
        $builder = new Container(
            new Compiler(),
            new Filesystem()
        );
        return $builder->build(
            $filename,
            $provider,
            $params,
            $definitions,
            $fqcn,
            $force_compile,
        );
    }

    public function __construct(
        private Compiler $compiler,
        private Filesystem $filesystem
    ) {
    }

    /**
     * @param string $filename
     * @param callable(): array{
     *      containers: array<string>,
     *      alias: array<string, string>,
     *      factories: array<string>,
     *      singletons: array<string>} $provider
     * @param array<string, mixed> $params
     * @param array<string, callable> $definitions
     * @param string $fqcn
     * @param bool $force_compile
     *
     * @return ContainerInterface
     *
     * @throws IOExceptionInterface if file not writable, or other problem with IO
     * @throws InfiniteRecursion Invalid compile, infinite recursion in dependencies
     * @throws InvalidContainerForCompile
     * @throws NotInstantiable if container cant be created (interface or abstract class)
     */
    public function build(
        string $filename,
        callable $provider,
        array $params = [],
        array $definitions = [],
        string $fqcn = 'App\Container',
        bool $force_compile = false,
    ): ContainerInterface {
        if (
            !$this->filesystem->exists($filename)
            || $force_compile
        ) {
            $this->filesystem->dumpFile(
                $filename,
                $this->compiler->compile(
                    ...call_user_func($provider) + [
                        'params' => $params,
                        'fqcn' => $fqcn,
                        'definitions' => $definitions
                    ]
                )
            );
        }

        $result = new $fqcn($params, $definitions);
        if (!($result instanceof ContainerInterface)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Invalid fqcn: `$fqcn`, must be instanceof %s",
                    ContainerInterface::class
                )
            );
        }
        return $result;
    }
}
