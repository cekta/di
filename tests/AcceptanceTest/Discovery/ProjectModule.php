<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest\Discovery;

use Cekta\DI\Module;
use ReflectionClass;

class ProjectModule implements Module
{
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
     * @inheritdoc
     */
    public function onDiscover(array $classes): string
    {
        $entries = [];
        foreach ($classes as $class) {
            if ($class->implementsInterface(Entrypoint::class) && $class->isInstantiable()) {
                $entries[] = $class->getName();
            }
        }
        $result = json_encode($entries);
        assert(is_string($result));
        return $result;
    }
}
