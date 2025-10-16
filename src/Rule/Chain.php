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
    public function apply(string $container, array $dependencies): array
    {
        foreach ($this->rules as $rule) {
            $dependencies = $rule->apply($container, $dependencies);
        }
        return $dependencies;
    }
}
