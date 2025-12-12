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
    private Configuration $config;

    /**
     * @param Configuration $config
     * @return array<string[], string[]>
     */
    public function __invoke(Configuration $config): array
    {
        $this->config = $config;
        $this->reflection_service = new ReflectionService($this->config->params, $this->config->alias);
        foreach ($this->config->containers as $container) {
            $this->stack[] = $container;
            $this->dependenciesMap[$container] = $this->reflection_service->getDependencies($container, $this->stack);
            $this->generateMap($this->dependenciesMap[$container]);
            array_pop($this->stack);
        }
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
        foreach ($containers as $dependency) {
            if (in_array($dependency->name, $this->stack)) {
                throw new CircularDependency($dependency->name, $this->stack);
            }
            $this->stack[] = $dependency->name;

            if (array_key_exists($dependency->name, $this->config->params)) {
                $this->required_keys[] = $dependency->name;
                array_pop($this->stack);
                continue;
            }
            if (array_key_exists($dependency->name, $this->config->alias)) {
                $this->used_alias[] = $dependency->name;
                $this->used_alias[] = $this->config->alias[$dependency->name];
                $dependency = new Dependency(
                    name: $this->config->alias[$dependency->name],
                    parameter: $dependency->parameter
                );
            }
            if (array_key_exists($dependency->name, $this->config->params)) {
                $this->required_keys[] = $dependency->name;
                array_pop($this->stack);
                continue;
            }
            if (in_array($dependency->name, $this->shared)) {
                array_pop($this->stack);
                continue;
            }
            if (array_key_exists($dependency->name, $this->dependenciesMap)) {
                $this->shared[] = $dependency->name;
                array_pop($this->stack);
                continue;
            }
            if (
                in_array($dependency->name, $this->config->singletons)
                || in_array($dependency->name, $this->config->factories)
            ) {
                $this->shared[] = $dependency->name;
            }

            if (
                $dependency->parameter->isOptional()
                && !array_key_exists($dependency->name, $this->config->alias)
            ) {
                array_pop($this->stack);
                continue;
            }

            $this->dependenciesMap[$dependency->name] = $this->reflection_service->getDependencies(
                $dependency->name,
                $this->stack
            );
            $this->generateMap($this->dependenciesMap[$dependency->name]);
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

        $args = '...[';
        $variadic = '';
        if (array_key_exists($target, $this->dependenciesMap)) {
            foreach ($this->dependenciesMap[$target] as $dependency) {
                if (
                    $dependency->parameter->isOptional()
                    && !array_key_exists($dependency->name, $this->config->params)
                    && !array_key_exists($dependency->name, $this->config->alias)
                ) {
                    continue;
                }
                if ($dependency->parameter->isVariadic()) {
                    $variadic = "...{$this->buildDependency($dependency->name)}";
                } else {
                    $args .= "'{$dependency->parameter->getName()}' => {$this->buildDependency($dependency->name)}, ";
                }
            }
        }
        $args .= ']';
        $container = "new \\$target($args, $variadic)";
        array_pop($this->build_stack);
        return $container;
    }
}
