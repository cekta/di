<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InvalidContainerForCompile;
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
    private array $required_keys = [];
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
     * @var string[]
     */
    private array $singletons;
    /**
     * @var string[]
     */
    private array $factories;

    /**
     * @param array<string> $containers
     * @param array<string, string> $alias
     * @param array<string, mixed> $params
     * @param array<string, callable> $definitions
     * @param string $fqcn
     * @param array<string> $singletons
     * @param array<string> $factories
     * @return string
     * @throws NotInstantiable
     * @throws InfiniteRecursion
     * @throws InvalidContainerForCompile
     */
    public function compile(
        array $containers = [],
        array $params = [],
        array $alias = [],
        array $definitions = [],
        string $fqcn = 'App\Container',
        array $singletons = [],
        array $factories = [],
    ): string {
        $this->params = $params;
        $this->alias = $alias;
        $this->definitions = $definitions;
        $this->singletons = $singletons;
        $this->factories = $factories;
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
            'required_keys' => array_unique($this->required_keys),
            'singletons' => $this->singletons,
            'factories' => $this->factories,
        ]);
    }

    /**
     * @param array<string> $containers
     * @throws NotInstantiable
     * @throws InfiniteRecursion
     * @throws InvalidContainerForCompile
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
            if (array_key_exists($container, $this->params) || array_key_exists($container, $this->definitions)) {
                $this->required_keys[] = $container;
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
            if (in_array($container, $this->singletons)  || in_array($container, $this->factories)) {
                $this->shared[] = $container;
            }
            try {
                // @phpstan-ignore argument.type
                $reflection = new Reflection($container);
            } catch (ReflectionException $exception) {
                throw new InvalidContainerForCompile($container, $this->stack, $exception);
            }
            if (!$reflection->isInstantiable()) {
                throw new NotInstantiable($reflection->getName(), $this->stack);
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
