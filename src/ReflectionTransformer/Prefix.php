<?php

declare(strict_types=1);

namespace Cekta\DI\ReflectionTransformer;

use Cekta\DI\ReflectionTransformerInterface;

class Prefix implements ReflectionTransformerInterface
{
    /**
     * @var string
     */
    private $prefix;
    /**
     * @var array
     */
    private $replace;

    public function __construct(string $prefix, array $replace)
    {
        $this->prefix = $prefix;
        $this->replace = $replace;
    }

    public function transform(string $name, array $params): array
    {
        if (strpos($name, $this->prefix) === 0) {
            $params = $this->transformParam($params);
        }
        return $params;
    }

    private function transformParam(array $params): array
    {
        foreach ($params as $key => $value) {
            if (array_key_exists($value, $this->replace)) {
                $params[$key] = $this->replace[$value];
            }
        }
        return $params;
    }
}
