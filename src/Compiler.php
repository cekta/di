<?php

declare(strict_types=1);

namespace Cekta\DI;

use ReflectionException;
use RuntimeException;

class Compiler
{
    /**
     * @var array<string>
     */
    private array $shared = [];

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
     * @param array<string> $containers
     * @param array<string, string> $alias
     * @param array<string, mixed> $params
     * @param array<string, callable> $definitions
     * @param string $fqcn
     * @return void
     */
    public function __construct(
        private array $containers = [],
        private array $alias = [],
        private array $params = [],
        private array $definitions = [],
        private string $fqcn = 'App\Container'
    ) {
    }

    /**
     * @return string
     * @throws ReflectionException for class not found
     * @throws RuntimeException for dependency not instantiable
     */
    public function compile(): string
    {
        $this->generateMap($this->containers);
        $dependencies = [];
        foreach (array_merge($this->containers, $this->shared, $this->alias) as $target) {
            $dependencies[$target] = $this->buildDependency($target);
        }
        $fqcn = new FQCN($this->fqcn);
        $template = new Template(__DIR__ . '/../template/container.compiler.php');
        return $template->render([
            'namespace' => $fqcn->getNamespace(),
            'class' => $fqcn->getClass(),
            'targets' => $this->containers,
            'dependencies' => $dependencies,
            'alias' => $this->alias,
            'param_keys' => array_unique($this->param_keys),
            'definition_keys' => array_unique($this->definition_keys),
        ]);
    }

    /**
     * @param array<string> $containers
     * @throws ReflectionException if class not found
     * @throws RuntimeException if not instantiable
     */
    private function generateMap(array $containers): void
    {
        foreach ($containers as $container) {
            if (array_key_exists($container, $this->alias)) {
                $container = $this->alias[$container];
            }
            if (array_key_exists($container, $this->params)) {
                $this->param_keys[] = $container;
                continue;
            }
            if (array_key_exists($container, $this->definitions)) {
                $this->definition_keys[] = $container;
                continue;
            }
            if (in_array($container, $this->shared)) {
                continue;
            }
            if (array_key_exists($container, $this->dependenciesMap)) {
                $this->shared[] = $container;
                continue;
            }
            /** @var class-string $container */
            $reflection = new Reflection($container);
            $this->dependenciesMap[$container] = $reflection->getDependencies();
            $dependencies = [];
            foreach ($this->dependenciesMap[$container] as $dependency) {
                $dependencies[] = $dependency['name'];
            }
            $this->generateMap($dependencies);
        }
    }

    private function buildDependency(string $target): string
    {
        if (array_key_exists($target, $this->params)) {
            return "\$this->params['$target']";
        }
        if (
            (in_array($target, $this->shared) && count($this->stack) !== 0)
            || array_key_exists($target, $this->alias)
            || array_key_exists($target, $this->definitions)
        ) {
            return "\$this->get('$target')";
        }
        $this->stack[] = $target;
        $container = "new \\$target(";
        if (array_key_exists($target, $this->dependenciesMap)) {
            foreach ($this->dependenciesMap[$target] as $dependency) {
                $name = $dependency['name'];
                $variadic = $dependency['variadic'] === true ? '...' : '';
                $container .= "$variadic{$this->buildDependency($name)}, ";
            }
        }
        $container .= ')';
        array_pop($this->stack);
        return $container;
    }
}
