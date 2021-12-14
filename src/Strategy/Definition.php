<?php

declare(strict_types=1);

namespace Cekta\DI\Strategy;

use Psr\Container\ContainerInterface;

class Definition extends KeyValue
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(array $values, ContainerInterface $container)
    {
        parent::__construct($values);
        $this->container = $container;
    }

    public function get($id)
    {
        $result = parent::get($id);
        if (is_callable($result)) {
            $result = $result($this->container);
        }
        return $result;
    }
}
