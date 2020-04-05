<?php

declare(strict_types=1);

namespace Cekta\DI;

interface ReflectionTransformer
{
    public function transform(string $name, array $params): array;
}
