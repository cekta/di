<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Loader\Factory;
use Cekta\DI\Loader\FactoryVariadic;

class Compiler
{
    /**
     * @var Reflection
     */
    private $reflection;
    /**
     * @var array<string, array<string>>
     */
    private $classes = [];
    /**
     * @var array<string>
     */
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
            $this->variadic[] = $name;
        }
        $this->classes[$name] = $this->reflection->getDependencies($name);
        return $this;
    }

    public function compile(): string
    {
        return "<?php

declare(strict_types=1);

return [{$this->compileClasses()}
];";
    }

    private function compileClasses(): string
    {
        $compiledContainers = '';
        foreach ($this->classes as $name => $dependencies) {
            $class = $this->getClass($name);
            $dependenciesString = str_replace("\n", "\n        ", var_export($dependencies, true));
            $compiledContainers .= <<<TAG

    '$name' => new $class(
        '$name',
        ...$dependenciesString
    ),
TAG;
        }
        return $compiledContainers;
    }

    private function getClass(string $name): string
    {
        if (in_array($name, $this->variadic)) {
            return FactoryVariadic::class;
        }
        return Factory::class;
    }
}
