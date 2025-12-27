<?php

declare(strict_types=1);

namespace Cekta\DI\DependencyMap\Dependency;

use Cekta\DI\DependencyMap\Dependency;

final readonly class Param extends Dependency
{
    /**
     * @inheritdoc
     */
    public function render(array $dm): string
    {
        return "\$this->get('{$this->name}')";
    }
}
