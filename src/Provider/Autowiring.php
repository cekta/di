<?php
declare(strict_types = 1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Autowiring\RuleInterface;
use Cekta\DI\Provider\Exception\ClassNotCreated;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

class Autowiring implements ProviderInterface
{
    /** @var RuleInterface[] */
    private $rules;

    public function __construct(RuleInterface ...$rules)
    {
        $this->rules = $rules;
    }

    public function provide(string $id, ContainerInterface $container)
    {
        foreach ($this->getDependencies($id) as $dependency) {
            $args[] = $container->get($dependency);
        }

        return $this->create($id, $args ?? []);
    }

    public function canBeProvided(string $id): bool
    {
        return class_exists($id);
    }

    public function create(string $id, array $args): object
    {
        return new $id(...$args);
    }

    /**
     * Return dependencies from class
     *
     * @param  string  $id
     *
     * @return string[]
     * @internal
     */
    public function getDependencies(string $id): array
    {
        try {
            $class = new ReflectionClass($id);
            $constructor = $class->getConstructor();

            return $constructor !== null
                ? $this->getMethodParameters($id, $constructor) : [];
        } catch (ReflectionException $exception) {
            throw new ClassNotCreated($id, $exception);
        }
    }

    private function getMethodParameters(string $id, ReflectionMethod $method): array
    {
        $replaces = $this->getReplaces($id);

        return array_reduce(
            $method->getParameters(),
            function ($result, $parameter) use ($replaces) {
                $name = $this->getParameterName($parameter);
                if (array_key_exists($name, $replaces)) {
                    $name = $replaces[$name];
                }

                $result[] = $name;
                return $result;
            }
        );
    }

    private function getParameterName(ReflectionParameter $parameter): string
    {
        $class = $parameter->getClass();
        return null === $class ? $parameter->name : $class->name;
    }

    private function getReplaces(string $id): array
    {
        return array_reduce($this->rules, function ($result, $rule) use ($id) {
            /** @var \Cekta\DI\Provider\Autowiring\Rule $rule */
            if ($rule->acceptable($id)) {
                $result = array_merge($result, $rule->accept());
            }

            return $result;
        }, []) ?? [];
    }
}
