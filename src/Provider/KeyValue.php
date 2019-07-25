<?php
declare(strict_types = 1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Exception\NotFound;
use Cekta\DI\Provider\KeyValue\LoaderInterface;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;

class KeyValue implements ProviderInterface
{
    /** @var array */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function provide(string $id, ContainerInterface $container)
    {
        if ($this->canBeProvided($id)) {
            $result = $this->values[$id];
            if ($result instanceof LoaderInterface) {
                $result = $result($container);
            }

            return $result;
        }

        throw new NotFound($id);
    }

    public function canBeProvided(string $id): bool
    {
        return array_key_exists($id, $this->values);
    }
}
