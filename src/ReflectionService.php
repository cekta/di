<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\NotFoundOnCompile;
use Cekta\DI\Exception\NotInstantiable;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

class ReflectionService
{
    /**
     * @param array<string, mixed|Lazy> $params
     * @param string[] $alias
     */
    public function __construct(
        private array $params,
        private array $alias,
    ) {
    }

    /**
     * @param string $container_name
     * @param array<string> $stack
     * @return array<Dependency>
     *
     * @throws NotFoundOnCompile
     * @throws NotInstantiable
     */
    public function getDependencies(string $container_name, array $stack): array
    {
        try {
            // @phpstan-ignore argument.type
            $reflection = new ReflectionClass($container_name);
        } catch (ReflectionException) {
            throw new NotFoundOnCompile($container_name, $stack);
        }

        if (!$reflection->isInstantiable()) {
            throw new NotInstantiable($reflection->getName(), $stack);
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return [];
        }
        $parameters = [];
        foreach ($constructor->getParameters() as $parameter) {
            $parameters[] = $this->makeDependencyDTO($reflection->getName(), $parameter);
        }
        return $parameters;
    }

    private function makeDependencyDTO(string $class, ReflectionParameter $parameter): Dependency
    {
        $prefix = $parameter->isVariadic() ? '...' : '';
        $type = $parameter->getType();
        $custom_name = "$prefix{$class}\${$parameter->name}";
        if (
            array_key_exists($custom_name, $this->params)
            || array_key_exists($custom_name, $this->alias)
        ) {
            $dependency_name = $custom_name;
        } elseif ($type instanceof ReflectionNamedType && $type->isBuiltin() || $type === null) {
            $dependency_name = $prefix . $parameter->name;
        } else {
            $dependency_name = $prefix . $type;
        }
        return new Dependency(
            name: $dependency_name,
            variadic: $parameter->isVariadic()
        );
    }
}
