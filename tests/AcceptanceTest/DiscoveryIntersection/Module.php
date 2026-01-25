<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest\DiscoveryIntersection;

use ReflectionClass;

readonly class Module implements \Cekta\DI\Module
{
    /**
     * @param array{
     *      entries?: string[],
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

    /**
     * @inheritDoc
     */
    public function onBuild(string $encoded_module): array
    {
        return $this->build_arguments;
    }

    /**
     * @inheritDoc
     */
    public function onCreate(string $encoded_module): array
    {
        return $this->params;
    }

    /**
     * @inheritDoc
     */
    public function discover(ReflectionClass $class): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getEncodedModule(): string
    {
        return '';
    }
}
