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
    public function apply(string $container, array $dependencies): array
    {
        if (str_starts_with($container, $this->needle)) {
            return array_map(function (DependencyDTO $dto) {
                if (array_key_exists($dto->getName(), $this->dependency_name_transformers)) {
                    return new DependencyDTO($this->dependency_name_transformers[$dto->getName()], $dto->isVariadic());
                }
                return $dto;
            }, $dependencies);
        }
        return $dependencies;
    }
}
