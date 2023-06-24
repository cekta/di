<?php

declare(strict_types=1);

namespace Cekta\DI\Strategy;

use Psr\Container\ContainerInterface;

class KeyValue implements ContainerInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $values;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function get($id)
    {
        return $this->values[$id];
    }

    public function has($id): bool
    {
        return array_key_exists($id, $this->values);
    }
}
