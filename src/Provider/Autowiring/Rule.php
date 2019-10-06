<?php

declare(strict_types=1);

namespace Cekta\DI\Provider\Autowiring;

class Rule implements RuleInterface
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

    public function acceptable(string $id): bool
    {
        return strpos($id, $this->prefix) === 0;
    }

    public function accept(): array
    {
        return $this->replace;
    }
}
