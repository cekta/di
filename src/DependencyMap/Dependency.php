<?php

declare(strict_types=1);

namespace Cekta\DI\DependencyMap;

abstract readonly class Dependency
{
    public function __construct(
        public string $name,
    ) {
    }

    /**
     * @param array<string, Dependency> $dm
     * @return string
     */
    abstract public function render(array $dm): string;

    /**
     * @param array<string, Dependency> $dm
     * @return string
     */
    protected function renderAsArgument(array $dm): string
    {
        return "\$this->get('$this->name')";
    }
}
