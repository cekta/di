<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\KeyValue\Loader;

use Cekta\DI\Provider\KeyValue\LoaderInterface;
use Psr\Container\ContainerInterface;

class Alias implements LoaderInterface
{
    /** @var string */
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
