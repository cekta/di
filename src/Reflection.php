<?php

declare(strict_types=1);

namespace Cekta\DI;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;

/**
 * @template T of object
 * @extends ReflectionClass<T>
 */
class Reflection extends ReflectionClass
{
    public function __construct(object|string $objectOrClass)
    {
        try {
            // @phpstan-ignore argument.type
            parent::__construct($objectOrClass);
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage(), 1, $e);
        }
    }

    /**
     * @return array<array{name: string, variadic: bool}>
     */
    public function getDependencies(): array
    {
        if (!$this->isInstantiable()) {
            throw new RuntimeException("`{$this->getName()}` must be instantiable", 2);
        }
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
