<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Loader\Alias;
use Cekta\DI\Loader\Factory;
use Cekta\DI\Loader\FactoryVariadic;

class Compiler
{
    /**
     * @var Reflection
     */
    private $reflection;
    private $classes = [];
    private $alias = [];
    private $variadic = [];

    public function __construct(Reflection $reflection)
    {
        $this->reflection = $reflection;
    }

    public function autowire(string $name): self
    {
        if (!$this->reflection->isInstantiable($name)) {
            return $this;
        }
        if ($this->reflection->isVariadic($name)) {
            $this->variadic[] = true;
        }
        return $this->registerClass(
            $name,
            ...$this->reflection->getDependencies($name)
        );
    }

    public function registerClass(string $name, string ...$dependencies): self
    {
        $this->classes[$name] = $dependencies;
        return $this;
    }

    public function registerInterface(string $name, string $implementation): self
    {
        $this->alias[$name] = $implementation;
        return $this;
    }

    public function compile(): string
    {
        return "<?php

declare(strict_types=1);

return [{$this->compileAlias()}{$this->compileClasses()}
];";
    }

    private function compileAlias(): string
    {
        $compiledContainers = '';
        $class = Alias::class;
        foreach ($this->alias as $name => $implementation) {
            $compiledContainers .= "\n    '$name' => new $class('$implementation'),";
        }
        return $compiledContainers;
    }

    private function compileClasses(): string
    {
        $compiledContainers = '';
        foreach ($this->classes as $name => $dependencies) {
            $class = $this->getClass($name);
            $compiledContainers .= <<<TAG

    '$name' => new $class(
        '$name',{$this->getDependenciesString(...$dependencies)}
    ),
TAG;
        }
        return $compiledContainers;
    }

    private function getClass($name): string
    {
        if (in_array($name, $this->variadic)) {
            return FactoryVariadic::class;
        }
        return Factory::class;
    }

    private function getDependenciesString(string ...$dependencies): string
    {
        $result = '';
        foreach ($dependencies as $dependency) {
            $result .= PHP_EOL . "        '$dependency',";
        }
        return $result;
    }
}
