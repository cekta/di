<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest\Discovery;

use Cekta\DI\Module;
use ReflectionClass;

class ProjectModule implements Module
{
    /**
     * @var string[]
     */
    private array $containers = [];
    public function buildArguments(string $encoded_module): array
    {
        $containers = json_decode($encoded_module, true);
        if (!is_array($containers)) {
            throw new \InvalidArgumentException();
        }
        /** @var string[] $containers */
        return [
            'containers' => [...$containers, 'for_test'],
        ];
    }

    public function params(string $encoded_module): array
    {
        $containers = json_decode($encoded_module, true);
        if (!is_array($containers)) {
            throw new \InvalidArgumentException();
        }
        return [
            'for_test' => $containers,
        ];
    }

    public function discover(ReflectionClass $class): void
    {
        if ($class->implementsInterface(Entrypoint::class) && $class->isInstantiable()) {
            $this->containers[] = $class->getName();
        }
    }

    public function getEncodedModule(): string
    {
        $result = json_encode($this->containers);
        assert(is_string($result));
        return $result;
    }
}
