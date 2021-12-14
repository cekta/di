<?php

namespace Cekta\DI;

use InvalidArgumentException;
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
     * @var ?callable
     */
    private $interfaceProvider;
    /**
     * @var ?callable
     */
    private $definitionProvider;
    private ?ContainerCompiledFactory $compiledFactory = null;
    private ?ContainerDevelopFactory $developFactory = null;

    public function build(): ContainerInterface
    {
        if ($this->compiledFactory === null) {
            $this->compiledFactory = new ContainerCompiledFactory();
        }
        if ($this->compiledFactory->isClassExist()) {
            return $this->compiledFactory->make($this->params);
        }
        if ($this->developFactory === null) {
            $this->developFactory = new ContainerDevelopFactory();
        }
        return $this->developFactory->make(
            $this->params,
            $this->getInterfaces(),
            $this->getDefinitions()
        );
    }

    /**
     * @param array<string, mixed> $params
     * @return $this
     */
    public function setParams(array $params): ContainerBuilder
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @param callable|null $interfaceProvider
     * @return ContainerBuilder
     */
    public function setInterfaceProvider(?callable $interfaceProvider): ContainerBuilder
    {
        $this->interfaceProvider = $interfaceProvider;
        return $this;
    }

    /**
     * @param callable|null $definitionProvider
     * @return ContainerBuilder
     */
    public function setDefinitionProvider(?callable $definitionProvider): ContainerBuilder
    {
        $this->definitionProvider = $definitionProvider;
        return $this;
    }

    public function setDevelopFactory(ContainerDevelopFactory $developFactory): ContainerBuilder
    {
        $this->developFactory = $developFactory;
        return $this;
    }

    public function setCompiledFactory(ContainerCompiledFactory $compiledFactory): ContainerBuilder
    {
        $this->compiledFactory = $compiledFactory;
        return $this;
    }

    /**
     * @return array<string, string>
     */
    private function getInterfaces(): array
    {
        if ($this->interfaceProvider === null) {
            return [];
        }
        $result = call_user_func($this->interfaceProvider);
        if (!is_array($result)) {
            throw new InvalidArgumentException('interface provider must return array');
        }
        foreach ($result as $key => $value) {
            if (!is_string($key)) {
                throw new InvalidArgumentException('interface provider must return array with all keys is string');
            }
            if (!is_string($value)) {
                throw new InvalidArgumentException('interface provider must return array with all values is string');
            }
        }
        return $result;
    }

    /**
     * @return array<string, callable>
     */
    private function getDefinitions(): array
    {
        if ($this->definitionProvider === null) {
            return [];
        }
        $result = call_user_func($this->definitionProvider);
        if (!is_array($result)) {
            throw new InvalidArgumentException('definition provider must return array');
        }
        foreach ($result as $key => $value) {
            if (!is_string($key)) {
                throw new InvalidArgumentException('definition provider must return array with all keys is string');
            }
            if (!is_callable($value)) {
                throw new InvalidArgumentException('definition provider must return array with all values is callable');
            }
        }
        return $result;
    }
}
