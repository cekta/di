<?php

declare(strict_types=1);

namespace Cekta\DI;

interface ReflectionTransformerInterface
{
    public function transform(string $name, array $params): array;
}
