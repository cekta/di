<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\KeyValue\Loader;

use Cekta\DI\Provider\KeyValue\LoaderInterface;
use Closure;
use Psr\Container\ContainerInterface;

class Service implements LoaderInterface
{
    /**
     * @var Closure
     */
    private $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public function __invoke(ContainerInterface $container)
    {
        return ($this->closure)($container);
    }
}
