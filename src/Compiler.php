<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\NotInstantiable;

/**
 * @external
 */
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
     * @var array<string, DependencyDTO[]>
     */
    private array $dependenciesMap = [];
    /**
     * @var array<string>
     */
    private array $required_keys = [];

    private ReflectionService $reflection_service;

    /**
     * @param array<string> $containers
     * @param array<string, mixed|Lazy> $params
     * @param array<string, string> $alias
     * @param string $fqcn
     * @param array<string> $singletons
     * @param array<string> $factories
     * @param Rule|null $rule
     */
    public function __construct(
        private array $containers = [],
        private array $params = [],
        private array $alias = [],
        private string $fqcn = 'App\Container',
        private array $singletons = [],
        private array $factories = [],
        private ?Rule $rule = null,
    ) {
        $this->reflection_service = new ReflectionService($this->rule);
    }

    /**
     * @return string
     * @throws InfiniteRecursion
     * @throws InvalidContainerForCompile
     * @throws NotInstantiable
     */
    public function compile(): string
    {
        $this->generateMap(array_map(fn(string $name) => new DependencyDTO($name), $this->containers));
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
            'required_keys' => array_unique($this->required_keys),
            'singletons' => $this->singletons,
            'factories' => $this->factories,
        ]);
    }

    /**
     * @param DependencyDTO[] $containers
     * @throws NotInstantiable
     * @throws InfiniteRecursion
     * @throws InvalidContainerForCompile
     */
    private function generateMap(array $containers): void
    {
        foreach ($containers as $container) {
            if (in_array($container->getName(), $this->stack)) {
                throw new InfiniteRecursion($container->getName(), $this->stack);
            }
            $this->stack[] = $container->getName();

            if (array_key_exists($container->getName(), $this->alias)) {
                $container = new DependencyDTO($this->alias[$container->getName()], $container->isVariadic());
            }
            if (array_key_exists($container->getName(), $this->params)) {
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
                in_array($container->getName(), $this->singletons)
                || in_array($container->getName(), $this->factories)
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
            || array_key_exists($target, $this->alias)
            || array_key_exists($target, $this->params)
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
