<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire;

use ReflectionMethod;

class ReflectionClass extends \ReflectionClass
{
    public function readDependecies(): array
    {
        $contructor = $this->getConstructor();
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
