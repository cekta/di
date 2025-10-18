<?php

declare(strict_types=1);

namespace Cekta\DI\Rule;

use Cekta\DI\DependencyDTO;
use Cekta\DI\Rule;

/**
 * @external
 */
class StartWith implements Rule
{
    /**
     * @param string $needle
     * @param array<string, string> $dependency_name_transformers
     */
    public function __construct(
        private string $needle,
        private array $dependency_name_transformers
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(string $container_name, string $dependency_name): string
    {
        if (
            str_starts_with($container_name, $this->needle)
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
