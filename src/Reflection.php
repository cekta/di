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
    /**
     * @param string $name
     * @return array<array{name: string, variadic: bool, parameter: string}>
     */
    public function getDependencies(string $name): array
    {
        try {
            $class = new ReflectionClass($name);
        } catch (ReflectionException $e) {
            return [];
        }
        $constructor = $class->getConstructor();
        if ($constructor === null) {
            return [];
        }
        $parameters = [];
        foreach ($constructor->getParameters() as $parameter) {
            $prefix = $parameter->isVariadic() ? '...' : '';
            $parameters[] = [
                'name' => $prefix . $this->getName($name, $parameter),
                'parameter' => "$prefix$name\$$parameter->name",
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
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType && $type->isBuiltin() || $type === null) {
            return $parameter->name;
        }

        return (string)$type;
    }
}
