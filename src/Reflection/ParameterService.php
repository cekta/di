<?php

declare(strict_types=1);

namespace Cekta\DI\Reflection;

use LogicException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

/**
 * @internal
 */
class ParameterService
{
    public function getName(ReflectionParameter $parameter): string
    {
        $type = $parameter->getType();
        if ($type === null) {
            return $parameter->name;
        }
        if ($type instanceof ReflectionNamedType) {
            return $this->fromNamed($type, $parameter->name);
        }
        if ($type instanceof ReflectionUnionType) {
            return $this->fromUnion($type, $parameter->name);
        }
        throw new LogicException("it can't be");
    }

    private function fromUnion(ReflectionUnionType $type, string $name): string
    {
        foreach ($type->getTypes() as $namedType) {
            if (!$namedType->isBuiltin()) {
                return $namedType->getName();
            }
        }
        return $name;
    }

    private function fromNamed(ReflectionNamedType $type, string $name): string
    {
        return $type->isBuiltin() ? $name : $type->getName();
    }
}
