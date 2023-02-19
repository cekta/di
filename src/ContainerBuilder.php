<?php

namespace Cekta\DI;

use LogicException;
use Psr\Container\ContainerInterface;
use ReflectionException;
use UnexpectedValueException;

/**
 * @external
 */
class ContainerBuilder
{
    /**
     * @var array<string, mixed>
     */
    private array $params = [];
    /**
     * @var array<string, string>
     */
    private array $alias = [];
    /**
     * @var array<string, callable>
     */
    private array $definitions = [];
    /**
     * @var array<string>
     */
    private array $shared = [];
    /**
     * @var array<string, array<string>>
     */
    private array $dependenciesMap = [];
    private string $fqcn = 'App\Container';
    private Reflection $reflection;
    private array $stack = [];

    public function build(): ContainerInterface
    {
        if (class_exists($this->fqcn)) {
            $result = new $this->fqcn($this->params, $this->alias, $this->definitions);
            if ($result instanceof ContainerInterface) {
                return $result;
            }
            throw new UnexpectedValueException("`{$this->fqcn}` must implement Psr\Container\ContainerInterface");
        }
        return new Container($this->params, $this->alias, $this->definitions);
    }

    /**
     * @param array<string, mixed> $params
     * @return $this
     */
    public function params(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @param array<string, string> $alias
     * @return $this
     */
    public function alias(array $alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @param array<string, callable> $definitions
     * @return $this
     */
    public function definitions(array $definitions): self
    {
        $this->definitions = $definitions;
        return $this;
    }

    public function fqcn(string $fqcn): self
    {
        $this->fqcn = $fqcn;
        return $this;
    }

    /**
     * @param array<string> $containers
     * @return string
     * @throws ReflectionException
     */
    public function compile(array $containers): string|false
    {
        $this->reflection = new Reflection();
        $namespace = $this->getNamespace();
        $class = $this->getClass();
        $containers = $this->generateContainers($containers);
        ob_start();
        include __DIR__ . '/../template/container.compiler';
        return ob_get_clean();
    }

    /**
     * @return string
     */
    private function getNamespace(): string
    {
        $position = strrpos($this->fqcn, '\\');
        if ($position === false) {
            throw new \InvalidArgumentException("Invalid fqcn: `{$this->fqcn}` must contain \\");
        }
        return substr($this->fqcn, 0, $position);
    }

    /**
     * @return string
     */
    private function getClass(): string
    {
        return substr($this->fqcn, strrpos($this->fqcn, '\\') + 1);
    }

    /**
     * @param string[] $targets
     * @return array<string, string>
     * @throws ReflectionException
     */
    private function generateContainers(array $targets): array
    {
        $this->generateMap($targets);
        $containers = [];
        foreach (array_merge($targets, $this->shared, $this->alias) as $target) {
            $containers[$target] = $this->buildContainer($target);
        }
        return $containers;
    }

    /**
     * @param array<string> $containers
     * @throws ReflectionException
     */
    private function generateMap(array $containers): void
    {
        foreach ($containers as $container) {
            if (array_key_exists($container, $this->alias)) {
                $container = $this->alias[$container];
            }
            if (
                array_key_exists($container, $this->params)
                || array_key_exists($container, $this->definitions)
                || array_key_exists($container, $this->shared)
            ) {
                continue;
            }
            if (array_key_exists($container, $this->dependenciesMap)) {
                $this->shared[] = $container;
                continue;
            }
            if ($this->reflection->isInstantiable($container)) {
                $this->dependenciesMap[$container] = $this->reflection->getDependencies($container);
                $this->generateMap($this->dependenciesMap[$container]);
                continue;
            }
            throw new UnexpectedValueException("`$container` is cant be resolved");
        }
    }

    private function buildContainer(string $target): string
    {
        if (
            (in_array($target, $this->shared) && count($this->stack))
            || array_key_exists($target, $this->alias)
            || array_key_exists($target, $this->definitions)
            || array_key_exists($target, $this->params)
        ) {
            return "\$this->get('$target')";
        }
        $this->stack[] = $target;
        $container = "new \\$target(";
        if (array_key_exists($target, $this->dependenciesMap)) {
            foreach ($this->dependenciesMap[$target] as $dependency) {
                $container .= "{$this->buildContainer($dependency)}, ";
            }
        }
        $container .= ')';
        array_pop($this->stack);
        return $container;
    }
}
