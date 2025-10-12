<?php

declare(strict_types=1);

namespace Cekta\DI;

class LoaderDTO
{
    /**
     * @param string[] $containers
     * @param array<string, string> $alias
     * @param string[] $factories
     * @param string[] $singletons
     */
    public function __construct(
        private array $containers = [],
        private array $alias = [],
        private array $factories = [],
        private array $singletons = [],
    ) {
    }

    /**
     * @return string[]
     */
    public function getContainers(): array
    {
        return $this->containers;
    }

    /**
     * @return string[]
     */
    public function getAlias(): array
    {
        return $this->alias;
    }

    /**
     * @return string[]
     */
    public function getFactories(): array
    {
        return $this->factories;
    }

    /**
     * @return string[]
     */
    public function getSingletons(): array
    {
        return $this->singletons;
    }
}
