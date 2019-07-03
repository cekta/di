<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire\Reader;

use Cekta\DI\Provider\Autowire\Reader\Exception\InvalidClassName;
use Cekta\DI\Provider\Autowire\ReaderInterface;
use Cekta\DI\Provider\Autowire\RuleInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

class WithoutCache implements ReaderInterface
{
    /**
     * @var RuleInterface[]
     */
    private $rules;

    public function __construct(RuleInterface ... $rules)
    {
        $this->rules = $rules;
    }

    public function getDependencies(string $className): array
    {
        try {
            $class = new ReflectionClass($className);
            return $this->getDependenciesForClass($class);
        } catch (ReflectionException $e) {
            throw new InvalidClassName($className);
        }
    }

    /**
     * @param ReflectionClass $class
     * @return string[]
     */
    private function getDependenciesForClass(ReflectionClass $class): array
    {
        $constructor = $class->getConstructor();
        if (null === $constructor) {
            return [];
        }
        $replaces = $this->getReplaces($class->name);
        return $this->getMethodParameters($constructor, $replaces);
    }

    /**
     * @param ReflectionMethod $method
     * @param array $replaces
     * @return string[]
     */
    private function getMethodParameters(ReflectionMethod $method, array $replaces): array
    {
        $result = [];
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

    private function getReplaces(string $name)
    {
        $result = [];
        foreach ($this->rules as $rule) {
            if ($rule->acceptable($name)) {
                $result += $rule->accept();
            }
        }
        return $result;
    }
}
