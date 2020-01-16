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
        $result = '';
        foreach ($this->alias as $name => $implementation) {
            $result .= "\n    '$name' => new Alias('$implementation'),";
        }
        foreach ($this->classes as $name => $dependencies) {
            $arguments = implode('\', \'', $dependencies);
            $arguments = strlen($arguments) > 0 ? "'$name', '$arguments'" : "'$name'";
            $result .= "\n    '$name' => new Factory($arguments),";
        }
        return "<?php

declare(strict_types=1);

use \Cekta\DI\Loader\Alias;
use \Cekta\DI\Loader\Factor;

return [$result
];";
    }
}
