<?php

declare(strict_types=1);

namespace Cekta\DI\Rule;

use Cekta\DI\Rule;

/**
 * @external
 */
class NullRule implements Rule
{
    /**
     * @inheritdoc
     */
    public function apply(string $container, array $dependencies): array
    {
        return $dependencies;
    }
}
