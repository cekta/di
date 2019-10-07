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
    public const STRING_TO_ALIAS = 1;

    public const CLOSURE_TO_SERVICE = 2;

    /** @var array */
    private $values;

    protected function closureToService(array $values): array
    {
        $result = [];
        foreach ($values as $key => $value) {
            if ($value instanceof Closure) {
                $value = new Service($value);
            }
            $result[$key] = $value;
        }
        return $result;
    }

    protected function stringToAlias(array $values): array
    {
        $result = [];
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $value = new Alias($value);
            }
            $result[$key] = $value;
        }
        return $result;
    }

    public function __construct(array $values, array $transformations = [])
    {
        $this->values = $values;
        foreach ($transformations as $transformation) {
            switch ($transformation) {
                case static::CLOSURE_TO_SERVICE:
                    $this->values = $this->closureToService($this->values);
                // no break
                case static::STRING_TO_ALIAS:
                    $this->values = $this->stringToAlias($this->values);
                    break;
            }
        }
    }

    public function provide(string $id)
    {
        if (!$this->canProvide($id)) {
            throw new NotFound($id);
        }
        return $this->values[$id];
    }

    public function canProvide(string $id): bool
    {
        return array_key_exists($id, $this->values);
    }
}
