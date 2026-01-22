<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest\DiscoveryIntersection;

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

    public function onBuild(string $encoded_module): array
    {
        return $this->build_arguments;
    }

    public function onCreate(string $encoded_module): array
    {
        return $this->params;
    }

    /**
     * @inheritdoc
     */
    public function onDiscover(array $classes): string
    {
        return '';
    }
}
