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
 */
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
     * @param Compiler|null $compiler
     * @param Filesystem|null $filesystem
     * @return ContainerInterface
     *
     * @throws IOExceptionInterface if file not writable, or other problem with IO
     * @throws InfiniteRecursion Invalid compile, infinite recursion in dependencies
     * @throws InvalidContainerForCompile
     * @throws NotInstantiable if container cant be created (interface or abstract class)
     */
    public static function build(
        string $filename,
        callable $provider,
        array $params = [],
        array $definitions = [],
        string $fqcn = 'App\Container',
        bool $force_compile = false,
        ?Compiler $compiler = null,
        ?Filesystem $filesystem = null
    ): ContainerInterface {
        $compiler = $compiler ?? new Compiler();
        $filesystem = $filesystem ?? new Filesystem();
        if (
            !$filesystem->exists($filename)
            || $force_compile
        ) {
            $filesystem->dumpFile(
                $filename,
                $compiler->compile(
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
