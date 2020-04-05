<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider;
use Cekta\DI\Provider\Exception\NotFound;

class KeyValue implements Provider
{
    /**
     * @var array
     */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
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
