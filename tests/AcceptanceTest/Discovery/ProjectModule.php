<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest\Discovery;

use Cekta\DI\Module;
use ReflectionClass;

class ProjectModule implements Module
{
    /**
     * @var array<string>
     */
    private array $entries = [];

    public function onBuild(string $encoded_module): array
    {
        $entries = json_decode($encoded_module, true);
        if (!is_array($entries)) {
            throw new \InvalidArgumentException();
        }
        /** @var string[] $entries */
        return [
            'entries' => [...$entries, 'for_test'],
        ];
    }

    public function onCreate(string $encoded_module): array
    {
        $entries = json_decode($encoded_module, true);
        if (!is_array($entries)) {
            throw new \InvalidArgumentException();
        }
        return [
            'for_test' => $entries,
        ];
    }

    /**
     * @inheritDoc
     */
    public function discover(ReflectionClass $class): void
    {
        if ($class->implementsInterface(Entrypoint::class) && $class->isInstantiable()) {
            $this->entries[] = $class->getName();
        }
    }

    /**
     * @inheritDoc
     */
    public function getEncodedModule(): string
    {
        $result = json_encode($this->entries);
        assert(is_string($result));
        return $result;
    }
}
