<?php

declare(strict_types=1);

namespace Cekta\DI\Rule;

use Cekta\DI\DependencyDTO;
use Cekta\DI\Rule;

/**
 * @external
 */
class Regex implements Rule
{
    /**
     * @param string $pattern
     * @param array<string, string> $dependency_name_transformers
     */
    public function __construct(
        private string $pattern,
        private array $dependency_name_transformers
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(string $container_name, string $dependency_name): string
    {
        if (
            preg_match($this->pattern, $container_name)
            && array_key_exists(
                $dependency_name,
                $this->dependency_name_transformers
            )
        ) {
            return $this->dependency_name_transformers[$dependency_name];
        }
        return $dependency_name;
    }
}
