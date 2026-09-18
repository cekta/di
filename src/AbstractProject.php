<?php

declare(strict_types=1);

namespace Cekta\DI;

/**
 * @external
 */
abstract class AbstractProject
{
    /**
     * @param array<string, mixed|Lazy> $params
     */
    public function __construct(
        public readonly string $filename,
        public readonly string $fqcn,
        public readonly array $params
    ) {
    }

    /**
     * @return array{
     *     entries?: array<string>,
     *     alias?: array<string, string>,
     *     singletons?: array<string>,
     *     factories?: array<string>
     * }
     */
    abstract public function definition(): array;
}
