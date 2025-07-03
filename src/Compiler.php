<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\NotInstantiable;
use ReflectionException;

class Compiler
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
     * @var array<string, array<array{name: string, variadic: bool}>>
     */
    private array $dependenciesMap = [];
    /**
     * @var array<string>
     */
    private array $param_keys = [];
    /**
     * @var array<string>
     */
    private array $definition_keys = [];
    /**
     * @var array<string, mixed>
     */
    private array $params;
    /**
     * @var string[]
     */
    private array $alias;
    /**
     * @var callable[]
     */
    private array $definitions;

    /**
     * @param array<string> $containers
     * @param array<string, string> $alias
     * @param array<string, mixed> $params
     * @param array<string, callable> $definitions
     * @param string $fqcn
     * @return string
     * @throws NotInstantiable
     * @throws ReflectionException
     * @throws InfiniteRecursion
     */
    public function compile(
        array $containers = [],
        array $params = [],
        array $alias = [],
        array $definitions = [],
        string $fqcn = 'App\Container'
    ): string {
        $this->params = $params;
        $this->alias = $alias;
        $this->definitions = $definitions;
        $this->generateMap($containers);
        $dependencies = [];
        foreach (array_merge($containers, $this->shared, $this->alias) as $target) {
            $dependencies[$target] = $this->buildDependency($target);
        }
        $fqcn = new FQCN($fqcn);
        $template = new Template(__DIR__ . '/../template/container.compiler.php');
        return $template->render([
            'namespace' => $fqcn->getNamespace(),
            'class' => $fqcn->getClass(),
            'targets' => $containers,
            'dependencies' => $dependencies,
            'alias' => $this->alias,
            'param_keys' => array_unique($this->param_keys),
            'definition_keys' => array_unique($this->definition_keys),
        ]);
    }

    /**
     * @param array<string> $containers
     * @throws ReflectionException
     * @throws NotInstantiable
     * @throws InfiniteRecursion
     */
    private function generateMap(array $containers): void
    {
        foreach ($containers as $container) {
            if (in_array($container, $this->stack)) {
                throw new InfiniteRecursion($container, $this->stack);
            }
            $this->stack[] = $container;

            if (array_key_exists($container, $this->alias)) {
                $container = $this->alias[$container];
            }
            if (array_key_exists($container, $this->params)) {
                $this->param_keys[] = $container;
                array_pop($this->stack);
                continue;
            }
            if (array_key_exists($container, $this->definitions)) {
                $this->definition_keys[] = $container;
                array_pop($this->stack);
                continue;
            }
            if (in_array($container, $this->shared)) {
                array_pop($this->stack);
                continue;
            }
            if (array_key_exists($container, $this->dependenciesMap)) {
                $this->shared[] = $container;
                array_pop($this->stack);
                continue;
            }
            /** @var class-string $container */
            $reflection = new Reflection($container);
            if (!$reflection->isInstantiable()) {
                throw new NotInstantiable("`{$reflection->getName()}` must be instantiable");
            }
            $this->dependenciesMap[$container] = $reflection->getDependencies();
            $dependencies = [];
            foreach ($this->dependenciesMap[$container] as $dependency) {
                $dependencies[] = $dependency['name'];
            }
            $this->generateMap($dependencies);
            array_pop($this->stack);
        }
    }

    private function buildDependency(string $target): string
    {
        if (array_key_exists($target, $this->params)) {
            return "\$this->params['$target']";
        }
        if (
            (in_array($target, $this->shared) && count($this->build_stack) !== 0)
            || array_key_exists($target, $this->alias)
            || array_key_exists($target, $this->definitions)
        ) {
            return "\$this->get('$target')";
        }
        $this->build_stack[] = $target;
        $container = "new \\$target(";
        if (array_key_exists($target, $this->dependenciesMap)) {
            foreach ($this->dependenciesMap[$target] as $dependency) {
                $name = $dependency['name'];
                $variadic = $dependency['variadic'] === true ? '...' : '';
                $container .= "$variadic{$this->buildDependency($name)}, ";
            }
        }
        $container .= ')';
        array_pop($this->build_stack);
        return $container;
    }
}
