<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire;

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

    public function acceptable(string $name): bool
    {
        return $this->prefix === substr($name, 0, strlen($this->prefix));
    }

    public function accept(): array
    {
        return $this->replace;
    }
}
