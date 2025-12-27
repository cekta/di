<?php

declare(strict_types=1);

namespace Cekta\DI\DependencyMap\Dependency;

use Cekta\DI\DependencyMap\Dependency;

final readonly class Alias extends Dependency
{
    public function __construct(
        string $name,
        public string $target,
    ) {
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    public function render(array $dm): string
    {
        return "\$this->get('$this->target')";
    }
}
