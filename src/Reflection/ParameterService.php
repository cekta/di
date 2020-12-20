<?php

declare(strict_types=1);

namespace Cekta\DI\Reflection;

use ReflectionParameter;

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
        $result = $parameter->getClass() === null ? $parameter->name : $parameter->getClass()->name;
        if (array_key_exists($result, $annotations)) {
            $result = $annotations[$result];
        }
        return $result;
    }
}
