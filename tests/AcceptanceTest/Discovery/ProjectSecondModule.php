<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest\Discovery;

use Cekta\DI\Module;
use ReflectionClass;

class ProjectSecondModule implements Module
{
    public const SECOND_TEST = 'second value';
    public function onBuild(string $encoded_module): array
    {
        return [];
    }

    public function onCreate(string $encoded_module): array
    {
        $params = json_decode($encoded_module, true);
        if (!is_array($params)) {
            throw new \InvalidArgumentException();
        }
        return $params;
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
        $params = [
            'second_test' => self::SECOND_TEST,
        ];
        $result = json_encode($params);
        assert(is_string($result));
        return $result;
    }
}
