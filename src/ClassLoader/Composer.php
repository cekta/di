<?php

declare(strict_types=1);

namespace Cekta\DI\ClassLoader;

use InvalidArgumentException;
use ReflectionClass;
use Throwable;

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
     * @return iterable <ReflectionClass<object>>
     */
    public function __invoke(): iterable
    {
        $data = require $this->filename;
        if (!is_array($data)) {
            throw new InvalidArgumentException("`$this->filename` must return array");
        }
        /** @var array<string, string> $data */
        foreach ($data as $class => $file) {
            if (
                in_array($class, $this->excludes)
                || !file_exists($file)
            ) {
                continue;
            }
            try {
                // @phpstan-ignore argument.type
                yield new ReflectionClass($class);
            } catch (Throwable) {
                continue;
            }
        }
    }
}
