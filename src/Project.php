<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\IntersectConfiguration;
use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * @external
 */
class Project
{
    /**
     * @var array<string, string>
     */
    private array $cached_modules;
    /**
     * @var Closure(): iterable<ReflectionClass<object>>
     */
    private readonly Closure $class_loader;

    /**
     * @param array<Module> $modules
     * @param ?callable(): iterable<ReflectionClass<object>> $class_loader
     */
    public function __construct(
        private readonly array $modules,
        private readonly string $container_filename,
        private readonly string $container_fqcn,
        private readonly string $discover_filename,
        ?callable $class_loader = null,
    ) {
        if ($class_loader === null) {
            $class_loader = function () {
                return [];
            };
        }
        /** @noinspection PhpClosureCanBeConvertedToFirstClassCallableInspection */
        $this->class_loader = Closure::fromCallable($class_loader);

        if (empty($this->modules)) {
            throw new InvalidArgumentException('`modules` must be not empty');
        }
    }

    public function container(): ContainerInterface
    {
        $this->makeDiscover();
        return $this->makeContainer();
    }

    public function clean(): void
    {
        if (file_exists($this->discover_filename)) {
            unlink($this->discover_filename);
        }
        if (file_exists($this->container_filename)) {
            unlink($this->container_filename);
        }
    }

    private function makeDiscover(): void
    {
        if (
            !file_exists($this->discover_filename)
            || (
                !is_array($data = require $this->discover_filename)
                || !array_key_exists('modules', $data)
                || !is_array($data['modules'])
                || !empty(array_diff_key($data['modules'], $this->modules))
            )
        ) {
            file_put_contents(
                $this->discover_filename,
                '<?php return ' . var_export(['modules' => $this->buildDiscover()], true) . ';'
            );
        }

        $data = require $this->discover_filename;
        $this->cached_modules = $data['modules'];
    }

    private function makeContainer(): ContainerInterface
    {
        if (!file_exists($this->container_filename)) {
            file_put_contents($this->container_filename, $this->buildContainer());
        }

        return $this->createContainer();
    }


    private function buildContainer(): string
    {
        $params = [
            'entries' => [],
            'alias' => [],
            'params' => [],
            'singletons' => [],
            'factories' => [],
        ];
        foreach ($this->modules as $key => $module) {
            $encoded_module = $this->cached_modules[$key];
            $r = $module->onBuild($encoded_module);
            $r['params'] = $module->onCreate($encoded_module);
            foreach (['params', 'alias'] as $key) {
                $record = $r[$key] ?? [];
                $params[$key] = [...$params[$key], ...$record];
            }

            foreach (['entries', 'singletons', 'factories'] as $key) {
                $params[$key] = [...$params[$key], ...($r[$key] ?? [])];
            }
        }
        $builder = new ContainerBuilder(
            entries: $params['entries'],
            params: $params['params'],
            alias: $params['alias'], // @phpstan-ignore argument.type
            fqcn: $this->container_fqcn,
            singletons: $params['singletons'],
            factories: $params['factories'],
        );
        return $builder->build();
    }

    private function createContainer(): ContainerInterface
    {
        $params = [];
        foreach ($this->modules as $key => $module) {
            $record = $module->onCreate($this->cached_modules[$key] ?? '');
            $intersect = array_intersect_key($params, $record);
            if (!empty($intersect)) {
                throw new IntersectConfiguration($intersect, 'params');
            }
            $params = [...$params, ...$record];
        }
        /** @var ContainerInterface $container */
        $container = new ($this->container_fqcn)($params);
        return $container;
    }

    /**
     * @return array<string>
     */
    private function buildDiscover(): array
    {
        $iterator = ($this->class_loader)();
        foreach ($iterator as $class) {
            foreach ($this->modules as $module) {
                $module->discover($class);
            }
        }
        $result = [];
        foreach ($this->modules as $module) {
            $result[] = $module->getEncodedModule();
        }
        return $result;
    }
}
