<?php

declare(strict_types=1);

namespace Cekta\DI\Provider\Autowiring;

class Compiler
{
    /**
     * @var Reflection
     */
    private $reflection;

    public function __construct(Reflection $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * @param array<string> $containers
     * @return string
     */
    public function compile(array $containers): string
    {
        $result = '';
        foreach ($this->containersWithDependencies($containers) as $container) {
            $result .= $this->compileContainer($container);
        }
        return "[$result\n];";
    }

    /**
     * @param array<string> $containers
     * @return array<string>
     */
    private function containersWithDependencies(array $containers): array
    {
        for ($i = 0; array_key_exists($i, $containers); $i++) {
            if (!$this->reflection->getClass($containers[$i])->isInstantiable()) {
                unset($containers[$i]);
                continue;
            }
            $containers = array_merge($containers, $this->reflection->getClass($containers[$i])->getDependencies());
        }
        return $containers;
    }

    private function compileContainer(string $name): string
    {
        $dependenciesString = '';
        foreach ($this->reflection->getClass($name)->getDependencies() as $dependency) {
            $dependenciesString .= "\$container['$dependency'], ";
        }
        $dependenciesString = substr($dependenciesString, 0, -2);
        return "
    '$name' => function(\Psr\Container\ContainerInterface \$container) {
        return new $name($dependenciesString);
    },";
    }
}
