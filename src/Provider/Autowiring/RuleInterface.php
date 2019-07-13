<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowiring;

interface RuleInterface
{
    public function acceptable(string $name): bool;

    public function accept(): array;
}
