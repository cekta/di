<?php

declare(strict_types=1);

namespace Cekta\DI;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * @template T of object
 * @extends ReflectionClass<T>
 */
class Reflection extends ReflectionClass
{
    /**
     * @return array<array{name: string, variadic: bool}>
     */
    public function getDependencies(): array
    {
        $constructor = $this->getConstructor();
        if ($constructor === null) {
            return [];
        }
        $parameters = [];
        foreach ($constructor->getParameters() as $parameter) {
            $prefix = $parameter->isVariadic() ? '...' : '';
            $parameters[] = [
                'name' => $prefix . $this->getParameterName($parameter),
                'variadic' => $parameter->isVariadic()
            ];
        }
        return $parameters;
    }

    private function getParameterName(ReflectionParameter $parameter): string
    {
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType && $type->isBuiltin() || $type === null) {
            return $parameter->name;
        }

        return (string)$type;
    }
}
