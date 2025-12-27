<?php

declare(strict_types=1);

namespace Cekta\DI\DependencyMap\Dependency;

use Cekta\DI\DependencyMap\Dependency;
use Cekta\DI\DependencyMap\Parameter;

readonly class Autowiring extends Dependency
{
    /**
     * @var Parameter[]
     */
    public array $parameters;

    public function __construct(
        string $name,
        Parameter ...$parameters
    ) {
        parent::__construct($name);
        $this->parameters = $parameters;
    }

    /**
     * @inheritdoc
     */
    public function render(array $dm): string
    {
        $args = '...[';
        foreach ($this->parameters as $parameter) {
            if (!$parameter->is_variadic && array_key_exists($parameter->name, $dm)) {
                $args .= "'$parameter->argument_name' => {$dm[$parameter->name]->renderAsArgument($dm)}, ";
            }
        }
        $args .= ']';

        $variadic = '';
        if (isset($parameter) && $parameter->is_variadic && array_key_exists($parameter->name, $dm)) {
            $variadic = "...{$dm[$parameter->name]->renderAsArgument($dm)}";
        }

        return "new \\$this->name($args, $variadic)";
    }

    /**
     * @inheritdoc
     */
    protected function renderAsArgument(array $dm): string
    {
        return $this->render($dm);
    }
}
