<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\InvalidLoaderResult;
use Cekta\DI\Exception\NotInstantiable;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @external
 */
class Container
{
    /**
     * @param string $filename
     * @param callable(): LoaderDTO $loader
     * @param array<string, mixed> $params
     * @param string $fqcn
     * @param bool $force_compile
     * @param Compiler|null $compiler
     * @param Filesystem|null $filesystem
     * @return ContainerInterface
     *
     * @throws IOExceptionInterface if file not writable, or other problem with IO
     * @throws InfiniteRecursion Invalid compile, infinite recursion in dependencies
     * @throws InvalidContainerForCompile
     * @throws NotInstantiable if container cant be created (interface or abstract class)
     * @throws InvalidLoaderResult if loader return not DTO
     */
    public static function build(
        string $filename,
        callable $loader,
        array $params = [],
        string $fqcn = 'App\Container',
        bool $force_compile = false,
        ?Compiler $compiler = null,
        ?Filesystem $filesystem = null,
    ): ContainerInterface {
        $filesystem = $filesystem ?? new Filesystem();
        if (
            !$filesystem->exists($filename)
            || $force_compile
        ) {
            if ($compiler === null) {
                $dto = call_user_func($loader);
                /**
                 * @var mixed $dto user $loader can return anything
                 * @noinspection PhpRedundantVariableDocTypeInspection
                 */
                if (!($dto instanceof LoaderDTO)) {
                    throw new InvalidLoaderResult();
                }
                /** @var LoaderDTO $dto */
                $compiler = new Compiler(
                    containers: $dto->getContainers(),
                    params: $params,
                    alias: $dto->getAlias(),
                    fqcn: $fqcn,
                    singletons: $dto->getSingletons(),
                    factories: $dto->getFactories(),
                    rule: $dto->getRule(),
                );
            }
            $filesystem->dumpFile($filename, $compiler->compile());
        }

        $result = new $fqcn($params);
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
