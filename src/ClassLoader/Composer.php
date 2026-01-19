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
     * @return iterable<ReflectionClass<object>>
     */
    public function __invoke(): iterable
    {
        $data = require $this->filename;
        if (!is_array($data)) {
            throw new InvalidArgumentException("`$this->filename` must return array");
        }
        foreach (array_keys($data) as $class) {
            try {
                // @phpstan-ignore argument.type
                yield new ReflectionClass($class);
            } catch (ReflectionException) {
                continue;
            }
        }
    }
}
