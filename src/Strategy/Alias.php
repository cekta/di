<?php

declare(strict_types=1);

namespace Cekta\DI\Strategy;

use Psr\Container\ContainerInterface;

class Alias extends KeyValue
{
    private ContainerInterface $container;

    public function __construct(array $values, ContainerInterface $container)
    {
        parent::__construct($values);
        $this->container = $container;
    }

    public function get(string $id)
    {
        $result = parent::get($id);
        if (is_string($result)) {
            $result = $this->container->get($result);
        }
        return $result;
    }
}
