<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\DependencyMap\Dependency;
use Cekta\DI\DependencyMap\Dependency\Alias;
use Cekta\DI\DependencyMap\Dependency\Autowiring;
use Cekta\DI\DependencyMap\Dependency\AutowiringShared;
use Cekta\DI\DependencyMap\Dependency\Container;
use Cekta\DI\DependencyMap\Dependency\Param;
use Cekta\DI\Exception\CircularDependency;

class DependencyMap
{
    /**
     * @var array<string, Dependency>
     */
    private array $dependencies_map = [];
    /**
     * @var array<string>
     */
    private array $stack = [];
    private Compiler $config;
    private ReflectionService $reflection_service;

    /**
     * @param Compiler $config
     * @return array<string, Dependency>
     */
    public function generate(Compiler $config): array
    {
        $this->config = $config;
        $this->reflection_service = new ReflectionService($this->config->params, $this->config->alias);
        foreach ($this->config->containers as $container) {
            $this->match($container);
        }
        return $this->dependencies_map;
    }

    private function makeDependency(string $name): Dependency
    {
        if (array_key_exists($name, $this->config->params)) {
            $dependency = new Param($name);
        } elseif (array_key_exists($name, $this->config->alias)) {
            $dependency = new Alias($name, $this->config->alias[$name]);
        } elseif (in_array($name, $this->config->containers)) {
            $dependency = new Container(
                $name,
                ...$this->reflection_service->getParameters($name, $this->stack)
            );
        } elseif (in_array($name, [...$this->config->factories, ...$this->config->singletons])) {
            $dependency = new AutowiringShared(
                $name,
                ...$this->reflection_service->getParameters($name, $this->stack)
            );
        } else {
            $dependency = new Autowiring(
                $name,
                ...$this->reflection_service->getParameters($name, $this->stack)
            );
        }
        return $dependency;
    }

    private function match(string $name): void
    {
        if (in_array($name, $this->stack)) {
            throw new CircularDependency($name, $this->stack);
        }
        $this->stack[] = $name;

        if (array_key_exists($name, $this->dependencies_map)) {
            $autowiring = $this->dependencies_map[$name];
            if ($autowiring::class === Autowiring::class) {
                $this->dependencies_map[$autowiring->name] = new AutowiringShared(
                    $autowiring->name,
                    ...$autowiring->parameters
                );
            }
        } else {
            $dependency = $this->makeDependency($name);

            switch ($dependency::class) {
                case Alias::class:
                    $this->match($dependency->target);
                    break;
                case Container::class:
                case Autowiring::class:
                    $this->autowiring($dependency);
                    break;
            }

            $this->dependencies_map[$dependency->name] = $dependency;
        }

        array_pop($this->stack);
    }

    private function autowiring(Autowiring $autowiring): void
    {
        foreach ($autowiring->parameters as $parameter) {
            $this->match($parameter->name);
        }
    }
}
