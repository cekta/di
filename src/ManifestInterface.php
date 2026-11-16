<?php

declare(strict_types=1);

namespace Cekta\DI;

interface ManifestInterface
{
    public function filename(): string;
    public function fqcn(): FQCN;
    /**
     * @return array<string, Lazy|mixed>
     */
    public function params(): array;
    public function definition(): Definition;
}
