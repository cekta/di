<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire\Reader;

interface RuleInterface
{
    public function acceptable(string $name): bool;

    public function accept(): array;
}
