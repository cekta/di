<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\CircularDependency;
use Cekta\DI\Exception\NotFoundOnCompile;
use Cekta\DI\Exception\NotInstantiable;

/**
 * @internal not for public usage
 */
class InternalCompiler
{
    /**
     * @var array<string>
     */
    private array $shared = [];

    /**
     * @var array<string>
     */
    private array $build_stack = [];
    /**
     * @var array<string>
     */
    private array $stack = [];
    /**
     * @var array<string, Dependency[]>
     */
    private array $dependenciesMap = [];
    /**
     * @var array<string>
     */
    private array $required_keys = [];

    private ReflectionService $reflection_service;
    /**
     * @var string[]
     */
    private array $used_alias = [];
    private Compiler $config;

    /**
     * @param Compiler $config
     * @return array<string[], string[]>
     */
    public function __invoke(Compiler $config): array
    {
        $this->config = $config;
        $this->reflection_service = new ReflectionService($this->config->params, $this->config->alias);
        $this->generateMap(array_map(fn(string $name) => new Dependency($name), $this->config->containers));
        $dependencies = [];
        foreach (array_merge($this->config->containers, $this->shared, $this->used_alias) as $target) {
            $dependencies[$target] = $this->buildDependency($target);
        }
        return [$dependencies, array_unique($this->required_keys)];
    }

    /**
     * @param Dependency[] $containers
     * @throws NotInstantiable
     * @throws CircularDependency
     * @throws NotFoundOnCompile
     */
    private function generateMap(array $containers): void
    {
        foreach ($containers as $container) {
            if (in_array($container->getName(), $this->stack)) {
                throw new CircularDependency($container->getName(), $this->stack);
            }
            $this->stack[] = $container->getName();

            if (array_key_exists($container->getName(), $this->config->params)) {
                $this->required_keys[] = $container->getName();
                array_pop($this->stack);
                continue;
            }
            if (array_key_exists($container->getName(), $this->config->alias)) {
                $this->used_alias[] = $container->getName();
                $this->used_alias[] = $this->config->alias[$container->getName()];
                $container = new Dependency($this->config->alias[$container->getName()], $container->isVariadic());
            }
            if (array_key_exists($container->getName(), $this->config->params)) {
                $this->required_keys[] = $container->getName();
                array_pop($this->stack);
                continue;
            }
            if (in_array($container->getName(), $this->shared)) {
                array_pop($this->stack);
                continue;
            }
            if (array_key_exists($container->getName(), $this->dependenciesMap)) {
                $this->shared[] = $container->getName();
                array_pop($this->stack);
                continue;
            }
            if (
                in_array($container->getName(), $this->config->singletons)
                || in_array($container->getName(), $this->config->factories)
            ) {
                $this->shared[] = $container->getName();
            }

            $this->dependenciesMap[$container->getName()] = $this->reflection_service->getDependencies(
                $container->getName(),
                $this->stack
            );
            $this->generateMap($this->dependenciesMap[$container->getName()]);
            array_pop($this->stack);
        }
    }

    private function buildDependency(string $target): string
    {
        if (
            (in_array($target, $this->shared) && count($this->build_stack) !== 0)
            || array_key_exists($target, $this->config->alias)
            || array_key_exists($target, $this->config->params)
        ) {
            return "\$this->get('$target')";
        }
        $this->build_stack[] = $target;
        $container = "new \\$target(";
        if (array_key_exists($target, $this->dependenciesMap)) {
            foreach ($this->dependenciesMap[$target] as $dependency) {
                $name = $dependency->getName();
                $variadic = $dependency->isVariadic() === true ? '...' : '';
                $container .= "$variadic{$this->buildDependency($name)}, ";
            }
        }
        $container .= ')';
        array_pop($this->build_stack);
        return $container;
    }
}
