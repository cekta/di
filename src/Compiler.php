<?php

declare(strict_types=1);

namespace Cekta\DI;

use InvalidArgumentException;
use ReflectionException;

class Compiler
{
    /**
     * @var array<string, string>
     */
    private array $alias;
    /**
     * @var array<string, mixed>
     */
    private array $params;
    /**
     * @var array<string, callable>
     */
    private array $definitions;
    /**
     * @var array<string>
     */
    private array $shared = [];
    /**
     * @var array<string, array<array{name: string, variadic: bool}>>
     */
    private array $dependenciesMap = [];

    /**
     * @var array<string>
     */
    private array $stack = [];

    /**
     * @param array<string> $targets
     * @param array<string, string> $alias
     * @param array<string, mixed> $params
     * @param array<string, callable> $definitions
     * @throws ReflectionException
     */
    public function compile(
        array $targets = [],
        array $alias = [],
        array $params = [],
        array $definitions = [],
        string $fqcn = 'App\Container'
    ): string|false {
        $class = self::getClass($fqcn);
        $namespace = self::getNamespace($fqcn);
        $this->alias = $alias;
        $this->params = $params;
        $this->definitions = $definitions;
        $this->generateMap($targets);
        $containers = [];
        foreach (array_merge($targets, $this->shared, $this->alias) as $target) {
            $containers[$target] = $this->buildContainer($target);
        }
        $template = new Template(__DIR__ . '/../template/container.compiler.php');
        return $template->render([
            'alias' => $alias,
            'params' => $params,
            'definitions' => $definitions,
            'containers' => $containers,
            'namespace' => $namespace,
            'class' => $class
        ]);
    }

    /**
     * @param string $fqcn
     * @return string
     * @throws InvalidArgumentException when fqcn not contain \
     */
    private static function getNamespace(string $fqcn): string
    {
        $position = strrpos($fqcn, '\\');
        if ($position === false) {
            throw new InvalidArgumentException("Invalid fqcn: `$fqcn` must contain \\");
        }
        return substr($fqcn, 0, $position);
    }

    private static function getClass(string $fqcn): string
    {
        return substr($fqcn, strrpos($fqcn, '\\') + 1);
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
            /** @var class-string $container */
            $reflection = new Reflection($container);
            $this->dependenciesMap[$container] = $reflection->getDependencies();
            $new_containers = [];
            foreach ($this->dependenciesMap[$container] as $dependency) {
                $new_containers[] = $dependency['name'];
            }
            $this->generateMap($new_containers);
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
