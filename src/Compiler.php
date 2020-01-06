<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Provider\Autowiring\Reflection;

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
        $result = '[';
        $loaded = [];
        foreach ($containers as $container) {
            if (!in_array($container, $loaded)) {
                $loaded[] = $container;
                $dependencies = $this->reflection->getClass($container)->getDependencies();
                if (!empty($dependencies)) {
                    array_push($containers, ...$dependencies);
                }
                $result .= $this->compileContainer($container, ...$dependencies);
            }
        }
        $result .= "\n" . '];';
        return $result;
    }

    private function compileContainer(string $name, string ...$dependencies): string
    {
        $args = '';
        foreach ($dependencies as $dependency) {
            $args .= "\$container[$dependency]";
        }
        return <<<TAG

    '$name' => function(\Psr\Container\ContainerInterface \$container) {
        return new $name($args);
    }
TAG;
    }
}
