<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire;

interface RuleInterface
{
    public function acceptable(string $name): bool;

    public function accept(): array;
}
