<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire;

use ReflectionMethod;
use ReflectionParameter;

class ReflectionClass extends \ReflectionClass
{
    /**
     * @var RuleInterface[]
     */
    private $rules;

    public function __construct(string $name, RuleInterface ... $rules)
    {
        $this->rules = $rules;
        parent::__construct($name);
    }

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
        $replaces = $this->getReplaces();
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

    private function getReplaces()
    {
        $result = [];
        foreach ($this->rules as $rule) {
            if ($rule->acceptable($this->name)) {
                $result += $rule->accept();
            }
        }
        return $result;
    }
}
