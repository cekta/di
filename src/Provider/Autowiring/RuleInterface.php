<?php

declare(strict_types=1);

namespace Cekta\DI\Provider\Autowiring;

interface RuleInterface
{
    public function acceptable(string $id): bool;

    public function accept(): array;
}
