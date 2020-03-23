<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

class Environment extends KeyValue
{
    private const TRANSFORM = [
        'true' => true,
        '(true)' => true,
        'false' => false,
        '(false)' => false,
        'null' => null,
        '(null)' => null,
        'empty' => '',
        '(empty)' => '',
    ];

    public function __construct(array $params)
    {
        foreach ($params as $key => $value) {
            $params[$key] = self::transform($value);
        }
        parent::__construct($params);
    }

    private static function transform($value)
    {
        if (is_string($value)) {
            if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                $value = $matches[2];
            }
            $value = self::transformString($value);
        }
        return $value;
    }

    private static function transformString(string $value)
    {
        $lower = strtolower($value);
        return array_key_exists($lower, self::TRANSFORM)
            ? self::TRANSFORM[$lower]
            : $value;
    }
}
