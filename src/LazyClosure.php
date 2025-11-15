<?php

declare(strict_types=1);

namespace Cekta\DI;

use Psr\Container\ContainerInterface;

class LazyClosure implements Lazy
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function load(ContainerInterface $container): mixed
    {
        return call_user_func($this->callback, $container);
    }
}
