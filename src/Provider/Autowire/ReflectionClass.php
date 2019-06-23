<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire;

use ReflectionMethod;
use ReflectionParameter;

class ReflectionClass extends \ReflectionClass
{
    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        $constructor = $this->getConstructor();
        if (null === $constructor) {
            return [];
        }
        return $this->getMethodParameters($constructor);
    }

    /**
     * @param ReflectionMethod $method
     * @return string[]
     */
    private function getMethodParameters(ReflectionMethod $method): array
    {
        $result = [];
        foreach ($method->getParameters() as $parameter) {
            $result[] = $this->getParameterName($parameter);
        }
        return $result;
    }

    private function getParameterName(ReflectionParameter $parameter): string
    {
        $class = $parameter->getClass();
        if (null !== $class) {
            return $class->name;
        }
        return $parameter->name;
    }
}
