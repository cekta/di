<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Exception\NotFound;
use Cekta\DI\LoaderInterface;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;

class KeyValue implements ProviderInterface
{
    /**
     * @var array
     */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function provide(string $name, ContainerInterface $container)
    {
        if (!$this->hasProvide($name)) {
            throw new NotFound($name);
        }
        $result = $this->values[$name];
        if ($result instanceof LoaderInterface) {
            $result = $result($container);
        }
        return $result;
    }

    public function hasProvide(string $name): bool
    {
        return array_key_exists($name, $this->values);
    }
}
