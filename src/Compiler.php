<?php

declare(strict_types=1);

namespace Cekta\DI;

use InvalidArgumentException;
use UnexpectedValueException;

class Compiler
{
    /**
     * @var array<string, mixed>
     */
    private array $params;
    /**
     * @var array<string, string>
     */
    private array $alias;
    /**
     * @var array<string, callable>
     */
    private array $definitions;
    private string $fqcn;
    /**
     * @var array<string>
     */
    private array $shared = [];
    /**
     * @var array<string, array<array{'name': string, 'variadic': bool}>>
     */
    private array $dependenciesMap = [];
    private Reflection $reflection;
    private array $stack = [];

    /**
     * @param array<string, mixed> $params
     * @param array<string, string> $alias
     * @param array<string, callable> $definitions
     * @param string $fqcn
     */
    public function __construct(array $params, array $alias, array $definitions, string $fqcn)
    {
        $this->params = $params;
        $this->alias = $alias;
        $this->definitions = $definitions;
        $this->fqcn = $fqcn;
    }

    /**
     * @param array<string> $containers
     * @return string|false
     */
    public function __invoke(array $containers): string|false
    {
        $this->reflection = new Reflection(new Container($this->params, $this->alias, $this->definitions));
        $namespace = $this->getNamespace();
        $class = $this->getClass();
        $alias = $this->alias;
        $definitions = $this->definitions;
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
            throw new InvalidArgumentException("Invalid fqcn: `$this->fqcn` must contain \\");
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
                /** @var array<string> $new_containers */
                $new_containers = array_map(static function (array $container) {
                    return $container['name'];
                }, $this->dependenciesMap[$container]);
                $this->generateMap($new_containers);
                continue;
            }
            throw new UnexpectedValueException("`$container` is cant be resolved");
        }
    }

    private function buildContainer(string $target): string
    {
        if (
            (in_array($target, $this->shared) && count($this->stack) !== 0)
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
                $name = $dependency['name'];
                $variadic = $dependency['variadic'] === true ? '...' : '';
                $container .= "$variadic{$this->buildContainer($name)}, ";
            }
        }
        $container .= ')';
        array_pop($this->stack);
        return $container;
    }
}
