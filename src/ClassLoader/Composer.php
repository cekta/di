<?php

declare(strict_types=1);

namespace Cekta\DI\ClassLoader;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

readonly class Composer
{
    /**
     * @param array<string> $excludes excludes class from filename
     */
    public function __construct(
        private string $filename,
        private array $excludes = [],
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
        /** @var array<string, string> $data */
        $result = [];
        foreach ($data as $class => $file) {
            if (
                in_array($class, $this->excludes)
                || !file_exists($file)
            ) {
                continue;
            }
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
