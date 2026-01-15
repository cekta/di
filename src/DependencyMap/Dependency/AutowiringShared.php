<?php

declare(strict_types=1);

namespace Cekta\DI\DependencyMap\Dependency;

final readonly class AutowiringShared extends Autowiring
{
    protected function renderAsArgument(array $dm): string
    {
        return "\$this->get('$this->name')";
    }
}
