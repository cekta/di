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
    /**
     * @param ReflectionParameter $parameter
     * @param array<string> $annotations
     * @return string
     */
    public function getName(ReflectionParameter $parameter, array $annotations): string
    {
        if (array_key_exists($parameter->name, $annotations)) {
            return $annotations[$parameter->name];
        }
        $type = $parameter->getType();
        if ($type === null) {
            return $parameter->name;
        }
        if (class_exists(ReflectionUnionType::class) && $type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $namedType) {
                return $this->getFromNamedType($parameter, $namedType);
            }
        }
        if (!$type instanceof ReflectionNamedType) {
            throw new LogicException("it can't be");
        }
        return $this->getFromNamedType($parameter, $type);
    }

    private function getFromNamedType(ReflectionParameter $parameter, ReflectionNamedType $type): string
    {
        return $type->isBuiltin() ? $parameter->name : $type->getName();
    }
}
