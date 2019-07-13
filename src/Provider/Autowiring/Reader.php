<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowiring;

use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

class Reader
{
    /**
     * @var RuleInterface[]
     */
    private $rules;

    public function __construct(RuleInterface ...$rules)
    {
        $this->rules = $rules;
    }

    /**
     * @param string $id
     * @return string[]
     * @throws ReflectionException
     */
    public function getDependencies(string $id): array
    {
        $class = new \ReflectionClass($id);
        $constructor = $class->getConstructor();
        if (null === $constructor) {
            return [];
        }
        return $this->getMethodParameters($id, $constructor);
    }

    private function getMethodParameters(string $id, ReflectionMethod $method): array
    {
        $result = [];
        $replaces = $this->getReplaces($id);
        foreach ($method->getParameters() as $parameter) {
            $name = $this->getParameterName($parameter);
            if (array_key_exists($name, $replaces)) {
                $name = $replaces[$name];
            }
            $result[] = $name;
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
    private function getReplaces(string $id)
    {
        $result = [];
        foreach ($this->rules as $rule) {
            if ($rule->acceptable($id)) {
                $result += $rule->accept();
            }
        }
        return $result;
    }
}
