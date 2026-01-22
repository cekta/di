<?php

declare(strict_types=1);

namespace Cekta\DI\ClassLoader;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

readonly class Composer
{
    public function __construct(
        public string $filename
    ) {
    }

    /**
     * @return array<ReflectionClass<object>>
     */
    public function __invoke(): array
    {
        $data = require $this->filename;
        if (!is_array($data)) {
            throw new InvalidArgumentException("`$this->filename` must return array");
        }
        $result = [];
        foreach (array_keys($data) as $class) {
            try {
                // @phpstan-ignore argument.type
                $result[] = new ReflectionClass($class);
            } catch (ReflectionException) {
                continue;
            }
        }
        return $result;
    }
}
