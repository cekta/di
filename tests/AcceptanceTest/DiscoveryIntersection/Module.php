<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest\DiscoveryIntersection;

use ReflectionClass;

readonly class Module implements \Cekta\DI\Module
{
    /**
     * @param array{
     *      containers?: string[],
     *      alias?: array<string, string>,
     *      singletons?: string[],
     *      factories?: string[],
     *  } $build_arguments
     * @param array<string, mixed> $params
     */
    public function __construct(
        private array $build_arguments,
        private array $params
    ) {
    }

    public function buildArguments(string $encoded_module): array
    {
        return $this->build_arguments;
    }

    public function params(string $encoded_module): array
    {
        return $this->params;
    }

    public function discover(ReflectionClass $class): void
    {
    }

    public function getEncodedModule(): string
    {
        return '';
    }
}
