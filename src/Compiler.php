<?php

declare(strict_types=1);

namespace Cekta\DI;

class Compiler
{
    /**
     * @var Reflection
     */
    private $reflection;
    private $classes = [];
    private $alias = [];

    public function __construct(Reflection $reflection)
    {
        $this->reflection = $reflection;
    }

    public function autowire(string $name): self
    {
        return $this->registerClass($name, $this->reflection->getDependencies($name));
    }

    public function registerClass(string $name, array $dependencies): self
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

{$this->getHeader()}
return [{$this->compileAlias()}{$this->compileClasses()}
];";
    }

    private function compileAlias(): string
    {
        $compiledContainers = '';
        foreach ($this->alias as $name => $implementation) {
            $compiledContainers .= "\n    '$name' => new Alias('$implementation'),";
        }
        return $compiledContainers;
    }

    private function compileClasses(): string
    {
        $compiledContainers = '';
        foreach ($this->classes as $name => $dependencies) {
            $arguments = implode('\', \'', $dependencies);
            $arguments = strlen($arguments) > 0 ? "'$name', '$arguments'" : "'$name'";
            $compiledContainers .= "\n    '$name' => new Factory($arguments),";
        }
        return $compiledContainers;
    }

    private function getHeader(): string
    {
        return $this->getAliasHeader() . $this->getFactoryHeader();
    }

    private function getAliasHeader(): string
    {
        if (count($this->alias) > 0) {
            return 'use \Cekta\DI\Loader\Alias;' . PHP_EOL;
        }
        return '';
    }

    private function getFactoryHeader(): string
    {
        if (count($this->classes) > 0) {
            return 'use \Cekta\DI\Loader\Factory;' . PHP_EOL;
        }
        return '';
    }
}
