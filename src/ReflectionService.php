<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\NotInstantiable;
use Cekta\DI\Rule\NullRule;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

class ReflectionService
{
    private Rule $rule;

    public function __construct(
        ?Rule $rule = null,
    ) {
        $this->rule = $rule ?? new NullRule();
    }

    /**
     * @param string $container_name
     * @param array<string> $stack
     * @return array<DependencyDTO>
     *
     * @throws InvalidContainerForCompile
     * @throws NotInstantiable
     */
    public function getDependencies(string $container_name, array $stack): array
    {
        try {
            // @phpstan-ignore argument.type
            $reflection = new ReflectionClass($container_name);
        } catch (ReflectionException $exception) {
            throw new InvalidContainerForCompile($container_name, $stack, $exception);
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
            $parameters[] = $this->makeDependencyDTO($parameter);
        }
        return $parameters;
    }

    private function makeDependencyDTO(ReflectionParameter $parameter): DependencyDTO
    {
        $prefix = $parameter->isVariadic() ? '...' : '';
        $type = $parameter->getType();
        $dependency_name = $prefix . $type;
        if ($type instanceof ReflectionNamedType && $type->isBuiltin() || $type === null) {
            $dependency_name = $prefix . $parameter->name;
        }
        // @phpstan-ignore method.nonObject (getDeclaringClass() always return ReflectionClass)
        $dependency_name = $this->rule->apply($parameter->getDeclaringClass()->getName(), $dependency_name);
        return new DependencyDTO(
            name: $dependency_name,
            variadic: $parameter->isVariadic()
        );
    }
}
