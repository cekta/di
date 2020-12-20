<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Strategy\Definition\Factory;

class DefinitionGenerator
{
    /**
     * @var Reflection
     */
    private $reflection;

    public function __construct(Reflection $reflection)
    {
        $this->reflection = $reflection;
    }

    public function __invoke(string ...$classes): string
    {
        $factory = Factory::class;
        $body = '';
        foreach ($classes as $class) {
            if ($this->reflection->isInstantiable($class)) {
                $dependencies = var_export($this->reflection->getDependencies($class), true);
                $body .= PHP_EOL . "'{$class}' => new $factory('$class', ...$dependencies)," . PHP_EOL;
            }
        }
        return "<?php

declare(strict_types=1);

return [{$body}];";
    }
}
