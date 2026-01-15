<?php

declare(strict_types=1);

namespace Cekta\DI;

use ReflectionClass;

/**
 * @external
 */
interface Module
{
    /**
     * @return array{
     *     containers?: string[],
     *     alias?: array<string, string>,
     *     singletons?: string[],
     *     factories?: string[],
     * }
     */
    public function buildArguments(string $encoded_module): array;

    /**
     * @return array<string, mixed>
     */
    public function params(string $encoded_module): array;

    /**
     * @param ReflectionClass<object> $class
     * @return void
     */
    public function discover(ReflectionClass $class): void;

    /**
     * called after discovery all classes, to store data for module
     * @return string
     */
    public function getEncodedModule(): string;
}
