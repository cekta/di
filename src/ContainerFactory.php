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

/**
 * @external
 * @deprecated will be removed,  use \Cekta\DI\Container
 */
class ContainerFactory
{
    private ?Compiler $compiler;
    private Filesystem $filesystem;

    public function __construct(
        ?Compiler $compiler = null,
        ?Filesystem $filesystem = null
    ) {
        $this->compiler = $compiler;
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    /**
     * @param array<string> $containers
     * @param array<string, mixed> $params
     * @param array<string, string> $alias
     * @param array<string, callable> $definitions
     *
     * @throws NotInstantiable if container cant be created (interface or abstract class)
     * @throws InfiniteRecursion Invalid compile, infinite recursion in dependencies
     * @throws IOExceptionInterface if file not writable, or other problem with IO
     * @throws InvalidArgumentException if fqcn result not implement ContainerInterface, or cant create Container
     * @throws InvalidContainerForCompile
     */
    public function make(
        string $filename,
        string $fqcn = 'App\Container',
        bool $force_compile = false,
        array $containers = [],
        array $params = [],
        array $alias = [],
        array $definitions = [],
    ): ContainerInterface {
        foreach ($definitions as $key => $definition) {
            if (!array_key_exists($key, $params)) {
                $params[$key] = new LazyClosure($definition);
            }
        }
        return Container::build(
            filename: $filename,
            loader: function () use ($containers, $alias) {
                return new LoaderDTO(
                    containers: $containers,
                    alias: $alias,
                );
            },
            params: $params,
            fqcn: $fqcn,
            force_compile: $force_compile,
            compiler: $this->compiler,
            filesystem: $this->filesystem,
        );
    }
}
