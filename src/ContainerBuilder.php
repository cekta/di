<?php

namespace Cekta\DI;

use Psr\Container\ContainerInterface;

/**
 * @external
 */
class ContainerBuilder
{
    /**
     * @var array<string, mixed>
     */
    private array $params = [];
    /**
     * @var array<string, string>
     */
    private array $alias = [];
    /**
     * @var array<string, callable>
     */
    private array $definitions = [];

    public function build(): ContainerInterface
    {
        return new Container($this->params, $this->alias, $this->definitions);
    }

    /**
     * @param array<string, mixed> $params
     * @return $this
     */
    public function params(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @param array<string, string> $alias
     * @return $this
     */
    public function alias(array $alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @param array<string, callable> $definitions
     * @return $this
     */
    public function definitions(array $definitions): self
    {
        $this->definitions = $definitions;
        return $this;
    }
}
