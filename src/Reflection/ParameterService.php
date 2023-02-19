<?php

declare(strict_types=1);

namespace Cekta\DI\Reflection;

use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

/**
 * @internal
 */
class ParameterService
{
    public function getName(ReflectionParameter $parameter): string
    {
        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType) {
            return $this->fromNamed($type, $parameter->name);
        }
        return $parameter->name;
    }

    private function fromNamed(ReflectionNamedType $type, string $name): string
    {
        return $type->isBuiltin() ? $name : $type->getName();
    }
}
