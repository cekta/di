<?php

declare(strict_types=1);

namespace Cekta\DI;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

class Reflection
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     * @return string[]
     * @throws ReflectionException
     */
    public function getDependencies(string $name): array
    {
        $class = new ReflectionClass($name);
        $constructor = $class->getConstructor();
        if ($constructor === null) {
            return [];
        }
        $parameters = [];
        foreach ($constructor->getParameters() as $parameter) {
            $parameters[] = $this->getName($name, $parameter);
        }
        return $parameters;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isInstantiable(string $name): bool
    {
        try {
            $class = new ReflectionClass($name);
            return $class->isInstantiable();
        } catch (ReflectionException $exception) {
            return false;
        }
    }

    private function getName(string $name, ReflectionParameter $parameter): string
    {
        $key = "{$name}\${$parameter->name}";
        if ($this->container->has($key)) {
            return $key;
        }
        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            return $type->getName();
        }
        if (
            $type instanceof ReflectionUnionType
            || $type instanceof ReflectionIntersectionType
        ) {
            return (string)$type;
        }
        return $parameter->name;
    }
}
