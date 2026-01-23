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
     * @throws ReflectionException
     */
    public function __invoke(): array
    {
        $data = require $this->filename;
        if (!is_array($data)) {
            throw new InvalidArgumentException("`$this->filename` must return array");
        }
        /** @var array<string, string> $data */
        $result = [];
        foreach ($data as $class => $file) {
            if (!file_exists($file)) {
                continue;
            }
            // @phpstan-ignore argument.type
            $result[] = new ReflectionClass($class);
        }
        return $result;
    }
}
