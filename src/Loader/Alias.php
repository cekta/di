<?php

declare(strict_types=1);

namespace Cekta\DI\Loader;

use Cekta\DI\Loader;
use Psr\Container\ContainerInterface;

class Alias implements Loader
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __invoke(ContainerInterface $container)
    {
        return $container->get($this->name);
    }
}
