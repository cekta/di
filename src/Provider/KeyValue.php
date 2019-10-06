<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Loader\Alias;
use Cekta\DI\Loader\Service;
use Cekta\DI\Provider\Exception\NotFound;
use Cekta\DI\ProviderInterface;
use Closure;

class KeyValue implements ProviderInterface
{
    /**
     * @var array
     */
    private $values;

    public static function closureToService(array $values): self
    {
        $result = [];
        foreach ($values as $key => $value) {
            if ($value instanceof Closure) {
                $value = new Service($value);
            }
            $result[$key] = $value;
        }
        return new self($result);
    }

    public static function stringToAlias(array $values): self
    {
        $result = [];
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $value = new Alias($value);
            }
            $result[$key] = $value;
        }
        return new self($result);
    }

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function provide(string $id)
    {
        if (!$this->canProvide($id)) {
            throw new NotFound($id);
        }
        $result = $this->values[$id];
        return $result;
    }

    public function canProvide(string $id): bool
    {
        return array_key_exists($id, $this->values);
    }
}
