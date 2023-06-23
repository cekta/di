<?php

declare(strict_types=1);

namespace Cekta\DI;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

class Reflection
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     * @return array<array{name: string, variadic: bool}>
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
            $parameters[] = [
                'name' => $this->getName($name, $parameter),
                'variadic' => $parameter->isVariadic()
            ];
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
        $prefix = $parameter->isVariadic() ? '...' : '';
        $key = "{$prefix}{$name}\${$parameter->name}";
        if ($this->container->has($key)) {
            return $key;
        }
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType && $type->isBuiltin() || $type === null) {
            return $prefix . $parameter->name;
        }

        return $prefix . $type;
    }
}
