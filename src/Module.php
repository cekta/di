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
     * method call every time to get params for Container object
     * @return array<string, mixed>
     */
    public function onCreate(string $encoded_module): array;

    /**
     * method call once, when build (generation) Container class
     * @return array{
     *     entries?: string[],
     *     alias?: array<string, string>,
     *     singletons?: string[],
     *     factories?: string[],
     * }
     */
    public function onBuild(string $encoded_module): array;

    /**
     * method call once, when make discovery project
     * @param array<ReflectionClass<object>> $classes
     * @return string
     */
    public function onDiscover(array $classes): string;
}
