<?php

declare(strict_types=1);

namespace Cekta\DI;

class Manifest implements ManifestInterface
{
    private FQCN $fqcn;

    /**
     * @param array<string, mixed|Lazy> $params
     * @param array{
     *     entries?: array<string>,
     *     alias?: array<string, string>,
     *     singletons?: array<string>,
     *     factories?: array<string>
     * } $definition
     */
    public function __construct(
        private readonly string $filename,
        string $fqcn,
        private readonly array $params = [],
        private readonly array $definition = [],
    ) {
        $this->fqcn = new FQCN($fqcn);
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function fqcn(): FQCN
    {
        return $this->fqcn;
    }

    /**
     * @return array<string, mixed|Lazy>
     */
    public function params(): array
    {
        return $this->params;
    }

    public function definition(): Definition
    {
        return new Definition(
            entries: $this->definition['entries'] ?? [],
            alias: $this->definition['alias'] ?? [],
            singletons: $this->definition['singletons'] ?? [],
            factories: $this->definition['factories'] ?? [],
        );
    }
}
