<?php

declare(strict_types=1);

namespace Cekta\DI\Rule;

use Cekta\DI\Rule;

/**
 * @external
 */
class Chain implements Rule
{
    /**
     * @var Rule[]
     */
    private array $rules;

    public function __construct(Rule ...$rules)
    {
        $this->rules = $rules;
    }

    /**
     * @inheritdoc
     */
    public function apply(string $container_name, string $dependency_name): string
    {
        foreach ($this->rules as $rule) {
            $dependency_name = $rule->apply($container_name, $dependency_name);
        }
        return $dependency_name;
    }
}
