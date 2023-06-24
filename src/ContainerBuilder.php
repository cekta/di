<?php

namespace Cekta\DI;

use Psr\Container\ContainerInterface;
use UnexpectedValueException;

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
    private string $fqcn = 'App\Container';

    public function build(): ContainerInterface
    {
        if (class_exists($this->fqcn)) {
            $result = new $this->fqcn($this->params, $this->alias, $this->definitions);
            if ($result instanceof ContainerInterface) {
                return $result;
            }
            throw new UnexpectedValueException("`$this->fqcn` must implement Psr\Container\ContainerInterface");
        }
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

    public function fqcn(string $fqcn): self
    {
        $this->fqcn = $fqcn;
        return $this;
    }

    /**
     * @param array<string> $containers
     * @return string|false
     */
    public function compile(array $containers): string|false
    {
        $compiler = new Compiler($this->params, $this->alias, $this->definitions, $this->fqcn);
        return $compiler($containers);
    }
}
