<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire;

use ReflectionClass;
use ReflectionMethod;

class Reflection
{
    public static function getDependecies(ReflectionClass $class): array
    {
        $contructor = $class->getConstructor();
        if (null === $contructor) {
            return [];
        }
        return self::readConstructor($contructor);
    }

    private static function readConstructor(ReflectionMethod $constructor): array
    {
        $result = [];
        foreach ($constructor->getParameters() as $parameter) {
            $class = $parameter->getClass();
            if (null !== $class) {
                $result[] = $class->name;
            } else {
                $result[] = $parameter->name;
            }
        }
        return $result;
    }
}
