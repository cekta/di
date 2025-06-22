<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\NotInstantiable;
use Psr\Container\ContainerInterface;
use ReflectionException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use UnexpectedValueException;

/**
 * @external
 */
class ContainerFactory
{
    private Compiler $compiler;
    private Filesystem $filesystem;

    public function __construct(
        ?Compiler $compiler = null,
        ?Filesystem $filesystem = null
    ) {
        $this->compiler = $compiler ?? new Compiler();
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    /**
     * @param string $filename
     * @param string $fqcn
     * @param bool $force_compile
     * @param array<string> $containers
     * @param array<string, mixed> $params
     * @param array<string, string> $alias
     * @param array<string, callable> $definitions
     * @return ContainerInterface
     * @throws NotInstantiable if container interface or abstract class
     * @throws ReflectionException if cant create ReflectionClass for dependency
     * @throws IOExceptionInterface if file not writable
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
        if (!$this->filesystem->exists($filename) || $force_compile) {
            $this->filesystem->dumpFile(
                $filename,
                $this->compiler->compile(
                    containers: $containers,
                    params: $params,
                    alias: $alias,
                    definitions: $definitions,
                    fqcn: $fqcn,
                )
            );
        }
        $result = new $fqcn($params, $definitions);
        if (!($result instanceof ContainerInterface)) {
            throw new UnexpectedValueException(
                sprintf(
                    "Invalid fqcn: `$fqcn`, must be instanceof %s",
                    ContainerInterface::class
                )
            );
        }
        return $result;
    }
}
