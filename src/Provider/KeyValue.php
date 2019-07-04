<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\KeyValue\Loader\Service;
use Cekta\DI\Provider\Exception\NotFound;
use Cekta\DI\Provider\KeyValue\LoaderInterface;
use Cekta\DI\ProviderInterface;
use Closure;
use Psr\Container\ContainerInterface;

class KeyValue implements ProviderInterface
{
    /**
     * @var array
     */
    private $values;

    public static function transform(array $values): self
    {
        $result = [];
        foreach ($values as $key => $value) {
            if ($value instanceof Closure) {
                $value = new Service($value);
            }
            $result[$key] = $value;
        }
        return new static($result);
    }

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function provide(string $id, ContainerInterface $container)
    {
        if (!$this->canProvide($id)) {
            throw new NotFound($id);
        }
        $result = $this->values[$id];
        if ($result instanceof LoaderInterface) {
            $result = $result($container);
        }
        return $result;
    }

    public function canProvide(string $id): bool
    {
        return array_key_exists($id, $this->values);
    }
}
